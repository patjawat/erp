<?php

namespace app\components;

use Yii;
use yii\base\Component;

class StockHelper extends Component
{
        //หาล๊อตที่ต้องจ่าย 
        public static function firstOut($assetItem, $warehouseId)
        {
                //เชื่อมกับใบรับเข้าเพื่อจาก lot ตามลำดับวันที่ก่อนหลัง
                $data = Yii::$app->db->createCommand("
                SELECT s.id,s.asset_item, e.movement_date, s.lot_number, s.qty, s.unit_price
                FROM stock s
                LEFT JOIN stock_events e ON e.lot_number = s.lot_number
                WHERE e.name = 'order_item'
                AND s.asset_item = :asset_item
                AND s.warehouse_id = :warehouse_id
                AND s.qty > 0
                ORDER BY e.movement_date DESC
                LIMIT 1
                ")
                        ->bindValue(':asset_item', $assetItem)
                        ->bindValue(':warehouse_id', $warehouseId)
                        ->queryOne();
                if ($data) {
                        return [
                                'id' => $data['id'],
                                'asset_item' => $data['asset_item'],
                                'lot_number' => $data['lot_number'],
                                'unit_price' => $data['unit_price']
                        ];
                } else {
                        return [
                                'id' => '',
                                'asset_item' => '',
                                'lot_number' => '',
                                'unit_price' => ''
                        ];
                }
        }
}
