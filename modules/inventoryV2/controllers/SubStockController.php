<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\components\InventoryService;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\web\Response;
use yii\web\UploadedFile;

class SubStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        if (empty($this->getSubWarehouseIdsForIssue())) {
            return $this->redirectToSubStockDashboard();
        }

        return $this->render('index');
    }

    /**
     * รายงานสรุปยอดคงเหลือ — มุมเจ้าหน้าที่คลังย่อย/แผนก
     * Scope: เฉพาะคลังย่อย (SUB) ที่ user เป็น officer ใน data_json.officer
     */
    public function actionBalance()
    {
        $accessible = Warehouse::findSubWarehousesForUser();
        $context = ReportController::buildBalanceContext(
            $accessible,
            $this->request->getQueryParams(),
            '-- ทุกคลังย่อย --'
        );

        return $this->render('balance', array_merge($context, [
            'accessibleWarehouseCount' => count($accessible),
        ]));
    }

    /**
     * Export Excel ของยอดคงเหลือ — มุมเจ้าหน้าที่คลังย่อย
     */
    public function actionExportBalance()
    {
        $accessible = Warehouse::findSubWarehousesForUser();
        $context = ReportController::buildBalanceContext(
            $accessible,
            $this->request->getQueryParams(),
            '-- ทุกคลังย่อย --'
        );
        ReportController::streamBalanceXlsx(
            $context['rows'],
            'balance-sub-warehouse'
        );
    }

    /**
     * Dashboard คลังย่อย — ใช้ข้อมูลจริงจากใบจ่ายที่ส่งมาที่คลังย่อย (มีตัวเลือกชื่อคลังย่อย)
     */
    public function actionDashboard()
    {
        $allowedSubIds = $this->getSubWarehouseIdsForIssue();
        if (empty($allowedSubIds)) {
            return $this->renderPermissionBlockedDashboard();
        }

        $allSubIds = $allowedSubIds;

        $warehouseId = $this->getFilterWarehouseId($allowedSubIds);
        if ($warehouseId !== null && !in_array($warehouseId, $allSubIds, true)) {
            $warehouseId = null;
        }

        $subWarehouseIds = $warehouseId !== null ? [$warehouseId] : $allSubIds;

        $stats = $this->getDashboardStats($subWarehouseIds);
        $pendingIssueList = $this->getPendingDisbursementList($subWarehouseIds, 10);
        $chartData = $this->getChartDataLast7Days($subWarehouseIds);
        $warehouses = $this->getSubWarehousesList();


        $usageHistory = [];
        $helpdeskIdFilter = (int) $this->request->get('helpdesk_id', 0);
        if ($helpdeskIdFilter > 0) {
            // DEBUG/ค้นหาแบบกว้าง: แสดงว่ามี stock_order ที่เกี่ยวข้องถูกสร้างและมีเลข helpdesk_id นี้อยู่ใน ref/data_json หรือไม่
            // (ไม่จำกัด status เผื่อกรณีบันทึกแต่ยังไม่ CONFIRMED)
            $needle = (string) $helpdeskIdFilter;
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere([
                    'or',
                    ['<>', 'source_type', 'REQUEST'],
                    ['source_type' => null],
                    ['source_type' => ''],
                ])
                ->andWhere([
                    'or',
                    ['like', 'ref', $needle],
                    ['like', 'data_json', $needle],
                ])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        } else if ($warehouseId > 0) {
            // แสดงเฉพาะคลังที่เลือก (ถ้าส่ง warehouse_id มา)
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere(['!=', 'status', StockOrder::STATUS_CANCELLED])
                ->andWhere([
                    'or',
                    ['source_type' => 'USAGE'],
                    ['like', 'ref', 'HELPDESK_ID:%'],
                    ['like', 'data_json', '"helpdesk_id"'],
                    ['like', 'data_json', 'helpdesk_id'],
                ])
                ->andWhere(['or', ['main_warehouse_id' => $warehouseId], ['sub_warehouse_id' => $warehouseId]])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        } else {
            // รวมทุกคลังย่อยที่ผู้ใช้มีสิทธิ
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere(['!=', 'status', StockOrder::STATUS_CANCELLED])
                ->andWhere([
                    'or',
                    ['source_type' => 'USAGE'],
                    ['like', 'ref', 'HELPDESK_ID:%'],
                    ['like', 'data_json', '"helpdesk_id"'],
                    ['like', 'data_json', 'helpdesk_id'],
                ])
                ->andWhere([
                    'or',
                    ['<>', 'source_type', 'REQUEST'],
                    ['source_type' => null],
                    ['source_type' => ''],
                ])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        }


        return $this->render('dashboard', [
            'pendingDisbursementCount' => $stats['pending_disbursement'],
            'criticalCount' => $stats['critical_count'],
            'monthlyValue' => $stats['monthly_value'],
            'expiringSoonCount' => $stats['expiring_soon'],
            'pendingIssueList' => $pendingIssueList,
            'chartData' => $chartData,
            'subWarehouseIds' => $subWarehouseIds,
            'warehouses' => $warehouses,
            'currentWarehouseId' => $warehouseId,
            'usageHistory' => $usageHistory,
            'canCreateRequisition' => $this->canCreateRequisition(),
        ]);
    }

    protected function renderPermissionBlockedDashboard()
    {
        return $this->render('dashboard', [
            'pendingDisbursementCount' => 0,
            'criticalCount' => 0,
            'monthlyValue' => 0,
            'expiringSoonCount' => 0,
            'pendingIssueList' => [],
            'chartData' => ['categories' => [], 'series' => []],
            'subWarehouseIds' => [],
            'warehouses' => [],
            'currentWarehouseId' => null,
            'usageHistory' => [],
            'permissionBlocked' => true,
            'permissionMessage' => $this->getSubStockPermissionMessage(),
            'canCreateRequisition' => $this->canCreateRequisition(),
        ]);
    }

    /**
     * สิทธิ์สร้างใบขอเบิกจากคลังหลัก — เฉพาะ user ที่ถูกกำหนดเป็นผู้รับผิดชอบ
     * (data_json.officer) ของคลังย่อยอย่างน้อย 1 คลัง หรือเป็น admin
     */
    protected function canCreateRequisition(): bool
    {
        if (Yii::$app->user->can('admin')) {
            return true;
        }
        return !empty(Warehouse::findSubWarehousesForUser());
    }

    protected function redirectToSubStockDashboard()
    {
        return $this->redirect(['/inventory-v2/sub-stock/dashboard']);
    }

    protected function getSubStockPermissionMessage()
    {
        $departmentName = $this->getCurrentUserDepartmentName();
        $subject = $departmentName !== '' ? $departmentName : 'หน่วยงานของคุณ';

        return $subject . 'ยังไม่ได้กำหนดแผนก/ฝ่ายที่มีสิทธิเบิก ในคลังย่อย';
    }

    protected function getCurrentUserDepartmentName()
    {
        if (Yii::$app->user->isGuest || !class_exists(Employees::class)) {
            return '';
        }

        $employee = Employees::findOne(['user_id' => Yii::$app->user->id]);
        if (!$employee || empty($employee->department) || !class_exists(Organization::class)) {
            return '';
        }

        $department = Organization::findOne(['id' => (int) $employee->department]);
        return $department ? trim((string) $department->name) : '';
    }

    /** รหัสคลังย่อยที่ user เป็นเจ้าหน้าที่คลัง (data_json.officer) */
    protected function getSubWarehouseIds()
    {
        $list = Warehouse::findSubWarehousesForUser();
        return array_values(array_map('intval', array_column($list, 'id')));
    }

    /** รายการคลังย่อยสำหรับ dropdown ของ dashboard ตามแผนก/ฝ่ายที่มีสิทธิเบิก */
    protected function getSubWarehousesList()
    {
        return Warehouse::findSubWarehousesForDepartment();
    }

    /** รหัสคลังย่อยที่ user มีสิทธิ "บันทึกจ่ายพัสดุ" (employee.department อยู่ใน warehouses.department) */
    protected function getSubWarehouseIdsForIssue()
    {
        $list = Warehouse::findSubWarehousesForDepartment();
        return array_values(array_map('intval', array_column($list, 'id')));
    }

    /** warehouse_id จาก query หรือ session (all = null) */
    protected function getFilterWarehouseId(array $allowedWarehouseIds = [])
    {
        $get = Yii::$app->request->get('warehouse_id');
        if ($get === 'all') {
            Yii::$app->session->remove('sub_dashboard_warehouse_id');
            return null;
        }

        if ($get === null || $get === '') {
            $sessionId = Yii::$app->session->get('sub_dashboard_warehouse_id');
            if ($sessionId !== null && $sessionId !== '' && in_array((int) $sessionId, $allowedWarehouseIds, true)) {
                return (int) $sessionId;
            }

            return !empty($allowedWarehouseIds) ? (int) reset($allowedWarehouseIds) : null;
        }

        $id = (int) $get;
        Yii::$app->session->set('sub_dashboard_warehouse_id', $id);
        return $id;
    }

    /**
     * สถิติ: รอคลังหลักจ่าย, ต่ำกว่าจุดวิกฤต, มูลค่าเบิกใช้เดือนนี้, หมดอายุภายใน 30 วัน
     */
    protected function getDashboardStats(array $subWarehouseIds)
    {
        $pendingDisbursement = (int) StockOrder::find()
            ->where([
                'order_type' => 'OUT',
                'source_type' => 'REQUEST',
                'status' => StockOrder::STATUS_APPROVED,
            ])
            ->andWhere(['sub_warehouse_id' => $subWarehouseIds])
            ->count();

        $criticalQuery = (new Query())
            ->from(['i' => StockItem::tableName()])
            ->innerJoin(
                ['b' => (new Query())
                    ->select(['item_code', 'SUM(balance_qty) as total_qty'])
                    ->from(StockBalance::tableName())
                    ->where(['warehouse_id' => $subWarehouseIds])
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

        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');
        $monthlyValue = (float) (new Query())
            ->select(new Expression('SUM(sd.qty * COALESCE(sd.unit_price, 0))'))
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where([
                'so.order_type' => 'OUT',
                'so.source_type' => 'REQUEST',
                'so.status' => StockOrder::STATUS_CONFIRMED,
            ])
            ->andWhere(['so.sub_warehouse_id' => $subWarehouseIds])
            ->andWhere(['>=', 'so.order_date', $monthStart])
            ->andWhere(['<=', 'so.order_date', $monthEnd])
            ->scalar();

        return [
            'pending_disbursement' => $pendingDisbursement,
            'critical_count' => $criticalCount,
            'monthly_value' => $monthlyValue,
            'expiring_soon' => 0,
        ];
    }

    /** รายการใบขอเบิกที่หัวหน้าอนุมัติแล้ว รอคลังหลักจ่าย */
    protected function getPendingDisbursementList(array $subWarehouseIds, $limit = 10)
    {
        $orders = StockOrder::find()
            ->with('stockDetails')
            ->where([
                'order_type' => 'OUT',
                'source_type' => 'REQUEST',
                'status' => StockOrder::STATUS_APPROVED,
            ])
            ->andWhere(['sub_warehouse_id' => $subWarehouseIds])
            ->orderBy(['order_date' => SORT_DESC])
            ->limit($limit)
            ->all();

        $list = [];
        foreach ($orders as $o) {
            $list[] = [
                'id' => $o->id,
                'doc_no' => $o->order_no,
                'detail_count' => is_array($o->stockDetails) ? count($o->stockDetails) : 0,
                'order_date' => $o->order_date,
                'main_warehouse_name' => $o->mainWarehouse ? $o->mainWarehouse->warehouse_name : 'คลังหลัก',
            ];
        }
        return $list;
    }

    /** ข้อมูลกราฟ: มูลค่าที่จ่ายมาคลังย่อย 7 วันล่าสุด */
    protected function getChartDataLast7Days(array $subWarehouseIds)
    {
        $days = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $days[] = $d;
            $start = $d . ' 00:00:00';
            $end = $d . ' 23:59:59';
            $v = (float) (new Query())
                ->select(new Expression('SUM(sd.qty * COALESCE(sd.unit_price, 0))'))
                ->from(['sd' => StockDetail::tableName()])
                ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                ->where([
                    'so.order_type' => 'OUT',
                    'so.source_type' => 'REQUEST',
                    'so.status' => StockOrder::STATUS_CONFIRMED,
                ])
                ->andWhere(['so.sub_warehouse_id' => $subWarehouseIds])
                ->andWhere(['>=', 'so.order_date', $start])
                ->andWhere(['<=', 'so.order_date', $end])
                ->scalar();
            $values[] = $v;
        }
        $labels = array_map(function ($d) {
            return date('j/n', strtotime($d));
        }, $days);
        return ['categories' => $labels, 'series' => $values];
    }

    /**
     * ประวัติการตัดจ่ายทั้งหมดของคลังย่อย พร้อมตัวกรองและสรุปมูลค่า
     */
    public function actionUseHistory()
    {
        $subWarehouseIds = array_map('intval', $this->getSubWarehouseIdsForIssue());
        if (empty($subWarehouseIds)) {
            return $this->renderPermissionBlockedDashboard();
        }

        $request = Yii::$app->request;
        $filters = [
            'q' => trim((string) $request->get('q', '')),
            'date_from' => trim((string) $request->get('date_from', '')),
            'date_to' => trim((string) $request->get('date_to', '')),
            'warehouse_id' => (int) $request->get('warehouse_id', 0),
            'source_type' => trim((string) $request->get('source_type', '')),
        ];

        if ($filters['warehouse_id'] > 0 && !in_array($filters['warehouse_id'], $subWarehouseIds, true)) {
            $filters['warehouse_id'] = 0;
        }

        $baseQuery = (new Query())
            ->select(['so.id'])
            ->from(['so' => StockOrder::tableName()])
            ->leftJoin(['sd' => StockDetail::tableName()], 'sd.stock_order_id = so.id')
            ->leftJoin(['si' => StockItem::tableName()], 'si.code = sd.item_code')
            ->where([
                'so.order_type' => StockOrder::ORDER_TYPE_OUT,
                'so.main_warehouse_id' => $subWarehouseIds,
            ])
            ->andWhere(['<>', 'so.status', StockOrder::STATUS_CANCELLED])
            ->andWhere([
                'or',
                ['<>', 'so.source_type', 'REQUEST'],
                ['so.source_type' => null],
                ['so.source_type' => ''],
            ])
            ->groupBy(['so.id']);

        if ($filters['warehouse_id'] > 0) {
            $baseQuery->andWhere(['so.main_warehouse_id' => $filters['warehouse_id']]);
        }

        if ($filters['source_type'] !== '') {
            $baseQuery->andWhere(['so.source_type' => $filters['source_type']]);
        }

        if ($filters['date_from'] !== '') {
            $fromTs = strtotime($filters['date_from'] . ' 00:00:00');
            if ($fromTs !== false) {
                $filters['date_from'] = date('Y-m-d', $fromTs);
                $baseQuery->andWhere(['>=', 'so.order_date', date('Y-m-d 00:00:00', $fromTs)]);
            } else {
                $filters['date_from'] = '';
            }
        }

        if ($filters['date_to'] !== '') {
            $toTs = strtotime($filters['date_to'] . ' 23:59:59');
            if ($toTs !== false) {
                $filters['date_to'] = date('Y-m-d', $toTs);
                $baseQuery->andWhere(['<=', 'so.order_date', date('Y-m-d 23:59:59', $toTs)]);
            } else {
                $filters['date_to'] = '';
            }
        }

        if ($filters['q'] !== '') {
            $baseQuery->andWhere([
                'or',
                ['like', 'so.order_no', $filters['q']],
                ['like', 'so.ref', $filters['q']],
                ['like', 'so.source_type', $filters['q']],
                ['like', 'so.data_json', $filters['q']],
                ['like', 'sd.item_code', $filters['q']],
                ['like', 'si.title', $filters['q']],
            ]);
        }

        $countIdsQuery = clone $baseQuery;
        $summaryIdsQuery = clone $baseQuery;
        $ordersIdsQuery = clone $baseQuery;

        $totalCount = (int) (new Query())
            ->from(['ids' => $countIdsQuery])
            ->count('*');

        $summary = (new Query())
            ->select([
                'line_count' => new Expression('COUNT(sd.id)'),
                'total_qty' => new Expression('COALESCE(SUM(sd.qty), 0)'),
                'total_value' => new Expression('COALESCE(SUM(sd.qty * COALESCE(sd.unit_price, 0)), 0)'),
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['ids' => $summaryIdsQuery], 'ids.id = sd.stock_order_id')
            ->one();

        $dataProvider = new ActiveDataProvider([
            'query' => StockOrder::find()
                ->alias('so')
                ->where(['so.id' => $ordersIdsQuery])
                ->with(['stockDetails.item', 'mainWarehouse'])
                ->orderBy(['so.order_date' => SORT_DESC, 'so.id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => false,
        ]);

        $warehouseOptions = [];
        foreach ($this->getSubWarehousesList() as $warehouse) {
            $warehouseOptions[(int) $warehouse->id] = (string) $warehouse->warehouse_name;
        }

        $sourceTypeOptions = StockOrder::find()
            ->select('source_type')
            ->distinct()
            ->where([
                'order_type' => StockOrder::ORDER_TYPE_OUT,
                'main_warehouse_id' => $subWarehouseIds,
            ])
            ->andWhere(['<>', 'status', StockOrder::STATUS_CANCELLED])
            ->andWhere([
                'or',
                ['<>', 'source_type', 'REQUEST'],
                ['source_type' => null],
                ['source_type' => ''],
            ])
            ->andWhere(['not', ['source_type' => null]])
            ->andWhere(['<>', 'source_type', ''])
            ->orderBy(['source_type' => SORT_ASC])
            ->column();

        return $this->render('use-history', [
            'dataProvider' => $dataProvider,
            'filters' => $filters,
            'summary' => [
                'order_count' => $totalCount,
                'line_count' => (int) ($summary['line_count'] ?? 0),
                'total_qty' => (float) ($summary['total_qty'] ?? 0),
                'total_value' => (float) ($summary['total_value'] ?? 0),
            ],
            'warehouseOptions' => $warehouseOptions,
            'sourceTypeOptions' => $sourceTypeOptions,
        ]);
    }

    /**
     * ตัดจ่ายพัสดุจากคลังย่อย
     * คลังย่อยต้องมีสต็อกก่อน (รับจากคลังหลักเมื่อคลังหลักจ่ายของแล้ว)
     */
    public function actionIssue()
    {
        $subIds = $this->getSubWarehouseIdsForIssue();
        if (empty($subIds)) {
            return $this->redirectToSubStockDashboard();
        }

        $subWarehouses = Warehouse::find()
            ->where(['id' => $subIds])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        if (empty($subWarehouses)) {
            $subIds = [];
        }

        // ถ้าไม่ส่ง warehouse_id มา ให้เลือกคลังย่อยแรกที่ผู้ใช้มีสิทธิ์เป็นค่าเริ่มต้น
        $requestedWarehouseId = (int) $this->request->get('warehouse_id', 0);
        $currentWarehouseId = ($requestedWarehouseId > 0 && in_array($requestedWarehouseId, $subIds, true))
            ? $requestedWarehouseId
            : (!empty($subWarehouses) ? (int) $subWarehouses[0]->id : null);

        $usageHistory = [];
        $helpdeskIdFilter = (int) $this->request->get('helpdesk_id', 0);
        if ($helpdeskIdFilter > 0) {
            // DEBUG/ค้นหาแบบกว้าง: แสดงว่ามี stock_order ที่เกี่ยวข้องถูกสร้างและมีเลข helpdesk_id นี้อยู่ใน ref/data_json หรือไม่
            // (ไม่จำกัด status เผื่อกรณีบันทึกแต่ยังไม่ CONFIRMED)
            $needle = (string) $helpdeskIdFilter;
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere([
                    'or',
                    ['like', 'ref', $needle],
                    ['like', 'data_json', $needle],
                ])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        } else if ($currentWarehouseId > 0) {
            // แสดงเฉพาะคลังที่เลือก (ถ้าส่ง warehouse_id มา)
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere(['!=', 'status', StockOrder::STATUS_CANCELLED])
                ->andWhere([
                    'or',
                    ['source_type' => 'USAGE'],
                    ['like', 'ref', 'HELPDESK_ID:%'],
                    ['like', 'data_json', '"helpdesk_id"'],
                    ['like', 'data_json', 'helpdesk_id'],
                ])
                ->andWhere(['or', ['main_warehouse_id' => $currentWarehouseId], ['sub_warehouse_id' => $currentWarehouseId]])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        } else {
            // รวมทุกคลังย่อยที่ผู้ใช้มีสิทธิ
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere(['!=', 'status', StockOrder::STATUS_CANCELLED])
                ->andWhere([
                    'or',
                    ['source_type' => 'USAGE'],
                    ['like', 'ref', 'HELPDESK_ID:%'],
                    ['like', 'data_json', '"helpdesk_id"'],
                    ['like', 'data_json', 'helpdesk_id'],
                ])
                ->andWhere([
                    'or',
                    ['<>', 'source_type', 'REQUEST'],
                    ['source_type' => null],
                    ['source_type' => ''],
                ])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        }

        return $this->render('issue', [
            'subWarehouses' => $subWarehouses,
            'currentWarehouseId' => $currentWarehouseId,
            'usageHistory' => $usageHistory,
            'canCreateRequisition' => $this->canCreateRequisition(),
        ]);
    }

    public function actionRepairPicker($q = '')
    {
        $q = trim((string) $q);

        $query = Helpdesk::find()
            ->where(['name' => 'repair'])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(80);

        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'repair_number', $q],
                ['like', 'title', $q],
                ['like', 'asset_number', $q],
                ['like', 'status', $q],
                ['like', 'repair_group', $q],
            ]);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'title' => '<i class="bi bi-tools me-1"></i> เลือกใบแจ้งซ่อม',
            'content' => $this->renderAjax('_repair_picker', [
                'models' => $query->all(),
                'q' => $q,
            ]),
            'footer' => '',
        ];
    }

    /**
     * API: รายการ Lot ที่มีในคลังย่อย (จาก stock_balance)
     */
    public function actionGetAvailableLots($warehouse_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $subIds = $this->getSubWarehouseIdsForIssue();
        $wid = (int) $warehouse_id;
        if ($wid <= 0 || !in_array($wid, $subIds, true)) {
            return [];
        }
        return $this->getAvailableLotRows($wid);
    }

    public function actionBulkAvailable($warehouse_id)
    {
        return $this->actionGetAvailableLots($warehouse_id);
    }

    public function actionExportIssueTemplate($warehouse_id)
    {
        $subIds = $this->getSubWarehouseIdsForIssue();
        $wid = (int) $warehouse_id;
        if ($wid <= 0 || !in_array($wid, $subIds, true)) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกคลังย่อยที่ถูกต้อง');
            return $this->redirect(['issue']);
        }

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, [
            'item_code',
            'item_name',
            'lot_number',
            'unit',
            'balance_qty',
            'issue_qty',
            'actual_qty',
            'note',
        ]);

        foreach ($this->getAvailableLotRows($wid) as $row) {
            fputcsv($stream, [
                $row['item_code'],
                $row['item_name'],
                $row['lot_number'],
                $row['unit'],
                $row['balance_qty'],
                '',
                '',
                '',
            ]);
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        $filename = 'sub-stock-issue-template-' . $wid . '-' . date('Ymd-His') . '.csv';
        return Yii::$app->response->sendContentAsFile($content, $filename, [
            'mimeType' => 'text/csv; charset=UTF-8',
            'inline' => false,
        ]);
    }

    public function actionImportIssueTemplate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$this->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $warehouseId = (int) $this->request->post('warehouse_id');
        $subIds = $this->getSubWarehouseIdsForIssue();
        if ($warehouseId <= 0 || !in_array($warehouseId, $subIds, true)) {
            return ['success' => false, 'message' => 'กรุณาเลือกคลังย่อยที่ถูกต้อง'];
        }

        $file = UploadedFile::getInstanceByName('csv_file');
        if (!$file) {
            return ['success' => false, 'message' => 'กรุณาเลือกไฟล์ CSV'];
        }
        if (strtolower($file->extension) !== 'csv') {
            return ['success' => false, 'message' => 'รองรับเฉพาะไฟล์ CSV'];
        }

        $handle = fopen($file->tempName, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'ไม่สามารถอ่านไฟล์ CSV ได้'];
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return ['success' => false, 'message' => 'ไฟล์ CSV ไม่มีหัวตาราง'];
        }

        $columns = [];
        foreach ($header as $index => $name) {
            $name = preg_replace('/^\xEF\xBB\xBF/', '', (string) $name);
            $columns[strtolower(trim($name))] = $index;
        }

        foreach (['item_code', 'lot_number'] as $required) {
            if (!array_key_exists($required, $columns)) {
                fclose($handle);
                return ['success' => false, 'message' => 'ไฟล์ CSV ต้องมีคอลัมน์ item_code และ lot_number'];
            }
        }
        if (!array_key_exists('issue_qty', $columns) && !array_key_exists('actual_qty', $columns)) {
            fclose($handle);
            return ['success' => false, 'message' => 'ไฟล์ CSV ต้องมีคอลัมน์ issue_qty หรือ actual_qty'];
        }

        $available = [];
        foreach ($this->getAvailableLotRows($warehouseId) as $row) {
            $available[$this->makeLotKey($row['item_code'], $row['lot_number'])] = $row;
        }

        $items = [];
        $errors = [];
        $skipped = 0;
        $line = 1;
        while (($csvRow = fgetcsv($handle)) !== false) {
            $line++;
            $itemCode = $this->csvValue($csvRow, $columns, 'item_code');
            $lotNumber = $this->csvValue($csvRow, $columns, 'lot_number');
            if ($itemCode === '' && $lotNumber === '') {
                $skipped++;
                continue;
            }

            $key = $this->makeLotKey($itemCode, $lotNumber);
            if (!isset($available[$key])) {
                $errors[] = "แถว {$line}: ไม่พบพัสดุ {$itemCode} Lot {$lotNumber} ในคลังนี้";
                continue;
            }

            $stockRow = $available[$key];
            $balanceQty = (float) $stockRow['balance_qty'];
            $issueRaw = $this->csvValue($csvRow, $columns, 'issue_qty');
            $actualRaw = $this->csvValue($csvRow, $columns, 'actual_qty');
            $issueQty = null;

            if ($issueRaw !== '') {
                $issueQty = $this->parseCsvNumber($issueRaw);
                if ($issueQty === null) {
                    $errors[] = "แถว {$line}: issue_qty ไม่ใช่ตัวเลข";
                    continue;
                }
            } elseif ($actualRaw !== '') {
                $actualQty = $this->parseCsvNumber($actualRaw);
                if ($actualQty === null) {
                    $errors[] = "แถว {$line}: actual_qty ไม่ใช่ตัวเลข";
                    continue;
                }
                if ($actualQty > $balanceQty) {
                    $errors[] = "แถว {$line}: actual_qty มากกว่ายอดคงเหลือ";
                    continue;
                }
                $issueQty = $balanceQty - $actualQty;
            }

            if ($issueQty === null) {
                $skipped++;
                continue;
            }
            if ($issueQty <= 0) {
                $skipped++;
                continue;
            }
            if ($issueQty > $balanceQty) {
                $errors[] = "แถว {$line}: จำนวนจ่ายเกินยอดคงเหลือ";
                continue;
            }

            if (isset($items[$key])) {
                $items[$key]['qty'] = round($items[$key]['qty'] + $issueQty, 2);
            } else {
                $items[$key] = [
                    'item_code' => $stockRow['item_code'],
                    'item_name' => $stockRow['item_name'],
                    'lot_number' => $stockRow['lot_number'],
                    'unit' => $stockRow['unit'],
                    'balance_qty' => $balanceQty,
                    'qty' => round($issueQty, 2),
                ];
            }

            if ($items[$key]['qty'] > $balanceQty) {
                $errors[] = "แถว {$line}: จำนวนรวมของ {$itemCode} Lot {$lotNumber} เกินยอดคงเหลือ";
                unset($items[$key]);
            }
        }
        fclose($handle);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'พบข้อมูลไม่ถูกต้องในไฟล์ CSV',
                'errors' => array_slice($errors, 0, 20),
                'summary' => ['ready' => count($items), 'skipped' => $skipped, 'errors' => count($errors)],
            ];
        }

        return [
            'success' => true,
            'items' => array_values($items),
            'summary' => ['ready' => count($items), 'skipped' => $skipped, 'errors' => 0],
        ];
    }

    protected function getAvailableLotRows($warehouseId)
    {
        $rows = (new Query())
            ->select([
                'sb.item_code',
                'i.title AS item_name',
                'sb.lot_number',
                'sb.balance_qty',
            ])
            ->from(['sb' => StockBalance::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sb.item_code')
            ->where(['sb.warehouse_id' => (int) $warehouseId])
            ->andWhere(['>', 'sb.balance_qty', 0])
            ->orderBy(['i.title' => SORT_ASC, 'sb.lot_number' => SORT_ASC])
            ->all();
        $out = [];
        $unitCache = [];
        foreach ($rows as $r) {
            $code = $r['item_code'];
            if (!array_key_exists($code, $unitCache)) {
                $item = StockItem::findOne($code);
                $unitCache[$code] = ($item && method_exists($item, 'getUnitName'))
                    ? ((string) ($item->getUnitName() ?: ''))
                    : '';
            }
            $out[] = [
                'item_code' => $code,
                'item_name' => $r['item_name'],
                'lot_number' => $r['lot_number'],
                'balance_qty' => (float) $r['balance_qty'],
                'unit' => $unitCache[$code],
            ];
        }
        return $out;
    }

    protected function makeLotKey($itemCode, $lotNumber)
    {
        return (string) $itemCode . "\x1F" . (string) $lotNumber;
    }

    protected function csvValue(array $row, array $columns, $name)
    {
        if (!array_key_exists($name, $columns)) {
            return '';
        }
        $index = $columns[$name];
        return isset($row[$index]) ? trim((string) $row[$index]) : '';
    }

    protected function parseCsvNumber($value)
    {
        $value = str_replace([',', ' '], '', trim((string) $value));
        if ($value === '' || !is_numeric($value)) {
            return null;
        }
        return round((float) $value, 2);
    }

    /**
     * บันทึกการตัดจ่ายพัสดุที่คลังย่อย (POST)
     */
    public function actionSaveUsage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$this->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }
        $warehouse_id = (int) $this->request->post('warehouse_id');
        $job_type = trim((string) $this->request->post('job_type', ''));
        $reference = trim((string) $this->request->post('reference', ''));
        $helpdesk_id = (int) $this->request->post('helpdesk_id', 0);
        $items = (array) $this->request->post('items', []);

        $subIds = $this->getSubWarehouseIdsForIssue();
        if (empty($subIds)) {
            return ['success' => false, 'message' => $this->getSubStockPermissionMessage()];
        }

        if ($warehouse_id <= 0 || !in_array($warehouse_id, $subIds, true)) {
            return ['success' => false, 'message' => 'กรุณาเลือกคลังย่อยที่ถูกต้อง'];
        }
        $items = array_filter($items, function ($r) {
            return !empty($r['item_code']) && !empty($r['lot_number']) && isset($r['qty']) && (float)$r['qty'] > 0;
        });
        if (empty($items)) {
            return ['success' => false, 'message' => 'กรุณาเพิ่มรายการพัสดุที่ตัดจ่าย'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($job_type === 'maintenance' && $helpdesk_id > 0) {
                $repairTicket = Helpdesk::find()
                    ->where(['id' => $helpdesk_id, 'name' => 'repair'])
                    ->one();

                if (!$repairTicket) {
                    $transaction->rollBack();
                    return ['success' => false, 'message' => 'ไม่พบใบแจ้งซ่อมที่เลือก'];
                }

                $repairNumber = trim((string) ($repairTicket->repair_number ?? ''));
                if ($repairNumber !== '') {
                    $reference = $repairNumber;
                }
            }

            $orderNo = $this->generateSubIssueOrderNo();
            $order = new StockOrder();
            $order->order_no = $orderNo;
            $order->order_type = StockOrder::ORDER_TYPE_OUT;
            $order->source_type = 'USAGE';
            $order->order_date = date('Y-m-d H:i:s');
            $order->main_warehouse_id = $warehouse_id;
            $order->status = StockOrder::STATUS_CONFIRMED;
            $orderData = ['job_type' => $job_type, 'reference' => $reference];
            if ($job_type === 'maintenance') {
                if ($helpdesk_id > 0) {
                    $orderData['helpdesk_id'] = $helpdesk_id;
                }
                if ($reference !== '') {
                    $orderData['repair_number'] = $reference;
                }
            }
            $order->data_json = $orderData;
            if (!$order->save(false)) {
                throw new \Exception('บันทึกหัวเอกสารไม่สำเร็จ');
            }

            foreach ($items as $row) {
                $itemCode = $row['item_code'];
                $lotNumber = $row['lot_number'];
                $qty = (float) $row['qty'];
                $balance = StockBalance::findOne([
                    'item_code' => $itemCode,
                    'warehouse_id' => $warehouse_id,
                    'lot_number' => $lotNumber,
                ]);
                if (!$balance || (float)$balance->balance_qty < $qty) {
                    throw new \Exception("พัสดุ {$itemCode} Lot {$lotNumber} มีไม่พอจ่าย (ยอดคงเหลือไม่เพียงพอ)");
                }
                InventoryService::updateBalance($itemCode, $warehouse_id, $qty, 'OUT', $lotNumber);
                $detail = new StockDetail();
                $detail->stock_order_id = $order->id;
                $detail->item_code = $itemCode;
                $detail->qty = $qty;
                $detail->remain_qty = 0;
                $detail->lot_number = $lotNumber;
                $detail->unit_price = 0;
                $detail->save(false);
            }

            $transaction->commit();
            return ['success' => true, 'message' => 'บันทึกการตัดจ่ายเรียบร้อย', 'order_no' => $orderNo];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function generateSubIssueOrderNo()
    {
        $prefix = 'SUB-OUT-' . date('Ymd') . '-';
        $n = 1;
        do {
            $no = $prefix . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
            $n++;
        } while (StockOrder::findOne(['order_no' => $no]) !== null);
        return $no;
    }

    public function actionRequisition()
    {
        if (empty($this->getSubWarehouseIdsForIssue())) {
            return $this->redirectToSubStockDashboard();
        }

            $model = new StockOrder();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('_form_requisition', [
            'model' => $model,
        ]);
    }


}
