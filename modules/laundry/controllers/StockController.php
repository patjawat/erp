<?php

namespace app\modules\laundry\controllers;

use app\modules\hr\models\Organization;
use app\modules\laundry\models\LaundryUnit;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * คลังผ้าของงานซักฟอก (แยกจากคลังพัสดุ — ผ้าเป็นทรัพย์สินหมุนเวียน ไม่มีมูลค่า)
 *   - คลังหลัก (main)  : ผ้าสะอาดส่วนกลาง เป็นชิ้นรายประเภท (ยอด perpetual จาก piece ledger)
 *   - คลังย่อย (sub)   : ยอดตั้งต้นรายหน่วยงาน (PAR) + ยอดนับล่าสุด + ส่วนขาด
 */
class StockController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['main', 'sub'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['par-save', 'par-delete'], 'roles' => ['laundry.manage']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['par-save' => ['POST'], 'par-delete' => ['POST']],
            ],
        ];
    }

    /** คลังหลัก: ยอดผ้าสะอาดคงเหลือรายประเภท (CLEAN) */
    public function actionMain()
    {
        $rows = (new Query())
            ->select([
                'item_id' => 'i.id',
                'item_name' => 'i.item_name',
                'item_code' => 'i.item_code',
                'balance' => new Expression(
                    "COALESCE(SUM(CASE WHEN e.to_location='CLEAN' AND e.status='CONFIRMED' THEN e.qty ELSE 0 END),0)"
                    . " - COALESCE(SUM(CASE WHEN e.from_location='CLEAN' AND e.status='CONFIRMED' THEN e.qty ELSE 0 END),0)"
                ),
            ])
            ->from(['i' => 'laundry_item'])
            ->leftJoin(['e' => 'laundry_piece_event'], 'e.item_id = i.id')
            ->where(['i.is_active' => 1])
            ->groupBy(['i.id', 'i.item_name', 'i.item_code'])
            ->orderBy(['i.item_name' => SORT_ASC])
            ->all();
        $total = array_sum(array_map(static fn($r) => (int) $r['balance'], $rows));
        return $this->render('stock-main', compact('rows', 'total'));
    }

    /** คลังย่อย: ยอดตั้งต้นรายหน่วยงาน (PAR) + ยอดนับล่าสุด + ส่วนขาด */
    public function actionSub()
    {
        $departmentId = (int) Yii::$app->request->get('department_id');

        // หน่วยงานซักฟอกทั้งหมด (เลือกเพื่อกำหนดยอดตั้งต้นได้)
        $units = LaundryUnit::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        $treeIds = array_map(static fn($u) => $u->tree_id, $units);
        $departments = $treeIds
            ? Organization::find()->select(['name', 'id'])->where(['id' => $treeIds])->orderBy(['name' => SORT_ASC])->indexBy('id')->column()
            : [];

        $rows = [];
        $availableItems = [];
        if ($departmentId) {
            // ยอดตั้งต้น + ยอดนับล่าสุด (จากตรวจนับผ้าล่าสุดของหน่วยงาน)
            $rows = (new Query())
                ->select([
                    'item_id' => 'p.item_id',
                    'item_name' => 'i.item_name',
                    'target_qty' => 'p.target_qty',
                    'min_qty' => 'p.min_qty',
                    'counted' => new Expression('(
                        SELECT ucl.qty FROM laundry_unit_count_line ucl
                        JOIN laundry_unit_count uc ON uc.id = ucl.count_id
                        WHERE ucl.item_id = p.item_id AND uc.tree_id = p.department_id
                        ORDER BY uc.counted_at DESC, uc.id DESC LIMIT 1
                    )'),
                ])
                ->from(['p' => 'laundry_par'])
                ->innerJoin(['i' => 'laundry_item'], 'i.id = p.item_id')
                ->where(['p.department_id' => $departmentId])
                ->orderBy(['i.item_name' => SORT_ASC])
                ->all();
            // ประเภทผ้าที่ยังไม่ได้ตั้งยอดตั้งต้นในหน่วยงานนี้
            $usedItems = array_column($rows, 'item_id');
            $availableItems = (new Query())->select(['item_name', 'id'])->from('laundry_item')
                ->where(['is_active' => 1])->andFilterWhere(['not in', 'id', $usedItems ?: [0]])
                ->orderBy(['item_name' => SORT_ASC])->indexBy('id')->column();
        }

        return $this->render('stock-sub', compact('departments', 'departmentId', 'rows', 'availableItems'));
    }

    /** ตั้ง/แก้ยอดตั้งต้น (PAR) รายประเภทของหน่วยงาน */
    public function actionParSave()
    {
        $req = Yii::$app->request;
        $departmentId = (int) $req->post('department_id');
        $itemId = (int) $req->post('item_id');
        $target = (int) $req->post('target_qty');
        $min = (int) $req->post('min_qty');
        $back = $this->redirect(['sub', 'department_id' => $departmentId]);

        if (!$departmentId || !$itemId) {
            Yii::$app->session->setFlash('error', 'ข้อมูลไม่ครบ');
            return $back;
        }
        if ($target < 0 || $min < 0 || $min > $target) {
            Yii::$app->session->setFlash('error', 'ยอดตั้งต้น/ขั้นต่ำไม่ถูกต้อง (ขั้นต่ำต้องไม่เกินยอดตั้งต้น)');
            return $back;
        }
        $now = date('Y-m-d H:i:s');
        $uid = Yii::$app->user->id ? (int) Yii::$app->user->id : null;
        $existing = (new Query())->from('laundry_par')->where(['department_id' => $departmentId, 'item_id' => $itemId])->one();
        if ($existing) {
            Yii::$app->db->createCommand()->update('laundry_par',
                ['target_qty' => $target, 'min_qty' => $min, 'updated_at' => $now, 'updated_by' => $uid],
                ['id' => $existing['id']])->execute();
        } else {
            Yii::$app->db->createCommand()->insert('laundry_par', [
                'department_id' => $departmentId, 'item_id' => $itemId,
                'target_qty' => $target, 'min_qty' => $min, 'updated_at' => $now, 'updated_by' => $uid,
            ])->execute();
        }
        Yii::$app->session->setFlash('success', 'บันทึกยอดตั้งต้นแล้ว');
        return $back;
    }

    public function actionParDelete()
    {
        $req = Yii::$app->request;
        $departmentId = (int) $req->post('department_id');
        $itemId = (int) $req->post('item_id');
        Yii::$app->db->createCommand()->delete('laundry_par', ['department_id' => $departmentId, 'item_id' => $itemId])->execute();
        Yii::$app->session->setFlash('success', 'ลบประเภทผ้าออกจากคลังย่อยแล้ว');
        return $this->redirect(['sub', 'department_id' => $departmentId]);
    }
}
