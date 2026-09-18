<?php

namespace app\modules\laundry\controllers;

use app\components\AppHelper;
use app\modules\hr\models\Organization;
use app\modules\laundry\models\LaundryUnit;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * ส่งผ้า/เบิกจ่าย — จัดผ้าสะอาดจ่ายให้หน่วยงาน (เติมให้ถึงยอดตั้งต้น)
 * ตารางภาพรวมรายวัน + เปิดรายการส่ง (อ้างอิงผลตรวจนับ หรือเลือกหน่วยงานกรอกเอง)
 */
class IssueController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'create'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['save'], 'roles' => ['laundry.manage']],
                ],
            ],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['save' => ['POST']]],
        ];
    }

    /** ภาพรวมการส่งผ้ารายวัน (ตาราง) */
    public function actionIndex()
    {
        $dateInput = trim((string) Yii::$app->request->get('date'));
        $date = $dateInput !== '' ? (AppHelper::convertToGregorian($dateInput) ?: date('Y-m-d')) : date('Y-m-d');

        $rows = (new Query())
            ->select([
                'i.id', 'i.issue_no', 'i.tree_id', 'i.issued_at', 'i.created_by',
                'unit_name' => 'o.name',
                'types' => new Expression('COUNT(l.id)'),
                'total' => new Expression('COALESCE(SUM(l.issue_qty),0)'),
            ])
            ->from(['i' => 'laundry_issue'])
            ->leftJoin(['o' => Organization::tableName()], 'o.id = i.tree_id')
            ->leftJoin(['l' => 'laundry_issue_line'], 'l.issue_id = i.id')
            ->where(['between', 'i.issued_at', $date . ' 00:00:00', $date . ' 23:59:59'])
            ->groupBy('i.id')->orderBy(['i.id' => SORT_DESC])->all();

        // ชื่อผู้จ่าย
        $staffNames = $this->staffNames(array_filter(array_column($rows, 'created_by')));
        return $this->render('index', compact('date', 'rows', 'staffNames'));
    }

    /** ฟอร์มเปิดรายการส่งผ้า (เลือกอ้างอิงตรวจนับ หรือหน่วยงาน → ตารางประเภทผ้า) */
    public function actionCreate()
    {
        $req = Yii::$app->request;
        $countId = (int) $req->get('count_id');
        $treeId = (int) $req->get('tree_id');

        // ถ้าอ้างอิงตรวจนับ → ได้หน่วยงานจากผลนับ
        $countCode = '';
        if ($countId) {
            $c = (new Query())->from('laundry_unit_count')->where(['id' => $countId])->one();
            if ($c) {
                $treeId = (int) $c['tree_id'];
                $countCode = (string) $c['count_no'];
            }
        }

        // ตัวเลือก: ผลตรวจนับล่าสุด (อ้างอิง) + หน่วยงาน
        $countOptions = [];
        foreach ((new Query())->select(['uc.id', 'uc.count_no', 'uc.counted_at', 'name' => 'o.name'])
            ->from(['uc' => 'laundry_unit_count'])->leftJoin(['o' => Organization::tableName()], 'o.id = uc.tree_id')
            ->orderBy(['uc.id' => SORT_DESC])->limit(50)->all() as $r) {
            $countOptions[$r['id']] = ($r['count_no'] ?: '#' . $r['id']) . ' · ' . ($r['name'] ?: '') . ' · ' . AppHelper::convertToThai($r['counted_at']);
        }
        $units = LaundryUnit::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC])->all();
        $unitOptions = [];
        if ($units) {
            $unitNames = Organization::find()->select(['name', 'id'])->where(['id' => array_map(fn($u) => $u->tree_id, $units)])->indexBy('id')->column();
            foreach ($units as $u) {
                $unitOptions[$u->tree_id] = $unitNames[$u->tree_id] ?? ('#' . $u->tree_id);
            }
        }

        // สร้างแถวรายการผ้าจากยอดตั้งต้น (PAR) ของหน่วยงาน
        $lines = [];
        $unitName = '';
        if ($treeId) {
            $unitName = $unitOptions[$treeId] ?? (Organization::find()->select('name')->where(['id' => $treeId])->scalar() ?: ('#' . $treeId));
            // ยอดนับ: จากผลนับที่อ้างอิง หรือผลนับล่าสุดของหน่วยงาน
            $lines = (new Query())
                ->select([
                    'item_id' => 'p.item_id', 'item_name' => 'i.item_name',
                    'target_qty' => 'p.target_qty', 'min_qty' => 'p.min_qty',
                    'main_balance' => new Expression("COALESCE((SELECT SUM(CASE WHEN e.to_location='CLEAN' THEN e.qty ELSE 0 END) - SUM(CASE WHEN e.from_location='CLEAN' THEN e.qty ELSE 0 END) FROM laundry_piece_event e WHERE e.item_id=p.item_id AND e.status='CONFIRMED'),0)"),
                    'counted' => new Expression($countId
                        ? "(SELECT ucl.qty FROM laundry_unit_count_line ucl WHERE ucl.count_id=" . $countId . " AND ucl.item_id=p.item_id LIMIT 1)"
                        : "(SELECT ucl.qty FROM laundry_unit_count_line ucl JOIN laundry_unit_count uc ON uc.id=ucl.count_id WHERE ucl.item_id=p.item_id AND uc.tree_id=p.department_id ORDER BY uc.counted_at DESC, uc.id DESC LIMIT 1)"),
                ])
                ->from(['p' => 'laundry_par'])
                ->innerJoin(['i' => 'laundry_item'], 'i.id = p.item_id')
                ->where(['p.department_id' => $treeId])
                ->orderBy(['i.item_name' => SORT_ASC])->all();
        }

        $staffName = current(array_values($this->staffNames([Yii::$app->user->id ? (int) Yii::$app->user->id : 0]))) ?: '';
        return $this->render('create', compact('countId', 'countCode', 'treeId', 'unitName', 'countOptions', 'unitOptions', 'lines', 'staffName'));
    }

    public function actionSave()
    {
        $req = Yii::$app->request;
        $treeId = (int) $req->post('tree_id');
        $countId = (int) $req->post('count_id') ?: null;
        $date = AppHelper::convertToGregorian(trim((string) $req->post('issued_date'))) ?: date('Y-m-d');
        $time = trim((string) $req->post('issued_time')) ?: date('H:i');
        $qty = (array) $req->post('issue_qty', []);
        $needs = (array) $req->post('need_qty', []);

        if (!$treeId || !Organization::find()->where(['id' => $treeId])->exists()) {
            Yii::$app->session->setFlash('error', 'ไม่พบหน่วยงาน');
            return $this->redirect(['create']);
        }
        $lines = [];
        foreach ($qty as $itemId => $q) {
            $q = trim((string) $q);
            if ($q === '' || !ctype_digit($q) || (int) $q <= 0 || !ctype_digit((string) $itemId)) {
                continue;
            }
            $lines[(int) $itemId] = ['issue' => (int) $q, 'need' => (int) (($needs[$itemId] ?? 0) ?: 0)];
        }
        if (!$lines) {
            Yii::$app->session->setFlash('error', 'กรุณาลงจำนวนจ่ายอย่างน้อยหนึ่งประเภท');
            return $this->redirect(['create', 'tree_id' => $treeId, 'count_id' => $countId]);
        }

        $db = Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            $ts = strtotime($date . ' ' . $time) ?: time();
            $now = date('Y-m-d H:i:s');
            $uid = Yii::$app->user->id ? (int) Yii::$app->user->id : null;
            $db->createCommand()->insert('laundry_issue', [
                'tree_id' => $treeId, 'count_id' => $countId,
                'issued_at' => date('Y-m-d H:i:s', $ts), 'created_by' => $uid, 'created_at' => $now,
            ])->execute();
            $issueId = (int) $db->getLastInsertID();
            $db->createCommand()->update('laundry_issue',
                ['issue_no' => 'SE' . (((int) date('Y', $ts) + 543) % 100) . '-' . str_pad((string) $issueId, 6, '0', STR_PAD_LEFT)],
                ['id' => $issueId])->execute();
            foreach ($lines as $itemId => $v) {
                $db->createCommand()->insert('laundry_issue_line', [
                    'issue_id' => $issueId, 'item_id' => $itemId, 'need_qty' => $v['need'], 'issue_qty' => $v['issue'],
                ])->execute();
                // ตัดคลังหลัก: piece_event ISSUE (CLEAN → WARD ของหน่วยงาน)
                $db->createCommand()->insert('laundry_piece_event', [
                    'event_no' => 'LP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
                    'event_type' => 'ISSUE', 'item_id' => $itemId, 'qty' => $v['issue'],
                    'from_location' => 'CLEAN', 'to_location' => 'WARD', 'to_department_id' => $treeId,
                    'status' => 'CONFIRMED', 'occurred_at' => date('Y-m-d H:i:s', $ts),
                    'created_by' => $uid, 'approved_at' => $now, 'approved_by' => $uid,
                ])->execute();
            }
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกการส่งผ้าแล้ว');
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ');
            return $this->redirect(['create', 'tree_id' => $treeId, 'count_id' => $countId]);
        }
        return $this->redirect(['index', 'date' => AppHelper::convertToThai($date)]);
    }

    private function staffNames(array $userIds): array
    {
        if (!$userIds) {
            return [];
        }
        return \yii\helpers\ArrayHelper::map(
            \app\modules\hr\models\Employees::find()->select(['user_id', 'prefix', 'fname', 'lname'])->where(['user_id' => $userIds])->asArray()->all(),
            'user_id',
            static fn($e) => trim(($e['prefix'] ?? '') . ($e['fname'] ?? '') . ' ' . ($e['lname'] ?? ''))
        );
    }
}
