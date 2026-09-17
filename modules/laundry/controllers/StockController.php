<?php

namespace app\modules\laundry\controllers;

use app\modules\hr\models\Organization;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
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
                ],
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

        // หน่วยงานที่มีการตั้งยอดตั้งต้น (PAR)
        $deptIds = (new Query())->select('department_id')->distinct()
            ->from('laundry_par')->column();
        $departments = $deptIds
            ? Organization::find()->select(['name', 'id'])->where(['id' => $deptIds])->orderBy(['name' => SORT_ASC])->indexBy('id')->column()
            : [];

        $rows = [];
        if ($departmentId) {
            // ยอดตั้งต้น + ยอดนับล่าสุดจากการสอบยอดที่อนุมัติแล้ว
            $rows = (new Query())
                ->select([
                    'item_id' => 'p.item_id',
                    'item_name' => 'i.item_name',
                    'target_qty' => 'p.target_qty',
                    'min_qty' => 'p.min_qty',
                    'counted' => new Expression('(
                        SELECT acl.actual_qty FROM laundry_annual_count_line acl
                        JOIN laundry_annual_count ac ON ac.id = acl.count_id
                        WHERE acl.item_id = p.item_id AND ac.department_id = p.department_id
                          AND ac.status = "APPROVED"
                        ORDER BY ac.count_year DESC, ac.id DESC LIMIT 1
                    )'),
                ])
                ->from(['p' => 'laundry_par'])
                ->innerJoin(['i' => 'laundry_item'], 'i.id = p.item_id')
                ->where(['p.department_id' => $departmentId])
                ->orderBy(['i.item_name' => SORT_ASC])
                ->all();
        }

        return $this->render('stock-sub', compact('departments', 'departmentId', 'rows'));
    }
}
