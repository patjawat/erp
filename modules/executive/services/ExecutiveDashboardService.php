<?php

namespace app\modules\executive\services;

use app\components\AppHelper;
use app\models\Categorise;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockMonthlyReport;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;

class ExecutiveDashboardService
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getSummary(?int $fiscalYear = null): array
    {
        $inventoryDashboard = $this->getInventoryDashboard($fiscalYear);
        $inventoryMetric = $this->getInventoryMetric();
        $inventoryMetric['value'] = $inventoryDashboard['summary']['value'];
        $inventoryMetric['url'] = ['/executive/dashboard/inventory', 'year' => $inventoryDashboard['selectedFiscalYear']];
        $inventoryMetric['description'] = 'มูลค่าคลังหลักตามปีงบประมาณที่เลือก · ต่ำกว่าจุดสั่งซื้อ '
            . number_format((int) $inventoryDashboard['risk']['critical'])
            . ' · ใกล้หมดอายุ ' . number_format((int) $inventoryDashboard['risk']['expiring']);
        return [
            'asOf' => date('Y-m-d H:i:s'),
            'selectedFiscalYear' => $inventoryDashboard['selectedFiscalYear'],
            'availableYears' => $inventoryDashboard['availableYears'],
            'cash' => $this->unavailableMetric(
                'เงินสดคงเหลือสุทธิ',
                'อยู่ระหว่างเชื่อมยอดเงินสดและเงินฝากธนาคาร',
                'bi-wallet2',
                'success'
            ),
            'payable' => $this->unavailableMetric(
                'เจ้าหนี้ค้างจ่าย',
                'อยู่ระหว่างกำหนดแหล่งยอดเจ้าหนี้ที่ผ่านการตรวจสอบ',
                'bi-receipt',
                'warning'
            ),
            'receivable' => $this->unavailableMetric(
                'ลูกหนี้รอเรียกเก็บ',
                'อยู่ระหว่างเชื่อมข้อมูลลูกหนี้และอายุหนี้',
                'bi-people',
                'info'
            ),
            'inventory' => $inventoryMetric,
        ];
    }

    private function getInventoryMetric(): array
    {
        try {
            $latestInPrice = (new Query())
                ->select(['sd_in.item_code', 'sd_in.lot_number', 'sd_in.unit_price'])
                ->from(['sd_in' => StockDetail::tableName()])
                ->innerJoin(['so_in' => StockOrder::tableName()], 'so_in.id = sd_in.stock_order_id')
                ->innerJoin(
                    ['latest' => (new Query())
                        ->select([
                            'sd_l.item_code',
                            'sd_l.lot_number',
                            new Expression('MAX(sd_l.id) AS latest_id'),
                        ])
                        ->from(['sd_l' => StockDetail::tableName()])
                        ->innerJoin(['so_l' => StockOrder::tableName()], 'so_l.id = sd_l.stock_order_id')
                        ->where(['so_l.order_type' => StockOrder::ORDER_TYPE_IN])
                        ->andWhere(['so_l.status' => StockOrder::STATUS_CONFIRMED])
                        ->groupBy(['sd_l.item_code', 'sd_l.lot_number'])],
                    'latest.item_code = sd_in.item_code AND latest.lot_number = sd_in.lot_number AND latest.latest_id = sd_in.id'
                );

            $baseBalance = (new Query())
                ->from(['b' => StockBalance::tableName()])
                ->innerJoin(['i' => StockItem::tableName()], 'i.code = b.item_code')
                ->leftJoin(
                    ['price' => $latestInPrice],
                    'price.item_code = b.item_code AND price.lot_number = b.lot_number'
                )
                ->where(['i.name' => 'asset_item', 'i.group_id' => 'MATER'])
                ->andWhere(['>', 'b.balance_qty', 0]);

            $inventoryValue = (float) (clone $baseBalance)
                ->select(new Expression('COALESCE(SUM(b.balance_qty * COALESCE(price.unit_price, 0)), 0)'))
                ->scalar($this->db);

            $itemCount = (int) (clone $baseBalance)
                ->select(new Expression('COUNT(DISTINCT b.item_code)'))
                ->scalar($this->db);

            $criticalCount = (int) (new Query())
                ->from(['i' => StockItem::tableName()])
                ->innerJoin(
                    ['balance' => (new Query())
                        ->select(['item_code', new Expression('SUM(balance_qty) AS total_qty')])
                        ->from(StockBalance::tableName())
                        ->groupBy('item_code')],
                    'balance.item_code = i.code'
                )
                ->where([
                    'i.name' => 'asset_item',
                    'i.group_id' => 'MATER',
                    'i.active' => 1,
                ])
                ->andWhere(['not', ['i.qty_min' => null]])
                ->andWhere(['>', 'i.qty_min', 0])
                ->andWhere('balance.total_qty < i.qty_min')
                ->count('*', $this->db);

            $expiringSoonCount = (int) (new Query())
                ->from(['sd' => StockDetail::tableName()])
                ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
                ->where([
                    'so.order_type' => StockOrder::ORDER_TYPE_IN,
                    'so.status' => StockOrder::STATUS_CONFIRMED,
                    'i.name' => 'asset_item',
                    'i.group_id' => 'MATER',
                ])
                ->andWhere(['>', 'sd.remain_qty', 0])
                ->andWhere(['between', 'sd.expiry_date', date('Y-m-d'), date('Y-m-d', strtotime('+90 days'))])
                ->select(new Expression("COUNT(DISTINCT CONCAT(sd.item_code, ':', sd.lot_number))"))
                ->scalar($this->db);

            return [
                'status' => 'available',
                'label' => 'มูลค่าวัสดุคงคลัง',
                'value' => $inventoryValue,
                'unit' => 'บาท',
                'icon' => 'bi-box-seam',
                'color' => 'primary',
                'description' => sprintf(
                    '%s รายการ · ต่ำกว่าจุดสั่งซื้อ %s · ใกล้หมดอายุ %s',
                    number_format($itemCount),
                    number_format($criticalCount),
                    number_format($expiringSoonCount)
                ),
                'details' => [
                    ['label' => 'รายการคงเหลือ', 'value' => $itemCount, 'status' => 'secondary'],
                    ['label' => 'ต่ำกว่าจุดสั่งซื้อ', 'value' => $criticalCount, 'status' => $criticalCount > 0 ? 'danger' : 'success'],
                    ['label' => 'ใกล้หมดอายุ', 'value' => $expiringSoonCount, 'status' => $expiringSoonCount > 0 ? 'warning' : 'success'],
                ],
                'url' => ['/executive/dashboard/inventory'],
            ];
        } catch (\Throwable $e) {
            return $this->unavailableMetric(
                'มูลค่าวัสดุคงคลัง',
                'ไม่สามารถประมวลผลข้อมูลคลังได้ในขณะนี้',
                'bi-box-seam',
                'primary'
            );
        }
    }

    public function getInventoryDashboard(?int $fiscalYear = null): array
    {
        $currentFiscalYear = (int) AppHelper::YearBudget();
        $availableYears = [$currentFiscalYear];
        $snapshotPeriods = (new Query())
            ->select(['report_year', 'report_month'])
            ->from(StockMonthlyReport::tableName())
            ->groupBy(['report_year', 'report_month'])
            ->all($this->db);
        foreach ($snapshotPeriods as $period) {
            $year = (int) $period['report_year'];
            $month = (int) $period['report_month'];
            $availableYears[] = $month >= 10 ? $year + 544 : $year + 543;
        }
        $availableYears = array_values(array_unique($availableYears));
        rsort($availableYears);
        $selectedFiscalYear = in_array((int) $fiscalYear, $availableYears, true)
            ? (int) $fiscalYear
            : $currentFiscalYear;

        $latestInPrice = (new Query())
            ->select(['sd_in.item_code', 'sd_in.lot_number', 'sd_in.unit_price'])
            ->from(['sd_in' => StockDetail::tableName()])
            ->innerJoin(['so_in' => StockOrder::tableName()], 'so_in.id = sd_in.stock_order_id')
            ->innerJoin(
                ['latest' => (new Query())
                    ->select([
                        'sd_l.item_code',
                        'sd_l.lot_number',
                        new Expression('MAX(sd_l.id) AS latest_id'),
                    ])
                    ->from(['sd_l' => StockDetail::tableName()])
                    ->innerJoin(['so_l' => StockOrder::tableName()], 'so_l.id = sd_l.stock_order_id')
                    ->where(['so_l.order_type' => StockOrder::ORDER_TYPE_IN])
                    ->andWhere(['so_l.status' => StockOrder::STATUS_CONFIRMED])
                    ->groupBy(['sd_l.item_code', 'sd_l.lot_number'])],
                'latest.item_code = sd_in.item_code AND latest.lot_number = sd_in.lot_number AND latest.latest_id = sd_in.id'
            );

        $baseBalance = (new Query())
            ->from(['b' => StockBalance::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = b.item_code')
            ->innerJoin(['w' => Warehouse::tableName()], 'w.id = b.warehouse_id')
            ->leftJoin(['price' => $latestInPrice], 'price.item_code = b.item_code AND price.lot_number = b.lot_number')
            ->where(['i.name' => 'asset_item', 'i.group_id' => 'MATER'])
            ->andWhere(['>', 'b.balance_qty', 0]);

        $snapshotLabel = 'ข้อมูลปัจจุบัน';
        if ($selectedFiscalYear === $currentFiscalYear) {
            $valueExpression = new Expression('COALESCE(SUM(b.balance_qty * COALESCE(price.unit_price, 0)), 0)');
            $mainValues = (clone $baseBalance)
                ->andWhere(['w.warehouse_type' => 'MAIN'])
                ->select(['name' => 'w.warehouse_name', 'value' => $valueExpression])
                ->groupBy(['w.id', 'w.warehouse_name'])
                ->indexBy('name')
                ->all($this->db);
            $summary = [
                'value' => (float) (clone $baseBalance)->andWhere(['w.warehouse_type' => 'MAIN'])->select($valueExpression)->scalar($this->db),
                'itemCount' => (int) (clone $baseBalance)->select(new Expression('COUNT(DISTINCT b.item_code)'))->scalar($this->db),
                'warehouseCount' => (int) (clone $baseBalance)->select(new Expression('COUNT(DISTINCT b.warehouse_id)'))->scalar($this->db),
            ];
            $warehouses = (clone $baseBalance)
                ->andWhere(['w.warehouse_type' => Warehouse::SUB_STOCK_TYPES])
                ->select([
                    'id' => 'b.warehouse_id',
                    'name' => 'w.warehouse_name',
                    'type' => 'w.warehouse_type',
                    'item_count' => new Expression('COUNT(DISTINCT b.item_code)'),
                    'value' => new Expression('SUM(b.balance_qty * COALESCE(price.unit_price, 0))'),
                    'org_root' => new Expression("COALESCE((SELECT MIN(t.root) FROM tree t WHERE FIND_IN_SET(t.id, REPLACE(COALESCE(w.department, ''), ' ', '')) > 0), 999999)"),
                    'org_lft' => new Expression("COALESCE((SELECT MIN(t.lft) FROM tree t WHERE FIND_IN_SET(t.id, REPLACE(COALESCE(w.department, ''), ' ', '')) > 0), 999999)"),
                    'org_lvl' => new Expression("COALESCE((SELECT MIN(t.lvl) FROM tree t WHERE FIND_IN_SET(t.id, REPLACE(COALESCE(w.department, ''), ' ', '')) > 0), 999999)"),
                    'org_name' => new Expression("(SELECT t.name FROM tree t WHERE FIND_IN_SET(t.id, REPLACE(COALESCE(w.department, ''), ' ', '')) > 0 ORDER BY t.lft ASC LIMIT 1)"),
                    'group_name' => new Expression("(SELECT p.name FROM tree n INNER JOIN tree p ON p.root = n.root AND n.lft BETWEEN p.lft AND p.rgt AND p.lvl = 1 WHERE FIND_IN_SET(n.id, REPLACE(COALESCE(w.department, ''), ' ', '')) > 0 ORDER BY p.lft ASC LIMIT 1)"),
                ])
                ->groupBy(['b.warehouse_id', 'w.warehouse_name', 'w.warehouse_type'])
                ->orderBy(new Expression("CASE WHEN w.warehouse_type = 'SUB' THEN 0 ELSE 1 END ASC, org_root ASC, org_lft ASC, w.warehouse_name ASC"))
                ->all($this->db);
        } else {
            $yearAD = $selectedFiscalYear - 543;
            $period = (new Query())
                ->select(['report_year', 'report_month'])
                ->from(StockMonthlyReport::tableName())
                ->where(['or',
                    ['and', ['report_year' => $yearAD - 1], ['>=', 'report_month', 10]],
                    ['and', ['report_year' => $yearAD], ['<=', 'report_month', 9]],
                ])
                ->orderBy(new Expression('CASE WHEN report_month >= 10 THEN report_month - 9 ELSE report_month + 3 END DESC'))
                ->one($this->db);
            $snapshotBase = (new Query())
                ->from(['r' => StockMonthlyReport::tableName()])
                ->innerJoin(['i' => StockItem::tableName()], 'i.code = r.item_code')
                ->innerJoin(['w' => Warehouse::tableName()], 'w.id = r.warehouse_id')
                ->where([
                    'r.report_year' => (int) ($period['report_year'] ?? 0),
                    'r.report_month' => (int) ($period['report_month'] ?? 0),
                    'i.name' => 'asset_item',
                    'i.group_id' => 'MATER',
                ])
                ->andWhere(['>', 'r.closing_qty', 0]);
            $snapshotValue = new Expression('COALESCE(SUM(r.closing_value), 0)');
            $mainValues = (clone $snapshotBase)
                ->andWhere(['w.warehouse_type' => 'MAIN'])
                ->select(['name' => 'w.warehouse_name', 'value' => $snapshotValue])
                ->groupBy(['w.id', 'w.warehouse_name'])
                ->indexBy('name')
                ->all($this->db);
            $summary = [
                'value' => (float) (clone $snapshotBase)->andWhere(['w.warehouse_type' => 'MAIN'])->select($snapshotValue)->scalar($this->db),
                'itemCount' => (int) (clone $snapshotBase)->select(new Expression('COUNT(DISTINCT r.item_code)'))->scalar($this->db),
                'warehouseCount' => (int) (clone $snapshotBase)->select(new Expression('COUNT(DISTINCT r.warehouse_id)'))->scalar($this->db),
            ];
            $warehouses = (clone $snapshotBase)
                ->andWhere(['w.warehouse_type' => Warehouse::SUB_STOCK_TYPES])
                ->select([
                    'id' => 'r.warehouse_id',
                    'name' => 'w.warehouse_name',
                    'type' => 'w.warehouse_type',
                    'item_count' => new Expression('COUNT(DISTINCT r.item_code)'),
                    'value' => new Expression('SUM(r.closing_value)'),
                    'org_root' => new Expression("COALESCE((SELECT MIN(t.root) FROM tree t WHERE FIND_IN_SET(t.id, REPLACE(COALESCE(w.department, ''), ' ', '')) > 0), 999999)"),
                    'org_lft' => new Expression("COALESCE((SELECT MIN(t.lft) FROM tree t WHERE FIND_IN_SET(t.id, REPLACE(COALESCE(w.department, ''), ' ', '')) > 0), 999999)"),
                    'org_lvl' => new Expression("COALESCE((SELECT MIN(t.lvl) FROM tree t WHERE FIND_IN_SET(t.id, REPLACE(COALESCE(w.department, ''), ' ', '')) > 0), 999999)"),
                    'org_name' => new Expression("(SELECT t.name FROM tree t WHERE FIND_IN_SET(t.id, REPLACE(COALESCE(w.department, ''), ' ', '')) > 0 ORDER BY t.lft ASC LIMIT 1)"),
                    'group_name' => new Expression("(SELECT p.name FROM tree n INNER JOIN tree p ON p.root = n.root AND n.lft BETWEEN p.lft AND p.rgt AND p.lvl = 1 WHERE FIND_IN_SET(n.id, REPLACE(COALESCE(w.department, ''), ' ', '')) > 0 ORDER BY p.lft ASC LIMIT 1)"),
                ])
                ->groupBy(['r.warehouse_id', 'w.warehouse_name', 'w.warehouse_type'])
                ->orderBy(new Expression("CASE WHEN w.warehouse_type = 'SUB' THEN 0 ELSE 1 END ASC, org_root ASC, org_lft ASC, w.warehouse_name ASC"))
                ->all($this->db);
            $snapshotLabel = $period
                ? sprintf('ยอดปิดเดือน %02d/%d', (int) $period['report_month'], (int) $period['report_year'] + 543)
                : 'ไม่มี snapshot ในปีที่เลือก';
        }

        $summary['mainWarehouses'] = [
            ['label' => 'คลังยา', 'value' => null, 'icon' => 'bi-capsule-pill', 'color' => 'success', 'note' => 'ยังไม่มีแหล่งข้อมูลคลังยาในระบบ MATER'],
            ['label' => 'คลังพัสดุ', 'value' => isset($mainValues['คลังพัสดุ']) ? (float) $mainValues['คลังพัสดุ']['value'] : null, 'icon' => 'bi-box-seam', 'color' => 'primary'],
            ['label' => 'คลังทันตกรรม', 'value' => isset($mainValues['คลังวัสดุทันตกรรม']) ? (float) $mainValues['คลังวัสดุทันตกรรม']['value'] : null, 'icon' => 'bi-heart-pulse', 'color' => 'info'],
            ['label' => 'คลังเทคนิคการแพทย์', 'value' => isset($mainValues['คลังวัสดุเทคนิคการแพทย์']) ? (float) $mainValues['คลังวัสดุเทคนิคการแพทย์']['value'] : null, 'icon' => 'bi-eyedropper', 'color' => 'warning'],
        ];

        [$fiscalStart, $fiscalEnd] = $this->fiscalDateRange($selectedFiscalYear);
        $disbursementDate = new Expression('COALESCE(NULLIF(so.disbursement_date, 0), UNIX_TIMESTAMP(so.updated_at))');
        $usageByWarehouse = (new Query())
            ->select([
                'warehouse_id' => 'so.sub_warehouse_id',
                'usage_value' => new Expression('COALESCE(SUM(sd.qty * COALESCE(sd.unit_price, 0)), 0)'),
            ])
            ->from(['so' => StockOrder::tableName()])
            ->innerJoin(['sd' => StockDetail::tableName()], 'sd.stock_order_id = so.id')
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
            ->where([
                'so.order_type' => StockOrder::ORDER_TYPE_OUT,
                'so.source_type' => 'REQUEST',
                'so.status' => StockOrder::STATUS_CONFIRMED,
                'i.name' => 'asset_item',
                'i.group_id' => 'MATER',
            ])
            ->andWhere(['between', $disbursementDate, strtotime($fiscalStart), strtotime($fiscalEnd)])
            ->andWhere(['not', ['so.sub_warehouse_id' => null]])
            ->groupBy('so.sub_warehouse_id')
            ->indexBy('warehouse_id')
            ->all($this->db);
        foreach ($warehouses as &$warehouse) {
            $warehouse['usage_value'] = isset($usageByWarehouse[$warehouse['id']])
                ? (float) $usageByWarehouse[$warehouse['id']]['usage_value']
                : 0.0;
            $warehouse['display_name'] = $warehouse['type'] === 'SUB' && (int) ($warehouse['org_lvl'] ?? 999999) >= 2 && !empty($warehouse['org_name'])
                ? $warehouse['org_name']
                : $warehouse['name'];
            $warehouse['group_name'] = $warehouse['group_name'] ?: 'หน่วยงานอื่น';
        }
        unset($warehouse);

        $itemBalances = (new Query())
            ->select(['item_code', new Expression('SUM(balance_qty) AS total_qty')])
            ->from(StockBalance::tableName())
            ->groupBy('item_code');

        $assessedCount = (int) (new Query())
            ->from(['i' => StockItem::tableName()])
            ->innerJoin(['balance' => $itemBalances], 'balance.item_code = i.code')
            ->where(['i.name' => 'asset_item', 'i.group_id' => 'MATER', 'i.active' => 1])
            ->andWhere(['>', 'i.qty_min', 0])
            ->count('*', $this->db);

        $criticalCount = (int) (new Query())
            ->from(['i' => StockItem::tableName()])
            ->innerJoin(['balance' => $itemBalances], 'balance.item_code = i.code')
            ->where(['i.name' => 'asset_item', 'i.group_id' => 'MATER', 'i.active' => 1])
            ->andWhere(['>', 'i.qty_min', 0])
            ->andWhere('balance.total_qty < i.qty_min')
            ->count('*', $this->db);

        $expiryBase = (new Query())
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
            ->leftJoin(['w' => Warehouse::tableName()], 'w.id = so.main_warehouse_id')
            ->where([
                'so.order_type' => StockOrder::ORDER_TYPE_IN,
                'so.status' => StockOrder::STATUS_CONFIRMED,
                'i.name' => 'asset_item',
                'i.group_id' => 'MATER',
            ])
            ->andWhere(['>', 'sd.remain_qty', 0])
            ->andWhere(['not', ['sd.expiry_date' => null]]);

        $today = date('Y-m-d');
        $next90Days = date('Y-m-d', strtotime('+90 days'));
        $expiredCount = (int) (clone $expiryBase)
            ->andWhere(['<', 'sd.expiry_date', $today])
            ->select(new Expression("COUNT(DISTINCT CONCAT(sd.item_code, ':', sd.lot_number))"))
            ->scalar($this->db);
        $expiringCount = (int) (clone $expiryBase)
            ->andWhere(['between', 'sd.expiry_date', $today, $next90Days])
            ->select(new Expression("COUNT(DISTINCT CONCAT(sd.item_code, ':', sd.lot_number))"))
            ->scalar($this->db);
        return [
            'asOf' => date('Y-m-d H:i:s'),
            'selectedFiscalYear' => $selectedFiscalYear,
            'availableYears' => $availableYears,
            'snapshotLabel' => $snapshotLabel,
            'summary' => $summary,
            'warehouses' => $warehouses,
            'risk' => [
                'critical' => $criticalCount,
                'expiring' => $expiringCount,
                'expired' => $expiredCount,
                'sufficient' => max(0, $assessedCount - $criticalCount),
            ],
        ];
    }

    public function getSubWarehouseDetail(int $warehouseId, ?int $fiscalYear = null): array
    {
        $warehouse = Warehouse::find()
            ->where(['id' => $warehouseId, 'warehouse_type' => Warehouse::SUB_STOCK_TYPES])
            ->one();
        if (!$warehouse) {
            throw new \yii\web\NotFoundHttpException('ไม่พบคลังย่อยที่ต้องการ');
        }

        $year = $fiscalYear ?: (int) AppHelper::YearBudget();
        [$start, $end] = $this->fiscalDateRange($year);
        $disbursementDate = new Expression('COALESCE(NULLIF(so.disbursement_date, 0), UNIX_TIMESTAMP(so.updated_at))');
        $rows = (new Query())
            ->select([
                'code' => 'sd.item_code',
                'name' => 'i.title',
                'category_code' => new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"),
                'category_name' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                'qty' => new Expression('SUM(sd.qty)'),
                'value' => new Expression('SUM(sd.qty * COALESCE(sd.unit_price, 0))'),
                'last_disbursed_at' => new Expression('MAX(COALESCE(NULLIF(so.disbursement_date, 0), UNIX_TIMESTAMP(so.updated_at)))'),
            ])
            ->from(['so' => StockOrder::tableName()])
            ->innerJoin(['sd' => StockDetail::tableName()], 'sd.stock_order_id = so.id')
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where([
                'so.sub_warehouse_id' => $warehouseId,
                'so.order_type' => StockOrder::ORDER_TYPE_OUT,
                'so.source_type' => 'REQUEST',
                'so.status' => StockOrder::STATUS_CONFIRMED,
                'i.name' => 'asset_item',
                'i.group_id' => 'MATER',
            ])
            ->andWhere(['between', $disbursementDate, strtotime($start), strtotime($end)])
            ->groupBy(['sd.item_code', 'i.title', 'cat.code', 'cat.title', 'i.category_id'])
            ->orderBy(['category_name' => SORT_ASC, 'value' => SORT_DESC])
            ->all($this->db);

        $categories = [];
        foreach ($rows as $row) {
            $key = (string) $row['category_code'];
            if (!isset($categories[$key])) {
                $categories[$key] = [
                    'code' => $key,
                    'name' => $row['category_name'],
                    'item_count' => 0,
                    'qty' => 0.0,
                    'value' => 0.0,
                ];
            }
            $categories[$key]['item_count']++;
            $categories[$key]['qty'] += (float) $row['qty'];
            $categories[$key]['value'] += (float) $row['value'];
        }
        $categories = array_values($categories);
        usort($categories, static fn(array $a, array $b): int => $b['value'] <=> $a['value']);

        return [
            'warehouse' => $warehouse,
            'fiscalYear' => $year,
            'rows' => $rows,
            'categories' => $categories,
            'totalValue' => array_sum(array_column($rows, 'value')),
            'totalQty' => array_sum(array_column($rows, 'qty')),
            'dateRange' => [$start, $end],
        ];
    }

    public function getInventoryAlertDetails(string $type): array
    {
        $allowed = ['low-stock', 'expiring', 'expired', 'sufficient'];
        if (!in_array($type, $allowed, true)) {
            throw new \yii\web\NotFoundHttpException('ไม่พบกลุ่มข้อมูลที่ต้องการ');
        }

        if (in_array($type, ['low-stock', 'sufficient'], true)) {
            $balances = (new Query())
                ->select(['item_code', new Expression('SUM(balance_qty) AS total_qty')])
                ->from(StockBalance::tableName())
                ->groupBy('item_code');
            $query = (new Query())
                ->select(['code' => 'i.code', 'name' => 'i.title', 'balance' => 'b.total_qty', 'minimum' => 'i.qty_min'])
                ->from(['i' => StockItem::tableName()])
                ->innerJoin(['b' => $balances], 'b.item_code = i.code')
                ->where(['i.name' => 'asset_item', 'i.group_id' => 'MATER', 'i.active' => 1])
                ->andWhere(['>', 'i.qty_min', 0]);
            $type === 'low-stock'
                ? $query->andWhere('b.total_qty < i.qty_min')->orderBy(new Expression('(i.qty_min - b.total_qty) DESC'))
                : $query->andWhere('b.total_qty >= i.qty_min')->orderBy(['i.title' => SORT_ASC]);
            return ['type' => $type, 'rows' => $query->all($this->db), 'kind' => 'stock'];
        }

        $today = date('Y-m-d');
        $query = (new Query())
            ->select([
                'code' => 'sd.item_code', 'name' => 'i.title', 'lot' => 'sd.lot_number',
                'warehouse' => 'w.warehouse_name', 'qty' => 'sd.remain_qty', 'expiry_date' => 'sd.expiry_date',
                'value' => new Expression('sd.remain_qty * COALESCE(sd.unit_price, 0)'),
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sd.item_code')
            ->leftJoin(['w' => Warehouse::tableName()], 'w.id = so.main_warehouse_id')
            ->where([
                'so.order_type' => StockOrder::ORDER_TYPE_IN,
                'so.status' => StockOrder::STATUS_CONFIRMED,
                'i.name' => 'asset_item', 'i.group_id' => 'MATER',
            ])
            ->andWhere(['>', 'sd.remain_qty', 0])
            ->andWhere(['not', ['sd.expiry_date' => null]])
            ->orderBy(['sd.expiry_date' => SORT_ASC]);
        $type === 'expired'
            ? $query->andWhere(['<', 'sd.expiry_date', $today])
            : $query->andWhere(['between', 'sd.expiry_date', $today, date('Y-m-d', strtotime('+90 days'))]);
        return ['type' => $type, 'rows' => $query->all($this->db), 'kind' => 'expiry'];
    }

    private function fiscalDateRange(int $fiscalYear): array
    {
        $endYear = $fiscalYear - 543;
        $start = sprintf('%d-10-01 00:00:00', $endYear - 1);
        $end = sprintf('%d-09-30 23:59:59', $endYear);
        if ($fiscalYear === (int) AppHelper::YearBudget() && strtotime($end) > time()) {
            $end = date('Y-m-d H:i:s');
        }
        return [$start, $end];
    }

    private function unavailableMetric(string $label, string $description, string $icon, string $color): array
    {
        return [
            'status' => 'unavailable',
            'label' => $label,
            'value' => null,
            'unit' => null,
            'icon' => $icon,
            'color' => $color,
            'description' => $description,
            'details' => [],
            'url' => null,
        ];
    }
}
