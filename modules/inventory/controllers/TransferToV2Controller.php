<?php

namespace app\modules\inventory\controllers;

use Yii;
use app\components\AppHelper;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockEventSearch;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\Warehouse;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * ส่งออก "คงเหลือสิ้นเดือน" จาก Inventory V1 ไปเป็นใบรับเข้า (ฉบับร่าง) ใน Inventory V2
 * ต้องเลือกประเภทวัสดุเพื่อให้สอดคล้องกับคลัง V2
 */
class TransferToV2Controller extends \yii\web\Controller
{
    /**
     * ฟอร์มเลือกประเภทวัสดุ + ช่วงวันที่ + คลัง V2 แล้วแสดงตัวอย่างรายการ และปุ่มสร้างใบรับเข้า
     */
    public function actionIndex()
    {
        $searchModel = new StockEventSearch(['date_filter' => 'this_month']);
        $searchModel->load(Yii::$app->request->queryParams);
        $searchModel->search(Yii::$app->request->queryParams);

        $mainWarehouseId = (string) Yii::$app->request->get('main_warehouse_id', '');
        $assetTypeId = $searchModel->asset_type_id;

        list($querys, $groupSummary) = $this->getReportData($searchModel);

        // เฉพาะรายการที่คงเหลือสิ้นเดือน > 0 และมีใน V2 (item_code + category_id ตรง)
        $rowsForV2 = [];
        foreach ($querys as $row) {
            $endQty = (float) ($row['end_qty'] ?? 0);
            if ($endQty <= 0) {
                continue;
            }
            $itemCode = trim((string) ($row['asset_item'] ?? ''));
            if ($itemCode === '') {
                continue;
            }
            $v2Item = StockItem::findOne(['item_code' => $itemCode]);
            if (!$v2Item) {
                continue;
            }
            $itemCategoryId = $v2Item->category_id !== null ? (string) $v2Item->category_id : '';
            if ($assetTypeId !== '' && $itemCategoryId !== (string) $assetTypeId) {
                continue;
            }
            $endPrice = (float) ($row['end_price'] ?? 0);
            $unitPrice = $endQty != 0 ? $endPrice / $endQty : 0;
            $rowsForV2[] = [
                'asset_item' => $itemCode,
                'asset_name' => $row['asset_name'] ?? '',
                'asset_type_name' => $row['asset_type_name'] ?? '',
                'end_qty' => $endQty,
                'end_price' => $endPrice,
                'unit_price' => $unitPrice,
            ];
        }

        $listWarehouses = $this->getV2MainWarehouses();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'rowsForV2' => $rowsForV2,
            'mainWarehouseId' => $mainWarehouseId,
            'listWarehouses' => $listWarehouses,
        ]);
    }

    /**
     * สร้างใบรับเข้าใน V2 (สถานะฉบับร่าง) จากรายการคงเหลือสิ้นเดือนที่เลือกประเภทวัสดุแล้ว
     */
    public function actionCreateReceive()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['index']);
        }

        $searchModel = new StockEventSearch();
        $searchModel->load(Yii::$app->request->post('StockEventSearch', []));
        $searchModel->search(Yii::$app->request->post());

        $assetTypeId = trim((string) ($searchModel->asset_type_id ?? ''));
        if ($assetTypeId === '') {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกประเภทวัสดุ');
            return $this->redirect(['index']);
        }

        $mainWarehouseId = (int) Yii::$app->request->post('main_warehouse_id', 0);
        $warehouse = Warehouse::findOne($mainWarehouseId);
        if (!$warehouse || $warehouse->warehouse_type !== 'MAIN') {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกคลังหลักในระบบ Inventory V2');
            return $this->redirect(['index']);
        }
        if (!$warehouse->allowsItemType($assetTypeId)) {
            Yii::$app->session->setFlash('error', 'คลังที่เลือกไม่รับพัสดุประเภทนี้ กรุณาเลือกคลังที่กำหนดประเภทที่รับเข้ารวมประเภทที่เลือก');
            return $this->redirect(['index']);
        }

        list($querys, ) = $this->getReportData($searchModel);

        $details = [];
        foreach ($querys as $row) {
            $endQty = (float) ($row['end_qty'] ?? 0);
            if ($endQty <= 0) {
                continue;
            }
            $itemCode = trim((string) ($row['asset_item'] ?? ''));
            if ($itemCode === '') {
                continue;
            }
            $v2Item = StockItem::findOne(['item_code' => $itemCode]);
            if (!$v2Item || (string) $v2Item->category_id !== $assetTypeId) {
                continue;
            }
            $endPrice = (float) ($row['end_price'] ?? 0);
            $unitPrice = $endQty != 0 ? $endPrice / $endQty : 0;
            $details[] = [
                'item_code' => $itemCode,
                'qty' => $endQty,
                'unit_price' => $unitPrice,
                'lot_number' => 'V1-' . date('Y-m-d') . '-' . preg_replace('/[^a-zA-Z0-9-]/', '-', $itemCode),
            ];
        }

        if (empty($details)) {
            Yii::$app->session->setFlash('error', 'ไม่มีรายการคงเหลือสิ้นเดือนที่ตรงกับประเภทวัสดุและมีในระบบ V2');
            return $this->redirect(['index']);
        }

        try {
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
            $orderDate = ($dateEnd !== null && $dateEnd !== '') ? $dateEnd . ' 12:00:00' : date('Y-m-d H:i:s');

            $order = new StockOrder();
            $order->order_type = StockOrder::ORDER_TYPE_IN;
            $order->status = StockOrder::STATUS_DRAFT;
            $order->source_type = StockOrder::SOURCE_INITIAL;
            $order->order_no = $this->generateReceiveOrderNo();
            $order->order_date = $orderDate;
            $order->main_warehouse_id = $mainWarehouseId;
            $order->ref = 'ย้ายจาก Inventory V1 (คงเหลือสิ้นเดือน)';

            if (!$order->save(false)) {
                throw new \RuntimeException('บันทึกใบรับเข้าไม่สำเร็จ');
            }

            foreach ($details as $d) {
                $detail = new StockDetail();
                $detail->stock_order_id = $order->id;
                $detail->item_code = $d['item_code'];
                $detail->qty = $d['qty'];
                $detail->unit_price = $d['unit_price'];
                $detail->lot_number = $d['lot_number'];
                $detail->remain_qty = $d['qty'];
                if (!$detail->save(false)) {
                    throw new \RuntimeException('บันทึกรายการไม่สำเร็จ: ' . $d['item_code']);
                }
            }

            Yii::$app->session->setFlash('success', 'สร้างใบรับเข้าใน Inventory V2 (ฉบับร่าง) เรียบร้อยแล้ว');
            return $this->redirect(Url::to(['/inventory-v2/receive/view', 'id' => $order->id]));
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect([
            'index',
            'StockEventSearch' => [
                'date_start' => $searchModel->date_start,
                'date_end' => $searchModel->date_end,
                'asset_type_id' => $searchModel->asset_type_id,
            ],
            'main_warehouse_id' => $mainWarehouseId,
        ]);
        }
    }

    private function getReportData(StockEventSearch $searchModel)
    {
        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        } catch (\Throwable $th) {
            $dateStart = $dateEnd = '';
        }

        $params = [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ];
        $conditions = [
            "a.name = 'asset_item'",
            "a.group_id = 'MATER'",
        ];
        $groupBy = 'a.code';
        $orderBy = "CAST(SUBSTRING_INDEX(a.code, '-', 1) AS UNSIGNED), 
        CAST(SUBSTRING_INDEX(a.code, '-', -1) AS UNSIGNED), 
        CAST(SUBSTRING(a.category_id, 2) AS UNSIGNED) LIMIT 99999999";

        if (!empty($searchModel->asset_type_id)) {
            $conditions[] = "a.category_id = :asset_type_id";
            $params[':asset_type_id'] = $searchModel->asset_type_id;
        }

        list($sql, $params) = StockEvent::buildStockAssetItemSql($conditions, $params, $groupBy, $orderBy);
        $querys = Yii::$app->db->createCommand($sql, $params)->queryAll();

        list($sqlSummary, $paramsSummary) = StockEvent::buildStockAssetItemSql($conditions, $params, null, null);
        $groupSummary = Yii::$app->db->createCommand($sqlSummary, $paramsSummary)->queryOne();

        return [$querys, $groupSummary ?: []];
    }

    private function getV2MainWarehouses()
    {
        return ArrayHelper::map(
            Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->andWhere(['or', ['delete' => null], ['delete' => '']])
                ->orderBy('warehouse_name')
                ->all(),
            'id',
            'warehouse_name'
        );
    }

    private function generateReceiveOrderNo()
    {
        $prefix = 'RCV-V1-' . date('Ymd-His') . '-';
        do {
            $no = $prefix . mt_rand(100, 999);
        } while (StockOrder::findOne(['order_no' => $no]) !== null);
        return $no;
    }

    /**
     * แสดงรายการใบเบิก V1 ที่ยังไม่จ่ายของ (transaction_type=OUT, order_status=pending)
     * พร้อม preview line items และ flag รายการที่ map กับ V2 master ไม่ได้
     */
    public function actionRequisitionIndex()
    {
        $orders = (new \yii\db\Query())
            ->select(['id', 'code', 'movement_date', 'warehouse_id', 'from_warehouse_id', 'ref', 'created_at', 'created_by'])
            ->from(StockEvent::tableName())
            ->where([
                'name' => 'order',
                'transaction_type' => 'OUT',
                'order_status' => 'pending',
            ])
            ->orderBy(['movement_date' => SORT_DESC, 'id' => SORT_DESC])
            ->all();

        if (empty($orders)) {
            return $this->render('requisition-index', [
                'rows' => [],
                'warehouseMap' => [],
                'existingOrderNos' => [],
            ]);
        }

        $orderIds = array_column($orders, 'id');
        $items = (new \yii\db\Query())
            ->select(['category_id AS parent_id', 'asset_item', 'qty', 'unit_price', 'lot_number'])
            ->from(StockEvent::tableName())
            ->where(['name' => 'order_item', 'category_id' => $orderIds])
            ->all();

        $itemsByOrder = [];
        $itemCodes = [];
        foreach ($items as $it) {
            $itemsByOrder[(int) $it['parent_id']][] = $it;
            if (!empty($it['asset_item'])) {
                $itemCodes[$it['asset_item']] = true;
            }
        }

        $existingV2Items = [];
        if (!empty($itemCodes)) {
            $found = StockItem::find()
                ->select('code')
                ->where(['code' => array_keys($itemCodes)])
                ->column();
            $existingV2Items = array_flip($found);
        }

        $warehouseIds = [];
        foreach ($orders as $o) {
            if (!empty($o['warehouse_id'])) $warehouseIds[(int) $o['warehouse_id']] = true;
            if (!empty($o['from_warehouse_id'])) $warehouseIds[(int) $o['from_warehouse_id']] = true;
        }
        $warehouseMap = [];
        if (!empty($warehouseIds)) {
            $warehouseMap = ArrayHelper::map(
                Warehouse::find()
                    ->select(['id', 'warehouse_name', 'warehouse_type'])
                    ->where(['id' => array_keys($warehouseIds)])
                    ->asArray()
                    ->all(),
                'id',
                function ($w) { return $w; }
            );
        }

        $existingOrderNos = array_flip(
            StockOrder::find()
                ->select('order_no')
                ->where(['order_no' => array_column($orders, 'code')])
                ->column()
        );

        $rows = [];
        $alreadyMigrated = 0;
        foreach ($orders as $o) {
            if (isset($existingOrderNos[$o['code']])) {
                $alreadyMigrated++;
                continue;
            }
            $oid = (int) $o['id'];
            $lines = $itemsByOrder[$oid] ?? [];
            $matched = 0;
            $skipped = 0;
            $skippedCodes = [];
            foreach ($lines as $l) {
                $code = trim((string) ($l['asset_item'] ?? ''));
                if ($code === '' || !isset($existingV2Items[$code])) {
                    $skipped++;
                    if ($code !== '') $skippedCodes[$code] = true;
                } else {
                    $matched++;
                }
            }
            $rows[] = [
                'id' => $oid,
                'code' => $o['code'],
                'movement_date' => $o['movement_date'] ?: $o['created_at'],
                'main_warehouse_id' => $o['warehouse_id'],
                'sub_warehouse_id' => $o['from_warehouse_id'],
                'ref' => $o['ref'],
                'line_total' => count($lines),
                'line_matched' => $matched,
                'line_skipped' => $skipped,
                'skipped_codes' => array_keys($skippedCodes),
                'transferable' => $matched > 0,
            ];
        }

        return $this->render('requisition-index', [
            'rows' => $rows,
            'warehouseMap' => $warehouseMap,
            'alreadyMigrated' => $alreadyMigrated,
        ]);
    }

    /**
     * ย้ายใบเบิก V1 ที่เลือกไป V2 (stock_order OUT/REQUEST/PENDING + stock_detail)
     * เก็บค่า order_no เดิม, ไม่แก้ไข stock_events ใน V1
     */
    public function actionCreateRequisitions()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['requisition-index']);
        }

        $selectedIds = (array) Yii::$app->request->post('order_ids', []);
        $selectedIds = array_values(array_filter(array_map('intval', $selectedIds)));
        if (empty($selectedIds)) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกใบเบิกอย่างน้อย 1 ใบ');
            return $this->redirect(['requisition-index']);
        }

        $orders = (new \yii\db\Query())
            ->select(['id', 'code', 'movement_date', 'warehouse_id', 'from_warehouse_id', 'ref', 'created_at'])
            ->from(StockEvent::tableName())
            ->where([
                'name' => 'order',
                'transaction_type' => 'OUT',
                'order_status' => 'pending',
                'id' => $selectedIds,
            ])
            ->all();

        if (empty($orders)) {
            Yii::$app->session->setFlash('error', 'ไม่พบใบเบิกที่เลือก');
            return $this->redirect(['requisition-index']);
        }

        $orderIds = array_column($orders, 'id');
        $items = (new \yii\db\Query())
            ->select(['category_id AS parent_id', 'asset_item', 'qty', 'unit_price', 'lot_number'])
            ->from(StockEvent::tableName())
            ->where(['name' => 'order_item', 'category_id' => $orderIds])
            ->all();
        $itemsByOrder = [];
        foreach ($items as $it) {
            $itemsByOrder[(int) $it['parent_id']][] = $it;
        }

        $itemCodes = array_unique(array_filter(array_column($items, 'asset_item')));
        $existingV2Items = !empty($itemCodes)
            ? array_flip(StockItem::find()->select('code')->where(['code' => $itemCodes])->column())
            : [];

        $existingOrderNos = array_flip(
            StockOrder::find()->select('order_no')->where(['order_no' => array_column($orders, 'code')])->column()
        );

        $createdCount = 0;
        $skippedDuplicate = [];
        $skippedNoItems = [];
        $skippedItemCodes = [];

        foreach ($orders as $o) {
            $code = (string) $o['code'];
            if (isset($existingOrderNos[$code])) {
                $skippedDuplicate[] = $code;
                continue;
            }

            $lines = $itemsByOrder[(int) $o['id']] ?? [];
            $validLines = [];
            foreach ($lines as $l) {
                $itemCode = trim((string) ($l['asset_item'] ?? ''));
                if ($itemCode === '' || !isset($existingV2Items[$itemCode])) {
                    if ($itemCode !== '') $skippedItemCodes[$itemCode] = true;
                    continue;
                }
                $validLines[] = $l;
            }
            if (empty($validLines)) {
                $skippedNoItems[] = $code;
                continue;
            }

            $tx = Yii::$app->db->beginTransaction();
            try {
                $orderDate = !empty($o['movement_date'])
                    ? $o['movement_date'] . ' 12:00:00'
                    : ($o['created_at'] ?: date('Y-m-d H:i:s'));

                $stockOrder = new StockOrder();
                $stockOrder->order_no = $code;
                $stockOrder->order_type = StockOrder::ORDER_TYPE_OUT;
                $stockOrder->source_type = 'REQUEST';
                $stockOrder->status = StockOrder::STATUS_PENDING;
                $stockOrder->order_date = $orderDate;
                $stockOrder->main_warehouse_id = (int) $o['warehouse_id'] ?: null;
                $stockOrder->sub_warehouse_id = (int) $o['from_warehouse_id'] ?: null;
                $stockOrder->ref = 'ย้ายจาก Inventory V1 (ใบเบิก ' . $code . ')';

                if (!$stockOrder->save(false)) {
                    throw new \RuntimeException('บันทึก stock_order ไม่สำเร็จ: ' . $code);
                }

                foreach ($validLines as $l) {
                    $detail = new StockDetail();
                    $detail->stock_order_id = $stockOrder->id;
                    $detail->item_code = $l['asset_item'];
                    $detail->qty = (float) $l['qty'];
                    $detail->unit_price = $l['unit_price'] !== null ? (float) $l['unit_price'] : null;
                    $detail->lot_number = !empty($l['lot_number']) ? $l['lot_number'] : ('V1-REQ-' . $code);
                    $detail->remain_qty = (float) $l['qty'];
                    if (!$detail->save(false)) {
                        throw new \RuntimeException('บันทึก stock_detail ไม่สำเร็จ: ' . $code . ' / ' . $l['asset_item']);
                    }
                }

                $tx->commit();
                $createdCount++;
            } catch (\Throwable $e) {
                $tx->rollBack();
                Yii::error('[TransferToV2/Requisition] ' . $code . ': ' . $e->getMessage(), __METHOD__);
                $skippedNoItems[] = $code . ' (' . $e->getMessage() . ')';
            }
        }

        $messages = ['ย้ายใบเบิกสำเร็จ ' . $createdCount . ' ใบ'];
        if (!empty($skippedDuplicate)) {
            $messages[] = 'ข้าม (order_no ซ้ำใน V2): ' . implode(', ', $skippedDuplicate);
        }
        if (!empty($skippedNoItems)) {
            $messages[] = 'ข้าม (ไม่มีรายการที่ map ได้ / error): ' . implode(', ', $skippedNoItems);
        }
        if (!empty($skippedItemCodes)) {
            $messages[] = 'item_code ที่ไม่มีใน V2 master: ' . implode(', ', array_keys($skippedItemCodes));
        }

        if ($createdCount > 0) {
            Yii::$app->session->setFlash('success', implode('<br>', $messages));
        } else {
            Yii::$app->session->setFlash('error', implode('<br>', $messages));
        }

        return $this->redirect(['requisition-index']);
    }
}
