<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use app\modules\plan\components\IcuEvaluator;
use app\modules\plan\models\PlanIcuMonth;
use app\modules\finance\models\FinanceCashTxn;

/**
 * ICU 3 มิติ — ผลการกำกับและประเมินสัญญาณเตือนภัยล่วงหน้า 3 ด้าน (แบบฟอร์มเขต เมนู 2.3) ระดับโรงพยาบาล
 */
class IcuController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['save' => ['POST']]],
        ];
    }

    /**
     * @param int|null $year ปีงบประมาณ (พ.ศ.)
     * @param string|null $level ระดับ รพ. (เกณฑ์ต่างกัน) — ค่าเริ่มต้นจาก params['hospitalLevel'] หรือ รพช.
     * @param int|null $m งวดเดือนที่ประเมิน 1-12 (ค่าเริ่มต้น = งวดล่าสุดที่ประเมินได้)
     */
    public function actionIndex($year = null, $level = null, $m = null)
    {
        $year = (int) ($year ?: FinanceCashTxn::currentFiscalYear());
        $level = array_key_exists((string) $level, IcuEvaluator::LEVELS) ? (string) $level : (Yii::$app->params['hospitalLevel'] ?? 'รพช.');
        $data = IcuEvaluator::yearly($year, $level);

        $evaluated = array_filter($data['rows'], fn ($r) => $r['evaluated']);
        $m = (int) $m;
        if (!isset($data['rows'][$m]) || !$data['rows'][$m]['evaluated']) {
            $m = $evaluated ? max(array_keys($evaluated)) : 0;
        }

        $tierCount = array_fill_keys(array_keys(IcuEvaluator::TIERS), 0);
        foreach ($evaluated as $r) {
            $tierCount[$r['res']['tier']]++;
        }

        return $this->render('index', [
            'year' => $year,
            'level' => $level,
            'm' => $m,
            'opening' => $data['opening'],
            'rows' => $data['rows'],
            'tierCount' => $tierCount,
            'evaluatedCount' => count($evaluated),
            'th' => IcuEvaluator::thresholds($level),
        ]);
    }

    /** บันทึกยอดกรอกทับรายเดือน — ช่องว่าง = กลับไปใช้ค่าคำนวณ */
    public function actionSave()
    {
        $post = Yii::$app->request->post();
        $year = (int) ($post['year'] ?? FinanceCashTxn::currentFiscalYear());
        $num = function ($v) {
            $v = trim(str_replace([',', ' '], '', (string) $v));
            return $v === '' ? null : (float) $v;
        };
        $n = 0;
        foreach ((array) ($post['ov'] ?? []) as $mon => $r) {
            $mon = (int) $mon;
            if ($mon < 1 || $mon > 12) {
                continue;
            }
            $cash = $num($r['cash'] ?? '');
            $com = $num($r['com'] ?? '');
            $note = trim((string) ($r['note'] ?? '')) ?: null;
            $row = PlanIcuMonth::findOne(['fiscal_year' => $year, 'month_no' => $mon]);
            if ($cash === null && $com === null && $note === null) {
                if ($row) {
                    $row->delete();
                    $n++;
                }
                continue;
            }
            $row = $row ?: new PlanIcuMonth(['fiscal_year' => $year, 'month_no' => $mon]);
            $row->cash_balance = $cash;
            $row->commitment = $com;
            $row->note = $note;
            if ($row->save()) {
                $n++;
            }
        }
        Yii::$app->session->setFlash('success', "บันทึกยอดปรับรายเดือนปีงบ {$year} แล้ว");
        return $this->redirect(['index', 'year' => $year, 'level' => $post['level'] ?? null]);
    }
}
