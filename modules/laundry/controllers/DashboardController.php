<?php

namespace app\modules\laundry\controllers;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * ภาพรวมงานซักฟอก — KPI นับยอด + กราฟ (ตามปีงบประมาณ พ.ศ.)
 */
class DashboardController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['laundry.view']],
                ],
            ],
        ];
    }

    /** ปีงบปัจจุบันเป็น พ.ศ. (งบเริ่ม 1 ต.ค.) */
    public static function currentFiscalYear(): int
    {
        $y = (int) date('Y');
        $m = (int) date('n');
        return ($m >= 10 ? $y + 1 : $y) + 543;
    }

    /** ช่วงวันที่ (ค.ศ.) ของปีงบ พ.ศ. ที่กำหนด */
    private function fiscalRange(int $fy): array
    {
        $gy = $fy - 543;
        return [sprintf('%04d-10-01', $gy - 1), sprintf('%04d-09-30', $gy)];
    }

    public function actionIndex()
    {
        $fiscalYear = (int) Yii::$app->request->get('fy', self::currentFiscalYear());
        if ($fiscalYear < 2500 || $fiscalYear > 2700) {
            $fiscalYear = self::currentFiscalYear();
        }
        [$start, $end] = $this->fiscalRange($fiscalYear);
        $startDt = $start . ' 00:00:00';
        $endDt = $end . ' 23:59:59';

        // ---- KPI ----
        // รับผ้า: น้ำหนักรวม (กก.) + จำนวนรายการหน่วยงานที่เก็บ ในปีงบ
        $weightRow = (new Query())
            ->select([
                'kg' => new Expression('COALESCE(SUM(w.net_kg),0)'),
                'stops' => new Expression('COUNT(DISTINCT w.stop_id)'),
            ])
            ->from(['w' => 'laundry_collection_weight'])
            ->innerJoin(['s' => 'laundry_collection_stop'], 's.id = w.stop_id')
            ->innerJoin(['r' => 'laundry_collection_round'], 'r.id = s.round_id')
            ->where(['between', 'r.collection_date', $start, $end])
            ->one();
        $recvKg = (float) ($weightRow['kg'] ?? 0);
        $recvStops = (int) ($weightRow['stops'] ?? 0);

        $rounds = (int) (new Query())->from('laundry_collection_round')
            ->where(['between', 'collection_date', $start, $end])->count();

        $batchesDone = (int) (new Query())->from('laundry_processing_batch')
            ->where(['status' => 'COMPLETED'])->andWhere(['between', 'ended_at', $startDt, $endDt])->count();

        // ผ้าเข้าคลังหลัก (ชิ้น): event ที่ปลายทาง CLEAN, CONFIRMED
        $intoClean = (int) (new Query())->from('laundry_piece_event')
            ->where(['to_location' => 'CLEAN', 'status' => 'CONFIRMED'])
            ->andWhere(['between', 'occurred_at', $startDt, $endDt])
            ->sum('qty');

        // เบิกจ่าย (ชิ้น): ออกจาก CLEAN ไป WARD (ISSUE)
        $issued = (int) (new Query())->from('laundry_piece_event')
            ->where(['event_type' => 'ISSUE', 'status' => 'CONFIRMED'])
            ->andWhere(['between', 'occurred_at', $startDt, $endDt])
            ->sum('qty');

        // ---- กราฟ 1: น้ำหนักผ้ารับเข้า รายเดือน (12 เดือนงบ ต.ค.-ก.ย.) ----
        $monthRows = (new Query())
            ->select([
                'ym' => new Expression("DATE_FORMAT(r.collection_date, '%Y-%m')"),
                'kg' => new Expression('COALESCE(SUM(w.net_kg),0)'),
            ])
            ->from(['w' => 'laundry_collection_weight'])
            ->innerJoin(['s' => 'laundry_collection_stop'], 's.id = w.stop_id')
            ->innerJoin(['r' => 'laundry_collection_round'], 'r.id = s.round_id')
            ->where(['between', 'r.collection_date', $start, $end])
            ->groupBy('ym')->indexBy('ym')->all();
        $monthLabels = [];
        $monthKg = [];
        $gy = $fiscalYear - 543;
        $thMonths = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];
        for ($i = 0; $i < 12; $i++) {
            $mIdx = 10 + $i;
            $yy = $gy - 1 + intdiv($mIdx - 1, 12);
            $mm = (($mIdx - 1) % 12) + 1;
            $key = sprintf('%04d-%02d', $yy, $mm);
            $monthLabels[] = $thMonths[$i];
            $monthKg[] = round((float) ($monthRows[$key]['kg'] ?? 0), 1);
        }

        // ---- กราฟ 2: ผ้าค้างในระบบ ตามสถานะ (ชิ้น, ยอดปัจจุบันสะสม) ----
        $backlog = [];
        foreach (['DIRTY' => 'รอนับหลังอบ', 'QC_HOLD' => 'รอตรวจ QC', 'REWORK' => 'ส่งซักซ้ำ', 'REPAIR' => 'ส่งซ่อม'] as $loc => $label) {
            $backlog[$label] = $this->locationBalance($loc);
        }

        // ---- คลังหลักคงเหลือ รายประเภท (จากตารางยอดคงเหลือ) ----
        $cleanByType = (new Query())
            ->select(['name' => 'i.item_name', 'qty' => 'b.qty'])
            ->from(['b' => 'laundry_stock_balance'])
            ->innerJoin(['i' => 'laundry_item'], 'i.id = b.item_id')
            ->where(['b.location' => 'CLEAN', 'b.department_id' => 0])
            ->andWhere(['>', 'b.qty', 0])
            ->orderBy(['b.qty' => SORT_DESC])
            ->limit(8)->all();

        // ---- หน่วยงานที่รับผ้ามากสุด (กก.) ----
        $topUnits = (new Query())
            ->select([
                'department_id' => 's.department_id',
                'kg' => new Expression('COALESCE(SUM(w.net_kg),0)'),
            ])
            ->from(['w' => 'laundry_collection_weight'])
            ->innerJoin(['s' => 'laundry_collection_stop'], 's.id = w.stop_id')
            ->innerJoin(['r' => 'laundry_collection_round'], 'r.id = s.round_id')
            ->where(['between', 'r.collection_date', $start, $end])
            ->groupBy('s.department_id')->orderBy(['kg' => SORT_DESC])->limit(8)->all();

        // ปีงบให้เลือก
        $years = range(self::currentFiscalYear() + 1, self::currentFiscalYear() - 4);

        return $this->render('index', compact(
            'fiscalYear', 'years', 'start', 'end',
            'recvKg', 'recvStops', 'rounds', 'batchesDone', 'intoClean', 'issued',
            'monthLabels', 'monthKg', 'backlog', 'cleanByType', 'topUnits'
        ));
    }

    /** ยอดคงเหลือสุทธิใน location หนึ่ง (ชิ้น) — จากตารางยอดคงเหลือ */
    private function locationBalance(string $loc): int
    {
        return \app\modules\laundry\services\LaundryBalance::locationTotal($loc);
    }
}
