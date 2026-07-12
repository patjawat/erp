<?php

namespace app\modules\inventoryV2\controllers;

use app\components\AppHelper;
use app\models\Categorise;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockMonthlyReport;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\components\InventoryService;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\NotFoundHttpException;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MainStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Dashboard คลังหลัก - แสดง KPIs และรายการจากข้อมูลจริง
     */
    public function actionDashboard()
    {
        $mainWarehouseIds = $this->getMainWarehouseIds();
        if (empty($mainWarehouseIds)) {
            $mainWarehouseIds = [-1];
        }

        $warehouseId = $this->getFilterWarehouseId();
        // ถ้าไม่ใช่ admin และเลือกคลังที่ไม่อยู่ในรายการที่รับผิดชอบ ให้ล้างการเลือก
        if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
            Yii::$app->session->remove('dashboard_warehouse_id');
            $warehouseId = null;
        }

        $stats = $this->getDashboardStats($warehouseId, $mainWarehouseIds);
        $warehouses = $this->getMainWarehousesList();
        $pendingRequisitions = $this->getPendingRequisitions($warehouseId, $mainWarehouseIds, 10);

        $thaiYear = (int) AppHelper::YearBudget();
        $yearOptions = [];
        for ($y = $thaiYear; $y >= $thaiYear - 3; $y--) {
            $yearOptions[] = $y;
        }
        $chartData = $this->getMovementChartData($warehouseId, $mainWarehouseIds, $thaiYear, null, 'IN');

        return $this->render('dashboard', [
            'stats' => $stats,
            'warehouses' => $warehouses,
            'pendingRequisitions' => $pendingRequisitions,
            'chartData' => $chartData,
            'currentWarehouseId' => $warehouseId,
            'yearOptions' => $yearOptions,
            'defaultYear' => $thaiYear,
        ]);
    }

    /**
     * รายงานสรุปยอดคงเหลือ — มุมผู้ดูแลคลังหลัก
     * Scope: เฉพาะคลังหลัก (MAIN) ที่ user เป็น officer ใน data_json.officer
     */
    public function actionBalance()
    {
        $accessible = $this->getMainWarehousesList();
        $context = \app\modules\inventoryV2\controllers\ReportController::buildBalanceContext(
            $accessible,
            $this->request->getQueryParams(),
            '-- ทุกคลังหลัก --'
        );

        return $this->render('balance', array_merge($context, [
            'accessibleWarehouseCount' => count($accessible),
        ]));
    }

    /**
     * Export Excel ของยอดคงเหลือ — มุมผู้ดูแลคลังหลัก
     */
    public function actionExportBalance()
    {
        $accessible = $this->getMainWarehousesList();
        $context = \app\modules\inventoryV2\controllers\ReportController::buildBalanceContext(
            $accessible,
            $this->request->getQueryParams(),
            '-- ทุกคลังหลัก --'
        );
        \app\modules\inventoryV2\controllers\ReportController::streamBalanceXlsx(
            $context['rows'],
            'balance-main-warehouse'
        );
    }

    /**
     * AJAX endpoint: ส่งข้อมูล chart มูลค่ารับเข้า/จ่ายออก แยกตามประเภท
     * Params: warehouse_id, year (พ.ศ.), month (1-12, optional), direction (IN|OUT|NET)
     */
    public function actionMovementChart()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $mainWarehouseIds = $this->getMainWarehouseIds();
        if (empty($mainWarehouseIds)) {
            $mainWarehouseIds = [-1];
        }

        $warehouseIdParam = $this->request->get('warehouse_id');
        $warehouseId = ($warehouseIdParam === null || $warehouseIdParam === '' || $warehouseIdParam === 'all')
            ? null : (int) $warehouseIdParam;
        if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
            $warehouseId = null;
        }

        $year = (int) ($this->request->get('year') ?: AppHelper::YearBudget());
        $month = $this->request->get('month');
        $month = ($month === null || $month === '' || $month === 'all') ? null : max(1, min(12, (int) $month));
        $direction = strtoupper((string) $this->request->get('direction', 'IN'));
        if (!in_array($direction, ['IN', 'OUT', 'NET'], true)) {
            $direction = 'IN';
        }

        return $this->getMovementChartData($warehouseId, $mainWarehouseIds, $year, $month, $direction);
    }

    /**
     * AJAX endpoint: รายการพัสดุในประเภทที่เลือก (drill-down จาก movement chart)
     * Params: warehouse_id, year (พ.ศ.), month (1-12, required), direction (IN|OUT), category (code)
     * Return: { items: [{code, name, unit, qty, value}], summary: {count, total, period_label, category_name, category_color, direction_label} }
     */
    public function actionMovementItems()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $mainWarehouseIds = $this->getMainWarehouseIds();
            if (empty($mainWarehouseIds)) {
                $mainWarehouseIds = [-1];
            }

            $warehouseIdParam = $this->request->get('warehouse_id');
            $warehouseId = ($warehouseIdParam === null || $warehouseIdParam === '' || $warehouseIdParam === 'all')
                ? null : (int) $warehouseIdParam;
            if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
                $warehouseId = null;
            }
            $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;
            if (empty($warehouseIds)) {
                $warehouseIds = [-1];
            }

            $thaiYear = (int) ($this->request->get('year') ?: AppHelper::YearBudget());
            $month = max(1, min(12, (int) $this->request->get('month', 0)));
            if ($month < 1) {
                return ['status' => 'error', 'message' => 'ต้องระบุเดือน'];
            }
            $direction = strtoupper((string) $this->request->get('direction', 'IN'));
            if (!in_array($direction, ['IN', 'OUT'], true)) {
                $direction = 'IN';
            }
            $categoryCode = (string) $this->request->get('category', '');
            if ($categoryCode === '') {
                return ['status' => 'error', 'message' => 'ต้องระบุประเภทพัสดุ'];
            }

            [$fromDate, $toDate] = $this->getFiscalMonthDateRange($thaiYear, $month);

            $categoryFilterSql = "COALESCE(cat.code, i.category_id, 'OTHER') = :category_code";
            $categoryFilterParams = [':category_code' => $categoryCode];

            if ($direction === 'IN') {
                $query = (new Query())
                    ->select([
                        'item_code' => 'i.code',
                        'item_name' => 'i.title',
                        'qty' => new Expression('SUM(sd.qty)'),
                        'value' => new Expression('SUM(sd.qty * COALESCE(sd.unit_price, 0))'),
                    ])
                    ->from(['sd' => StockDetail::tableName()])
                    ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                    ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
                    ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
                    ->where(['so.order_type' => 'IN'])
                    ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
                    ->andWhere(['so.main_warehouse_id' => $warehouseIds])
                    ->andWhere(['between', 'so.order_date', $fromDate, $toDate])
                    ->andWhere($categoryFilterSql, $categoryFilterParams)
                    ->groupBy(['i.code', 'i.title'])
                    ->orderBy(['value' => SORT_DESC, 'i.title' => SORT_ASC]);
            } else {
                // OUT: ใช้ราคา IN lot ล่าสุดตาม lot_number (ตรรกะเดียวกับ getMovementChartData)
                $latestInPrice = (new Query())
                    ->select(['sd_in.item_code', 'sd_in.lot_number', 'sd_in.unit_price'])
                    ->from(['sd_in' => StockDetail::tableName()])
                    ->innerJoin(['so_in' => StockOrder::tableName()], 'so_in.id = sd_in.stock_order_id')
                    ->innerJoin(
                        ['latest' => (new Query())
                            ->select(['sd_l.item_code', 'sd_l.lot_number', new Expression('MAX(sd_l.id) AS mid')])
                            ->from(['sd_l' => StockDetail::tableName()])
                            ->innerJoin(['so_l' => StockOrder::tableName()], 'so_l.id = sd_l.stock_order_id')
                            ->where(['so_l.order_type' => 'IN'])
                            ->andWhere(['so_l.main_warehouse_id' => $warehouseIds])
                            ->groupBy(['sd_l.item_code', 'sd_l.lot_number'])],
                        'latest.item_code = sd_in.item_code AND latest.lot_number = sd_in.lot_number AND latest.mid = sd_in.id'
                    );

                $query = (new Query())
                    ->select([
                        'item_code' => 'i.code',
                        'item_name' => 'i.title',
                        'qty' => new Expression('SUM(sd.qty)'),
                        'value' => new Expression('SUM(sd.qty * COALESCE(in_lot.unit_price, sd.unit_price, 0))'),
                    ])
                    ->from(['sd' => StockDetail::tableName()])
                    ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                    ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
                    ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
                    ->leftJoin(['in_lot' => $latestInPrice], 'in_lot.item_code = sd.item_code AND in_lot.lot_number = sd.lot_number')
                    ->where(['so.order_type' => 'OUT'])
                    ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
                    ->andWhere(['so.main_warehouse_id' => $warehouseIds])
                    ->andWhere(['between', 'so.order_date', $fromDate, $toDate])
                    ->andWhere($categoryFilterSql, $categoryFilterParams)
                    ->groupBy(['i.code', 'i.title'])
                    ->orderBy(['value' => SORT_DESC, 'i.title' => SORT_ASC]);
            }

            $rows = $query->all();

            $itemCodes = array_values(array_unique(array_filter(array_map(fn($r) => $r['item_code'] ?? null, $rows))));
            $itemModels = [];
            if (!empty($itemCodes)) {
                $itemModels = StockItem::find()
                    ->where(['code' => $itemCodes])
                    ->indexBy('code')
                    ->all();
            }

            // Batch-fetch primary upload per item ref → avoid N+1 from ShowImg()
            $refToImgUrl = [];
            $refs = array_values(array_unique(array_filter(array_map(
                fn($m) => $m && !empty($m->ref) ? (string) $m->ref : null,
                $itemModels
            ))));
            if (!empty($refs)) {
                $uploads = \app\modules\filemanager\models\Uploads::find()
                    ->where(['ref' => $refs])
                    ->orderBy(['id' => SORT_ASC])
                    ->all();
                foreach ($uploads as $u) {
                    $ref = (string) $u->ref;
                    if (!isset($refToImgUrl[$ref])) {
                        $refToImgUrl[$ref] = \app\modules\filemanager\components\FileManagerHelper::getImg($u->id);
                    }
                }
            }

            $items = [];
            $totalValue = 0.0;
            $totalQty = 0.0;
            foreach ($rows as $r) {
                $model = $itemModels[$r['item_code']] ?? null;
                $qty = (float) $r['qty'];
                $value = (float) $r['value'];
                $img = null;
                if ($model && !empty($model->ref) && isset($refToImgUrl[(string) $model->ref])) {
                    $img = $refToImgUrl[(string) $model->ref];
                }
                $items[] = [
                    'code' => (string) $r['item_code'],
                    'name' => (string) $r['item_name'],
                    'unit' => $model ? ($model->getUnitName() ?? '') : '',
                    'img' => $img,
                    'qty' => round($qty, 2),
                    'value' => round($value, 2),
                ];
                $totalQty += $qty;
                $totalValue += $value;
            }

            // category meta
            $catRow = Categorise::find()
                ->where(['name' => 'asset_type', 'group_id' => 'MATER', 'code' => $categoryCode])
                ->one();
            $categoryName = $catRow ? (string) $catRow->title : ($categoryCode === 'OTHER' ? 'อื่นๆ' : $categoryCode);

            // resolve category color (must match chart palette)
            $allCats = Categorise::find()
                ->where(['name' => 'asset_type', 'group_id' => 'MATER'])
                ->orderBy(['code' => SORT_ASC])
                ->all();
            $palette = self::categoryColorPalette();
            $categoryColor = '#9ca3af';
            foreach ($allCats as $idx => $c) {
                if ((string) $c->code === $categoryCode) {
                    $categoryColor = $palette[$idx % count($palette)];
                    break;
                }
            }

            $monthFull = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
            $periodLabel = $monthFull[$month - 1] . ' ' . $thaiYear;
            $directionLabel = $direction === 'IN' ? 'รับเข้า' : 'จ่ายออก';

            $currentWarehouseName = 'ทั้งหมด';
            if ($warehouseId !== null) {
                foreach ($this->getMainWarehousesList() as $w) {
                    if ((int) $w->id === (int) $warehouseId) {
                        $currentWarehouseName = $w->warehouse_name;
                        break;
                    }
                }
            }

            return [
                'status' => 'success',
                'items' => $items,
                'summary' => [
                    'count' => count($items),
                    'total_value' => round($totalValue, 2),
                    'total_qty' => round($totalQty, 2),
                    'period_label' => $periodLabel,
                    'category_code' => $categoryCode,
                    'category_name' => $categoryName,
                    'category_color' => $categoryColor,
                    'direction' => $direction,
                    'direction_label' => $directionLabel,
                    'warehouse_name' => $currentWarehouseName,
                ],
            ];
        } catch (\Throwable $e) {
            Yii::error('movement-items failed: ' . $e->getMessage(), __METHOD__);
            return [
                'status' => 'error',
                'message' => 'โหลดข้อมูลไม่สำเร็จ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * รายการพัสดุที่มีสต๊อก (>0) ในคลังที่เลือก (หรือรวมทุกคลังหลักเมื่อเลือก "ทั้งหมด")
     */
    public function actionItemsWithStock()
    {
        $mainWarehouseIds = $this->getMainWarehouseIds();
        if (empty($mainWarehouseIds)) {
            $mainWarehouseIds = [-1];
        }

        $warehouseId = $this->getFilterWarehouseId();
        if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
            Yii::$app->session->remove('dashboard_warehouse_id');
            $warehouseId = null;
        }

        $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;

        $itemsQuery = (new Query())
            ->from(['sb' => StockBalance::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sb.item_code')
            ->select([
                'sb.item_code',
                'item_name' => 'i.title',
                'total_qty' => new Expression('SUM(sb.balance_qty)'),
            ])
            ->where(['sb.warehouse_id' => $warehouseIds])
            ->andWhere(['>', 'sb.balance_qty', 0])
            ->groupBy(['sb.item_code', 'i.title'])
            ->orderBy(['i.title' => SORT_ASC]);

        $totalCount = (int) (clone $itemsQuery)->count();
        $pagination = new Pagination([
            'totalCount' => $totalCount,
            'pageSize' => 20,
            'pageParam' => 'page',
        ]);

        $rows = (clone $itemsQuery)
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $itemCodes = array_values(array_unique(array_filter(array_map(function ($r) {
            return $r['item_code'] ?? null;
        }, $rows))));

        $itemModels = [];
        if (!empty($itemCodes)) {
            $itemModels = StockItem::find()
                ->where(['item_code' => $itemCodes])
                ->indexBy('item_code')
                ->all();
        }

        $items = [];
        foreach ($rows as $r) {
            $code = $r['item_code'];
            $model = $itemModels[$code] ?? null;
            $items[] = [
                'item_code' => $code,
                'item_name' => $r['item_name'],
                'unit_name' => $model ? $model->getUnitName() : null,
                'total_qty' => (float) $r['total_qty'],
                'img' => $model ? $model->ShowImg() : null,
            ];
        }

        $warehouses = $this->getMainWarehousesList();

        return $this->render('items-with-stock', [
            'items' => $items,
            'pagination' => $pagination,
            'totalCount' => $totalCount,
            'warehouses' => $warehouses,
            'currentWarehouseId' => $warehouseId,
        ]);
    }


    /**
     * Offcanvas: ใบเบิกที่รอจัดของ (APPROVED) — รายชื่อสำหรับการ์ดบน dashboard
     */
    public function actionPendingRequisitionsOffcanvas()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $q = trim((string) $this->request->get('q', ''));
            $limit = 30;

            $mainWarehouseIds = $this->getMainWarehouseIds();
            if (empty($mainWarehouseIds)) $mainWarehouseIds = [-1];
            $warehouseId = $this->getFilterWarehouseId();
            if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
                Yii::$app->session->remove('dashboard_warehouse_id');
                $warehouseId = null;
            }

            $query = StockOrder::find()
                ->with(['mainWarehouse', 'subWarehouse', 'stockDetails'])
                ->where([
                    'order_type' => 'OUT',
                    'source_type' => 'REQUEST',
                    'status' => StockOrder::STATUS_APPROVED,
                ])
                ->andWhere(['main_warehouse_id' => $warehouseId ? [$warehouseId] : $mainWarehouseIds]);

            if ($q !== '') {
                $query->andWhere(['or',
                    ['like', 'order_no', $q],
                ]);
            }

            $totalCount = (int) (clone $query)->count();
            $rows = (clone $query)->orderBy(['order_date' => SORT_DESC])->limit($limit)->all();

            return [
                'status' => 'success',
                'content' => $this->renderPartial('_pending_requisitions_offcanvas_content', [
                    'items' => $rows,
                    'totalCount' => $totalCount,
                    'shownCount' => count($rows),
                    'q' => $q,
                    'currentWarehouseName' => $this->resolveWarehouseName($warehouseId),
                    'fullPageUrl' => \yii\helpers\Url::to(['/inventory-v2/issue/index']),
                ]),
                'total_count' => $totalCount,
                'shown_count' => count($rows),
            ];
        } catch (\Throwable $e) {
            Yii::error('pending-requisitions-offcanvas failed: ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => 'โหลดข้อมูลไม่สำเร็จ: ' . $e->getMessage()];
        }
    }

    /**
     * Offcanvas: วัสดุที่ขอเบิกแต่ยอดไม่พอจ่าย — ใช้ตรรกะเดียวกับ getInsufficientToDisburseCount
     */
    public function actionInsufficientOffcanvas()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $q = trim((string) $this->request->get('q', ''));
            $limit = 30;

            $mainWarehouseIds = $this->getMainWarehouseIds();
            if (empty($mainWarehouseIds)) $mainWarehouseIds = [-1];
            $warehouseId = $this->getFilterWarehouseId();
            if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
                Yii::$app->session->remove('dashboard_warehouse_id');
                $warehouseId = null;
            }
            $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;
            if (empty($warehouseIds)) $warehouseIds = [-1];

            $reqSub = (new Query())
                ->select(['sd.item_code', 'SUM(sd.qty) AS requested_qty'])
                ->from(['sd' => StockDetail::tableName()])
                ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                ->where([
                    'so.order_type' => 'OUT',
                    'so.source_type' => 'REQUEST',
                    'so.status' => [StockOrder::STATUS_PENDING, StockOrder::STATUS_APPROVED],
                ])
                ->andWhere(['so.main_warehouse_id' => $warehouseIds])
                ->groupBy('sd.item_code');

            $balSub = (new Query())
                ->select(['item_code', 'SUM(balance_qty) AS balance_qty'])
                ->from(StockBalance::tableName())
                ->where(['warehouse_id' => $warehouseIds])
                ->groupBy('item_code');

            $base = (new Query())
                ->from(['req' => $reqSub])
                ->leftJoin(['bal' => $balSub], 'bal.item_code = req.item_code')
                ->innerJoin(['i' => StockItem::tableName()], 'i.code = req.item_code')
                ->select([
                    'item_code' => 'req.item_code',
                    'item_name' => 'i.title',
                    'requested_qty' => 'req.requested_qty',
                    'balance_qty' => new Expression('COALESCE(bal.balance_qty, 0)'),
                    'shortfall' => new Expression('req.requested_qty - COALESCE(bal.balance_qty, 0)'),
                ])
                ->where('req.requested_qty > COALESCE(bal.balance_qty, 0)');

            if ($q !== '') {
                $base->andWhere(['or',
                    ['like', 'i.code', $q],
                    ['like', 'i.title', $q],
                ]);
            }

            $totalCount = (int) (clone $base)->count();
            $rows = (clone $base)->orderBy(['shortfall' => SORT_DESC])->limit($limit)->all();

            $itemCodes = array_values(array_unique(array_filter(array_column($rows, 'item_code'))));
            $itemModels = !empty($itemCodes)
                ? StockItem::find()->where(['code' => $itemCodes])->indexBy('code')->all()
                : [];

            // ผูกรูป + หน่วยจาก master
            $refs = [];
            foreach ($itemModels as $m) {
                if (!empty($m->ref)) $refs[] = (string) $m->ref;
            }
            $refToImg = [];
            if (!empty($refs)) {
                $uploads = \app\modules\filemanager\models\Uploads::find()
                    ->where(['ref' => array_values(array_unique($refs))])
                    ->orderBy(['id' => SORT_ASC])->all();
                foreach ($uploads as $u) {
                    if (!isset($refToImg[(string) $u->ref])) {
                        $refToImg[(string) $u->ref] = \app\modules\filemanager\components\FileManagerHelper::getImg($u->id);
                    }
                }
            }

            $items = [];
            foreach ($rows as $r) {
                $m = $itemModels[$r['item_code']] ?? null;
                $img = ($m && !empty($m->ref) && isset($refToImg[(string) $m->ref])) ? $refToImg[(string) $m->ref] : null;
                $items[] = [
                    'item_code' => (string) $r['item_code'],
                    'item_name' => (string) $r['item_name'],
                    'requested_qty' => (float) $r['requested_qty'],
                    'balance_qty' => (float) $r['balance_qty'],
                    'shortfall' => (float) $r['shortfall'],
                    'unit_name' => $m ? ($m->getUnitName() ?? '') : '',
                    'img' => $img,
                ];
            }

            return [
                'status' => 'success',
                'content' => $this->renderPartial('_insufficient_offcanvas_content', [
                    'items' => $items,
                    'totalCount' => $totalCount,
                    'shownCount' => count($items),
                    'q' => $q,
                    'currentWarehouseName' => $this->resolveWarehouseName($warehouseId),
                    'fullPageUrl' => \yii\helpers\Url::to(['/inventory-v2/report/insufficient-to-disburse']),
                ]),
                'total_count' => $totalCount,
                'shown_count' => count($items),
            ];
        } catch (\Throwable $e) {
            Yii::error('insufficient-offcanvas failed: ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => 'โหลดข้อมูลไม่สำเร็จ: ' . $e->getMessage()];
        }
    }

    /**
     * Offcanvas: Top N พัสดุเรียงตามมูลค่ารวม (qty × ราคา IN lot ล่าสุด) — 5 นาที cache ต่อคลัง
     */
    public function actionTopValueOffcanvas()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $q = trim((string) $this->request->get('q', ''));
            $limit = 30;

            $mainWarehouseIds = $this->getMainWarehouseIds();
            if (empty($mainWarehouseIds)) $mainWarehouseIds = [-1];
            $warehouseId = $this->getFilterWarehouseId();
            if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
                Yii::$app->session->remove('dashboard_warehouse_id');
                $warehouseId = null;
            }
            $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;
            if (empty($warehouseIds)) $warehouseIds = [-1];

            $cacheKey = 'top-value-offcanvas:' . md5(json_encode([$warehouseIds, $q, $limit]));
            $payload = Yii::$app->cache->getOrSet($cacheKey, function () use ($warehouseIds, $q, $limit) {
                // sb.balance_qty × latest IN lot unit_price ต่อ item
                $latestInPrice = (new Query())
                    ->select(['sd_in.item_code', 'sd_in.lot_number', 'sd_in.unit_price'])
                    ->from(['sd_in' => StockDetail::tableName()])
                    ->innerJoin(['so_in' => StockOrder::tableName()], 'so_in.id = sd_in.stock_order_id')
                    ->innerJoin(
                        ['latest' => (new Query())
                            ->select(['sd_l.item_code', 'sd_l.lot_number', new Expression('MAX(sd_l.id) AS mid')])
                            ->from(['sd_l' => StockDetail::tableName()])
                            ->innerJoin(['so_l' => StockOrder::tableName()], 'so_l.id = sd_l.stock_order_id')
                            ->where(['so_l.order_type' => 'IN'])
                            ->andWhere(['so_l.main_warehouse_id' => $warehouseIds])
                            ->groupBy(['sd_l.item_code', 'sd_l.lot_number'])],
                        'latest.item_code = sd_in.item_code AND latest.lot_number = sd_in.lot_number AND latest.mid = sd_in.id'
                    );

                $base = (new Query())
                    ->from(['sb' => StockBalance::tableName()])
                    ->innerJoin(['i' => StockItem::tableName()], 'i.code = sb.item_code')
                    ->leftJoin(['in_lot' => $latestInPrice], 'in_lot.item_code = sb.item_code AND in_lot.lot_number = sb.lot_number')
                    ->select([
                        'item_code' => 'sb.item_code',
                        'item_name' => 'i.title',
                        'total_qty' => new Expression('SUM(sb.balance_qty)'),
                        'total_value' => new Expression('SUM(sb.balance_qty * COALESCE(in_lot.unit_price, 0))'),
                    ])
                    ->where(['sb.warehouse_id' => $warehouseIds])
                    ->andWhere(['>', 'sb.balance_qty', 0])
                    ->groupBy(['sb.item_code', 'i.title']);

                if ($q !== '') {
                    $base->andWhere(['or',
                        ['like', 'i.code', $q],
                        ['like', 'i.title', $q],
                    ]);
                }

                // total count (distinct items)
                $totalCount = (int) (new Query())
                    ->from(['sb2' => StockBalance::tableName()])
                    ->innerJoin(['i2' => StockItem::tableName()], 'i2.code = sb2.item_code')
                    ->where(['sb2.warehouse_id' => $warehouseIds])
                    ->andWhere(['>', 'sb2.balance_qty', 0])
                    ->select('sb2.item_code')
                    ->groupBy('sb2.item_code')
                    ->count();

                $rows = $base->orderBy(['total_value' => SORT_DESC])->limit($limit)->all();
                return ['rows' => $rows, 'total' => $totalCount];
            }, 300);

            $rows = $payload['rows'];
            $totalCount = $payload['total'];

            $itemCodes = array_values(array_unique(array_filter(array_column($rows, 'item_code'))));
            $itemModels = !empty($itemCodes)
                ? StockItem::find()->where(['code' => $itemCodes])->indexBy('code')->all()
                : [];
            $refs = [];
            foreach ($itemModels as $m) {
                if (!empty($m->ref)) $refs[] = (string) $m->ref;
            }
            $refToImg = [];
            if (!empty($refs)) {
                $uploads = \app\modules\filemanager\models\Uploads::find()
                    ->where(['ref' => array_values(array_unique($refs))])
                    ->orderBy(['id' => SORT_ASC])->all();
                foreach ($uploads as $u) {
                    if (!isset($refToImg[(string) $u->ref])) {
                        $refToImg[(string) $u->ref] = \app\modules\filemanager\components\FileManagerHelper::getImg($u->id);
                    }
                }
            }

            $grandTotal = 0.0;
            foreach ($rows as $r) { $grandTotal += (float) $r['total_value']; }
            $items = [];
            foreach ($rows as $r) {
                $m = $itemModels[$r['item_code']] ?? null;
                $img = ($m && !empty($m->ref) && isset($refToImg[(string) $m->ref])) ? $refToImg[(string) $m->ref] : null;
                $value = (float) $r['total_value'];
                $items[] = [
                    'item_code' => (string) $r['item_code'],
                    'item_name' => (string) $r['item_name'],
                    'total_qty' => (float) $r['total_qty'],
                    'total_value' => $value,
                    'percent' => $grandTotal > 0.005 ? round(($value / $grandTotal) * 100, 1) : 0,
                    'unit_name' => $m ? ($m->getUnitName() ?? '') : '',
                    'img' => $img,
                ];
            }

            return [
                'status' => 'success',
                'content' => $this->renderPartial('_top_value_offcanvas_content', [
                    'items' => $items,
                    'totalCount' => $totalCount,
                    'shownCount' => count($items),
                    'shownValueSum' => $grandTotal,
                    'q' => $q,
                    'currentWarehouseName' => $this->resolveWarehouseName($warehouseId),
                    'fullPageUrl' => \yii\helpers\Url::to(['/inventory-v2/main-stock/items-with-stock']),
                ]),
                'total_count' => $totalCount,
                'shown_count' => count($items),
            ];
        } catch (\Throwable $e) {
            Yii::error('top-value-offcanvas failed: ' . $e->getMessage(), __METHOD__);
            return ['status' => 'error', 'message' => 'โหลดข้อมูลไม่สำเร็จ: ' . $e->getMessage()];
        }
    }

    /** Helper: resolve warehouse display name */
    protected function resolveWarehouseName($warehouseId)
    {
        if ($warehouseId === null) return 'ทั้งหมด';
        foreach ($this->getMainWarehousesList() as $w) {
            if ((int) $w->id === (int) $warehouseId) return $w->warehouse_name;
        }
        return 'ทั้งหมด';
    }

    /**
     * Offcanvas: ส่งรายการพัสดุที่ "ต่ำกว่าจุดสั่งซื้อ" (ยอดคงเหลือ < min_qty) เป็น JSON (content เป็น HTML)
     */
    public function actionCriticalItemsOffcanvas()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $q = trim((string) $this->request->get('q', ''));

            $mainWarehouseIds = $this->getMainWarehouseIds();
            if (empty($mainWarehouseIds)) {
                $mainWarehouseIds = [-1];
            }

            $warehouseId = $this->getFilterWarehouseId();
            if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
                Yii::$app->session->remove('dashboard_warehouse_id');
                $warehouseId = null;
            }

            $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;

            // สรุปยอดคงเหลือต่อ item_code ในคลังที่เลือก
            $balanceSub = (new Query())
                ->select(['item_code', 'SUM(balance_qty) AS total_qty'])
                ->from(StockBalance::tableName())
                ->where(['warehouse_id' => $warehouseIds])
                ->groupBy('item_code');

            // INNER JOIN: เฉพาะพัสดุที่อยู่ใน scope ของคลังนี้ (มี row ใน stock_balance)
            $itemsQuery = (new Query())
                ->from(['i' => StockItem::tableName()])
                ->innerJoin(['b' => $balanceSub], 'b.item_code = i.code')
                ->select([
                    'item_code' => 'i.code',
                    'item_name' => 'i.title',
                    'min_qty' => 'i.qty_min',
                    'current_qty' => 'b.total_qty',
                    'shortfall' => new Expression('i.qty_min - b.total_qty'),
                ])
                ->where([
                    'i.active' => 1,
                ])
                ->andWhere(['not', ['i.qty_min' => null]])
                ->andWhere(['>', 'i.qty_min', 0])
                ->andWhere('b.total_qty < i.qty_min');

            if ($q !== '') {
                $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
                $codeCond = ['like', 'i.code', $q];

                if (!empty($tokens)) {
                    $nameCond = ['and'];
                    foreach ($tokens as $t) {
                        $nameCond[] = ['like', 'i.title', $t];
                    }
                } else {
                    $nameCond = ['like', 'i.title', $q];
                }

                $itemsQuery->andWhere(['or', $codeCond, $nameCond]);
            }

            $totalCount = (int) (clone $itemsQuery)->count();

            $rows = (clone $itemsQuery)
                ->orderBy(['i.qty_min' => SORT_DESC, 'i.title' => SORT_ASC])
                ->limit(20)
                ->all();

            $itemCodes = array_values(array_unique(array_filter(array_map(function ($r) {
                return $r['item_code'] ?? null;
            }, $rows))));

            $itemModels = [];
            if (!empty($itemCodes)) {
                $itemModels = StockItem::find()
                    ->where(['item_code' => $itemCodes])
                    ->indexBy('item_code')
                    ->all();
            }

            $items = [];
            foreach ($rows as $r) {
                $code = $r['item_code'];
                $model = $itemModels[$code] ?? null;
                $items[] = [
                    'item_code' => $code,
                    'item_name' => $r['item_name'],
                    'min_qty' => (float) $r['min_qty'],
                    'current_qty' => (float) $r['current_qty'],
                    'shortfall' => (float) $r['shortfall'],
                    'unit_name' => $model ? $model->getUnitName() : null,
                    'img' => $model ? $model->ShowImg() : null,
                ];
            }

            $currentWarehouseName = 'ทั้งหมด';
            if ($warehouseId !== null) {
                foreach ($this->getMainWarehousesList() as $w) {
                    if ((int)$w->id === (int)$warehouseId) {
                        $currentWarehouseName = $w->warehouse_name;
                        break;
                    }
                }
            }

            $warehouseIdParam = $warehouseId !== null ? (string) (int) $warehouseId : 'all';

            return [
                'status' => 'success',
                'content' => $this->renderPartial('_critical_offcanvas_content', [
                    'items' => $items,
                    'totalCount' => $totalCount,
                    'shownCount' => count($items),
                    'currentWarehouseName' => $currentWarehouseName,
                    'q' => $q,
                    'fullPageUrl' => \yii\helpers\Url::to(['/inventory-v2/main-stock/balance', 'warehouse_id' => $warehouseId]),
                ]),
                'total_count' => $totalCount,
                'shown_count' => count($items),
            ];
        } catch (\Throwable $e) {
            Yii::error('critical-items-offcanvas failed: ' . $e->getMessage(), __METHOD__);
            return [
                'status' => 'error',
                'message' => 'โหลดข้อมูลไม่สำเร็จ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Export Excel: รายการพัสดุที่มีสต๊อก (>0) ตามคลังที่เลือก (และค้นหาด้วย q)
     */
    public function actionExportItemsWithStockExcel()
    {
        $q = trim((string) $this->request->get('q', ''));

        $mainWarehouseIds = $this->getMainWarehouseIds();
        if (empty($mainWarehouseIds)) {
            $mainWarehouseIds = [-1];
        }

        $warehouseId = $this->getFilterWarehouseId();
        if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
            Yii::$app->session->remove('dashboard_warehouse_id');
            $warehouseId = null;
        }

        $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;

        $itemsQuery = (new Query())
            ->from(['sb' => StockBalance::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sb.item_code')
            ->select([
                'sb.item_code',
                'item_name' => 'i.title',
                'total_qty' => new Expression('SUM(sb.balance_qty)'),
            ])
            ->where(['sb.warehouse_id' => $warehouseIds])
            ->andWhere(['>', 'sb.balance_qty', 0])
            ->groupBy(['sb.item_code', 'i.title'])
            ->orderBy(['i.title' => SORT_ASC]);

        if ($q !== '') {
            $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
            $codeCond = ['like', 'i.code', $q];

            if (!empty($tokens)) {
                $nameCond = ['and'];
                foreach ($tokens as $t) {
                    $nameCond[] = ['like', 'i.title', $t];
                }
            } else {
                $nameCond = ['like', 'i.title', $q];
            }

            $itemsQuery->andWhere(['or', $codeCond, $nameCond]);
        }

        $rows = $itemsQuery->all();
        $itemCodes = array_values(array_unique(array_filter(array_map(function ($r) {
            return $r['item_code'] ?? null;
        }, $rows))));

        $itemModels = [];
        if (!empty($itemCodes)) {
            $itemModels = StockItem::find()
                ->where(['item_code' => $itemCodes])
                ->indexBy('item_code')
                ->all();
        }

        $warehouseIdParam = $warehouseId !== null ? (string) (int) $warehouseId : 'all';
        $filename = 'items-with-stock-' . $warehouseIdParam . '-' . date('Ymd-His') . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายการพัสดุที่มีสต๊อก');

        $sheet->setCellValue('A1', 'รายการพัสดุที่มีสต๊อก');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(14);

        $headers = ['รหัสพัสดุ', 'ชื่อพัสดุ', 'หน่วย', 'จำนวนคงเหลือ'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }
        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A3:' . $lastCol . '3')->getFont()->setBold(true);
        $sheet->getStyle('A3:' . $lastCol . '3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A3:' . $lastCol . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 4;
        foreach ($rows as $r) {
            $code = $r['item_code'];
            $model = $itemModels[$code] ?? null;
            $unitName = $model ? $model->getUnitName() : null;

            $sheet->setCellValue('A' . $rowNum, $code);
            $sheet->setCellValue('B' . $rowNum, $r['item_name']);
            $sheet->setCellValue('C' . $rowNum, $unitName ?? '-');
            $sheet->setCellValue('D' . $rowNum, (float) $r['total_qty']);
            $rowNum++;
        }

        $sheet->getStyle('D4:D' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $this->response->format = Response::FORMAT_RAW;
        $this->response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($filename) . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        Yii::$app->end();
    }

    /**
     * Export Excel: รายการพัสดุที่ต่ำกว่าจุดสั่งซื้อ (ยอดคงเหลือ < min_qty)
     */
    public function actionExportCriticalItemsExcel()
    {
        $q = trim((string) $this->request->get('q', ''));

        $mainWarehouseIds = $this->getMainWarehouseIds();
        if (empty($mainWarehouseIds)) {
            $mainWarehouseIds = [-1];
        }

        $warehouseId = $this->getFilterWarehouseId();
        if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
            Yii::$app->session->remove('dashboard_warehouse_id');
            $warehouseId = null;
        }

        $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;

        $balanceSub = (new Query())
            ->select(['item_code', 'SUM(balance_qty) AS total_qty'])
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $warehouseIds])
            ->groupBy('item_code');

        // INNER JOIN: เฉพาะพัสดุที่อยู่ใน scope ของคลังนี้ (มี row ใน stock_balance)
        $itemsQuery = (new Query())
            ->from(['i' => StockItem::tableName()])
            ->innerJoin(['b' => $balanceSub], 'b.item_code = i.code')
            ->select([
                'item_code' => 'i.code',
                'item_name' => 'i.title',
                'min_qty' => 'i.qty_min',
                'current_qty' => 'b.total_qty',
                'shortfall' => new Expression('i.qty_min - b.total_qty'),
            ])
            ->where(['i.active' => 1])
            ->andWhere(['not', ['i.qty_min' => null]])
            ->andWhere(['>', 'i.qty_min', 0])
            ->andWhere('b.total_qty < i.qty_min');

        if ($q !== '') {
            $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
            $codeCond = ['like', 'i.code', $q];

            if (!empty($tokens)) {
                $nameCond = ['and'];
                foreach ($tokens as $t) {
                    $nameCond[] = ['like', 'i.title', $t];
                }
            } else {
                $nameCond = ['like', 'i.title', $q];
            }

            $itemsQuery->andWhere(['or', $codeCond, $nameCond]);
        }

        $rows = $itemsQuery
            ->orderBy(['i.qty_min' => SORT_DESC, 'i.title' => SORT_ASC])
            ->all();

        $itemCodes = array_values(array_unique(array_filter(array_map(function ($r) {
            return $r['item_code'] ?? null;
        }, $rows))));

        $itemModels = [];
        if (!empty($itemCodes)) {
            $itemModels = StockItem::find()
                ->where(['item_code' => $itemCodes])
                ->indexBy('item_code')
                ->all();
        }

        $warehouseIdParam = $warehouseId !== null ? (string) (int) $warehouseId : 'all';
        $filename = 'critical-items-' . $warehouseIdParam . '-' . date('Ymd-His') . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ต่ำกว่าจุดสั่งซื้อ');

        $sheet->setCellValue('A1', 'รายการพัสดุที่ต่ำกว่าจุดสั่งซื้อ');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true)->setSize(14);

        $headers = ['รหัสพัสดุ', 'ชื่อพัสดุ', 'ยอดคงเหลือ', 'จุดสั่งซื้อ', 'หน่วย'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }
        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A3:' . $lastCol . '3')->getFont()->setBold(true);
        $sheet->getStyle('A3:' . $lastCol . '3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A3:' . $lastCol . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 4;
        foreach ($rows as $r) {
            $code = $r['item_code'];
            $model = $itemModels[$code] ?? null;
            $unitName = $model ? $model->getUnitName() : null;

            $sheet->setCellValue('A' . $rowNum, $code);
            $sheet->setCellValue('B' . $rowNum, $r['item_name']);
            $sheet->setCellValue('C' . $rowNum, (float) $r['current_qty']);
            $sheet->setCellValue('D' . $rowNum, (float) $r['min_qty']);
            $sheet->setCellValue('E' . $rowNum, $unitName ?? '-');
            $rowNum++;
        }

        $sheet->getStyle('C4:D' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $this->response->format = Response::FORMAT_RAW;
        $this->response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($filename) . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        Yii::$app->end();
    }

    /** คลังหลักที่ยังไม่ถูกลบ (ถ้าไม่ใช่ admin เฉพาะคลังที่ผู้ใช้ถูกกำหนดเป็นผู้รับผิดชอบใน warehouse) */
    protected function getMainWarehouseIds()
    {
        $query = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->select('id');
        $this->applyOfficerFilter($query);
        return $query->column();
    }

    /** รายการคลังหลักสำหรับ dropdown (ถ้าไม่ใช่ admin เฉพาะคลังที่ผู้ใช้ถูกกำหนดเป็นผู้รับผิดชอบใน warehouse) */
    protected function getMainWarehousesList()
    {
        $query = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->orderBy('warehouse_name');
        $this->applyOfficerFilter($query);
        return $query->all();
    }

    /**
     * กรองคลังเฉพาะที่ current user ถูกกำหนดเป็นผู้รับผิดชอบ (data_json.officer)
     * ไม่ใช้กับ admin
     */
    protected function applyOfficerFilter($query)
    {
        if (Yii::$app->user->can('admin')) {
            return;
        }
        $userId = (string) Yii::$app->user->id;
        $query->andWhere(
            new Expression("JSON_CONTAINS(COALESCE(data_json,'{}'), '\"$userId\"', '$.officer')")
        );
    }

    /** warehouse id จาก session หรือ query (all = null) */
    protected function getFilterWarehouseId()
    {
        $session = Yii::$app->session;
        if ($this->request->get('warehouse_id') === 'all' || $this->request->get('warehouse_id') === '') {
            $session->remove('dashboard_warehouse_id');
            return null;
        }
        $id = $this->request->get('warehouse_id');
        if ($id !== null && $id !== '') {
            $id = (int) $id;
            $session->set('dashboard_warehouse_id', $id);
            return $id;
        }
        return $session->get('dashboard_warehouse_id');
    }

    /**
     * สถิติ Dashboard: รอจ่าย, รายการวิกฤต, มูลค่าคลัง, จำนวน Lot/รายการ
     */
    protected function getDashboardStats($warehouseId, array $mainWarehouseIds)
    {
        $baseRequisition = StockOrder::find()
            ->where([
                'order_type' => 'OUT',
                'source_type' => 'REQUEST',
                'status' => StockOrder::STATUS_APPROVED,
            ])
            ->andWhere(['main_warehouse_id' => $warehouseId ? [$warehouseId] : $mainWarehouseIds]);

        $pendingCount = (int) (clone $baseRequisition)->count();

        // INNER JOIN: นับเฉพาะพัสดุที่อยู่ใน scope ของคลังที่เลือก (มี row ใน stock_balance)
        // กัน false positive จาก master ที่ตั้ง qty_min ไว้แต่ไม่เคยรับเข้าคลังนี้
        $criticalQuery = (new Query())
            ->from(['i' => StockItem::tableName()])
            ->innerJoin(
                ['b' => (new Query())
                    ->select(['item_code', 'SUM(balance_qty) as total_qty'])
                    ->from(StockBalance::tableName())
                    ->where($warehouseId ? ['warehouse_id' => $warehouseId] : ['warehouse_id' => $mainWarehouseIds])
                    ->groupBy('item_code')],
                'b.item_code = i.code'
            )
            ->where(['and',
                ['i.active' => 1],
                ['not', ['i.qty_min' => null]],
                ['>', 'i.qty_min', 0],
            ])
            ->andWhere('b.total_qty < i.qty_min');
        $criticalCount = (int) $criticalQuery->count();

        $valueQuery = (new Query())
            ->from(['sb' => StockBalance::tableName()])
            ->leftJoin(
                ['sd' => StockDetail::tableName()],
                'sd.item_code = sb.item_code AND sd.lot_number = sb.lot_number'
            )
            ->innerJoin(
                ['so' => StockOrder::tableName()],
                'so.id = sd.stock_order_id AND so.order_type = \'IN\''
            )
            ->innerJoin(
                ['latest' => (new Query())
                    ->select(['sd2.item_code', 'sd2.lot_number', 'MAX(sd2.id) as mid'])
                    ->from(['sd2' => StockDetail::tableName()])
                    ->innerJoin(['so2' => StockOrder::tableName()], 'so2.id = sd2.stock_order_id AND so2.order_type = \'IN\'')
                    ->groupBy(['sd2.item_code', 'sd2.lot_number'])],
                'latest.item_code = sd.item_code AND latest.lot_number = sd.lot_number AND latest.mid = sd.id'
            )
            ->where($warehouseId ? ['sb.warehouse_id' => $warehouseId] : ['sb.warehouse_id' => $mainWarehouseIds]);
        $valueQuery->select(['SUM(sb.balance_qty * COALESCE(sd.unit_price, 0)) as total']);
        $totalValue = (float) $valueQuery->scalar();

        $usageQuery = (new Query())
            ->from(['sb' => StockBalance::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sb.item_code')
            ->where($warehouseId ? ['sb.warehouse_id' => $warehouseId] : ['sb.warehouse_id' => $mainWarehouseIds])
            ->andWhere(['>', 'sb.balance_qty', 0]);
        $lotsCount = (int) (clone $usageQuery)->count();
        $itemsCount = (int) (clone $usageQuery)->select('sb.item_code')->groupBy('sb.item_code')->count();

        $insufficientCount = $this->getInsufficientToDisburseCount($warehouseId, $mainWarehouseIds);

        return [
            'pending_count' => $pendingCount,
            'critical_count' => $criticalCount,
            'total_value' => $totalValue,
            'lots_count' => $lotsCount,
            'items_with_stock' => $itemsCount,
            'insufficient_to_disburse_count' => $insufficientCount,
        ];
    }

    /**
     * นับจำนวนรายการวัสดุที่ขอเบิก (ใบ PENDING + APPROVED) แต่ยอดในสต็อกไม่พอจ่าย
     * นับจาก: ยอดที่ขอเบิก (รวมจาก stock_detail ของใบ OUT/REQUEST/APPROVED) เทียบกับยอดคงเหลือใน stock_balance
     */
    protected function getInsufficientToDisburseCount($warehouseId, array $mainWarehouseIds)
    {
        $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;
        $reqSub = (new Query())
            ->select(['sd.item_code', 'SUM(sd.qty) AS requested_qty'])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where([
                'so.order_type' => 'OUT',
                'so.source_type' => 'REQUEST',
                'so.status' => [StockOrder::STATUS_PENDING, StockOrder::STATUS_APPROVED],
            ])
            ->andWhere(['so.main_warehouse_id' => $warehouseIds])
            ->groupBy('sd.item_code');
        $balSub = (new Query())
            ->select(['item_code', 'SUM(balance_qty) AS balance_qty'])
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $warehouseIds])
            ->groupBy('item_code');
        $q = (new Query())
            ->from(['req' => $reqSub])
            ->leftJoin(['bal' => $balSub], 'bal.item_code = req.item_code')
            ->where('req.requested_qty > COALESCE(bal.balance_qty, 0)');
        return (int) $q->count();
    }

    /** ใบขอเบิกรอคลังจ่าย (อนุมัติแล้ว APPROVED) */
    protected function getPendingRequisitions($warehouseId, array $mainWarehouseIds, $limit = 10)
    {
        $query = StockOrder::find()
            ->with(['mainWarehouse', 'subWarehouse', 'stockDetails'])
            ->where([
                'order_type' => 'OUT',
                'source_type' => 'REQUEST',
                'status' => StockOrder::STATUS_APPROVED,
            ])
            ->andWhere(['main_warehouse_id' => $warehouseId ? [$warehouseId] : $mainWarehouseIds])
            ->orderBy(['order_date' => SORT_DESC])
            ->limit($limit);
        return $query->all();
    }

    /**
     * ข้อมูลกราฟ: การรับเข้าและจ่ายออกต่อเดือน ในปีงบประมาณไทย (ต.ค. - ก.ย.)
     */
    /** Palette สำหรับ category (OKLCH-derived) — index คงที่ตาม alphabetical category code */
    protected static function categoryColorPalette()
    {
        return [
            '#2563eb', // blue
            '#0d9488', // teal
            '#16a34a', // green
            '#d97706', // amber
            '#dc2626', // red
            '#7c3aed', // violet
            '#0891b2', // cyan
            '#db2777', // pink
            '#475569', // slate
            '#65a30d', // lime
        ];
    }

    /**
     * ข้อมูล chart มูลค่ารับเข้า/จ่ายออก แยกตามประเภทวัสดุ ตามปี พ.ศ.
     *
     * @param int|null $warehouseId  null = ทุกคลังหลัก
     * @param int[] $mainWarehouseIds
     * @param int $thaiYear ปี พ.ศ. (ม.ค.-ธ.ค.)
     * @param int|null $month 1-12 หรือ null = ทั้งปี
     * @param string $direction IN | OUT | NET
     */
    protected function getFiscalMonthOrder()
    {
        return [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
    }

    protected function getFiscalYearDateRange($thaiYear)
    {
        $yearAD = (int) $thaiYear - 543;
        return [
            sprintf('%04d-10-01 00:00:00', $yearAD - 1),
            sprintf('%04d-09-30 23:59:59', $yearAD),
        ];
    }

    protected function getFiscalMonthDateRange($thaiYear, $month)
    {
        $yearAD = (int) $thaiYear - 543;
        $month = max(1, min(12, (int) $month));
        $monthYearAD = $month >= 10 ? $yearAD - 1 : $yearAD;
        $fromDate = sprintf('%04d-%02d-01 00:00:00', $monthYearAD, $month);
        $lastDay = (int) date('t', strtotime($fromDate));
        return [
            $fromDate,
            sprintf('%04d-%02d-%02d 23:59:59', $monthYearAD, $month, $lastDay),
        ];
    }

    protected function getMovementChartData($warehouseId, array $mainWarehouseIds, $thaiYear, $month = null, $direction = 'IN')
    {
        $monthLabels = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        $monthOrder = $this->getFiscalMonthOrder();
        $fiscalMonthLabels = array_map(fn($m) => $monthLabels[$m - 1], $monthOrder);
        $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;
        if (empty($warehouseIds)) {
            $warehouseIds = [-1];
        }

        [$fromDate, $toDate] = $this->getFiscalYearDateRange($thaiYear);
        $yearAD = (int) $thaiYear - 543;

        $needIn = ($direction === 'IN' || $direction === 'NET');
        $needOut = ($direction === 'OUT' || $direction === 'NET');

        // category index: code → ['title', 'color']
        $catRows = Categorise::find()
            ->where(['name' => 'asset_type', 'group_id' => 'MATER'])
            ->orderBy(['code' => SORT_ASC])
            ->all();
        $palette = self::categoryColorPalette();
        $catIndex = [];
        foreach ($catRows as $idx => $cat) {
            $catIndex[(string) $cat->code] = [
                'title' => (string) $cat->title,
                'color' => $palette[$idx % count($palette)],
            ];
        }
        $otherColor = '#9ca3af';

        // bucket: [category_code][month_1-12] = value
        $bucket = [];
        $ensureBucket = function ($code) use (&$bucket) {
            if (!isset($bucket[$code])) {
                $bucket[$code] = array_fill(1, 12, 0.0);
            }
        };

        // กำหนดว่าเดือนใดบ้างที่ "ปิดแล้ว" (มี snapshot ใน stock_monthly_report สำหรับ warehouse + fiscal year)
        // closed[month_1-12] = true ถ้าทุก warehouse ที่เลือกถูกปิดเดือนนี้แล้ว
        $closedMonths = $this->getClosedMonthsInFiscalYear($warehouseIds, $thaiYear);

        // อ่านข้อมูลจาก snapshot สำหรับเดือนที่ปิดแล้ว (รวม ADJUST แล้ว)
        $this->fillBucketFromSnapshot(
            $bucket,
            $catIndex,
            $ensureBucket,
            $otherColor,
            $warehouseIds,
            $thaiYear,
            $closedMonths,
            $direction,
            $needIn,
            $needOut
        );

        // เดือนที่ยังไม่ปิด — ใช้ real-time จาก stock_order/stock_detail
        // สร้าง list ของเดือนเปิด (calendar month_1-12) เพื่อ filter NOT IN ในระดับ SQL
        $openCalendarMonths = [];
        foreach ($monthOrder as $cm) {
            if (empty($closedMonths[$cm])) {
                $openCalendarMonths[] = $cm;
            }
        }

        if (!empty($openCalendarMonths)) {
            $this->fillBucketRealtime(
                $bucket,
                $catIndex,
                $ensureBucket,
                $otherColor,
                $warehouseIds,
                $fromDate,
                $toDate,
                $openCalendarMonths,
                $direction,
                $needIn,
                $needOut
            );
        }

        // is_closed flag ต่อเดือนตามลำดับ fiscal (12 ค่า)
        $closedFlags = array_map(fn($m) => !empty($closedMonths[$m]), $monthOrder);

        // ถ้าโหมดรายเดือน — กรองเหลือเฉพาะเดือนนั้น (สำหรับ totals)
        $series = [];
        $totalsByCategory = [];
        $grandTotal = 0.0;

        foreach ($bucket as $code => $monthlyData) {
            $info = $catIndex[$code] ?? ['title' => $code, 'color' => $otherColor];
            $data12 = array_map(fn($m) => $monthlyData[$m] ?? 0.0, $monthOrder);
            $totalForPeriod = ($month === null)
                ? array_sum($data12)
                : ($monthlyData[$month] ?? 0);

            // skip ถ้าเป็น 0 ทั้งหมด
            if (abs($totalForPeriod) < 0.005 && array_sum(array_map('abs', $data12)) < 0.005) {
                continue;
            }

            $series[] = [
                'code' => $code,
                'name' => $info['title'],
                'color' => $info['color'],
                'data' => array_map(fn($v) => round($v, 2), $data12),
            ];
            $totalsByCategory[] = [
                'code' => $code,
                'name' => $info['title'],
                'color' => $info['color'],
                'value' => round($totalForPeriod, 2),
            ];
            $grandTotal += $totalForPeriod;
        }

        // sort series + totals by descending total
        usort($totalsByCategory, fn($a, $b) => $b['value'] <=> $a['value']);
        $orderByCode = [];
        foreach ($totalsByCategory as $idx => $t) {
            $orderByCode[$t['code']] = $idx;
        }
        usort($series, fn($a, $b) => ($orderByCode[$a['code']] ?? 99) <=> ($orderByCode[$b['code']] ?? 99));

        foreach ($totalsByCategory as &$t) {
            $t['percent'] = (abs($grandTotal) > 0.005) ? round(($t['value'] / $grandTotal) * 100, 1) : 0;
        }
        unset($t);

        return [
            'mode' => $month === null ? 'year' : 'month',
            'year' => (int) $thaiYear,
            'month' => $month,
            'direction' => $direction,
            'monthOrder' => $monthOrder,
            'months' => $fiscalMonthLabels,
            'closed_flags' => $closedFlags,
            'series' => $series,
            'totals' => $totalsByCategory,
            'total' => round($grandTotal, 2),
            'is_empty' => empty($series),
        ];
    }

    /**
     * คืน map [calendar_month_1-12 => true] ของเดือนที่ "ปิดแล้ว" ใน fiscal year ที่ระบุ
     *
     * นิยาม "ปิดแล้ว": มี snapshot row อย่างน้อย 1 row ใน stock_monthly_report
     * สำหรับ (year, month) ของ fiscal year นั้น ภายใน scope warehouseIds
     *
     * เดิมใช้นิยามเข้ม "ทุก warehouse ต้องมี snapshot" — แต่ถ้าคลังใดไม่มี item เคลื่อนไหว
     * เลย closeMonth จะไม่ save row ของคลังนั้น (count = 0) ทำให้ flag stays false
     * แม้ user ปิดเดือนสำเร็จแล้ว → false negative
     *
     * Key เป็น month 1-12 ตาม fiscal calendar (Oct-Sep) ภายใน fiscal year เดียว
     * — SQL where clause กรอง fiscal range เรียบร้อยแล้ว ไม่มี collision ระหว่างปี
     */
    protected function getClosedMonthsInFiscalYear(array $warehouseIds, $thaiYear)
    {
        $yearAD = (int) $thaiYear - 543;
        // fiscal year: Oct (yearAD-1) → Sep yearAD
        $rows = (new Query())
            ->select([
                'report_year' => 'smr.report_year',
                'report_month' => 'smr.report_month',
            ])
            ->from(['smr' => StockMonthlyReport::tableName()])
            ->where(['smr.warehouse_id' => $warehouseIds])
            ->andWhere(['or',
                ['and', ['smr.report_year' => $yearAD - 1], ['>=', 'smr.report_month', 10]],
                ['and', ['smr.report_year' => $yearAD], ['<=', 'smr.report_month', 9]],
            ])
            ->groupBy(['smr.report_year', 'smr.report_month'])
            ->all();

        $closed = [];
        foreach ($rows as $r) {
            $closed[(int) $r['report_month']] = true;
        }
        return $closed;
    }

    protected function fillBucketFromSnapshot(
        array &$bucket,
        array &$catIndex,
        callable $ensureBucket,
        string $otherColor,
        array $warehouseIds,
        $thaiYear,
        array $closedMonths,
        string $direction,
        bool $needIn,
        bool $needOut
    ) {
        if (empty($closedMonths)) {
            return;
        }
        $yearAD = (int) $thaiYear - 543;

        // ค่าแสดงต่อเดือนจาก snapshot:
        //   IN  = in_value + adjust_in_value
        //   OUT = total_out_value + adjust_out_value
        //   NET = IN - OUT
        $select = [
            'category_code' => new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"),
            'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
            'm' => 'smr.report_month',
            'in_val' => new Expression('SUM(COALESCE(smr.in_value, 0) + COALESCE(smr.adjust_in_value, 0))'),
            'out_val' => new Expression('SUM(COALESCE(smr.total_out_value, 0) + COALESCE(smr.adjust_out_value, 0))'),
        ];

        $rows = (new Query())
            ->select($select)
            ->from(['smr' => StockMonthlyReport::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = smr.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where(['smr.warehouse_id' => $warehouseIds])
            ->andWhere(['smr.report_month' => array_keys($closedMonths)])
            ->andWhere(['or',
                ['and', ['smr.report_year' => $yearAD - 1], ['>=', 'smr.report_month', 10]],
                ['and', ['smr.report_year' => $yearAD], ['<=', 'smr.report_month', 9]],
            ])
            ->groupBy([new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"), 'smr.report_month'])
            ->all();

        foreach ($rows as $r) {
            $code = (string) $r['category_code'];
            $m = (int) $r['m'];
            if ($m < 1 || $m > 12) continue;
            $ensureBucket($code);
            if (!isset($catIndex[$code])) {
                $catIndex[$code] = ['title' => (string) $r['category_title'], 'color' => $otherColor];
            }
            $inVal = (float) ($r['in_val'] ?? 0);
            $outVal = (float) ($r['out_val'] ?? 0);
            if ($direction === 'IN') {
                $bucket[$code][$m] += $inVal;
            } elseif ($direction === 'OUT') {
                $bucket[$code][$m] += $outVal;
            } else { // NET
                $bucket[$code][$m] += ($inVal - $outVal);
            }
        }
    }

    protected function fillBucketRealtime(
        array &$bucket,
        array &$catIndex,
        callable $ensureBucket,
        string $otherColor,
        array $warehouseIds,
        string $fromDate,
        string $toDate,
        array $openCalendarMonths,
        string $direction,
        bool $needIn,
        bool $needOut
    ) {
        // IN: qty * sd.unit_price
        if ($needIn) {
            $inQuery = (new Query())
                ->select([
                    'category_code' => new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"),
                    'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                    'm' => new Expression('MONTH(so.order_date)'),
                    'value' => new Expression('SUM(sd.qty * COALESCE(sd.unit_price, 0))'),
                ])
                ->from(['sd' => StockDetail::tableName()])
                ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
                ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
                ->where(['so.order_type' => StockOrder::ORDER_TYPE_IN])
                ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
                ->andWhere(['so.main_warehouse_id' => $warehouseIds])
                ->andWhere(['between', 'so.order_date', $fromDate, $toDate])
                ->andWhere(['in', new Expression('MONTH(so.order_date)'), $openCalendarMonths])
                ->groupBy([new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"), new Expression('MONTH(so.order_date)')]);

            foreach ($inQuery->all() as $r) {
                $code = (string) $r['category_code'];
                $m = (int) $r['m'];
                if ($m < 1 || $m > 12) continue;
                $ensureBucket($code);
                if (!isset($catIndex[$code])) {
                    $catIndex[$code] = ['title' => (string) $r['category_title'], 'color' => $otherColor];
                }
                $v = (float) $r['value'];
                $bucket[$code][$m] += $v;
            }
        }

        // OUT: qty * IN lot unit_price (ตาม lot_number) — ตรรกะเดียวกับ close-month
        if ($needOut) {
            $latestInPrice = (new Query())
                ->select(['sd_in.item_code', 'sd_in.lot_number', 'sd_in.unit_price'])
                ->from(['sd_in' => StockDetail::tableName()])
                ->innerJoin(['so_in' => StockOrder::tableName()], 'so_in.id = sd_in.stock_order_id')
                ->innerJoin(
                    ['latest' => (new Query())
                        ->select(['sd_l.item_code', 'sd_l.lot_number', new Expression('MAX(sd_l.id) AS mid')])
                        ->from(['sd_l' => StockDetail::tableName()])
                        ->innerJoin(['so_l' => StockOrder::tableName()], 'so_l.id = sd_l.stock_order_id')
                        ->where(['so_l.order_type' => StockOrder::ORDER_TYPE_IN])
                        ->andWhere(['so_l.main_warehouse_id' => $warehouseIds])
                        ->groupBy(['sd_l.item_code', 'sd_l.lot_number'])],
                    'latest.item_code = sd_in.item_code AND latest.lot_number = sd_in.lot_number AND latest.mid = sd_in.id'
                );

            $outQuery = (new Query())
                ->select([
                    'category_code' => new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"),
                    'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                    'm' => new Expression('MONTH(so.order_date)'),
                    'value' => new Expression('SUM(sd.qty * COALESCE(in_lot.unit_price, sd.unit_price, 0))'),
                ])
                ->from(['sd' => StockDetail::tableName()])
                ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
                ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
                ->leftJoin(['in_lot' => $latestInPrice], 'in_lot.item_code = sd.item_code AND in_lot.lot_number = sd.lot_number')
                ->where(['so.order_type' => StockOrder::ORDER_TYPE_OUT])
                ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
                ->andWhere(['so.main_warehouse_id' => $warehouseIds])
                ->andWhere(['between', 'so.order_date', $fromDate, $toDate])
                ->andWhere(['in', new Expression('MONTH(so.order_date)'), $openCalendarMonths])
                ->groupBy([new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"), new Expression('MONTH(so.order_date)')]);

            foreach ($outQuery->all() as $r) {
                $code = (string) $r['category_code'];
                $m = (int) $r['m'];
                if ($m < 1 || $m > 12) continue;
                $ensureBucket($code);
                if (!isset($catIndex[$code])) {
                    $catIndex[$code] = ['title' => (string) $r['category_title'], 'color' => $otherColor];
                }
                $v = (float) $r['value'];
                $bucket[$code][$m] += ($direction === 'NET') ? -$v : $v;
            }
        }

        // ADJUST: fold เข้า IN/OUT ตามเครื่องหมาย qty
        //   qty > 0 → เพิ่มยอด (รวมเข้าฝั่ง IN)
        //   qty < 0 → ลดยอด (รวมเข้าฝั่ง OUT ในรูปบวก, หรือลบใน NET)
        // ใช้ราคา IN ล่าสุดของ item (cross-lot) เพราะ ADJUST lot_number = 'ADJUST' ไม่ตรงกับ lot จริง
        $latestInPriceByItem = (new Query())
            ->select(['sd_in.item_code', 'sd_in.unit_price'])
            ->from(['sd_in' => StockDetail::tableName()])
            ->innerJoin(['so_in' => StockOrder::tableName()], 'so_in.id = sd_in.stock_order_id')
            ->innerJoin(
                ['latest_item' => (new Query())
                    ->select(['sd_l.item_code', new Expression('MAX(sd_l.id) AS mid')])
                    ->from(['sd_l' => StockDetail::tableName()])
                    ->innerJoin(['so_l' => StockOrder::tableName()], 'so_l.id = sd_l.stock_order_id')
                    ->where(['so_l.order_type' => StockOrder::ORDER_TYPE_IN])
                    ->andWhere(['so_l.main_warehouse_id' => $warehouseIds])
                    ->groupBy(['sd_l.item_code'])],
                'latest_item.item_code = sd_in.item_code AND latest_item.mid = sd_in.id'
            );

        // ฝั่ง IN (qty บวก): ใช้ตอน direction = IN หรือ NET
        if ($needIn) {
            $adjInQuery = (new Query())
                ->select([
                    'category_code' => new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"),
                    'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                    'm' => new Expression('MONTH(so.order_date)'),
                    'value' => new Expression('SUM(sd.qty * COALESCE(in_item.unit_price, 0))'),
                ])
                ->from(['sd' => StockDetail::tableName()])
                ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
                ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
                ->leftJoin(['in_item' => $latestInPriceByItem], 'in_item.item_code = sd.item_code')
                ->where(['so.order_type' => StockOrder::ORDER_TYPE_ADJUST])
                ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
                ->andWhere(['so.main_warehouse_id' => $warehouseIds])
                ->andWhere(['between', 'so.order_date', $fromDate, $toDate])
                ->andWhere(['in', new Expression('MONTH(so.order_date)'), $openCalendarMonths])
                ->andWhere(['>', 'sd.qty', 0])
                ->groupBy([new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"), new Expression('MONTH(so.order_date)')]);

            foreach ($adjInQuery->all() as $r) {
                $code = (string) $r['category_code'];
                $m = (int) $r['m'];
                if ($m < 1 || $m > 12) continue;
                $ensureBucket($code);
                if (!isset($catIndex[$code])) {
                    $catIndex[$code] = ['title' => (string) $r['category_title'], 'color' => $otherColor];
                }
                $v = (float) $r['value'];
                $bucket[$code][$m] += $v;
            }
        }

        // ฝั่ง OUT (qty ลบ): เก็บมาเป็นค่าบวก
        if ($needOut) {
            $adjOutQuery = (new Query())
                ->select([
                    'category_code' => new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"),
                    'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                    'm' => new Expression('MONTH(so.order_date)'),
                    'value' => new Expression('SUM(-sd.qty * COALESCE(in_item.unit_price, 0))'),
                ])
                ->from(['sd' => StockDetail::tableName()])
                ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
                ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
                ->leftJoin(['in_item' => $latestInPriceByItem], 'in_item.item_code = sd.item_code')
                ->where(['so.order_type' => StockOrder::ORDER_TYPE_ADJUST])
                ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
                ->andWhere(['so.main_warehouse_id' => $warehouseIds])
                ->andWhere(['between', 'so.order_date', $fromDate, $toDate])
                ->andWhere(['in', new Expression('MONTH(so.order_date)'), $openCalendarMonths])
                ->andWhere(['<', 'sd.qty', 0])
                ->groupBy([new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"), new Expression('MONTH(so.order_date)')]);

            foreach ($adjOutQuery->all() as $r) {
                $code = (string) $r['category_code'];
                $m = (int) $r['m'];
                if ($m < 1 || $m > 12) continue;
                $ensureBucket($code);
                if (!isset($catIndex[$code])) {
                    $catIndex[$code] = ['title' => (string) $r['category_title'], 'color' => $otherColor];
                }
                $v = (float) $r['value'];
                $bucket[$code][$m] += ($direction === 'NET') ? -$v : $v;
            }
        }
    }
    //     public function actionReceive()
    // {
    //     $model = new StockOrder();
    //     $model->order_type = 'IN'; // ระบุเป็นประเภทรับเข้า
    //     $model->order_date = date('Y-m-d H:i:s');
    //     $model->order_no = 'RCV' . date('YmdHis'); // แนะนำให้ทำ Auto Gen ใน model
    //     $model->status = 'CONFIRMED'; // หรือ DRAFT ตาม workflow

    //     $items = [new StockDetail()];

    //     if ($this->request->isPost) {
    //         $model->load($this->request->post());

    //         // รับค่ารายการพัสดุจากหน้าฟอร์ม (Array of objects)
    //         $postDetails = $this->request->post('StockDetail', []);
    //         $items = [];

    //         // Database Transaction เพื่อความปลอดภัยของข้อมูล
    //         $dbTransaction = Yii::$app->db->beginTransaction();
    //         try {
    //             if ($model->save()) {
    //                 foreach ($postDetails as $i => $detailData) {
    //                     $detail = new StockDetail();
    //                     $detail->load($detailData, ''); // load ข้อมูลรายบรรทัด
    //                     $detail->stock_order_id = $model->id; // เชื่อม id กับหัวเอกสาร

    //                     if ($detail->save()) {
    //                         // 🚀 ส่งข้อมูลไป Update ยอดคงเหลือ Real-time ใน StockBalance
    //                         // และบันทึกลง StockDetail (Transaction Log) ผ่าน Service
    //                         $isUpdated = InventoryService::moveStock(
    //                             $detail->item_code, 
    //                             $model->warehouse_id, 
    //                             $detail->qty, 
    //                             'IN', 
    //                             $model->id
    //                         );

    //                         if (!$isUpdated) {
    //                             throw new \Exception("ไม่สามารถอัปเดตยอดสต็อกรายการที่ " . ($i + 1));
    //                         }
    //                     } else {
    //                         throw new \Exception("บันทึกรายการพัสดุไม่สำเร็จ: " . implode(', ', $detail->getFirstErrors()));
    //                     }
    //                 }

    //                 $dbTransaction->commit();
    //                 Yii::$app->session->setFlash('success', 'บันทึกรับเข้าวัสดุเรียบร้อยแล้ว');
    //                 return $this->redirect(['view', 'id' => $model->id]);
    //             }
    //         } catch (\Exception $e) {
    //             $dbTransaction->rollBack();
    //             Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    //         }
    //     }

    //     return $this->render('_form_receive', [
    //         'model' => $model,
    //         'items' => (empty($items)) ? [new StockDetail()] : $items,
    //         'listWarehouse' => ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(), 'id', 'warehouse_name'),
    //         // ดึงรายการพัสดุไปใช้ใน Tom-Select
    //         'listItems' => StockItem::find()->where(['is_active' => 1])->all(),
    //     ]);
    // }

    public function actionReceive()
    {
        $model = new StockOrder();
        $model->order_type = 'IN';
        $model->status = 'CONFIRMED'; // กำหนดสถานะรอไว้เลย

        if ($this->request->isPost) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $db = \Yii::$app->db;
            $transaction = $db->beginTransaction();

            try {
                // 1. โหลดข้อมูลหัวเอกสาร
                if ($model->load($this->request->post()) && $model->save()) {

                    // 2. รับค่า StockDetail จากฟอร์ม
                    $details = $this->request->post('StockDetail', []);

                    if (empty($details)) {
                        throw new \Exception("กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ");
                    }

                    foreach ($details as $i => $data) {
                        $detail = new StockDetail();

                        // สำคัญ: ต้องโหลดแบบเซตค่าตรงๆ หรือ load($data, '') 
                        // เพราะเราสร้าง Array name="StockDetail[i][field]" ใน JS
                        if ($detail->load($data, '')) {
                            $detail->stock_order_id = $model->id;

                            if (!$detail->save()) {
                                // ถ้า Detail บันทึกไม่สำเร็จ ให้ส่ง Error กลับไปดู
                                $errors = implode(', ', $detail->getFirstErrors());
                                throw new \Exception("รายการที่ " . ($i + 1) . " ติดปัญหา: " . $errors);
                            }

                            // 3. อัปเดตสต็อกจริง (Service ที่เราทำไว้ตอนแรก)
                            $success = InventoryService::moveStock(
                                $detail->item_code,
                                $model->main_warehouse_id,
                                $detail->qty,
                                'IN',
                                $model->id
                            );

                            if (!$success) {
                                throw new \Exception("ระบบไม่สามารถอัปเดตยอดคงเหลือในคลังได้");
                            }
                        }
                    }

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
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        // กรณีโหลดหน้าเว็บปกติ (GET) — คลังสินค้าแสดงตามการกำหนดเจ้าหน้าที่รับผิดชอบคลัง (admin เห็นทั้งหมด)
        return $this->render('_form_receive', [
            'model' => $model,
            'listWarehouse' => ArrayHelper::map(Warehouse::findMainWarehousesForReceive(), 'id', 'warehouse_name'),
        ]);
    }

    public function actionUpdateReceive($id)
    {
        $model = StockOrder::find()
            ->where(['id' => $id])
            ->with('stockDetails.item') // โหลดรายละเอียดและข้อมูลพัสดุมาพร้อมกันเลย
            ->one();

        $oldItems = $model->stockDetails; // เก็บรายการเก่าไว้สำหรับเปรียบเทียบหรือคืนสต็อก

        if ($this->request->isPost) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $db = \Yii::$app->db;
            $transaction = $db->beginTransaction();

            try {
                // 1. ลบรายการเก่าใน DB ออกก่อน (ภายใต้ Transaction)
                StockDetail::deleteAll(['stock_order_id' => $model->id]);
                // 2. รับค่าจากฟอร์ม (ซึ่งตอนนี้ rowIndex จะไม่ซ้ำกันแล้ว)
                $details = $this->request->post('StockDetail', []);
                // 1. คืนสต็อกของรายการเก่าทั้งหมดก่อน (Reverse Stock)
                foreach ($oldItems as $oldItem) {
                    InventoryService::moveStock(
                        $oldItem->item_code,
                        $model->main_warehouse_id,
                        $oldItem->qty,
                        'OUT', // ใช้ OUT เพื่อหักลดยอดที่เคยรับเข้า (Reverse IN)
                        $model->id
                    );
                }

                // 2. โหลดและบันทึกข้อมูลหัวเอกสารใหม่
                if ($model->load($this->request->post()) && $model->save()) {

                    // ลบรายการ Detail เดิมทั้งหมดเพื่อเตรียมบันทึกใหม่ (แบบ Re-insert)
                    // หรือจะใช้วิธีเช็ค ID เพื่อ Update รายบรรทัดก็ได้ แต่ Re-insert จะจัดการง่ายกว่าในเคสนี้
                    StockDetail::deleteAll(['stock_order_id' => $model->id]);

                    $details = $this->request->post('StockDetail', []);
                    foreach ($details as $i => $data) {
                        $detail = new StockDetail();
                        if ($detail->load($data, '')) {
                            $detail->stock_order_id = $model->id;

                            if ($detail->save()) {
                                // 3. ปรับปรุงสต็อกตามยอดใหม่ (Update Balance)
                                InventoryService::moveStock(
                                    $detail->item_code,
                                    $model->main_warehouse_id,
                                    $detail->qty,
                                    'IN',
                                    $model->id
                                );
                            } else {
                                throw new \Exception("รายการที่ " . ($i + 1) . " บันทึกไม่สำเร็จ");
                            }
                        }
                    }

                    $transaction->commit();
                    return [
                        'success' => true,
                        'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ];
            }
        }

        // คลังสินค้าแสดงตามการกำหนดเจ้าหน้าที่รับผิดชอบคลัง (admin เห็นทั้งหมด)
        return $this->render('_form_receive', [
            'model' => $model,
            'items' => $model->stockDetails, // ส่งรายการเดิมไปแสดงในตาราง
            'listWarehouse' => ArrayHelper::map(Warehouse::findMainWarehousesForReceive(), 'id', 'warehouse_name'),
        ]);
    }


    protected function findModel($id)
    {
        if (($model = StockOrder::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
