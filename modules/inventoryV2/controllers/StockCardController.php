<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\models\StockDetail;
use Yii;
use yii\web\Controller;

class StockCardController extends Controller
{
    /**
     * admin/warehouse เห็นทุกคลัง; ที่เหลือ (inventory) เฉพาะคลังหลักที่ตนเป็น officer
     * @return array{0:bool,1:int[]} [canSeeAll, allowedMainWarehouseIds]
     */
    protected function warehouseScope()
    {
        $canSeeAll = !\Yii::$app->user->isGuest
            && (\Yii::$app->user->can('admin') || \Yii::$app->user->can('warehouse'));
        $allowedIds = $canSeeAll ? [] : array_map(
            'intval',
            \yii\helpers\ArrayHelper::getColumn(Warehouse::findMainWarehousesForReceive(), 'id')
        );
        return [$canSeeAll, $allowedIds];
    }

    public function actionIndex()
    {
        [$canSeeAll, $allowedIds] = $this->warehouseScope();
        $query = Warehouse::find();
        if (!$canSeeAll) {
            // เห็นเฉพาะคลังหลักที่ตนรับผิดชอบ
            $query->andWhere(['id' => $allowedIds ?: [0]]);
        }
        $warehouses = $query->asArray()->all();
        return $this->render('index', [
            'warehouses' => \yii\helpers\ArrayHelper::map($warehouses, 'id', 'warehouse_name')
        ]);
    }

    public function actionGetStockData($item_code, $start_date, $end_date, $warehouse_id = null)
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    [$canSeeAll, $allowedIds] = $this->warehouseScope();
    // ผู้ใช้ที่ถูก scope: ถ้าเจาะจงคลังที่ไม่ใช่ของตน = 403; ถ้าไม่เจาะจง = จำกัดเฉพาะคลังของตน
    if (!$canSeeAll && $warehouse_id !== null && $warehouse_id !== ''
        && !in_array((int) $warehouse_id, $allowedIds, true)) {
        throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์ดูบัตรคุมพัสดุของคลังนี้');
    }
    $scopeIds = $allowedIds ?: [0];

    // --- 1. คำนวณยอดยกมา (Brought Forward - BF) ก่อนวันที่เริ่มต้น ---
    $queryBF = StockDetail::find()->joinWith('stockOrder')
        ->where(['stock_detail.item_code' => $item_code])
        ->andWhere(['<', 'stock_order.order_date', $start_date . ' 00:00:00']);

    // กรองคลังสำหรับยอดยกมา (ถ้ามีการเลือก) มิฉะนั้นจำกัดเฉพาะคลังที่ตนรับผิดชอบ
    if ($warehouse_id) {
        $queryBF->andWhere(['stock_order.main_warehouse_id' => $warehouse_id]);
    } elseif (!$canSeeAll) {
        $queryBF->andWhere(['stock_order.main_warehouse_id' => $scopeIds]);
    }

    $modelsBF = $queryBF->all();
    $qtyBF = 0;
    $valueBF = 0;

    foreach ($modelsBF as $m) {
        $q = (float)$m->qty;
        $p = (float)$m->unit_price;
        if ($m->stockOrder->order_type === 'IN') {
            $qtyBF += $q;
            $valueBF += ($q * $p);
        } else {
            $qtyBF -= $q;
            $valueBF -= ($q * $p);
        }
    }

    // --- 2. ดึงข้อมูลธุรกรรมในช่วงวันที่เลือก (Transactions) ---
    $queryTrans = StockDetail::find()->joinWith('stockOrder')
        ->where(['stock_detail.item_code' => $item_code])
        ->andWhere(['between', 'stock_order.order_date', $start_date . ' 00:00:00', $end_date . ' 23:59:59']);

    // กรองคลังสำหรับรายการระหว่างงวด (ถ้ามีการเลือก) มิฉะนั้นจำกัดเฉพาะคลังที่ตนรับผิดชอบ
    if ($warehouse_id) {
        $queryTrans->andWhere(['stock_order.main_warehouse_id' => $warehouse_id]);
    } elseif (!$canSeeAll) {
        $queryTrans->andWhere(['stock_order.main_warehouse_id' => $scopeIds]);
    }

    $transactions = $queryTrans->orderBy([
        'stock_order.order_date' => SORT_ASC, 
        'stock_order.id' => SORT_ASC
    ])->all();

    $data = [];
    $runningQty = $qtyBF;      // เริ่มต้นสะสมจากยอดยกมา
    $runningValue = $valueBF;  // เริ่มต้นมูลค่าจากยอดยกมา
    $totalIn = 0;              // ตัวแปรสรุปยอดรับเข้าในช่วงเวลา
    $totalOut = 0;             // ตัวแปรสรุปยอดจ่ายออกในช่วงเวลา

    foreach ($transactions as $model) {
        $qty = (float)$model->qty;
        $price = (float)$model->unit_price;
        $totalRow = $qty * $price;

        if ($model->stockOrder->order_type === 'IN') {
            $runningQty += $qty;
            $runningValue += $totalRow;
            $totalIn += $qty; 
            $inQty = number_format($qty, 2);
            $outQty = '-';
        } else {
            $runningQty -= $qty;
            $runningValue -= $totalRow;
            $totalOut += $qty;
            $inQty = '-';
            $outQty = number_format($qty, 2);
        }

        $data[] = [
            'date' => date('d/m/Y H:i', strtotime($model->stockOrder->order_date)),
            'order_no' => $model->stockOrder->order_no,
            'description' => $model->stockOrder->source_type ?? 'ธุรกรรมคลัง',
            'price' => number_format($price, 2),
            'in_qty' => $inQty,
            'out_qty' => $outQty,
            'balance_qty' => number_format($runningQty, 2),    
            'balance_value' => number_format($runningValue, 2), 
            'lot' => $model->lot_number,
        ];
    }

    // --- 3. ส่งค่ากลับไปยัง JavaScript (JSON) ---
    return [
        'qtyBF' => number_format($qtyBF, 2),
        'valueBF' => number_format($valueBF, 2),
        'totalIn' => number_format($totalIn, 2),   
        'totalOut' => number_format($totalOut, 2), 
        'currentQty' => number_format($runningQty, 2),
        'currentValue' => number_format($runningValue, 2),
        'transactions' => $data
    ];
}

}
