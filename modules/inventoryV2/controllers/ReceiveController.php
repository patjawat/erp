<?php

namespace app\modules\inventoryV2\controllers;

use app\components\AppHelper;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\components\InventoryService;
use app\modules\sm\models\Vendor;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockOrderSearch;
use app\modules\purchase\models\Order as PurchaseOrder;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * StockOrderController implements the CRUD actions for StockOrder model.
 */
class ReceiveController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'cancel' => ['POST'],
                        'dismiss-po' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all StockOrder models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockOrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['order_type' => 'IN']);
        $dataProvider->query->andWhere(['sub_warehouse_id' => null]);
        $dataProvider->query->with(['mainWarehouse', 'stockDetails', 'stockDetails.item', 'stockDetails.item.categoryType']);

        // ไม่ใช่ admin: จำกัดเฉพาะคลังที่ถูกกำหนดเป็นผู้รับผิดชอบ (warehouse/update > ผู้รับผิดชอบคลัง)
        $isAdmin = \Yii::$app->user->can('admin');
        $accessibleWarehouses = Warehouse::findMainWarehousesForReceive();
        $accessibleWarehouseIds = ArrayHelper::getColumn($accessibleWarehouses, 'id');
        if (!$isAdmin) {
            $dataProvider->query->andWhere(['main_warehouse_id' => $accessibleWarehouseIds]);
        }

        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        if ($start !== null && $start !== '') {
            $dataProvider->query->andWhere(['>=', 'order_date', $start . ' 00:00:00']);
        }
        if ($end !== null && $end !== '') {
            $dataProvider->query->andWhere(['<=', 'order_date', $end . ' 23:59:59']);
        }

        $dataProvider->sort->defaultOrder = ['order_date' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

        $statusSummaryQuery = StockOrder::find()
            ->where(['order_type' => 'IN'])
            ->andWhere(['sub_warehouse_id' => null]);
        if (!$isAdmin) {
            $statusSummaryQuery->andWhere(['main_warehouse_id' => $accessibleWarehouseIds]);
        }
        $statusSummary = $statusSummaryQuery
            ->select(['status', 'COUNT(*) as cnt'])
            ->groupBy('status')
            ->asArray()
            ->all();
        $statusSummaryMap = array_column($statusSummary, 'cnt', 'status');

        $warehouses = $isAdmin
            ? ['' => 'ทุกคลัง'] + ArrayHelper::map(
                Warehouse::find()
                    ->where(['warehouse_type' => 'MAIN'])
                    ->andWhere(['or', ['delete' => null], ['delete' => '']])
                    ->orderBy('warehouse_name')
                    ->all(),
                'id',
                'warehouse_name'
            )
            : ['' => 'ทุกคลัง'] + ArrayHelper::map($accessibleWarehouses, 'id', 'warehouse_name');

        // รวมยอดเงินทั้งหมด (ตามตัวกรองปัจจุบัน)
        $totalAmountQuery = StockOrder::find()->select('id')->where(['order_type' => 'IN'])->andWhere(['sub_warehouse_id' => null]);
        if (!$isAdmin) {
            $totalAmountQuery->andWhere(['main_warehouse_id' => $accessibleWarehouseIds]);
        }
        if ($start !== null && $start !== '') {
            $totalAmountQuery->andWhere(['>=', 'order_date', $start . ' 00:00:00']);
        }
        if ($end !== null && $end !== '') {
            $totalAmountQuery->andWhere(['<=', 'order_date', $end . ' 23:59:59']);
        }
        $totalAmountQuery->andFilterWhere(['main_warehouse_id' => $searchModel->main_warehouse_id]);
        if ($searchModel->order_no !== null && $searchModel->order_no !== '') {
            $totalAmountQuery->andWhere(['like', 'order_no', $searchModel->order_no]);
        }
        if ($searchModel->status !== null && $searchModel->status !== '') {
            $totalAmountQuery->andWhere(['status' => $searchModel->status]);
        }
        if ($searchModel->category_id !== null && $searchModel->category_id !== '') {
            $categorySub = (new \yii\db\Query())
                ->select('sd.stock_order_id')
                ->from(['sd' => 'stock_detail'])
                ->innerJoin(
                    ['c' => 'categorise'],
                    "c.code = sd.item_code AND c.name = 'asset_item' AND c.group_id = 'MATER'"
                )
                ->where(['c.category_id' => (string) $searchModel->category_id]);
            $totalAmountQuery->andWhere(['id' => $categorySub]);
        }
        $totalFromSet = $searchModel->total_from !== null && $searchModel->total_from !== '';
        $totalToSet = $searchModel->total_to !== null && $searchModel->total_to !== '';
        if ($totalFromSet || $totalToSet) {
            $havingSub = (new \yii\db\Query())
                ->select('stock_order_id')
                ->from(StockDetail::tableName())
                ->groupBy('stock_order_id');
            $havings = [];
            if ($totalFromSet) {
                $havings[] = ['>=', new Expression('SUM(qty * COALESCE(unit_price, 0))'), (float) $searchModel->total_from];
            }
            if ($totalToSet) {
                $havings[] = ['<=', new Expression('SUM(qty * COALESCE(unit_price, 0))'), (float) $searchModel->total_to];
            }
            if (!empty($havings)) {
                $havingSub->having(array_merge(['and'], $havings));
            }
            $totalAmountQuery->andWhere(['id' => $havingSub]);
        }
        $totalAmount = (float) StockDetail::find()
            ->where(['stock_order_id' => $totalAmountQuery])
            ->sum(new Expression('qty * COALESCE(unit_price, 0)'));

        $fullItemTypeList = StockItem::ListStockItemType();
        if ($isAdmin) {
            $listItemType = ['' => 'ทุกประเภท'] + $fullItemTypeList;
        } else {
            $allowedCodes = [];
            $unrestricted = false;
            foreach ($accessibleWarehouses as $w) {
                $codes = $w->getAllowedItemTypeCodes();
                if (empty($codes)) {
                    $unrestricted = true;
                    break;
                }
                $allowedCodes = array_merge($allowedCodes, $codes);
            }
            $listItemType = $unrestricted
                ? ['' => 'ทุกประเภท'] + $fullItemTypeList
                : ['' => 'ทุกประเภท'] + array_intersect_key($fullItemTypeList, array_flip(array_unique($allowedCodes)));
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'statusSummary' => $statusSummaryMap,
            'warehouses' => $warehouses,
            'totalAmount' => $totalAmount,
            'listItemType' => $listItemType,
        ]);
    }


    /**
     * Displays a single StockOrder model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * ส่งออกใบรับเข้า 1 ใบเป็น Excel (รายละเอียดหัวเอกสาร + รายการพัสดุ)
     */
    public function actionExportExcel($id)
    {
        $model = $this->findModel($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ใบรับเข้า');

        // --- หัวเอกสาร ---
        $sheet->setCellValue('A1', 'ใบรับเข้าวัสดุ');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $orderDateThai = '-';
        if (!empty($model->order_date)) {
            $ts = is_numeric($model->order_date) ? (int) $model->order_date : strtotime($model->order_date);
            $orderDateThai = \app\components\ThaiDateHelper::formatThaiDate($model->order_date) . ' ' . date('H:i', $ts);
        }

        $sheet->setCellValue('A3', 'เลขที่ใบรับเข้า:');
        $sheet->setCellValue('B3', $model->order_no);
        $sheet->setCellValue('A4', 'วันที่รับเข้า:');
        $sheet->setCellValue('B4', $orderDateThai);
        $sheet->setCellValue('A5', 'คลังที่รับเข้า:');
        $sheet->setCellValue('B5', $model->mainWarehouse ? $model->mainWarehouse->warehouse_name : '-');
        $sheet->setCellValue('A6', 'สถานะ:');
        $sheet->setCellValue('B6', $model->status);
        $sheet->getStyle('A3:A6')->getFont()->setBold(true);

        // --- ตารางรายการ ---
        $headerRow = 8;
        $headers = ['ลำดับ', 'รหัสพัสดุ', 'ชื่อพัสดุ', 'เลข Lot', 'วันหมดอายุ', 'จำนวน', 'ราคา/หน่วย (บาท)', 'รวม (บาท)'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $headerRow, $h);
            $col++;
        }
        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $rowNum = $headerRow + 1;
        $grandTotal = 0;
        foreach ($model->stockDetails as $index => $item) {
            $qty = (float) $item->qty;
            $price = (float) ($item->unit_price ?? 0);
            $total = $qty * $price;
            $grandTotal += $total;
            $itemName = $item->item ? $item->item->item_name : $item->item_code;

            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $item->item_code);
            $sheet->setCellValue('C' . $rowNum, $itemName);
            $sheet->setCellValue('D' . $rowNum, $item->lot_number ?: '-');
            $sheet->setCellValue('E' . $rowNum, $item->expiry_date ?: '-');
            $sheet->setCellValue('F' . $rowNum, $qty);
            $sheet->setCellValue('G' . $rowNum, $price);
            $sheet->setCellValue('H' . $rowNum, $total);
            $rowNum++;
        }

        // แถวรวมยอดท้ายตาราง
        $sheet->setCellValue('A' . $rowNum, 'รวมยอดเงินทั้งหมด');
        $sheet->mergeCells('A' . $rowNum . ':G' . $rowNum);
        $sheet->setCellValue('H' . $rowNum, $grandTotal);
        $sheet->getStyle('A' . $rowNum . ':H' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // จัด format ตัวเลข + border + width
        $dataLastRow = $rowNum;
        $sheet->getStyle('F' . ($headerRow + 1) . ':H' . $dataLastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $dataLastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filenameUtf8 = 'ใบรับเข้า-' . $model->order_no . '-' . date('Ymd-His') . '.xlsx';
        $filenameAscii = 'receive-' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $model->order_no) . '-' . date('Ymd-His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filenameAscii . '"; filename*=UTF-8\'\'' . rawurlencode($filenameUtf8));
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * ส่งออกรายการใบรับเข้าทั้งหมดตามตัวกรองปัจจุบัน (ไม่แบ่งหน้า) เป็น Excel แบบสรุป 1 แถวต่อ 1 ใบ
     */
    public function actionExportExcelList()
    {
        $searchModel = new StockOrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['order_type' => 'IN']);
        $dataProvider->query->andWhere(['sub_warehouse_id' => null]);
        $dataProvider->query->with(['mainWarehouse', 'stockDetails', 'stockDetails.item', 'stockDetails.item.categoryType']);

        // ไม่ใช่ admin: จำกัดเฉพาะคลังที่ถูกกำหนดเป็นผู้รับผิดชอบ เหมือนหน้ารายการ
        if (!\Yii::$app->user->can('admin')) {
            $accessibleWarehouseIds = ArrayHelper::getColumn(Warehouse::findMainWarehousesForReceive(), 'id');
            $dataProvider->query->andWhere(['main_warehouse_id' => $accessibleWarehouseIds]);
        }

        $start = AppHelper::convertToGregorian($searchModel->date_start);
        $end = AppHelper::convertToGregorian($searchModel->date_end);
        if ($start !== null && $start !== '') {
            $dataProvider->query->andWhere(['>=', 'order_date', $start . ' 00:00:00']);
        }
        if ($end !== null && $end !== '') {
            $dataProvider->query->andWhere(['<=', 'order_date', $end . ' 23:59:59']);
        }

        $dataProvider->sort->defaultOrder = ['order_date' => SORT_DESC];
        $dataProvider->pagination = false;

        $models = $dataProvider->getModels();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายการใบรับเข้า');

        $sheet->setCellValue('A1', 'รายการใบรับเข้าวัสดุ');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A2', 'ส่งออกเมื่อ: ' . \app\components\ThaiDateHelper::formatThaiDate(date('Y-m-d')) . ' ' . date('H:i'));
        $sheet->mergeCells('A2:H2');

        $headerRow = 4;
        $headers = ['ลำดับ', 'เลขที่เอกสาร', 'วันที่', 'คลัง', 'ประเภทวัสดุ', 'จำนวนรายการ', 'มูลค่ารับเข้า (บาท)', 'สถานะ'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $headerRow, $h);
            $col++;
        }
        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $statusLabels = [
            'DRAFT' => 'ร่าง',
            'CONFIRMED' => 'บันทึกแล้ว',
            'CANCELLED' => 'ยกเลิก',
        ];

        $rowNum = $headerRow + 1;
        $grandTotal = 0;
        foreach ($models as $index => $item) {
            $rowTotal = 0;
            $typeNames = [];
            foreach ($item->stockDetails as $d) {
                $rowTotal += (float) $d->qty * (float) ($d->unit_price ?? 0);
                if ($d->item && $d->item->categoryType) {
                    $typeNames[$d->item->categoryType->code] = $d->item->categoryType->title;
                }
            }
            $grandTotal += $rowTotal;

            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $item->order_no);
            $sheet->setCellValue('C' . $rowNum, $item->order_date ? \app\components\ThaiDateHelper::formatThaiDate($item->order_date) : '-');
            $sheet->setCellValue('D' . $rowNum, $item->mainWarehouse ? $item->mainWarehouse->warehouse_name : '-');
            $sheet->setCellValue('E' . $rowNum, !empty($typeNames) ? implode(', ', $typeNames) : '-');
            $sheet->setCellValue('F' . $rowNum, count($item->stockDetails));
            $sheet->setCellValue('G' . $rowNum, $rowTotal);
            $sheet->setCellValue('H' . $rowNum, $statusLabels[$item->status] ?? $item->status);
            $rowNum++;
        }

        $sheet->setCellValue('A' . $rowNum, 'รวมยอดเงินทั้งหมด');
        $sheet->mergeCells('A' . $rowNum . ':F' . $rowNum);
        $sheet->setCellValue('G' . $rowNum, $grandTotal);
        $sheet->getStyle('A' . $rowNum . ':H' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $dataLastRow = $rowNum;
        $sheet->getStyle('G' . ($headerRow + 1) . ':G' . $dataLastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $dataLastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filenameUtf8 = 'รายการใบรับเข้า-' . date('Ymd-His') . '.xlsx';
        $filenameAscii = 'receive-list-' . date('Ymd-His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filenameAscii . '"; filename*=UTF-8\'\'' . rawurlencode($filenameUtf8));
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * รายการใบสั่งซื้อที่รอรับเข้าคลัง (สำหรับ picker ในหน้าสร้างใบรับเข้า)
     * กรองตามประเภทวัสดุที่คลังที่เลือกอนุญาตรับ + ค้นหาด้วยเลขที่ PO/PQ/PR/ผู้ขาย/ประเภท/จำนวนเงิน
     *
     * หมายเหตุ: ค้นหาด้วยการ match กับค่าที่แสดงจริงในรายการ (ผู้ขาย/ประเภท/จำนวนเงิน คำนวณจาก relation
     * ไม่ใช่ data_json['vendor_name'] ที่หลายใบเป็นแค่ '-' เพราะไม่ได้บันทึกไว้) — ทำใน PHP หลัง query
     * เนื่องจากปริมาณใบสั่งซื้อที่รอรับเข้าต่อคลังโดยทั่วไปมีไม่มาก จึงคัดกรองหลัง fetch ได้โดยไม่กระทบ performance
     * @return array
     */
    public function actionPendingPo()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $warehouseId = (int) $this->request->get('warehouse_id');
        $q = trim((string) $this->request->get('q', ''));
        if (!$warehouseId) {
            return ['results' => []];
        }
        $warehouse = Warehouse::findOne($warehouseId);
        if (!$warehouse) {
            return ['results' => []];
        }
        $allowedCodes = $warehouse->getAllowedItemTypeCodes();

        $query = PurchaseOrder::find()->where(['name' => 'order', 'status' => 5]);
        if (!empty($allowedCodes)) {
            $query->andWhere(['category_id' => $allowedCodes]);
        }
        $orders = $query->orderBy(['id' => SORT_DESC])->limit(500)->all();

        // ใบสั่งซื้อที่มีใบรับเข้าคลังผูกอยู่แล้ว ต้องไม่แสดงซ้ำ แม้สถานะจะถูกดึงกลับมาเป็น 5
        // (เกิดได้เมื่อไปแก้ไขใบตรวจรับของ PO ที่รับเข้าคลังไปแล้ว)
        $receivedPoIds = $this->getReceivedPoOrderIds();

        $results = [];
        foreach ($orders as $order) {
            if (isset($receivedPoIds[(int) $order->id])) {
                continue;
            }
            $vendorTitle = $order->vendor ? $order->vendor->title : ($order->vendor_name ?: '-');
            $assetTypeTitle = $order->assetType ? $order->assetType->title : '-';
            $totalAmount = (float) $order->calculateVAT()['priceAfterVAT'];

            if ($q !== '') {
                $haystack = implode(' ', [
                    $order->po_number,
                    $order->pq_number,
                    $order->pr_number,
                    $vendorTitle,
                    $assetTypeTitle,
                    number_format($totalAmount, 2),
                ]);
                if (mb_stripos($haystack, $q) === false) {
                    continue;
                }
            }

            $results[] = [
                'id' => $order->id,
                'po_number' => $order->po_number,
                'pq_number' => $order->pq_number,
                'vendor_title' => $vendorTitle,
                'asset_type_title' => $assetTypeTitle,
                'item_count' => count($order->ListOrderItems()),
                'total_amount' => $totalAmount,
            ];
            if (count($results) >= 30) {
                break;
            }
        }

        return ['results' => $results];
    }

    /**
     * รายละเอียดใบสั่งซื้อที่เลือก (สำหรับ auto-fill ในหน้าสร้างใบรับเข้า)
     * แปลงรายการจาก orders(order_item, asset_item) ให้อยู่ในรูปแบบเดียวกับที่ตารางรายการฝั่ง _form.php ใช้ (เหมือน import CSV)
     * @param int $id orders.id
     * @return array
     */
    public function actionPendingPoItems($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $order = PurchaseOrder::findOne(['id' => $id, 'name' => 'order']);
        if (!$order) {
            return ['success' => false, 'message' => 'ไม่พบใบสั่งซื้อ'];
        }
        if ((int) $order->status !== 5) {
            return ['success' => false, 'message' => 'ใบสั่งซื้อนี้ถูกรับเข้าคลังไปแล้ว หรือไม่อยู่ในสถานะรอรับเข้า'];
        }
        $receivedPoIds = $this->getReceivedPoOrderIds();
        if (isset($receivedPoIds[(int) $order->id])) {
            return [
                'success' => false,
                'message' => 'ใบสั่งซื้อนี้มีใบรับเข้าคลังอยู่แล้ว (' . implode(', ', $receivedPoIds[(int) $order->id]) . ') ไม่สามารถรับเข้าซ้ำได้',
            ];
        }

        $warehouseId = (int) $this->request->get('warehouse_id');
        $warehouse = $warehouseId ? Warehouse::findOne($warehouseId) : null;

        $contactId = null;
        if (!empty($order->vendor_id)) {
            $vendor = Vendor::findOne(['code' => $order->vendor_id, 'name' => 'vendor']);
            $contactId = $vendor ? $vendor->id : null;
        }

        $items = [];
        $skipped = [];
        $categoryId = null; // ประเภทวัสดุ (asset_type code) จากรายการแรกที่รับเข้าได้ — ใช้ auto-select dropdown ประเภทวัสดุ
        foreach ($order->ListOrderItems() as $orderItem) {
            $stockItem = StockItem::findOne(['item_code' => $orderItem->asset_item]);
            if (!$stockItem) {
                $skipped[] = ['name' => $orderItem->asset_item, 'reason' => 'ไม่พบพัสดุนี้ในระบบคลัง'];
                continue;
            }
            if ($warehouse && !$warehouse->allowsItemType($stockItem->category_id)) {
                $skipped[] = ['name' => $stockItem->item_name, 'reason' => 'คลังที่เลือกไม่รับพัสดุประเภทนี้'];
                continue;
            }
            if ($categoryId === null && $stockItem->category_id !== null && $stockItem->category_id !== '') {
                $categoryId = (string) $stockItem->category_id;
            }
            $imgUrl = '';
            if (!empty($stockItem->ref)) {
                $upload = Uploads::find()->where(['ref' => $stockItem->ref])->one();
                if ($upload) {
                    $imgUrl = FileManagerHelper::getImg($upload->id);
                }
            }
            $items[] = [
                'item_code' => $stockItem->item_code,
                'item_name' => $stockItem->item_name,
                'unit_name' => $stockItem->unitName ?: '-',
                'category_title' => $stockItem->categoryType ? $stockItem->categoryType->title : '-',
                'image_url' => $imgUrl,
                'qty' => (float) $orderItem->qty,
                'unit_price' => (float) $orderItem->price,
                'lot_number' => '',
                'expiry_date' => '',
            ];
        }

        return [
            'success' => true,
            'order_id' => $order->id,
            'po_number' => $order->po_number,
            'delivery_note_no' => isset($order->data_json['gr_number']) ? $order->data_json['gr_number'] : '',
            'contact_id' => $contactId,
            'category_id' => $categoryId,
            'items' => $items,
            'skipped_items' => $skipped,
        ];
    }

    /**
     * Creates a new StockOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new StockOrder();
        $model->order_type = 'IN';
        $model->status = 'CONFIRMED';

        if ($this->request->isPost) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $db = \Yii::$app->db;
            $transaction = $db->beginTransaction();

            try {
                $isDraft = (bool) $this->request->post('save_as_draft', false);
                if ($isDraft) {
                    $model->status = 'DRAFT';
                }

                $model->load($this->request->post());
                // เลขที่ใบรับเข้าเว้นว่างได้เสมอ (รวมถึงรับจาก PO) — ถ้าว่างระบบออกเลข RCV- ให้อัตโนมัติ
                if (trim((string) $model->order_no) === '') {
                    $model->order_no = $this->generateReceiveOrderNo();
                }
                // แปลงวันที่จาก พ.ศ. (ไทย) เป็น ค.ศ. ก่อนบันทึก
                if (!empty($model->order_date)) {
                    $model->order_date = AppHelper::convertToGregorian($model->order_date);
                    if ($model->order_date !== null) {
                        $model->order_date .= ' ' . date('H:i:s');
                    }
                }

                // เลขที่ส่งสินค้า + ลิงก์ใบสั่งซื้อต้นทาง (ถ้ารับเข้าจาก PO picker) — เก็บใน data_json
                $poOrderIdRaw = $this->request->post('po_order_id');
                $poOrderId = ($poOrderIdRaw !== null && $poOrderIdRaw !== '') ? (int) $poOrderIdRaw : null;
                $json = is_array($model->data_json) ? $model->data_json : (is_string($model->data_json) ? (json_decode($model->data_json, true) ?: []) : []);
                $json['delivery_note_no'] = trim((string) $this->request->post('delivery_note_no', ''));
                if ($poOrderId) {
                    $json['po_order_id'] = $poOrderId;
                }
                $model->data_json = $json;
                if (!$isDraft) {
                    $model->setStockPostedAt(time());
                }

                $details = $this->request->post('StockDetail', []);
                if (!is_array($details)) {
                    throw new \InvalidArgumentException('รูปแบบรายการวัสดุไม่ถูกต้อง');
                }
                $details = $this->normalizeDetailExpiryDates($details);
                if (!empty($details) && $model->main_warehouse_id) {
                    $warehouse = Warehouse::findOne($model->main_warehouse_id);
                    if ($warehouse) {
                        foreach ($details as $i => $data) {
                            $itemCode = $data['item_code'] ?? null;
                            if (!$itemCode) continue;
                            $item = StockItem::findOne(['item_code' => $itemCode]);
                            if ($item && !$warehouse->allowsItemType($item->category_id)) {
                                throw new \Exception("พัสดุ [{$item->item_name}] ไม่สามารถรับเข้าคลังนี้ได้ เนื่องจากคลังกำหนดเฉพาะประเภทที่รับเข้า (รายการที่ " . ($i + 1) . ")");
                            }
                        }
                    }
                }
                if ($model->save()) {

                    // บันทึกฉบับร่าง: อนุญาตให้ไม่มีรายการ; validate จำนวน/ราคา/หน่วย; บันทึกรายการที่ครบ; ไม่อัปเดตสต็อก
                    if ($isDraft) {
                        foreach ($details as $i => $data) {
                            $rowNum = $i + 1;
                            $itemCode = trim($data['item_code'] ?? '');
                            if ($itemCode === '') continue;
                            $lot = trim($data['lot_number'] ?? '');
                            $qty = isset($data['qty']) ? (float) $data['qty'] : 0;
                            $price = isset($data['unit_price']) ? (float) $data['unit_price'] : 0;
                            if ($lot === '') {
                                throw new \Exception("รายการที่ {$rowNum}: กรุณากรอก Lot number");
                            }
                            if ($qty <= 0) {
                                throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกจำนวน (ต้องมากกว่า 0)");
                            }
                            if ($price < 0) {
                                throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกราคา/หน่วย (ต้องไม่น้อยกว่า 0)");
                            }
                            $detail = new StockDetail();
                            $detail->stock_order_id = $model->id;
                            $detail->item_code = $itemCode;
                            $detail->lot_number = $lot;
                            $detail->qty = $qty;
                            $detail->unit_price = $price;
                            $detail->expiry_date = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
                            if (!$detail->save(false)) {
                                $errors = implode(', ', $detail->getFirstErrors());
                                throw new \Exception("รายการที่ {$rowNum}: " . $errors);
                            }
                        }
                        $this->saveExpenseItemsAndReceipts($model);
                        $transaction->commit();
                        return [
                            'success' => true,
                            'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                        ];
                    }

                    if (empty($details)) {
                        throw new \Exception("กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ");
                    }

                    foreach ($details as $i => $data) {
                        $rowNum = $i + 1;
                        $lot = trim($data['lot_number'] ?? '');
                        $qty = isset($data['qty']) ? (float) $data['qty'] : null;
                        $price = isset($data['unit_price']) ? (float) $data['unit_price'] : null;
                        if ($lot === '') {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอก Lot number");
                        }
                        if ($qty === null || $qty <= 0) {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกจำนวน (ต้องมากกว่า 0)");
                        }
                        if ($price === null || $price === '' || $price < 0) {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกราคา/หน่วย (ต้องไม่น้อยกว่า 0)");
                        }
                    }

                    foreach ($details as $i => $data) {
                        $detail = new StockDetail();

                        if ($detail->load($data, '')) {
                            $detail->stock_order_id = $model->id;

                            if (!$detail->save()) {
                                $errors = implode(', ', $detail->getFirstErrors());
                                throw new \Exception("รายการที่ " . ($i + 1) . " ติดปัญหา: " . $errors);
                            }

                            $success = InventoryService::moveStock(
                                $detail->item_code,
                                $model->main_warehouse_id,
                                $detail->qty,
                                'IN',
                                $model->id,
                                $detail->id,
                                $detail->lot_number
                            );

                            if (!$success) {
                                throw new \Exception("ระบบไม่สามารถอัปเดตยอดคงเหลือในคลังได้");
                            }
                        }
                    }

                    $this->markPoOrderReceived($poOrderId);
                    $this->saveExpenseItemsAndReceipts($model);
                    $transaction->commit();
                    return [
                        'success' => true,
                        'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                    ];
                } else {
                    // ถ้า Model หลัก (StockOrder) save ไม่ผ่าน
                    $errors = implode(', ', $model->getFirstErrors());
                    throw new \Exception("ข้อมูลหลักไม่ถูกต้อง: " . $errors);
                }
            } catch (\yii\db\Exception $e) {
                $transaction->rollBack();
                \Yii::error($e, __METHOD__);
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ดูแลระบบ'
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        // กรณีโหลดหน้าเว็บปกติ (GET) - แสดงวันที่เป็น พ.ศ.
        $model->order_date = AppHelper::convertToThai(date('Y-m-d'));
        $listVendors = ['' => '-- เลือกผู้ขาย (ไม่บังคับ) --'] + ArrayHelper::map(
            Vendor::find()->where(['name' => 'vendor'])->orderBy('title')->all(),
            'id',
            'title'
        );
        return $this->render('create', [
            'model' => $model,
            'listWarehouse' => ArrayHelper::map(
                Warehouse::findMainWarehousesForReceive(),
                'id',
                'warehouse_name'
            ),
            'listItemType' => StockItem::ListStockItemType(),
            'items' => [], // สำหรับหน้า create จะไม่มีรายการเริ่มต้น
            'listVendors' => $listVendors,
            'deliveryNoteNo' => '',
            'poOrderId' => null,
        ]);
    }

    /**
     * Updates an existing StockOrder model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */

   public function actionUpdate($id)
{
    $model = StockOrder::find()
        ->where(['id' => $id])
        ->with(['stockDetails', 'stockDetails.item', 'stockDetails.item.categoryType'])
        ->one();
    if (!$model) {
        throw new \yii\web\NotFoundHttpException("ไม่พบข้อมูลเอกสาร");
    }

    $oldItems = $model->stockDetails;
    $oldWarehouseId = $model->main_warehouse_id;
    $wasDraft = ($model->status === 'DRAFT');

    if ($this->request->isPost) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $lockedOrder = InventoryService::lockOrder($model->id);
            if (!in_array($lockedOrder['status'], [StockOrder::STATUS_DRAFT, StockOrder::STATUS_CONFIRMED], true)) {
                throw new \Exception('เอกสารนี้ถูกเปลี่ยนสถานะโดยผู้ใช้อื่นแล้ว กรุณาโหลดหน้าใหม่');
            }

            foreach ($oldItems as $oldItem) {
                InventoryService::lockStockPool($oldItem->item_code, $oldWarehouseId, $oldItem->lot_number);
            }
            $oldItems = StockDetail::find()
                ->where(['stock_order_id' => $model->id])
                ->orderBy(['id' => SORT_ASC])
                ->all();
            $wasDraft = ($lockedOrder['status'] === StockOrder::STATUS_DRAFT);
            if (!$wasDraft) {
                foreach ($oldItems as $oldItem) {
                    if ((float) $oldItem->remain_qty + 0.000001 < abs((float) $oldItem->qty)) {
                        throw new \Exception(
                            "ไม่สามารถแก้ไขใบรับเข้าได้ เพราะวัสดุ {$oldItem->item_code} Lot {$oldItem->lot_number} ถูกนำไปจ่ายแล้ว"
                        );
                    }
                }
            }

            $isDraft = (bool) $this->request->post('save_as_draft', false);
            if (!$wasDraft && $isDraft) {
                $isDraft = false; // เอกสารรับเข้าคลังแล้ว ไม่ให้เปลี่ยนกลับเป็นร่าง
            }

            $detailsData = $this->request->post('StockDetail', []);
            if (!is_array($detailsData)) {
                throw new \InvalidArgumentException('รูปแบบรายการวัสดุไม่ถูกต้อง');
            }
            $detailsData = $this->normalizeDetailExpiryDates($detailsData);

            // Reverse สต็อกเก่าเฉพาะเมื่อเอกสารเคยรับเข้าจริงแล้ว (CONFIRMED)
            if (!$wasDraft) {
                foreach ($oldItems as $oldItem) {
                    InventoryService::updateBalance(
                        $oldItem->item_code,
                        $oldWarehouseId,
                        $oldItem->qty,
                        'OUT',
                        $oldItem->lot_number
                    );
                }
            }

            StockDetail::deleteAll(['stock_order_id' => $model->id]);

            $model->load($this->request->post());
            if ($isDraft) {
                $model->status = 'DRAFT';
            } else {
                $model->status = 'CONFIRMED';
                if ($wasDraft || !$model->getStockPostedAt()) {
                    $model->setStockPostedAt(time());
                }
            }
            // เลขที่ใบรับเข้าเว้นว่างได้เสมอ (รวมถึงรับจาก PO) — ถ้าว่างระบบออกเลข RCV- ให้อัตโนมัติ
            if (trim((string) $model->order_no) === '') {
                $model->order_no = $this->generateReceiveOrderNo();
            }
            if (!empty($model->order_date)) {
                $model->order_date = AppHelper::convertToGregorian($model->order_date);
                if ($model->order_date !== null) {
                    $model->order_date .= ' ' . date('H:i:s');
                }
            }

            // เลขที่ส่งสินค้า + ลิงก์ใบสั่งซื้อต้นทาง (ถ้ารับเข้าจาก PO picker) — เก็บใน data_json
            $poOrderIdRaw = $this->request->post('po_order_id');
            $poOrderId = ($poOrderIdRaw !== null && $poOrderIdRaw !== '') ? (int) $poOrderIdRaw : null;
            $json = is_array($model->data_json) ? $model->data_json : (is_string($model->data_json) ? (json_decode($model->data_json, true) ?: []) : []);
            $json['delivery_note_no'] = trim((string) $this->request->post('delivery_note_no', ''));
            if ($poOrderId) {
                $json['po_order_id'] = $poOrderId;
            }
            $model->data_json = $json;

            if (!empty($detailsData) && $model->main_warehouse_id) {
                $warehouse = Warehouse::findOne($model->main_warehouse_id);
                if ($warehouse) {
                    foreach ($detailsData as $i => $data) {
                        $itemCode = $data['item_code'] ?? null;
                        if (!$itemCode) continue;
                        $item = StockItem::findOne(['item_code' => $itemCode]);
                        if ($item && !$warehouse->allowsItemType($item->category_id)) {
                            throw new \Exception("พัสดุ [{$item->item_name}] ไม่สามารถรับเข้าคลังนี้ได้ เนื่องจากคลังกำหนดเฉพาะประเภทที่รับเข้า (รายการที่ " . ($i + 1) . ")");
                        }
                    }
                }
            }
            if ($model->save()) {

                if ($isDraft) {
                    foreach ($detailsData as $i => $data) {
                        $rowNum = $i + 1;
                        $itemCode = trim($data['item_code'] ?? '');
                        if ($itemCode === '') continue;
                        $lot = trim($data['lot_number'] ?? '');
                        $qty = isset($data['qty']) ? (float) $data['qty'] : 0;
                        $price = isset($data['unit_price']) ? (float) $data['unit_price'] : 0;
                        if ($lot === '') {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอก Lot number");
                        }
                        if ($qty <= 0) {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกจำนวน (ต้องมากกว่า 0)");
                        }
                        if ($price < 0) {
                            throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกราคา/หน่วย (ต้องไม่น้อยกว่า 0)");
                        }
                        $detail = new StockDetail();
                        $detail->isNewRecord = true;
                        $detail->id = null;
                        $detail->stock_order_id = $model->id;
                        $detail->item_code = $itemCode;
                        $detail->lot_number = $lot;
                        $detail->qty = $qty;
                        $detail->unit_price = $price;
                        $detail->expiry_date = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
                        if (!$detail->save(false)) {
                            $errors = implode(', ', $detail->getFirstErrors());
                            throw new \Exception("รายการที่ {$rowNum}: " . $errors);
                        }
                    }
                    $this->saveExpenseItemsAndReceipts($model);
                    $transaction->commit();
                    return [
                        'success' => true,
                        'message' => 'บันทึกฉบับร่างเรียบร้อย',
                        'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                    ];
                }

                if (empty($detailsData)) {
                    throw new \Exception("กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ");
                }

                foreach ($detailsData as $i => $data) {
                    $rowNum = $i + 1;
                    $lot = trim($data['lot_number'] ?? '');
                    $qty = isset($data['qty']) ? (float) $data['qty'] : null;
                    $price = isset($data['unit_price']) ? (float) $data['unit_price'] : null;
                    if ($lot === '') {
                        throw new \Exception("รายการที่ {$rowNum}: กรุณากรอก Lot number");
                    }
                    if ($qty === null || $qty <= 0) {
                        throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกจำนวน (ต้องมากกว่า 0)");
                    }
                    if ($price === null || $price === '' || $price < 0) {
                        throw new \Exception("รายการที่ {$rowNum}: กรุณากรอกราคา/หน่วย (ต้องไม่น้อยกว่า 0)");
                    }
                }

                foreach ($detailsData as $i => $data) {
                    $detail = new StockDetail();
                    if ($detail->load($data, '')) {
                        $detail->isNewRecord = true;
                        $detail->id = null;
                        $detail->stock_order_id = $model->id;

                        if ($detail->save()) {
                            InventoryService::moveStock(
                                $detail->item_code,
                                $model->main_warehouse_id,
                                $detail->qty,
                                'IN',
                                $model->id,
                                $detail->id,
                                $detail->lot_number
                            );
                        } else {
                            $errors = implode(', ', $detail->getFirstErrors());
                            throw new \Exception("รายการที่ " . ($i + 1) . " บันทึกไม่สำเร็จ: " . $errors);
                        }
                    }
                }

                $this->markPoOrderReceived($poOrderId);
                $this->saveExpenseItemsAndReceipts($model);
                $transaction->commit();
                return [
                    'success' => true,
                    'message' => 'แก้ไขข้อมูลและปรับปรุงสต็อกเรียบร้อยแล้ว',
                    'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                ];
            } else {
                $errors = implode(', ', $model->getFirstErrors());
                throw new \Exception("ข้อมูลหัวเอกสารไม่ถูกต้อง: " . $errors);
            }

        } catch (\yii\db\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e, __METHOD__);
            return [
                'success' => false,
                'message' => 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ดูแลระบบ'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    // กรณีโหลดหน้าแก้ไขปกติ (GET) - แสดงวันที่เป็น พ.ศ.
    $model->order_date = $model->order_date ? AppHelper::convertToThai($model->order_date) : AppHelper::convertToThai(date('Y-m-d'));
    $listVendors = ['' => '-- เลือกผู้ขาย (ไม่บังคับ) --'] + ArrayHelper::map(
        Vendor::find()->where(['name' => 'vendor'])->orderBy('title')->all(),
        'id',
        'title'
    );
    return $this->render('update', [
        'model' => $model,
        'items' => $oldItems,
        'listWarehouse' => ArrayHelper::map(
                Warehouse::findMainWarehousesForReceive(),
                'id',
                'warehouse_name'
            ),
        'listItemType' => StockItem::ListStockItemType(),
        'listVendors' => $listVendors,
        'deliveryNoteNo' => $model->getDeliveryNoteNo(),
        'poOrderId' => $model->getPoOrderId(),
    ]);
}

    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        // ป้องกันการยกเลิกซ้ำ หรือยกเลิกใบที่ยังไม่ได้ยืนยัน (ถ้าต้องการ)
        if ($model->status === 'CANCELLED') {
            \Yii::$app->session->setFlash('warning', 'เอกสารนี้ถูกยกเลิกไปแล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $lockedOrder = InventoryService::lockOrder($model->id);
            if ($lockedOrder['status'] === StockOrder::STATUS_CANCELLED) {
                throw new \Exception('เอกสารนี้ถูกยกเลิกโดยผู้ใช้อื่นแล้ว');
            }
            if (!in_array($lockedOrder['status'], [StockOrder::STATUS_DRAFT, StockOrder::STATUS_CONFIRMED], true)) {
                throw new \Exception('สถานะเอกสารไม่อนุญาตให้ยกเลิก กรุณาโหลดหน้าใหม่');
            }

            $details = StockDetail::find()
                ->where(['stock_order_id' => $model->id])
                ->orderBy(['id' => SORT_ASC])
                ->all();
            foreach ($details as $detail) {
                InventoryService::lockStockPool($detail->item_code, $model->main_warehouse_id, $detail->lot_number);
            }
            $details = StockDetail::find()
                ->where(['stock_order_id' => $model->id])
                ->orderBy(['id' => SORT_ASC])
                ->all();

            // ใบร่างยังไม่เคยเพิ่ม Balance; ใบยืนยันหักคืนได้เฉพาะต้นทางที่ยังไม่ถูกนำไปจ่าย
            if ($lockedOrder['status'] === StockOrder::STATUS_CONFIRMED) {
                foreach ($details as $detail) {
                    if ((float) $detail->remain_qty + 0.000001 < abs((float) $detail->qty)) {
                        throw new \Exception(
                            "ไม่สามารถยกเลิกใบรับเข้าได้ เพราะวัสดุ {$detail->item_code} Lot {$detail->lot_number} ถูกนำไปจ่ายแล้ว"
                        );
                    }
                    InventoryService::updateBalance(
                        $detail->item_code,
                        $model->main_warehouse_id,
                        abs((float) $detail->qty),
                        'OUT',
                        $detail->lot_number
                    );
                    $detail->remain_qty = 0;
                    if (!$detail->save(false, ['remain_qty'])) {
                        throw new \Exception("ไม่สามารถปิดยอด Lot ต้นทาง สำหรับรหัส: {$detail->item_code}");
                    }
                }
            }

            // 2. เปลี่ยนสถานะเอกสาร
            $model->status = 'CANCELLED';
            if ($model->save(false)) { // ใช้ false เพื่อข้าม validation บางตัวถ้าจำเป็น
                $this->revertPoOrderPending($model->getPoOrderId());
                $transaction->commit();
                \Yii::$app->session->setFlash('success', 'ยกเลิกเอกสารและคืนสต็อกเรียบร้อยแล้ว');
            } else {
                throw new \Exception("ไม่สามารถบันทึกสถานะการยกเลิกได้");
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }


    /**
     * Deletes an existing StockOrder model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if (!\Yii::$app->user->can('admin')) {
            \Yii::$app->session->setFlash('error', 'ไม่มีสิทธิ์ลบใบรับเข้า (เฉพาะผู้ดูแลระบบเท่านั้น)');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model = $this->findModel($id);

        if (!$model->canDelete()) {
            \Yii::$app->session->setFlash('error', $model->getUndeletableReason());
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // ถ้าตัดสต็อกไปแล้ว (CONFIRMED) ต้องหักยอดคงเหลือคืนก่อนลบ
            // stock_detail จะถูกลบอัตโนมัติผ่าน FK ON DELETE CASCADE
            if ($model->status === StockOrder::STATUS_CONFIRMED) {
                foreach ($model->stockDetails as $detail) {
                    InventoryService::updateBalance(
                        $detail->item_code,
                        $model->main_warehouse_id,
                        $detail->qty,
                        'OUT',
                        $detail->lot_number
                    );

                    // ลบแถว stock_balance ทิ้งถ้ายอดเหลือ 0 หลังหักคืน
                    // (เงื่อนไข canDelete() การันตีว่ายังไม่มีการเบิก/โอนออกจากลอตนี้ จึงต้องกลับไป 0 เสมอ)
                    $lot = !empty($detail->lot_number) ? $detail->lot_number : '-';
                    $balance = StockBalance::findOne([
                        'item_code' => $detail->item_code,
                        'warehouse_id' => $model->main_warehouse_id,
                        'lot_number' => $lot,
                    ]);
                    if ($balance && abs((float) $balance->balance_qty) < 0.000001) {
                        $balance->delete();
                    }
                }
            }

            $poOrderId = $model->getPoOrderId();

            if (!$model->delete()) {
                throw new \Exception('ไม่สามารถลบใบรับเข้าได้');
            }

            $this->revertPoOrderPending($poOrderId);
            $transaction->commit();
            \Yii::$app->session->setFlash('success', 'ลบใบรับเข้าเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->redirect(['index']);
    }

    /**
     * นำใบสั่งซื้อออกจากรายการ "รอรับเข้าคลัง" ด้วยมือ (สำหรับรายการเก่าที่ค้างอยู่/รับเข้าไปแล้วทางอื่น)
     * ไม่ได้ลบใบสั่งซื้อ แต่เดินสถานะไปเป็น 6 (รับเข้าคลังแล้ว) และบันทึกผู้กด/เวลา/เหตุผลไว้ใน data_json
     * @param int $id orders.id
     * @return array
     */
    public function actionDismissPo($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $order = PurchaseOrder::findOne(['id' => (int) $id, 'name' => 'order']);
        if (!$order) {
            return ['success' => false, 'message' => 'ไม่พบใบสั่งซื้อ'];
        }
        if ((int) $order->status !== 5) {
            return ['success' => false, 'message' => 'ใบสั่งซื้อนี้ไม่ได้อยู่ในสถานะรอรับเข้าคลัง'];
        }

        $json = $order->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        if (!is_array($json)) {
            $json = [];
        }
        $json['stock_dismissed'] = [
            'at' => date('Y-m-d H:i:s'),
            'by' => \Yii::$app->user->isGuest ? null : \Yii::$app->user->id,
            'note' => trim((string) $this->request->post('note', '')),
        ];
        $order->data_json = $json;
        $order->status = 6;
        if (!$order->save(false)) {
            return ['success' => false, 'message' => 'บันทึกไม่สำเร็จ'];
        }

        return ['success' => true, 'po_number' => $order->po_number];
    }

    /**
     * รวม orders.id ของใบสั่งซื้อที่มีใบรับเข้าคลัง (ที่ยังไม่ยกเลิก) ผูกอยู่แล้ว
     * ใช้กันการรับเข้าซ้ำ กรณีสถานะใบสั่งซื้อถูกดึงกลับมาเป็น 5 จากการแก้ไขใบตรวจรับ
     * @return array map ของ orders.id => เลขที่ใบรับเข้าที่ผูกอยู่ (array of string)
     */
    protected function getReceivedPoOrderIds()
    {
        $rows = StockOrder::find()
            ->select(['order_no', 'data_json'])
            ->where(['order_type' => StockOrder::ORDER_TYPE_IN])
            ->andWhere(['<>', 'status', StockOrder::STATUS_CANCELLED])
            ->andWhere(['like', 'data_json', 'po_order_id'])
            ->asArray()
            ->all();

        $map = [];
        foreach ($rows as $row) {
            $json = is_array($row['data_json'])
                ? $row['data_json']
                : (json_decode((string) $row['data_json'], true) ?: []);
            if (empty($json['po_order_id'])) {
                continue;
            }
            $poId = (int) $json['po_order_id'];
            $map[$poId][] = (string) $row['order_no'];
        }

        return $map;
    }

    /**
     * เปลี่ยนสถานะใบสั่งซื้อต้นทางเป็น "ส่งเข้าคลังแล้ว" (status = 6) หลังบันทึกรับเข้าคลังสำเร็จ
     * @param int|null $poOrderId orders.id
     */
    protected function markPoOrderReceived($poOrderId)
    {
        if (!$poOrderId) {
            return;
        }
        $poOrder = PurchaseOrder::findOne(['id' => $poOrderId, 'name' => 'order']);
        if ($poOrder) {
            $poOrder->status = 6;
            $poOrder->save(false);
        }
    }

    /**
     * ย้อนสถานะใบสั่งซื้อกลับเป็น "รอรับเข้าคลัง" (status = 5) เมื่อยกเลิก/ลบใบรับเข้าที่ผูกกับ PO นี้
     * @param int|null $poOrderId orders.id
     */
    protected function revertPoOrderPending($poOrderId)
    {
        if (!$poOrderId) {
            return;
        }
        $poOrder = PurchaseOrder::findOne(['id' => $poOrderId, 'name' => 'order']);
        if ($poOrder && (int) $poOrder->status === 6) {
            $poOrder->status = 5;
            $poOrder->save(false);
        }
    }

    /**
     * สร้างเลขที่ใบรับเข้าให้อัตโนมัติ (ใช้เมื่อไม่ใช่การจัดซื้อและผู้ใช้เว้นว่าง)
     * รูปแบบ RCV-YYYYMMDD-HHmmss-XXX ให้ไม่ซ้ำ
     */
    protected function generateReceiveOrderNo()
    {
        $prefix = 'RCV-' . date('Ymd-His') . '-';
        do {
            $no = $prefix . mt_rand(100, 999);
        } while (StockOrder::findOne(['order_no' => $no]) !== null);
        return $no;
    }

    /**
     * บันทึกรายการค่าใช้จ่ายและไฟล์ใบเสร็จแนบ จาก POST และ FILES ลง data_json
     * @param StockOrder $model ต้องมี id แล้ว (หลัง save)
     */
    protected function saveExpenseItemsAndReceipts(StockOrder $model)
    {
        $expensePost = $this->request->post('ExpenseItems');
        if ($expensePost === null) {
            return;
        }
        if (!is_array($expensePost)) {
            return;
        }
        $existing = $model->getExpenseItems();
        $dir = null;
        $expenseList = [];
        foreach ($expensePost as $i => $row) {
            $desc = trim($row['description'] ?? '');
            $amount = isset($row['amount']) && $row['amount'] !== '' ? (float) $row['amount'] : 0;
            $receiptPath = isset($existing[$i]['receipt_path']) ? $existing[$i]['receipt_path'] : null;
            $file = UploadedFile::getInstanceByName('ExpenseItems[' . $i . '][receipt]');
            if ($file && $file->error === \UPLOAD_ERR_OK) {
                if ($dir === null) {
                    $dir = \Yii::getAlias('@webroot/uploads/receive-receipts');
                    if (!is_dir($dir)) {
                        FileHelper::createDirectory($dir, 0755, true);
                    }
                }
                $baseName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->baseName);
                $name = $model->id . '_' . $i . '_' . $baseName . '.' . $file->extension;
                $path = $dir . '/' . $name;
                if ($file->saveAs($path)) {
                    $receiptPath = '/uploads/receive-receipts/' . $name;
                }
            }
            if ($desc !== '' || $amount > 0 || $receiptPath !== null) {
                $expenseList[] = [
                    'description' => $desc,
                    'amount' => $amount,
                    'receipt_path' => $receiptPath,
                ];
            }
        }
        $model->setExpenseItems($expenseList);
        $model->save(false);
    }

    /**
     * แปลงวันหมดอายุของทุกรายการเป็น Y-m-d ก่อนส่งให้ StockDetail/MySQL
     * รองรับทั้ง ค.ศ./พ.ศ. และรูปแบบ d/m/Y หรือ Y-m-d
     */
    protected function normalizeDetailExpiryDates(array $details)
    {
        $rowNum = 0;
        foreach ($details as &$data) {
            $rowNum++;
            if (!is_array($data)) {
                throw new \InvalidArgumentException('รายการที่ ' . $rowNum . ': รูปแบบข้อมูลไม่ถูกต้อง');
            }

            $raw = trim((string) ($data['expiry_date'] ?? ''));
            if ($raw === '') {
                $data['expiry_date'] = null;
                continue;
            }

            $normalized = AppHelper::normalizeDateToDb($raw);
            if ($normalized === null) {
                throw new \InvalidArgumentException(
                    'รายการที่ ' . $rowNum . ': วันหมดอายุไม่ถูกต้อง กรุณาใช้รูปแบบ วัน/เดือน/ปี เช่น 16/08/2571'
                );
            }
            $data['expiry_date'] = $normalized;
        }
        unset($data);

        return $details;
    }

    /**
     * Finds the StockOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return StockOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StockOrder::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
