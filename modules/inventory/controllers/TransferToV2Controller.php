<?php

namespace app\modules\inventory\controllers;

use Yii;
use app\components\AppHelper;
use app\models\UploadCsvForm;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockEventSearch;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\components\TransferV2HistoryMigrator;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\UploadedFile;

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
     * แสดงรายการใบเบิก/ใบจ่าย V1 ที่ยังย้ายเข้า V2 ได้
     * รองรับ filter: pending = ใบเบิกที่ยังไม่ตัดยอด, success = ใบจ่ายที่ตัดยอดแล้ว, all = ทั้งคู่
     * ใบที่ status=success จะถูกย้ายเข้า V2 เป็น CONFIRMED พร้อมตัด stock_balance
     */
    public function actionRequisitionIndex()
    {
        $statusFilter = Yii::$app->request->get('status_filter', 'pending');
        $allowedStatuses = $this->resolveV1StatusFilter($statusFilter);

        $orders = (new \yii\db\Query())
            ->select(['id', 'code', 'movement_date', 'warehouse_id', 'from_warehouse_id', 'ref', 'order_status', 'created_at', 'created_by'])
            ->from(StockEvent::tableName())
            ->where([
                'name' => 'order',
                'transaction_type' => 'OUT',
                'order_status' => $allowedStatuses,
            ])
            ->orderBy(['movement_date' => SORT_DESC, 'id' => SORT_DESC])
            ->all();

        if (empty($orders)) {
            return $this->render('requisition-index', [
                'rows' => [],
                'warehouseMap' => [],
                'statusFilter' => $statusFilter,
                'alreadyMigrated' => 0,
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
                'v1_status' => $o['order_status'],
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
            'statusFilter' => $statusFilter,
            'alreadyMigrated' => $alreadyMigrated,
        ]);
    }

    /**
     * แปลง filter key เป็น array ของ stock_events.order_status ที่ยอมรับได้
     */
    private function resolveV1StatusFilter($key)
    {
        switch ($key) {
            case 'success':
                return ['success'];
            case 'all':
                return ['pending', 'success'];
            case 'pending':
            default:
                return ['pending'];
        }
    }

    /**
     * สร้าง marker structured ว่า "ใบนี้ย้ายมาจาก V1" — เก็บใน data_json.migrated_from_v1
     */
    private function buildV1Marker(array $sourceOrder)
    {
        return [
            'source_id' => (int) $sourceOrder['id'],
            'source_code' => (string) $sourceOrder['code'],
            'source_status' => (string) ($sourceOrder['order_status'] ?? ''),
            'migrated_at' => time(),
            'migrated_by' => Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id,
            'v1_movement_date' => $sourceOrder['movement_date'] ?? null,
        ];
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
            ->select(['id', 'code', 'movement_date', 'warehouse_id', 'from_warehouse_id', 'ref', 'order_status', 'created_at'])
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
                $stockOrder->ref = 'V1';
                $stockOrder->data_json = ['migrated_from_v1' => $this->buildV1Marker($o)];

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

    /**
     * ย้ายใบจ่ายที่ success แล้วใน V1 ไป V2 เป็น CONFIRMED
     * พร้อมตัด stock_balance ใน V2 ผ่าน InventoryService::adjustBalance(allowNegative=true)
     * (ใช้แทน processFIFO เพราะ lot v1 อาจไม่มีใน v2 — ตัด balance ตรงๆ ตามที่ v1 บันทึก)
     */
    public function actionCreateIssued()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['requisition-index', 'status_filter' => 'success']);
        }

        $selectedIds = (array) Yii::$app->request->post('order_ids', []);
        $selectedIds = array_values(array_filter(array_map('intval', $selectedIds)));
        if (empty($selectedIds)) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกใบจ่ายอย่างน้อย 1 ใบ');
            return $this->redirect(['requisition-index', 'status_filter' => 'success']);
        }

        $orders = (new \yii\db\Query())
            ->select(['id', 'code', 'movement_date', 'warehouse_id', 'from_warehouse_id', 'ref', 'order_status', 'created_at'])
            ->from(StockEvent::tableName())
            ->where([
                'name' => 'order',
                'transaction_type' => 'OUT',
                'order_status' => 'success',
                'id' => $selectedIds,
            ])
            ->all();

        if (empty($orders)) {
            Yii::$app->session->setFlash('error', 'ไม่พบใบจ่ายที่เลือก');
            return $this->redirect(['requisition-index', 'status_filter' => 'success']);
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

            $mainWarehouseId = (int) $o['warehouse_id'] ?: null;
            if (!$mainWarehouseId) {
                $skippedNoItems[] = $code . ' (ไม่มี warehouse_id ในต้นฉบับ)';
                continue;
            }

            $tx = Yii::$app->db->beginTransaction();
            try {
                $orderDate = !empty($o['movement_date'])
                    ? $o['movement_date'] . ' 12:00:00'
                    : ($o['created_at'] ?: date('Y-m-d H:i:s'));
                $disbursementTs = strtotime($orderDate) ?: time();

                $stockOrder = new StockOrder();
                $stockOrder->order_no = $code;
                $stockOrder->order_type = StockOrder::ORDER_TYPE_OUT;
                $stockOrder->source_type = 'REQUEST';
                $stockOrder->status = StockOrder::STATUS_CONFIRMED;
                $stockOrder->order_date = $orderDate;
                $stockOrder->main_warehouse_id = $mainWarehouseId;
                $stockOrder->sub_warehouse_id = (int) $o['from_warehouse_id'] ?: null;
                $stockOrder->disbursement_date = $disbursementTs;
                $stockOrder->ref = 'V1';
                $stockOrder->data_json = ['migrated_from_v1' => $this->buildV1Marker($o)];

                if (!$stockOrder->save(false)) {
                    throw new \RuntimeException('บันทึก stock_order ไม่สำเร็จ: ' . $code);
                }

                foreach ($validLines as $l) {
                    $qty = (float) $l['qty'];
                    if ($qty <= 0) {
                        continue;
                    }
                    $lotNumber = !empty($l['lot_number']) ? $l['lot_number'] : ('V1-OUT-' . $code);

                    $detail = new StockDetail();
                    $detail->stock_order_id = $stockOrder->id;
                    $detail->item_code = $l['asset_item'];
                    $detail->qty = $qty;
                    $detail->unit_price = $l['unit_price'] !== null ? (float) $l['unit_price'] : null;
                    $detail->lot_number = $lotNumber;
                    $detail->remain_qty = 0; // จ่ายแล้ว ไม่มี FIFO carryover
                    if (!$detail->save(false)) {
                        throw new \RuntimeException('บันทึก stock_detail ไม่สำเร็จ: ' . $code . ' / ' . $l['asset_item']);
                    }

                    // ตัด balance ตรงๆ ผ่าน adjustBalance (allowNegative=true เพื่อไม่ block migration)
                    // ยอดติดลบจะถูกเคลียร์ในขั้นตอน Reconcile (ดู StockAdjust::actionNegativeBalance)
                    InventoryService::adjustBalance(
                        $l['asset_item'],
                        $mainWarehouseId,
                        $lotNumber,
                        -$qty,
                        true
                    );
                }

                $tx->commit();
                $createdCount++;
            } catch (\Throwable $e) {
                $tx->rollBack();
                Yii::error('[TransferToV2/Issued] ' . $code . ': ' . $e->getMessage(), __METHOD__);
                $skippedNoItems[] = $code . ' (' . $e->getMessage() . ')';
            }
        }

        $messages = ['ย้ายใบจ่ายสำเร็จ ' . $createdCount . ' ใบ (ตัด stock_balance ใน V2 แล้ว — ตรวจยอดติดลบที่ "ปรับยอดคลัง > ยอดติดลบ")'];
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

        return $this->redirect(['requisition-index', 'status_filter' => 'success']);
    }

    /**
     * หน้านำเข้า "ยอดยกมา" จากผลการนับจริง (CSV) → สร้างใบรับเข้า V2 (IN/INITIAL)
     * Columns: item_code, lot_number, qty, unit_price (+ optional: note)
     * ใช้กับ workflow แนะนำ "ยอดยกมาจากการนับจริง" — ปลอดภัยกว่าใช้ end_qty จาก V1 ที่อาจเพี้ยน
     */
    public function actionImportCount()
    {
        $model = new UploadCsvForm();
        $listWarehouses = $this->getV2MainWarehouses();

        return $this->render('import-count', [
            'model' => $model,
            'listWarehouses' => $listWarehouses,
        ]);
    }

    /**
     * GET: download CSV template สำหรับให้ user กรอกผลนับจริง
     */
    public function actionImportCountTemplate()
    {
        $headers = ['item_code', 'lot_number', 'qty', 'unit_price', 'note'];
        $samples = [
            ['M-001', 'LOT-2026-001', '100', '15.50', 'ตัวอย่าง: lot จากการนับจริง'],
            ['M-002', '', '50', '8.00', 'ตัวอย่าง: ไม่ระบุ lot จะ default เป็น INITIAL-{วันที่}'],
        ];

        $filename = 'inventory_count_template_' . date('Ymd') . '.csv';
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv; charset=utf-8');

        $out = fopen('php://temp', 'w+');
        // BOM เพื่อให้ Excel เปิดภาษาไทย/ตัวเลขถูกต้อง
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($samples as $r) {
            fputcsv($out, $r);
        }
        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);

        return $content;
    }

    /**
     * AJAX: preview CSV ที่อัปโหลด — validate item_code กับ V2 master, แสดงตัวอย่าง
     */
    public function actionImportCountPreview()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new UploadCsvForm();
        $model->csvFile = UploadedFile::getInstanceByName('csvFile');

        if (!$model->validate()) {
            return ['status' => 'error', 'errors' => $model->getErrors()];
        }

        $filePath = Yii::getAlias('@runtime') . '/import_count_' . time() . '.' . $model->csvFile->extension;
        $model->csvFile->saveAs($filePath);

        $rows = $this->parseCountCsv($filePath);
        if (empty($rows)) {
            return ['status' => 'error', 'message' => 'ไฟล์ว่างหรือ format ไม่ถูกต้อง (ต้องมี header แถวแรก)'];
        }

        $itemCodes = array_unique(array_filter(array_column($rows, 'item_code')));
        $existingItems = !empty($itemCodes)
            ? array_flip(StockItem::find()->select('code')->where(['code' => $itemCodes])->column())
            : [];

        $preview = [];
        $validCount = 0;
        $invalidCount = 0;
        foreach ($rows as $r) {
            $isValid = isset($existingItems[$r['item_code']]) && $r['qty'] > 0;
            if ($isValid) {
                $validCount++;
            } else {
                $invalidCount++;
            }
            $preview[] = array_merge($r, [
                'is_valid' => $isValid,
                'error' => !isset($existingItems[$r['item_code']])
                    ? 'ไม่พบ item_code ใน V2 master'
                    : ($r['qty'] <= 0 ? 'qty ต้องมากกว่า 0' : ''),
            ]);
        }

        return [
            'status' => 'success',
            'filePath' => $filePath,
            'preview' => $preview,
            'validCount' => $validCount,
            'invalidCount' => $invalidCount,
        ];
    }

    /**
     * POST: confirm สร้างใบรับเข้า V2 จาก CSV ที่ preview แล้ว
     * อัปเดต stock_balance ทันทีผ่าน InventoryService::moveStock('IN')
     */
    public function actionImportCountSave()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['import-count']);
        }

        $filePath = (string) Yii::$app->request->post('filePath', '');
        $mainWarehouseId = (int) Yii::$app->request->post('main_warehouse_id', 0);
        $orderDate = trim((string) Yii::$app->request->post('order_date', ''));
        $note = trim((string) Yii::$app->request->post('note', 'ยอดยกมาจากการนับจริง'));

        if (!$filePath || !file_exists($filePath)) {
            Yii::$app->session->setFlash('error', 'ไม่พบไฟล์ที่อัปโหลด — กรุณาอัปโหลดใหม่');
            return $this->redirect(['import-count']);
        }

        $warehouse = Warehouse::findOne($mainWarehouseId);
        if (!$warehouse || $warehouse->warehouse_type !== 'MAIN') {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกคลังหลัก V2');
            return $this->redirect(['import-count']);
        }

        $rows = $this->parseCountCsv($filePath);
        $itemCodes = array_unique(array_filter(array_column($rows, 'item_code')));
        $existingItems = !empty($itemCodes)
            ? array_flip(StockItem::find()->select('code')->where(['code' => $itemCodes])->column())
            : [];

        $validRows = array_values(array_filter($rows, function ($r) use ($existingItems) {
            return isset($existingItems[$r['item_code']]) && $r['qty'] > 0;
        }));

        if (empty($validRows)) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการที่ถูกต้องในไฟล์');
            return $this->redirect(['import-count']);
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $dateNorm = $orderDate !== '' ? AppHelper::convertToGregorian($orderDate) : null;
            $orderDateFull = $dateNorm ? $dateNorm . ' 12:00:00' : date('Y-m-d H:i:s');

            $stockOrder = new StockOrder();
            $stockOrder->order_no = $this->generateInitialOrderNo();
            $stockOrder->order_type = StockOrder::ORDER_TYPE_IN;
            $stockOrder->source_type = StockOrder::SOURCE_INITIAL;
            $stockOrder->status = StockOrder::STATUS_CONFIRMED;
            $stockOrder->order_date = $orderDateFull;
            $stockOrder->main_warehouse_id = $mainWarehouseId;
            $stockOrder->ref = $note ?: 'ยอดยกมาจากการนับจริง';
            $stockOrder->data_json = [
                'imported_from_count' => [
                    'imported_at' => time(),
                    'imported_by' => Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id,
                    'source' => 'CSV physical count',
                    'rows' => count($validRows),
                ],
            ];

            if (!$stockOrder->save(false)) {
                throw new \RuntimeException('บันทึก stock_order ไม่สำเร็จ');
            }

            $defaultLot = 'INITIAL-' . ($dateNorm ?: date('Y-m-d'));
            $imported = 0;

            foreach ($validRows as $r) {
                $lotNumber = $r['lot_number'] !== '' ? $r['lot_number'] : ($defaultLot . '-' . $r['item_code']);

                $detail = new StockDetail();
                $detail->stock_order_id = $stockOrder->id;
                $detail->item_code = $r['item_code'];
                $detail->qty = $r['qty'];
                $detail->unit_price = $r['unit_price'] > 0 ? $r['unit_price'] : null;
                $detail->lot_number = $lotNumber;
                $detail->remain_qty = $r['qty']; // FIFO carryover
                if (!$detail->save(false)) {
                    throw new \RuntimeException('บันทึก stock_detail ไม่สำเร็จ: ' . $r['item_code']);
                }

                // อัปเดต stock_balance ทันที (เทียบ flow processReceive ของ V2)
                InventoryService::updateBalance($r['item_code'], $mainWarehouseId, $r['qty'], 'IN', $lotNumber);
                $imported++;
            }

            $tx->commit();
            @unlink($filePath);

            Yii::$app->session->setFlash('success',
                "นำเข้ายอดยกมาสำเร็จ {$imported} รายการ — สร้างใบ {$stockOrder->order_no} แล้ว stock_balance อัปเดตทันที"
            );
            return $this->redirect(Url::to(['/inventory-v2/receive/view', 'id' => $stockOrder->id]));
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error('[TransferToV2/ImportCount] ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'นำเข้าไม่สำเร็จ: ' . $e->getMessage());
            return $this->redirect(['import-count']);
        }
    }

    /**
     * Parse CSV ของผลนับจริง — คืน array ของ row ที่ normalize แล้ว
     */
    private function parseCountCsv($filePath)
    {
        $rows = [];
        if (($h = fopen($filePath, 'r')) === false) {
            return $rows;
        }
        $rowIdx = 0;
        // ข้าม BOM ถ้ามี
        $first = fread($h, 3);
        if ($first !== "\xEF\xBB\xBF") {
            rewind($h);
        }
        while (($data = fgetcsv($h, 2000, ',')) !== false) {
            $rowIdx++;
            if ($rowIdx === 1) {
                continue; // header
            }
            $data = array_pad($data, 5, '');
            $itemCode = trim((string) $data[0]);
            $lot = trim((string) $data[1]);
            $qty = (float) str_replace(',', '', (string) $data[2]);
            $price = (float) str_replace(',', '', (string) $data[3]);
            $note = trim((string) $data[4]);

            if ($itemCode === '' && $qty == 0.0) {
                continue;
            }

            $rows[] = [
                'row' => $rowIdx,
                'item_code' => $itemCode,
                'lot_number' => $lot,
                'qty' => $qty,
                'unit_price' => $price,
                'note' => $note,
            ];
        }
        fclose($h);
        return $rows;
    }

    private function generateInitialOrderNo()
    {
        $prefix = 'INIT-' . date('Ymd-His') . '-';
        do {
            $no = $prefix . mt_rand(100, 999);
        } while (StockOrder::findOne(['order_no' => $no]) !== null);
        return $no;
    }

    /**
     * Preview ประวัติการรับ-จ่ายของวัสดุชิ้นเดียวจาก V1 → V2 (history-only, ไม่แตะ stock_balance)
     * รับ stock_id (PK ของ Stock model V1) → derive asset_item + warehouse_id
     * คลังปลายทาง V2 = warehouse_id จาก stock_events V1 ตรงๆ (V1 และ V2 ใช้ warehouses ตารางเดียวกัน)
     */
    public function actionItemHistory($stock_id)
    {
        $stock = \app\modules\inventory\models\Stock::findOne((int) $stock_id);
        if (!$stock) {
            Yii::$app->session->setFlash('error', 'ไม่พบ Stock record id=' . $stock_id);
            return $this->redirect(['/inventory']);
        }

        list($parents, $childrenByParent, $existingV2Items, $existingOrderNos) =
            $this->fetchItemHistoryData($stock->asset_item, (int) $stock->warehouse_id);

        $v2Item = StockItem::findOne(['item_code' => $stock->asset_item]);

        // โหลด warehouse master เพื่อ validate + แสดงชื่อ
        $warehouseIds = [];
        foreach ($parents as $p) {
            if (!empty($p['warehouse_id'])) $warehouseIds[(int) $p['warehouse_id']] = true;
            if (!empty($p['from_warehouse_id'])) $warehouseIds[(int) $p['from_warehouse_id']] = true;
        }
        $warehouseMap = [];
        if (!empty($warehouseIds)) {
            $warehouseMap = ArrayHelper::map(
                Warehouse::find()
                    ->select(['id', 'warehouse_name', 'warehouse_type'])
                    ->where(['id' => array_keys($warehouseIds)])
                    ->asArray()->all(),
                'id',
                function ($w) { return $w; }
            );
        }

        // map สถานะการย้าย ต่อแต่ละใบ
        $rows = [];
        $stats = ['willMigrate' => 0, 'duplicate' => 0, 'noMatch' => 0, 'noWarehouse' => 0, 'totalLines' => 0];
        foreach ($parents as $p) {
            $pid = (int) $p['id'];
            $lines = $childrenByParent[$pid] ?? [];
            $matched = 0;
            $skippedCodes = [];
            foreach ($lines as $l) {
                $code = trim((string) ($l['asset_item'] ?? ''));
                if ($code !== '' && isset($existingV2Items[$code])) {
                    $matched++;
                } elseif ($code !== '') {
                    $skippedCodes[$code] = true;
                }
            }
            $mainWhId = (int) $p['warehouse_id'];
            $hasWarehouse = $mainWhId > 0 && isset($warehouseMap[$mainWhId]);
            $isDuplicate = isset($existingOrderNos[$p['code']]);
            $isTransferable = !$isDuplicate && $matched > 0 && $hasWarehouse;

            if ($isDuplicate) $stats['duplicate']++;
            elseif (!$hasWarehouse) $stats['noWarehouse']++;
            elseif ($isTransferable) $stats['willMigrate']++;
            else $stats['noMatch']++;
            $stats['totalLines'] += count($lines);

            $rows[] = [
                'id' => $pid,
                'code' => $p['code'],
                'transaction_type' => $p['transaction_type'],
                'movement_date' => $p['movement_date'] ?: $p['created_at'],
                'main_warehouse_id' => $mainWhId,
                'sub_warehouse_id' => (int) ($p['from_warehouse_id'] ?? 0),
                'lines' => $lines,
                'matched' => $matched,
                'skipped_codes' => array_keys($skippedCodes),
                'has_warehouse' => $hasWarehouse,
                'is_duplicate' => $isDuplicate,
                'is_transferable' => $isTransferable,
            ];
        }

        return $this->render('item-history', [
            'stock' => $stock,
            'v2Item' => $v2Item,
            'rows' => $rows,
            'stats' => $stats,
            'warehouseMap' => $warehouseMap,
        ]);
    }

    /**
     * Confirm ย้ายประวัติ — สร้าง stock_order + stock_detail ใน V2 ทุกใบที่เลือก
     * History-only: ไม่เรียก updateBalance / adjustBalance
     * คลังปลายทาง derive จาก parent.warehouse_id ของแต่ละใบ V1 (auto-map ไม่ต้องเลือก)
     */
    public function actionItemHistorySave()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['/inventory']);
        }

        $stockId = (int) Yii::$app->request->post('stock_id', 0);
        $selectedIds = array_values(array_filter(array_map('intval', (array) Yii::$app->request->post('order_ids', []))));

        $stock = \app\modules\inventory\models\Stock::findOne($stockId);
        if (!$stock) {
            Yii::$app->session->setFlash('error', 'ไม่พบ Stock record');
            return $this->redirect(['/inventory']);
        }
        if (empty($selectedIds)) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกใบที่จะย้ายอย่างน้อย 1 ใบ');
            return $this->redirect(['item-history', 'stock_id' => $stockId]);
        }

        list($parents, $childrenByParent, $existingV2Items, $existingOrderNos) =
            $this->fetchItemHistoryData($stock->asset_item, (int) $stock->warehouse_id);

        // โหลด warehouse id ทั้งหมดที่ต้องใช้
        $warehouseIds = [];
        foreach ($parents as $p) {
            if (!empty($p['warehouse_id'])) $warehouseIds[(int) $p['warehouse_id']] = true;
        }
        $existingWarehouses = !empty($warehouseIds)
            ? array_flip(Warehouse::find()->select('id')->where(['id' => array_keys($warehouseIds)])->column())
            : [];

        $migrator = new TransferV2HistoryMigrator(Yii::$app->user->isGuest ? null : Yii::$app->user->id);
        $summary = $migrator->migrateOrders(
            $parents,
            $childrenByParent,
            $existingV2Items,
            $existingOrderNos,
            $existingWarehouses,
            $selectedIds
        );
        $createdCount = $summary['createdCount'];
        $skippedDuplicate = $summary['skippedDuplicate'];
        $skippedNoItems = $summary['skippedNoItems'];
        $skippedNoWarehouse = $summary['skippedNoWarehouse'];
        $errors = $summary['errors'];
        $totalLinesMigrated = $summary['totalLinesMigrated'];
        $totalLinesSkipped = $summary['totalLinesSkipped'];
        $partialReports = $summary['partialReports'];
        $missingItemCodes = $summary['missingItemCodes'];

        $messages = [
            'ย้ายประวัติสำเร็จ ' . $createdCount . ' ใบ · ' . $totalLinesMigrated . ' รายการ (history-only — stock_balance ไม่กระทบ)',
        ];
        if ($totalLinesSkipped > 0) {
            $messages[] = 'มี ' . $totalLinesSkipped . ' line ถูกข้าม (ดูรายละเอียดใบที่ย้ายไม่ครบ)';
        }
        if ($partialReports) $messages[] = 'ใบที่ย้ายไม่ครบ: ' . implode(' · ', $partialReports);
        if ($skippedDuplicate) $messages[] = 'ข้าม (order_no ซ้ำใน V2): ' . implode(', ', $skippedDuplicate);
        if ($skippedNoItems) $messages[] = 'ข้าม (ไม่มี line ที่ map ได้): ' . implode(', ', $skippedNoItems);
        if ($skippedNoWarehouse) $messages[] = 'ข้าม (ไม่พบ warehouse_id ใน V2): ' . implode(', ', $skippedNoWarehouse);
        if ($missingItemCodes) $messages[] = 'item_code ที่ขาดใน V2 master: ' . implode(', ', array_keys($missingItemCodes));
        if ($errors) $messages[] = 'error: ' . implode(', ', $errors);

        Yii::$app->session->setFlash($createdCount > 0 ? 'success' : 'error', implode('<br>', $messages));
        return $this->redirect(['item-history', 'stock_id' => $stockId]);
    }

    /**
     * Bulk: ย้ายประวัติ "ทุก asset_item ในคลังที่เลือก" จาก V1 → V2 ในคราวเดียว (history-only)
     * รับ warehouse_id → ย้ายทุกใบ status=success ที่ warehouse_id ของ V1 record ตรงกัน
     * Idempotent: ใบที่ order_no ซ้ำใน V2 จะถูก skip อัตโนมัติ (รันซ้ำได้)
     */
    public function actionBulkItemHistorySave()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['/inventory']);
        }
        $warehouseId = (int) Yii::$app->request->post('warehouse_id', 0);
        if ($warehouseId <= 0) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกคลัง');
            return $this->redirect(['bulk-item-history']);
        }
        $warehouse = Warehouse::findOne($warehouseId);
        if (!$warehouse) {
            Yii::$app->session->setFlash('error', 'ไม่พบคลัง id=' . $warehouseId . ' ใน V2');
            return $this->redirect(['bulk-item-history']);
        }

        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $migrator = new TransferV2HistoryMigrator(Yii::$app->user->isGuest ? null : Yii::$app->user->id);
        list($parents, $childrenByParent, $existingV2Items, $existingOrderNos) =
            $migrator->fetchHistoryDataByWarehouse($warehouseId);

        $existingWarehouses = [$warehouseId => true];
        $selectedIds = array_map('intval', array_column($parents, 'id'));

        $summary = $migrator->migrateOrders(
            $parents,
            $childrenByParent,
            $existingV2Items,
            $existingOrderNos,
            $existingWarehouses,
            $selectedIds
        );

        $messages = [
            'ย้ายประวัติทั้งคลัง "' . Html::encode($warehouse->warehouse_name) . '" สำเร็จ '
                . $summary['createdCount'] . ' ใบ · ' . $summary['totalLinesMigrated']
                . ' รายการ (history-only — stock_balance ไม่กระทบ)',
        ];
        if ($summary['totalLinesSkipped'] > 0) {
            $messages[] = 'มี ' . $summary['totalLinesSkipped'] . ' line ถูกข้าม';
        }
        if (!empty($summary['skippedDuplicate'])) {
            $messages[] = 'ข้าม (order_no ซ้ำใน V2): ' . count($summary['skippedDuplicate']) . ' ใบ';
        }
        if (!empty($summary['skippedNoItems'])) {
            $messages[] = 'ข้าม (ไม่มี line ที่ map ได้): ' . count($summary['skippedNoItems']) . ' ใบ';
        }
        if (!empty($summary['skippedNoWarehouse'])) {
            $messages[] = 'ข้าม (ไม่ตรง warehouse): ' . count($summary['skippedNoWarehouse']) . ' ใบ';
        }
        if (!empty($summary['missingItemCodes'])) {
            $codes = array_keys($summary['missingItemCodes']);
            $sample = array_slice($codes, 0, 20);
            $more = count($codes) > 20 ? ' (+อีก ' . (count($codes) - 20) . ')' : '';
            $messages[] = 'item_code ที่ขาดใน V2 master: ' . implode(', ', $sample) . $more;
        }
        if (!empty($summary['errors'])) {
            $messages[] = 'error: ' . count($summary['errors']) . ' ใบ — โปรดดู log';
        }

        Yii::$app->session->setFlash(
            $summary['createdCount'] > 0 ? 'success' : 'error',
            implode('<br>', $messages)
        );
        return $this->redirect(['bulk-item-history', 'warehouse_id' => $warehouseId]);
    }

    /**
     * หน้าเลือกคลังเพื่อ bulk migrate (entry จาก dashboard)
     */
    public function actionBulkItemHistory($warehouse_id = null)
    {
        $warehouses = Warehouse::find()
            ->select(['id', 'warehouse_name', 'warehouse_type'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->asArray()->all();

        return $this->render('bulk-item-history', [
            'warehouses' => $warehouses,
            'selectedWarehouseId' => $warehouse_id ? (int) $warehouse_id : null,
        ]);
    }

    /**
     * รวม query หาประวัติของ "ใบเบิก/รับเข้า" ที่มี item ตัวที่กำหนดเป็นรายการอยู่
     * Whole-document mode: ดึง "ทุก line ของใบ" ไม่ใช่แค่ line ของ asset_item ที่เปิดดู
     *   - Step 1: หา parent_ids ที่มี item ตัวนี้
     *   - Step 2: หา parent records
     *   - Step 3: ดึง "ทุก line" ของ parent (ไม่ filter ด้วย asset_item)
     * คืน [parents, childrenByParent, existingV2Items, existingOrderNos]
     */
    private function fetchItemHistoryData($assetItem, $warehouseId)
    {
        // Step 1: หา parent_ids ที่ "มี item นี้เป็น line"
        $parentIdsForItem = (new \yii\db\Query())
            ->select('category_id')
            ->distinct()
            ->from(StockEvent::tableName())
            ->where([
                'name' => 'order_item',
                'asset_item' => $assetItem,
                'warehouse_id' => $warehouseId,
                'order_status' => 'success',
            ])
            ->column();

        // Step 2: หา parent records (header)
        $parents = [];
        if (!empty($parentIdsForItem)) {
            $parents = (new \yii\db\Query())
                ->select(['id', 'code', 'transaction_type', 'movement_date', 'warehouse_id', 'from_warehouse_id', 'order_status', 'created_at'])
                ->from(StockEvent::tableName())
                ->where([
                    'name' => 'order',
                    'id' => $parentIdsForItem,
                    'order_status' => 'success',
                ])
                ->andWhere(['in', 'transaction_type', ['IN', 'OUT']])
                ->orderBy(['movement_date' => SORT_ASC, 'id' => SORT_ASC])
                ->all();
        }

        // Step 3: ดึง "ทุก line" ของ parent เหล่านี้ (ไม่ filter ด้วย asset_item)
        $children = [];
        $allParentIds = array_column($parents, 'id');
        if (!empty($allParentIds)) {
            $children = (new \yii\db\Query())
                ->select(['t.id', 't.category_id AS parent_id', 't.asset_item', 't.qty', 't.unit_price', 't.lot_number'])
                ->from(['t' => StockEvent::tableName()])
                ->where([
                    't.name' => 'order_item',
                    't.category_id' => $allParentIds,
                    't.order_status' => 'success',
                ])
                ->all();
        }

        $childrenByParent = [];
        foreach ($children as $c) {
            $childrenByParent[(int) $c['parent_id']][] = $c;
        }

        $itemCodes = array_unique(array_filter(array_column($children, 'asset_item')));
        // ใช้ raw query บน categorise — ไม่ filter group_id เพื่อให้รับ item จาก group อื่น
        // (V1 อาจมี item ที่ name='asset_item' แต่ group_id≠'MATER' เช่น MED, EQUIP)
        // history-only mode: V2 แสดง code ได้ แม้ item_name อาจว่างถ้าอยู่นอก scope MATER
        $existingV2Items = !empty($itemCodes)
            ? array_flip((new \yii\db\Query())
                ->select('code')
                ->distinct()
                ->from('categorise')
                ->where(['name' => 'asset_item', 'code' => $itemCodes])
                ->column())
            : [];

        $existingOrderNos = !empty($parents)
            ? array_flip(StockOrder::find()->select('order_no')->where(['order_no' => array_column($parents, 'code')])->column())
            : [];

        return [$parents, $childrenByParent, $existingV2Items, $existingOrderNos];
    }
}
