<?php

namespace app\modules\SubWarehouse\controllers;

use Yii;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        // คลังย่อยที่ user มีสิทธิ์ — ใช้ตัวเดียวกับฟอร์ม "เบิกอะไหล่จากคลัง (เดิม)"
        $allowedWarehouses = \app\modules\inventoryV2\models\Warehouse::findSubWarehousesForUser();

        // เลือกคลังที่ active: query param > session > คลังแรกที่มีสิทธิ์
        $activeWarehouseId = (int) Yii::$app->request->get('warehouse_id', 0);
        $warehouse = null;
        if ($activeWarehouseId > 0) {
            foreach ($allowedWarehouses as $w) {
                if ((int) $w->id === $activeWarehouseId) {
                    $warehouse = \app\modules\inventory\models\Warehouse::findOne($w->id);
                    break;
                }
            }
        }
        if (!$warehouse) {
            $sessionWh = Yii::$app->session->get('sub-warehouse');
            if ($sessionWh) {
                foreach ($allowedWarehouses as $w) {
                    if ((int) $w->id === (int) $sessionWh->id) {
                        $warehouse = \app\modules\inventory\models\Warehouse::findOne($w->id);
                        break;
                    }
                }
            }
        }
        if (!$warehouse && !empty($allowedWarehouses)) {
            $warehouse = \app\modules\inventory\models\Warehouse::findOne($allowedWarehouses[0]->id);
        }

        $stockItems = [];
        $totalItemCount = 0;
        $totalValue = 0.0;
        $dataProvider = null;
        $searchModel = null;

        if ($warehouse) {
            // ใช้ pattern เดียวกับ /me/store-v2/index (StockSearch + leftJoin categorise/warehouses)
            $itemTypes = is_array($warehouse->data_json['item_type'] ?? null) ? $warehouse->data_json['item_type'] : [];

            $searchModel = new \app\modules\inventory\models\StockSearch([
                'warehouse_id' => $warehouse->id,
            ]);
            $dataProvider = $searchModel->search($this->request->queryParams);
            $dataProvider->query->leftJoin('categorise p', 'p.code=stock.asset_item');
            $dataProvider->query->leftJoin('warehouses w', 'w.id=stock.warehouse_id');
            if (!empty($itemTypes)) {
                $dataProvider->query->andWhere(['IN', 'p.category_id', $itemTypes]);
            }
            $dataProvider->query->andFilterWhere(['p.category_id' => $searchModel->asset_type]);
            $dataProvider->query->andFilterWhere(['w.id' => $warehouse->id]);
            $dataProvider->query->andFilterWhere([
                'or',
                ['like', 'asset_item', $searchModel->q],
                ['like', 'title', $searchModel->q],
            ]);
            $dataProvider->setSort([
                'defaultOrder' => ['unit_price' => SORT_DESC],
            ]);
            $dataProvider->query->groupBy('asset_item');
            $dataProvider->pagination->pageSize = 8;

            // โหลด top 8 สำหรับตาราง "รายการล่าสุด"
            $stockItems = $dataProvider->getModels();

            // นับจำนวนรายการทั้งหมด + มูลค่ารวม (ไม่ขึ้นกับ pagination)
            $totalItemCount = (int) $dataProvider->getTotalCount();
            foreach ((clone $dataProvider->query)->limit(null)->offset(null)->all() as $it) {
                $qty = (float) $it->SumQty();
                $totalValue += $qty * (float) ($it->unit_price ?? 0);
            }
        }

        return $this->render('index', [
            'warehouse' => $warehouse,
            'allowedWarehouses' => $allowedWarehouses,
            'stockItems' => $stockItems,
            'totalItemCount' => $totalItemCount,
            'totalValue' => $totalValue,
        ]);
    }

    public function actionRequest()
    {
        $mode = Yii::$app->request->get('mode', 'in');
        if (!in_array($mode, ['in', 'out'], true)) {
            $mode = 'in';
        }
        return $this->render('request', ['mode' => $mode]);
    }
}
