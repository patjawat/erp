<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\components\MovementBridge;
use app\modules\inventoryV2\models\Warehouse;
use Yii;
use yii\web\Controller;

class StockCardController extends Controller
{
    public function actionIndex()
    {
        $warehouses = Warehouse::find()->asArray()->all();
        return $this->render('index', [
            'warehouses' => \yii\helpers\ArrayHelper::map($warehouses, 'id', 'warehouse_name')
        ]);
    }

    /**
     * บัตรควบคุมพัสดุ (Stock Card) — รวมประวัติจาก V1 (stock_events) + V2 (stock_order/stock_detail)
     * เพื่อให้สามารถใช้งานต่อเนื่องเมื่อย้ายเวอร์ชั่น
     */
    public function actionGetStockData($item_code, $start_date, $end_date, $warehouse_id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $warehouseId = $warehouse_id ? (int) $warehouse_id : null;

        // 1) ยอดยกมา = สรุปทุก movement ก่อน start_date
        $beforeMoves = MovementBridge::movements([
            'itemCode'    => $item_code,
            'dateTo'      => date('Y-m-d', strtotime($start_date . ' -1 day')),
            'warehouseId' => $warehouseId,
            'orderBy'     => 'ASC',
        ]);
        $qtyBF = 0.0;
        $valueBF = 0.0;
        foreach ($beforeMoves as $m) {
            $q = (float) $m['qty'];
            $v = (float) $m['total_price'];
            if ($m['order_type'] === 'IN') {
                $qtyBF   += $q;
                $valueBF += $v;
            } else {
                $qtyBF   -= $q;
                $valueBF -= $v;
            }
        }

        // 2) Movements ในช่วงเวลาที่เลือก
        $transactions = MovementBridge::movements([
            'itemCode'    => $item_code,
            'dateFrom'    => $start_date,
            'dateTo'      => $end_date,
            'warehouseId' => $warehouseId,
            'orderBy'     => 'ASC',
        ]);

        $runningQty   = $qtyBF;
        $runningValue = $valueBF;
        $totalIn  = 0.0;
        $totalOut = 0.0;
        $data = [];
        foreach ($transactions as $t) {
            $qty   = (float) $t['qty'];
            $price = (float) $t['unit_price'];
            $row   = $qty * $price;
            if ($t['order_type'] === 'IN') {
                $runningQty   += $qty;
                $runningValue += $row;
                $totalIn      += $qty;
                $inQty  = number_format($qty, 2);
                $outQty = '-';
            } else {
                $runningQty   -= $qty;
                $runningValue -= $row;
                $totalOut     += $qty;
                $inQty  = '-';
                $outQty = number_format($qty, 2);
            }
            $data[] = [
                'date' => date('d/m/Y H:i', strtotime($t['movement_date'])),
                'order_no' => $t['order_no'],
                'description' => $t['source'] === 'V1' ? 'V1' : ($t['order_type'] === 'IN' ? 'รับเข้า' : 'จ่ายออก'),
                'price' => number_format($price, 2),
                'in_qty' => $inQty,
                'out_qty' => $outQty,
                'balance_qty' => number_format($runningQty, 2),
                'balance_value' => number_format($runningValue, 2),
                'lot' => (string) ($t['lot_number'] ?? ''),
                'source' => $t['source'],
                'warehouse' => $t['warehouse_name'] ?? '',
            ];
        }

        return [
            'qtyBF' => number_format($qtyBF, 2),
            'valueBF' => number_format($valueBF, 2),
            'totalIn' => number_format($totalIn, 2),
            'totalOut' => number_format($totalOut, 2),
            'currentQty' => number_format($runningQty, 2),
            'currentValue' => number_format($runningValue, 2),
            'transactions' => $data,
        ];
    }
}
