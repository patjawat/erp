<?php

namespace app\modules\inventoryV2\controllers;

use app\models\Categorise;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockMonthlyReport;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockItemWarehouseSetting;
use app\modules\inventoryV2\models\Warehouse;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\web\Controller;
use yii\web\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * รายงานสรุปรายงานวัสดุคงคลัง (ส่งบัญชี)
 */
class ReportController extends Controller
{
    /** รหัสคลังที่จัดเป็น "จ่ายส่วนของ รพ.สต." (ที่เหลือนับเป็นโรงพยาบาล) */
    public static function getDisburseSubWarehouseIds()
    {
        return (array) (Yii::$app->params['inventoryV2.disburseSubWarehouseIds'] ?? []);
    }

    /**
     * รายงานสรุปรายงานวัสดุคงคลัง แยกตามประเภทวัสดุ
     */
    public function actionMaterialSummary()
    {
        $year = (int) ($this->request->get('year') ?: date('Y'));
        $month = (int) ($this->request->get('month') ?: (int) date('n'));
        $warehouseId = $this->request->get('warehouse_id') ? (int) $this->request->get('warehouse_id') : null;

        $listWarehouse = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $warehouses = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map($listWarehouse, 'id', 'warehouse_name');

        $rows = $this->aggregateByCategory($year, $month, $warehouseId);
        $hasData = !empty($rows);
        $closeMeta = $hasData ? $this->getCloseMetadata($year, $month, $warehouseId) : null;

        return $this->render('material-summary', [
            'year' => $year,
            'month' => $month,
            'warehouseId' => $warehouseId,
            'warehouses' => $warehouses,
            'rows' => $rows,
            'hasData' => $hasData,
            'closeMeta' => $closeMeta,
        ]);
    }

    /**
     * ดึงเวลา/ผู้ปิดเดือน ของ snapshot ปัจจุบัน
     * — closed_at = MAX(created_at) ของ rows ที่ตรงกับ year/month/warehouse
     * — closed_by_name = ชื่อพนักงานจาก Employees ที่ match user_id
     * @return array{closed_at: int|null, closed_by_name: string|null}
     */
    protected function getCloseMetadata($year, $month, $warehouseId = null): array
    {
        $q = (new Query())
            ->select(['created_at' => new Expression('MAX(created_at)'), 'created_by'])
            ->from(StockMonthlyReport::tableName())
            ->where(['report_year' => $year, 'report_month' => $month])
            ->groupBy('created_by')
            ->orderBy(['created_at' => SORT_DESC]);
        if ($warehouseId !== null && $warehouseId !== '') {
            $q->andWhere(['warehouse_id' => $warehouseId]);
        }
        $row = $q->limit(1)->one();
        if (!$row) {
            return ['closed_at' => null, 'closed_by_name' => null];
        }
        $name = null;
        if (!empty($row['created_by'])) {
            $emp = \app\modules\hr\models\Employees::find()
                ->where(['user_id' => (int) $row['created_by']])
                ->one();
            if ($emp) {
                $name = trim(($emp->fname ?? '') . ' ' . ($emp->lname ?? ''));
            }
        }
        return [
            'closed_at' => !empty($row['created_at']) ? strtotime($row['created_at']) : null,
            'closed_by_name' => $name ?: null,
        ];
    }

    /**
     * Drill-down: รายการ item ที่ประกอบเป็นยอด cell ของ summary (category × kind × month × warehouse)
     *
     * เปิดจาก material-summary view เมื่อ accountant คลิกตัวเลขใน table cell
     * Return JSON เพื่อ render ลง modal ฝั่ง client ทันที (ไม่ใช้ partial HTML)
     */
    public function actionCategoryDrilldown()
    {
        $this->response->format = Response::FORMAT_JSON;

        $year = (int) ($this->request->get('year') ?: date('Y'));
        $month = (int) ($this->request->get('month') ?: (int) date('n'));
        $warehouseId = $this->request->get('warehouse_id') !== null && $this->request->get('warehouse_id') !== ''
            ? (int) $this->request->get('warehouse_id') : null;
        $category = (string) $this->request->get('category', '');
        $kind = (string) $this->request->get('kind', '');

        $allowedKinds = ['opening', 'in', 'out_sub', 'out_hosp', 'total_out', 'closing'];
        if (!in_array($kind, $allowedKinds, true)) {
            return ['success' => false, 'message' => 'kind ไม่ถูกต้อง'];
        }
        if ($category === '') {
            return ['success' => false, 'message' => 'ไม่ระบุ category'];
        }

        $kindLabels = [
            'opening' => 'สินค้าคงเหลือยกมา',
            'in' => 'ซื้อระหว่างเดือน',
            'out_sub' => 'จ่ายส่วนของ รพ.สต.',
            'out_hosp' => 'จ่ายส่วนของโรงพยาบาล',
            'total_out' => 'รวมจ่ายออก',
            'closing' => 'ยอดยกไป',
        ];
        $qtyCol = "r.{$kind}_qty";
        $valueCol = "r.{$kind}_value";

        $query = (new Query())
            ->select([
                'item_code' => 'r.item_code',
                'item_name' => new Expression('COALESCE(i.title, r.item_code)'),
                'unit_name' => 'r.unit_name',
                'qty' => new Expression("SUM($qtyCol)"),
                'value' => new Expression("SUM($valueCol)"),
                'category_code' => new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"),
                'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
            ])
            ->from(['r' => StockMonthlyReport::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = r.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where(['r.report_year' => $year, 'r.report_month' => $month])
            ->andWhere("COALESCE(cat.code, i.category_id, 'OTHER') = :cat", [':cat' => $category])
            ->groupBy(['r.item_code', 'i.title', 'r.unit_name', 'cat.code', 'cat.title', 'i.category_id'])
            ->orderBy([new Expression("SUM($valueCol) DESC")]);

        if ($warehouseId !== null) {
            $query->andWhere(['r.warehouse_id' => $warehouseId]);
        }

        $raw = $query->all();

        // Batch resolve image URLs (avoid N+1) — pattern เดียวกับ loadBalanceData
        $itemCodes = array_values(array_unique(array_map(fn($r) => (string) $r['item_code'], $raw)));
        $stockItems = empty($itemCodes)
            ? []
            : StockItem::find()->where(['code' => $itemCodes])->indexBy('code')->all();
        $refs = array_values(array_filter(array_map(fn($i) => $i->ref ?? null, $stockItems)));
        $uploadsByRef = empty($refs)
            ? []
            : \app\modules\filemanager\models\Uploads::find()->where(['ref' => $refs])->indexBy('ref')->all();
        $placeholderUrl = Yii::getAlias('@web') . '/img/placeholder-img.jpg';
        $resolveImage = function ($itemCode) use ($stockItems, $uploadsByRef, $placeholderUrl) {
            $it = $stockItems[$itemCode] ?? null;
            if (!$it || empty($it->ref) || !isset($uploadsByRef[$it->ref])) {
                return $placeholderUrl;
            }
            return FileManagerHelper::getImg($uploadsByRef[$it->ref]->id);
        };

        $items = [];
        $totalQty = 0.0;
        $totalValue = 0.0;
        $categoryTitle = '';
        foreach ($raw as $r) {
            $q = (float) $r['qty'];
            $v = (float) $r['value'];
            if (abs($v) < 0.005) {
                continue;
            }
            $totalQty += $q;
            $totalValue += $v;
            if ($categoryTitle === '') {
                $categoryTitle = (string) $r['category_title'];
            }
            $items[] = [
                'item_code' => (string) $r['item_code'],
                'item_name' => (string) $r['item_name'],
                'unit_name' => (string) ($r['unit_name'] ?? '-'),
                'qty' => $q,
                'value' => $v,
                'image_url' => $resolveImage((string) $r['item_code']),
            ];
        }

        // คิดสัดส่วนเทียบ total (ใช้ absolute เพื่อให้ติดลบยัง render bar ได้)
        $absTotal = 0.0;
        foreach ($items as $it) {
            $absTotal += abs($it['value']);
        }
        if ($absTotal > 0) {
            foreach ($items as &$it) {
                $it['percent_of_total'] = round((abs($it['value']) / $absTotal) * 100, 2);
            }
            unset($it);
        } else {
            foreach ($items as &$it) {
                $it['percent_of_total'] = 0.0;
            }
            unset($it);
        }

        $monthNames = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];
        $periodLabel = ($monthNames[$month] ?? '') . ' ' . ($year + 543);
        $warehouseLabel = '— ทุกคลังหลัก —';
        if ($warehouseId !== null) {
            $w = Warehouse::findOne($warehouseId);
            $warehouseLabel = $w ? $w->warehouse_name : (string) $warehouseId;
        }

        return [
            'success' => true,
            'items' => $items,
            'summary' => [
                'count' => count($items),
                'total_qty' => $totalQty,
                'total_value' => $totalValue,
            ],
            'meta' => [
                'category_code' => $category,
                'category_label' => $categoryTitle ?: $category,
                'period_label' => $periodLabel,
                'warehouse_label' => $warehouseLabel,
                'kind' => $kind,
                'kind_label' => $kindLabels[$kind],
            ],
        ];
    }

    /**
     * Legacy route — redirect ตามสิทธิ์ของ user
     * - ถ้าเป็นเจ้าหน้าที่คลังหลัก → main-stock/balance
     * - ถ้าเป็นเจ้าหน้าที่คลังย่อย → sub-stock/balance
     * เก็บไว้เพื่อ backward compatibility (bookmark / external link)
     */
    public function actionBalanceByWarehouse()
    {
        $params = $this->request->getQueryParams();
        return $this->redirect(array_merge(
            [self::resolveBalanceRoute()],
            $params
        ));
    }

    /**
     * เลือก route ของหน้ายอดคงเหลือตามสิทธิ์ user
     * - มี main warehouse → ไป main, ไม่มีก็ไป sub (default)
     */
    public static function resolveBalanceRoute(): string
    {
        $hasMain = !empty(Warehouse::findMainWarehousesForReceive());
        return $hasMain
            ? '/inventory-v2/main-stock/balance'
            : '/inventory-v2/sub-stock/balance';
    }

    /**
     * สร้าง context สำหรับหน้าสรุปยอดคงเหลือตามคลัง (ใช้ร่วมกันระหว่าง main-stock/balance และ sub-stock/balance)
     *
     * @param Warehouse[] $accessibleWarehouses คลังที่ user มีสิทธิ์ใน scope ของหน้านั้น (MAIN หรือ SUB)
     * @param array $query query params (warehouse_id, category_id, status, search)
     * @param string $allLabel label สำหรับ option "ทุกคลัง" ของ dropdown
     * @return array view context (warehouseId, warehouses, rows, summary, categories, categoryId, status, search)
     */
    public static function buildBalanceContext(array $accessibleWarehouses, array $query, string $allLabel = '-- ทุกคลัง --'): array
    {
        $rawWarehouseId = $query['warehouse_id'] ?? null;
        $warehouseId = ($rawWarehouseId !== null && $rawWarehouseId !== '')
            ? (int) $rawWarehouseId
            : null;
        $categoryId = $query['category_id'] ?? null;
        $status = $query['status'] ?? null;
        $search = $query['search'] ?? null;

        $accessibleIds = array_map('intval', array_column($accessibleWarehouses, 'id'));

        // ห้ามดูทะลุคลังที่ไม่ได้รับสิทธิ์ผ่าน URL ตรง
        if ($warehouseId !== null && !in_array($warehouseId, $accessibleIds, true)) {
            $warehouseId = null;
        }
        // ถ้ามีคลังเดียวให้ default เลือกอัตโนมัติ
        if ($warehouseId === null && count($accessibleIds) === 1) {
            $warehouseId = $accessibleIds[0];
        }

        $warehouses = ['' => $allLabel];
        foreach ($accessibleWarehouses as $w) {
            $warehouses[$w->id] = $w->warehouse_name;
        }

        $warehouseIds = $warehouseId ? [$warehouseId] : $accessibleIds;
        $data = self::loadBalanceData($warehouseIds, $accessibleWarehouses);
        $rows = $data['rows'];

        if ($categoryId || $status || $search) {
            $rows = self::applyBalanceFilters($rows, $categoryId, $status, $search);
            $summary = [
                'total_value' => array_sum(array_column($rows, 'value')),
                'items_count' => count($rows),
                'below_min_count' => count(array_filter($rows, fn($r) => $r['below_min'])),
                'below_max_count' => count(array_filter($rows, fn($r) => $r['below_max'] && !$r['below_min'])),
            ];
        } else {
            $summary = $data['summary'];
        }

        // Scope ประเภทวัสดุตามคลังที่เลือก (อ่านจาก data_json.item_type ของคลัง)
        $warehousesForCategory = $warehouseId
            ? array_values(array_filter($accessibleWarehouses, fn($w) => (int) $w->id === (int) $warehouseId))
            : $accessibleWarehouses;

        $allowedCodes = null;
        foreach ($warehousesForCategory as $w) {
            $codes = $w->getAllowedItemTypeCodes();
            if (empty($codes)) {
                $allowedCodes = null;
                break;
            }
            $allowedCodes = $allowedCodes === null ? $codes : array_unique(array_merge($allowedCodes, $codes));
        }

        $categoryQuery = Categorise::find()->where(['name' => 'asset_type', 'group_id' => 'MATER']);
        if (is_array($allowedCodes)) {
            $categoryQuery->andWhere(['code' => $allowedCodes]);
        }
        $categories = \yii\helpers\ArrayHelper::map($categoryQuery->orderBy('title')->all(), 'code', 'title');

        if ($categoryId !== null && $categoryId !== '' && !isset($categories[$categoryId])) {
            $categoryId = null;
        }

        return [
            'warehouseId' => $warehouseId,
            'warehouses' => $warehouses,
            'rows' => $rows,
            'summary' => $summary,
            'categories' => $categories,
            'categoryId' => $categoryId,
            'status' => $status,
            'search' => $search,
        ];
    }

    /**
     * Filter rows ตาม category / status / search — ใช้ใน buildBalanceContext
     */
    protected static function applyBalanceFilters(array $rows, $categoryId, $status, $search): array
    {
        return array_values(array_filter($rows, function ($item) use ($categoryId, $status, $search) {
            if ($categoryId) {
                if (($item['category_id'] ?? null) != $categoryId) {
                    return false;
                }
            }
            if ($status) {
                $isBelowMin = $item['below_min'] ?? false;
                $isBelowMax = $item['below_max'] ?? false;
                if ($status === 'below_min' && !$isBelowMin) return false;
                if ($status === 'below_max' && (!$isBelowMax || $isBelowMin)) return false;
                if ($status === 'normal' && ($isBelowMin || $isBelowMax)) return false;
            }
            if ($search) {
                $s = mb_strtolower(trim($search), 'UTF-8');
                $itemCode = mb_strtolower($item['item_code'] ?? '', 'UTF-8');
                $itemName = mb_strtolower($item['item_name'] ?? '', 'UTF-8');
                $sDigits = preg_replace('/[^0-9.]/', '', $s);
                $balanceStr = (string) ($item['balance_qty'] ?? '');
                $valueStr = (string) ($item['value'] ?? '');
                $hit = strpos($itemCode, $s) !== false
                    || strpos($itemName, $s) !== false
                    || ($sDigits !== '' && strpos($balanceStr, $sDigits) !== false)
                    || ($sDigits !== '' && strpos($valueStr, $sDigits) !== false);
                if (!$hit) return false;
            }
            return true;
        }));
    }

    /**
     * ดึงข้อมูลรายการวัสดุคงเหลือตามคลัง (ใช้ทั้งหน้าแสดงและ export Excel)
     * @param int[] $warehouseIds
     * @param \app\modules\inventoryV2\models\Warehouse[] $accessibleWarehouses คลังที่ user มีสิทธิ์ใน scope ของหน้านั้น (MAIN หรือ SUB)
     * @return array{rows: array, summary: array}
     */
    public static function loadBalanceData(array $warehouseIds, array $accessibleWarehouses): array
    {
        $listMain = array_values(array_filter($accessibleWarehouses, fn($w) => ($w->warehouse_type ?? null) === 'MAIN'));
        $listSub = array_values(array_filter($accessibleWarehouses, fn($w) => ($w->warehouse_type ?? null) !== 'MAIN'));
        if (empty($warehouseIds)) {
            return [
                'rows' => [],
                'summary' => ['total_value' => 0, 'below_min_count' => 0, 'below_max_count' => 0, 'items_count' => 0],
            ];
        }
        // มูลค่าคงเหลือคิดแบบ ledger (เงินเข้า−ออกจริงทุก transaction) ให้ตรงกับหน้าประวัติการเคลื่อนไหว
        // เดิมใช้ balance_qty × ราคา IN ล่าสุดต่อ lot ซึ่งคลาดจาก ledger (เศษปัด + ของหลายราคาปนกัน)
        // ใช้ perspective เดียวกับ getItemHistoryData: main = มุมต้นทาง, sub(ไม่ใช่ migrated) = มุมปลายทาง
        $ledgerMap = self::loadLedgerValues($warehouseIds);

        $query = (new Query())
            ->select([
                's.warehouse_id',
                's.item_code',
                new Expression('i.title AS item_name'),
                new Expression('i.category_id AS category_id'),
                new Expression('s.min_qty AS min_qty'),
                new Expression('s.max_qty AS max_qty'),
                new Expression('COALESCE(cat.title, i.category_id, \'อื่นๆ\') AS category_title'),
                new Expression('COALESCE(SUM(sb.balance_qty), 0) AS balance_qty'),
            ])
            ->from(['s' => StockItemWarehouseSetting::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = s.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->leftJoin(
                ['sb' => StockBalance::tableName()],
                'sb.item_code = s.item_code AND sb.warehouse_id = s.warehouse_id'
            )
            ->where(['s.warehouse_id' => $warehouseIds])
            ->andWhere(['i.name' => 'asset_item', 'i.group_id' => 'MATER'])
            ->andWhere(['i.active' => 1])
            ->groupBy(['s.warehouse_id', 's.item_code', 'i.title', 's.min_qty', 's.max_qty', 'cat.title', 'i.category_id']);

        $warehouseById = [];
        foreach ($accessibleWarehouses as $warehouse) {
            $warehouseById[(int) $warehouse->id] = $warehouse;
        }
        $allowedTypeConditions = ['or'];
        foreach ($warehouseIds as $warehouseId) {
            $warehouse = $warehouseById[(int) $warehouseId] ?? null;
            if ($warehouse === null) {
                continue;
            }
            $condition = ['s.warehouse_id' => (int) $warehouseId];
            $allowedTypes = $warehouse->getAllowedItemTypeCodes();
            if (!empty($allowedTypes)) {
                $condition = ['and', $condition, ['i.category_id' => $allowedTypes]];
            }
            $allowedTypeConditions[] = $condition;
        }
        if (count($allowedTypeConditions) > 1) {
            $query->andWhere($allowedTypeConditions);
        }

        $raw = $query->all();
        $warehouseNames = [];
        foreach ($listMain as $w) {
            $warehouseNames[$w->id] = 'คลังหลัก: ' . $w->warehouse_name;
        }
        foreach ($listSub as $w) {
            $warehouseNames[$w->id] = $w->warehouse_name;
        }

        // Batch resolve image URLs (avoid N+1 + avoid base64 placeholder bloat)
        $itemCodes = array_values(array_unique(array_map(fn($r) => (string) $r['item_code'], $raw)));
        $items = empty($itemCodes)
            ? []
            : StockItem::find()->where(['code' => $itemCodes])->indexBy('code')->all();
        $refs = array_values(array_filter(array_map(fn($i) => $i->ref ?? null, $items)));
        $uploadsByRef = empty($refs)
            ? []
            : Uploads::find()->where(['ref' => $refs])->indexBy('ref')->all();
        $placeholderUrl = Yii::getAlias('@web') . '/img/placeholder-img.jpg';

        $resolveImage = function ($itemCode) use ($items, $uploadsByRef, $placeholderUrl) {
            $item = $items[$itemCode] ?? null;
            if (!$item || empty($item->ref) || !isset($uploadsByRef[$item->ref])) {
                return $placeholderUrl;
            }
            // สร้าง URL ตรง ๆ — เลี่ยง getImg() ที่ยิง Uploads::findOne() + file_exists ซ้ำต่อแถว (N+1 ตอน render)
            // ถ้าไฟล์หาย show จะคืน placeholder เอง และ onerror ฝั่ง client ก็รองรับอยู่แล้ว
            return \yii\helpers\Url::to(['/filemanager/uploads/show', 'id' => $uploadsByRef[$item->ref]->id], true);
        };

        $rows = [];
        $totalValue = 0;
        $belowMinCount = 0;
        $belowMaxCount = 0;

        foreach ($raw as $r) {
            $balance = (float) $r['balance_qty'];
            $value = $ledgerMap[(int) $r['warehouse_id'] . ':' . (string) $r['item_code']] ?? 0.0;
            $minQty = $r['min_qty'] !== null ? (float) $r['min_qty'] : null;
            $maxQty = $r['max_qty'] !== null ? (float) $r['max_qty'] : null;
            $belowMin = $minQty !== null && $minQty > 0 && $balance < $minQty;
            $belowMax = $maxQty !== null && $maxQty > 0 && $balance < $maxQty;
            if ($belowMin) {
                $belowMinCount++;
            }
            if ($belowMax) {
                $belowMaxCount++;
            }
            $totalValue += $value;
            $item = $items[$r['item_code']] ?? null;
            $unitName = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : null;
            $rows[] = [
                'warehouse_id' => (int) $r['warehouse_id'],
                'warehouse_name' => $warehouseNames[(int) $r['warehouse_id']] ?? (string) $r['warehouse_id'],
                'item_code' => (string) $r['item_code'],
                'item_name' => (string) $r['item_name'],
                'category_id' => $r['category_id'] !== null ? (string) $r['category_id'] : null,
                'category_title' => (string) ($r['category_title'] ?? 'อื่นๆ'),
                'unit_name' => $unitName ? (string) $unitName : '-',
                'image_url' => $resolveImage($r['item_code']),
                'balance_qty' => $balance,
                'value' => $value,
                'min_qty' => $minQty,
                'max_qty' => $maxQty,
                'below_min' => $belowMin,
                'below_max' => $belowMax,
            ];
        }
        return [
            'rows' => $rows,
            'summary' => [
                'total_value' => $totalValue,
                'below_min_count' => $belowMinCount,
                'below_max_count' => $belowMaxCount,
                'items_count' => count($rows),
            ],
        ];
    }

    /**
     * มูลค่าคงเหลือแบบ ledger ต่อ (warehouse_id, item_code) — เงินเข้า−ออกจริงจากทุก transaction
     * ยึด perspective เดียวกับ getItemHistoryData:
     *   - มุมคลังหลัก (main_warehouse_id): IN=+, OUT/TRANSFER=−, ADJUST=+qty*price (qty signed)
     *   - มุมคลังย่อย (sub_warehouse_id, เฉพาะที่ไม่ใช่ V1-migrated และ main<>sub): OUT/TRANSFER=+, IN=−
     * คืน map คีย์ "warehouseId:itemCode" => value
     * @param int[] $warehouseIds
     * @return array<string,float>
     */
    public static function loadLedgerValues(array $warehouseIds): array
    {
        if (empty($warehouseIds)) {
            return [];
        }
        $whList = implode(',', array_map('intval', $warehouseIds));
        $sd = StockDetail::tableName();
        $so = StockOrder::tableName();
        // เงื่อนไข V1-migrated เหมือนใน getItemHistoryData (migrated นับเฉพาะมุมคลังหลัก)
        $migrated = "(COALESCE(sd.ref,'')='V1' OR COALESCE(sd.data_json,'') LIKE '%\"migrated_from_v1\"%')";

        $sql = "
            SELECT wh, item_code, SUM(sv) AS value FROM (
                SELECT so.main_warehouse_id AS wh, sd.item_code,
                    CASE so.order_type
                        WHEN 'IN' THEN sd.qty * COALESCE(sd.unit_price, 0)
                        WHEN 'OUT' THEN -sd.qty * COALESCE(sd.unit_price, 0)
                        WHEN 'TRANSFER' THEN -sd.qty * COALESCE(sd.unit_price, 0)
                        WHEN 'ADJUST' THEN
                            CASE
                                WHEN CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(sd.data_json, '$.adjust_value_only')), '0') AS UNSIGNED) = 1
                                    THEN COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(sd.data_json, '$.value_delta')) AS DECIMAL(15,6)), 0)
                                ELSE sd.qty * COALESCE(sd.unit_price, 0)
                            END
                        ELSE -sd.qty * COALESCE(sd.unit_price, 0)
                    END AS sv
                FROM {$sd} sd
                INNER JOIN {$so} so ON so.id = sd.stock_order_id
                WHERE so.status = 'CONFIRMED' AND so.main_warehouse_id IN ({$whList})
                UNION ALL
                SELECT so.sub_warehouse_id AS wh, sd.item_code,
                    CASE so.order_type
                        WHEN 'OUT' THEN sd.qty * COALESCE(sd.unit_price, 0)
                        WHEN 'IN' THEN -sd.qty * COALESCE(sd.unit_price, 0)
                        WHEN 'TRANSFER' THEN sd.qty * COALESCE(sd.unit_price, 0)
                        ELSE -sd.qty * COALESCE(sd.unit_price, 0)
                    END AS sv
                FROM {$sd} sd
                INNER JOIN {$so} so ON so.id = sd.stock_order_id
                WHERE so.status = 'CONFIRMED' AND so.sub_warehouse_id IN ({$whList})
                    AND so.sub_warehouse_id <> COALESCE(so.main_warehouse_id, -1)
                    AND NOT {$migrated}
            ) u
            GROUP BY wh, item_code
        ";

        $map = [];
        foreach (Yii::$app->db->createCommand($sql)->queryAll() as $row) {
            $map[(int) $row['wh'] . ':' . (string) $row['item_code']] = (float) $row['value'];
        }
        return $map;
    }

    /**
     * ประวัติการเคลื่อนไหวของวัสดุ 1 รายการ ภายในคลังที่ระบุ (modal popup)
     * รองรับทั้งคลังหลัก (main_warehouse_id = X) และคลังย่อย (sub_warehouse_id = X)
     */
    public function actionItemHistory($item_code, $warehouse_id, $start_date = null, $end_date = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->getItemHistoryData($item_code, $warehouse_id, $start_date, $end_date);
    }

    /**
     * ดึงข้อมูลประวัติการเคลื่อนไหววัสดุ — ใช้ทั้ง modal (JSON) และ Excel export
     * @return array{meta: array, summary: array, transactions: array}
     */
    protected function getItemHistoryData($item_code, $warehouse_id, $start_date = null, $end_date = null)
    {
        $warehouseId = (int) $warehouse_id;
        $startDate = $start_date ?: date('Y-m-01');
        $endDate = $end_date ?: date('Y-m-d');

        $warehouse = Warehouse::findOne($warehouseId);
        $warehouseLabel = $warehouse
            ? (($warehouse->warehouse_type === 'MAIN' ? 'คลังหลัก: ' : 'คลังย่อย: ') . $warehouse->warehouse_name)
            : (string) $warehouseId;

        $item = StockItem::find()->where(['code' => $item_code])->one();
        $itemName = $item->title ?? (string) $item_code;
        $unitName = ($item && method_exists($item, 'getUnitName')) ? (string) $item->getUnitName() : '-';

        $imageUrl = Yii::getAlias('@web') . '/img/placeholder-img.jpg';
        if ($item && !empty($item->ref)) {
            $upload = Uploads::find()->where(['ref' => $item->ref])->one();
            if ($upload) {
                $imageUrl = FileManagerHelper::getImg($upload->id);
            }
        }

        $baseSelect = [
            'order_id' => 'so.id',
            'detail_id' => 'sd.id',
            'so.order_no',
            'so.order_type',
            'so.source_type',
            'so.order_date',
            'so.main_warehouse_id',
            'so.sub_warehouse_id',
            'order_data_json' => 'so.data_json',
            'sd.qty',
            'sd.unit_price',
            'sd.lot_number',
        ];

        // ADJUST: qty เก็บแบบ signed (StockAdjustController รับ "บวก=เพิ่ม, ลบ=ลด")
        // TRANSFER: main = ต้นทาง (out), sub = ปลายทาง (in)
        $perspective = function ($row) use ($warehouseId) {
            $type   = $row['order_type'];
            $isMain = (int) $row['main_warehouse_id'] === $warehouseId;
            $isSub  = (int) ($row['sub_warehouse_id'] ?? 0) === $warehouseId;

            if ($type === 'ADJUST') {
                return ((float) $row['qty']) >= 0 ? 'in' : 'out';
            }
            if ($type === 'TRANSFER') {
                if ($isMain && !$isSub) return 'out';
                if ($isSub && !$isMain) return 'in';
            }
            if ($type === 'IN' && $isMain) return 'in';
            if ($type === 'OUT' && $isMain) return 'out';
            if ($type === 'OUT' && $isSub && !$isMain) return 'in';
            if ($type === 'IN' && $isSub && !$isMain) return 'out';
            return 'out';
        };

        $v1MigratedHistorySql = "(COALESCE(sd.ref, '') = 'V1' OR COALESCE(sd.data_json, '') LIKE '%\"migrated_from_v1\"%')";
        $warehouseHistoryCondition = [
            'or',
            ['and',
                new Expression($v1MigratedHistorySql),
                ['so.main_warehouse_id' => $warehouseId],
            ],
            ['and',
                new Expression('NOT ' . $v1MigratedHistorySql),
                ['or',
                    ['so.main_warehouse_id' => $warehouseId],
                    ['so.sub_warehouse_id' => $warehouseId],
                ],
            ],
        ];

        // 1) ยอดยกมาก่อนวันที่เริ่มต้น
        $bfRows = (new Query())
            ->select($baseSelect)
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['sd.item_code' => $item_code])
            ->andWhere($warehouseHistoryCondition)
            ->andWhere(['<', 'so.order_date', $startDate . ' 00:00:00'])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->all();

        $qtyBF = 0.0;
        $valueBF = 0.0;
        foreach ($bfRows as $r) {
            $q = abs((float) $r['qty']);   // ADJUST อาจมี qty < 0 — ใช้ค่าสัมบูรณ์
            $p = (float) $r['unit_price'];
            if ($perspective($r) === 'in') {
                $qtyBF += $q;
                $valueBF += $q * $p;
            } else {
                $qtyBF -= $q;
                $valueBF -= $q * $p;
            }
        }

        // 2) Transactions ในช่วงเวลา
        $txRows = (new Query())
            ->select($baseSelect)
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['sd.item_code' => $item_code])
            ->andWhere($warehouseHistoryCondition)
            ->andWhere(['between', 'so.order_date', $startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->orderBy(['so.order_date' => SORT_ASC, 'so.id' => SORT_ASC])
            ->all();

        $sourceLabel = [
            'PO' => 'รับเข้าจาก PO',
            'NORMAL' => 'รับเข้าทั่วไป',
            'DONATE' => 'รับบริจาค',
            'INITIAL' => 'ยอดยกมา (ตั้งต้น)',
            'FREE_GIFT' => 'ของแถม',
            'REQUEST' => 'จ่ายตามใบขอเบิก',
            'ADJUST' => 'ปรับยอด',
            'TRANSFER' => 'โอนย้ายคลัง',
            'ISSUE' => 'จ่ายออก',
        ];

        $runningQty = $qtyBF;
        $runningValue = $valueBF;
        $totalIn = 0.0;
        $totalOut = 0.0;
        $totalInValue = 0.0;
        $totalOutValue = 0.0;
        $transactions = [];
        $reversedDetailIds = [];

        $reverseRows = (new Query())
            ->select(['so.data_json'])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where([
                'sd.item_code' => $item_code,
                'so.main_warehouse_id' => $warehouseId,
                'so.order_type' => StockOrder::ORDER_TYPE_ADJUST,
                'so.status' => StockOrder::STATUS_CONFIRMED,
            ])
            ->andWhere(['like', 'so.data_json', '"reverse_detail_id"'])
            ->all();
        foreach ($reverseRows as $reverseRow) {
            $orderData = is_string($reverseRow['data_json']) ? json_decode($reverseRow['data_json'], true) : [];
            if (is_array($orderData) && !empty($orderData['reverse_detail_id'])) {
                $reversedDetailIds[(int) $orderData['reverse_detail_id']] = true;
            }
        }

        foreach ($txRows as $r) {
            if ((string) $r['order_type'] !== StockOrder::ORDER_TYPE_ADJUST || empty($r['order_data_json'])) {
                continue;
            }
            $orderData = is_string($r['order_data_json']) ? json_decode($r['order_data_json'], true) : [];
            if (is_array($orderData) && !empty($orderData['reverse_detail_id'])) {
                $reversedDetailIds[(int) $orderData['reverse_detail_id']] = true;
            }
        }

        // ยอดคงเหลือคงค้างต่อ lot ในคลังนี้ (ปัจจุบัน) — ใช้ไฮไลต์ lot ที่ยังมีของเหลือในประวัติ
        $lotBalanceMap = [];
        foreach ((new Query())
            ->select(['lot_number', 'bal' => new Expression('SUM(balance_qty)')])
            ->from(StockBalance::tableName())
            ->where(['item_code' => $item_code, 'warehouse_id' => $warehouseId])
            ->groupBy('lot_number')
            ->all() as $lb) {
            $lotBalanceMap[(string) $lb['lot_number']] = (float) $lb['bal'];
        }

        foreach ($txRows as $r) {
            $q = abs((float) $r['qty']);   // ADJUST อาจมี qty < 0 — ใช้ค่าสัมบูรณ์
            $p = (float) $r['unit_price'];
            $direction = $perspective($r);
            $delta = $q * $p;

            if ($direction === 'in') {
                $runningQty += $q;
                $runningValue += $delta;
                $totalIn += $q;
                $totalInValue += $delta;
            } else {
                $runningQty -= $q;
                $runningValue -= $delta;
                $totalOut += $q;
                $totalOutValue += $delta;
            }

            $sourceKey = (string) ($r['source_type'] ?: $r['order_type']);

            // ADJUST: อนุญาตแก้ไข/ลบรายการปรับยอด (แก้เวลาปรับผิดพลาด)
            // ยกเว้นรายการ reverse ที่ระบบสร้าง (history_only_reverse) และ value_only แก้จำนวนไม่ได้
            $isAdjust = (string) $r['order_type'] === StockOrder::ORDER_TYPE_ADJUST;
            $adjustMode = '';
            $isSystemReverse = false;
            if ($isAdjust && !empty($r['order_data_json'])) {
                $od = json_decode($r['order_data_json'], true);
                if (is_array($od)) {
                    $adjustMode = (string) ($od['adjust_mode'] ?? '');
                    $isSystemReverse = !empty($od['history_only_reverse']);
                }
            }
            $canManageAdjust = $isAdjust && $adjustMode !== 'history_reverse' && !$isSystemReverse;
            $canEditAdjust = $canManageAdjust && $q > 0.000001; // value_only (qty=0) แก้จำนวนไม่ได้

            $transactions[] = [
                'order_id' => (int) $r['order_id'],
                'detail_id' => (int) $r['detail_id'],
                'date' => date('d/m/Y', strtotime($r['order_date'])),
                'time' => date('H:i', strtotime($r['order_date'])),
                'date_iso' => date('Y-m-d', strtotime($r['order_date'])), // สำหรับ prefill ช่องแก้วันที่ (ADJUST)
                'order_no' => (string) $r['order_no'],
                'order_type' => (string) $r['order_type'],
                'source_label' => $sourceLabel[$sourceKey] ?? ($sourceKey ?: '-'),
                'direction' => $direction,
                'qty' => $q,
                'signed_qty' => (float) $r['qty'], // จำนวนจริงพร้อมเครื่องหมาย (ADJUST +/-) สำหรับ prefill ตอนแก้ไข
                'unit_price' => $p,
                'amount' => $delta,
                'balance_qty' => $runningQty,
                'balance_value' => $runningValue,
                'lot' => (string) ($r['lot_number'] ?? '-'),
                'lot_remain' => $lotBalanceMap[(string) ($r['lot_number'] ?? '')] ?? 0.0,
                'lot_has_balance' => (($lotBalanceMap[(string) ($r['lot_number'] ?? '')] ?? 0.0) > 0.000001),
                'can_reverse' => (string) $r['order_type'] !== StockOrder::ORDER_TYPE_ADJUST && empty($reversedDetailIds[(int) $r['detail_id']]),
                'reverse_status' => !empty($reversedDetailIds[(int) $r['detail_id']]) ? 'reversed' : null,
                'can_edit_qty' => (string) $r['order_type'] === StockOrder::ORDER_TYPE_OUT,
                'can_delete_issue' => (string) $r['order_type'] === StockOrder::ORDER_TYPE_OUT,
                'can_edit_adjust' => $canEditAdjust,
                'can_delete_adjust' => $canManageAdjust,
            ];
        }

        // 3) ยอดคงเหลือปัจจุบันที่ stock_balance (truth source)
        $currentBalance = (float) (new Query())
            ->from(StockBalance::tableName())
            ->where(['item_code' => $item_code, 'warehouse_id' => $warehouseId])
            ->sum('balance_qty');

        // รายลอตสำหรับเครื่องมือแก้ยอดคงเหลือ: balance_qty (stock_balance) เทียบ remain_qty (FIFO source details)
        $lotRemainMap = [];
        $lotReceivedMap = [];
        foreach ((new Query())
            ->select(['lot_number' => 'sd.lot_number', 'rem' => new Expression('SUM(sd.remain_qty)'), 'recv' => new Expression('SUM(sd.qty)')])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['sd.item_code' => $item_code])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['or',
                ['and', ['so.main_warehouse_id' => $warehouseId], ['or',
                    ['so.order_type' => StockOrder::ORDER_TYPE_IN],
                    ['and', ['so.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'sd.qty', 0]],
                ]],
                ['and', ['so.order_type' => StockOrder::ORDER_TYPE_TRANSFER], ['so.sub_warehouse_id' => $warehouseId], ['>', 'sd.qty', 0]],
            ])
            ->groupBy('sd.lot_number')
            ->all() as $lr) {
            $lotRemainMap[(string) $lr['lot_number']] = (float) $lr['rem'];
            $lotReceivedMap[(string) $lr['lot_number']] = (float) $lr['recv'];
        }
        $lots = [];
        foreach (array_values(array_unique(array_merge(array_keys($lotBalanceMap), array_keys($lotRemainMap)))) as $lotNo) {
            $bal = (float) ($lotBalanceMap[$lotNo] ?? 0.0);
            $rem = (float) ($lotRemainMap[$lotNo] ?? 0.0);
            if (abs($bal) < 0.000001 && abs($rem) < 0.000001) {
                continue;
            }
            $lots[] = [
                'lot_number' => (string) $lotNo,
                'balance_qty' => round($bal, 4),
                'remain_qty' => round($rem, 4),
                'received_qty' => round((float) ($lotReceivedMap[$lotNo] ?? 0.0), 4), // เพดานยอดคงเหลือ (รับเข้าสะสม)
                'consistent' => abs($bal - $rem) < 0.000001,
            ];
        }
        usort($lots, function ($a, $b) {
            return ($a['consistent'] <=> $b['consistent']) ?: strcmp($a['lot_number'], $b['lot_number']);
        });

        return [
            'meta' => [
                'item_code' => (string) $item_code,
                'item_name' => $itemName,
                'unit_name' => $unitName,
                'image_url' => $imageUrl,
                'warehouse_id' => $warehouseId,
                'warehouse_label' => $warehouseLabel,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'qty_bf' => $qtyBF,
                'value_bf' => $valueBF,
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'total_in_value' => $totalInValue,
                'total_out_value' => $totalOutValue,
                'net_qty' => $totalIn - $totalOut,
                'net_value' => $totalInValue - $totalOutValue,
                'current_qty' => $currentBalance,
                'current_value' => $runningValue,
                'tx_count' => count($transactions),
            ],
            'transactions' => $transactions,
            'lots' => $lots,
        ];
    }

    /**
     * Export ประวัติการเคลื่อนไหววัสดุ 1 รายการ ภายในคลัง — Excel
     */
    public function actionExportItemHistory($item_code, $warehouse_id, $start_date = null, $end_date = null)
    {
        $data = $this->getItemHistoryData($item_code, $warehouse_id, $start_date, $end_date);
        $meta = $data['meta'];
        $summary = $data['summary'];
        $transactions = $data['transactions'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ประวัติเคลื่อนไหววัสดุ');

        // Header block
        $sheet->setCellValue('A1', 'ประวัติการเคลื่อนไหววัสดุ');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'รหัสวัสดุ: ' . $meta['item_code'] . '  |  ชื่อ: ' . $meta['item_name'] . '  |  หน่วย: ' . $meta['unit_name']);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setSize(10);

        $sheet->setCellValue('A3', 'คลัง: ' . $meta['warehouse_label'] . '  |  ช่วง: ' . $meta['start_date'] . ' ถึง ' . $meta['end_date']);
        $sheet->mergeCells('A3:I3');
        $sheet->getStyle('A3')->getFont()->setSize(10);

        // Summary row
        $sheet->setCellValue('A5', 'ยอดยกมา (จำนวน)');
        $sheet->setCellValue('B5', $summary['qty_bf']);
        $sheet->setCellValue('C5', 'มูลค่ายกมา');
        $sheet->setCellValue('D5', $summary['value_bf']);
        $sheet->setCellValue('E5', 'รับเข้ารวม');
        $sheet->setCellValue('F5', $summary['total_in']);
        $sheet->setCellValue('G5', 'จ่ายออกรวม');
        $sheet->setCellValue('H5', $summary['total_out']);
        $sheet->setCellValue('I5', 'คงเหลือปัจจุบัน');
        $sheet->setCellValue('J5', $summary['current_qty']);
        $sheet->getStyle('A5:J5')->getFont()->setBold(true);
        $sheet->getStyle('A5:J5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFF3CD');
        $sheet->getStyle('B5')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('D5')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('F5')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H5')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('J5')->getNumberFormat()->setFormatCode('#,##0.00');

        // Headers
        $headers = ['ลำดับ', 'วันที่', 'เวลา', 'เลขที่เอกสาร', 'รายการ', 'ทิศทาง', 'จำนวน', 'ราคา/หน่วย', 'ยอดสะสม', 'มูลค่าสะสม', 'Lot'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '7', $h);
            $col++;
        }
        $lastCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A7:' . $lastCol . '7')->getFont()->setBold(true);
        $sheet->getStyle('A7:' . $lastCol . '7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A7:' . $lastCol . '7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Opening row (BF)
        $rowNum = 8;
        $sheet->setCellValue('A' . $rowNum, '');
        $sheet->setCellValue('B' . $rowNum, $meta['start_date']);
        $sheet->setCellValue('C' . $rowNum, '');
        $sheet->setCellValue('D' . $rowNum, '');
        $sheet->setCellValue('E' . $rowNum, 'ยอดยกมา');
        $sheet->setCellValue('F' . $rowNum, '');
        $sheet->setCellValue('G' . $rowNum, '');
        $sheet->setCellValue('H' . $rowNum, '');
        $sheet->setCellValue('I' . $rowNum, $summary['qty_bf']);
        $sheet->setCellValue('J' . $rowNum, $summary['value_bf']);
        $sheet->setCellValue('K' . $rowNum, '');
        $sheet->getStyle('A' . $rowNum . ':K' . $rowNum)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F8F9FA');
        $sheet->getStyle('A' . $rowNum . ':K' . $rowNum)->getFont()->setItalic(true);
        $rowNum++;

        foreach ($transactions as $i => $t) {
            $direction = $t['direction'] === 'in' ? 'รับเข้า' : 'จ่ายออก';
            $signedQty = $t['direction'] === 'in' ? $t['qty'] : -$t['qty'];
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $t['date']);
            $sheet->setCellValue('C' . $rowNum, $t['time']);
            $sheet->setCellValue('D' . $rowNum, $t['order_no']);
            $sheet->setCellValue('E' . $rowNum, $t['source_label']);
            $sheet->setCellValue('F' . $rowNum, $direction);
            $sheet->setCellValue('G' . $rowNum, $signedQty);
            $sheet->setCellValue('H' . $rowNum, $t['unit_price']);
            $sheet->setCellValue('I' . $rowNum, $t['balance_qty']);
            $sheet->setCellValue('J' . $rowNum, $t['balance_value']);
            $sheet->setCellValue('K' . $rowNum, $t['lot']);
            $rowNum++;
        }

        // Number format for numeric columns
        $sheet->getStyle('G8:J' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        // Auto-size columns
        foreach (range('A', 'K') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        $sheet->getStyle('A7:' . $lastCol . ($rowNum - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Output
        $filename = 'item-history-' . preg_replace('/[^A-Za-z0-9_\-]/', '', $meta['item_code']) . '-w' . (int) $warehouse_id . '-' . date('Ymd-His') . '.xlsx';
        $tempPath = Yii::getAlias('@runtime') . '/item_history_' . uniqid('', true) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        Yii::$app->response->sendFile($tempPath, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false,
        ])->on(Response::EVENT_AFTER_SEND, function ($event) use ($tempPath) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        });
    }

    /**
     * Legacy export route — redirect ไปยัง main/sub ตามสิทธิ์
     */
    public function actionExportBalanceByWarehouse()
    {
        $route = self::resolveBalanceRoute() === '/inventory-v2/main-stock/balance'
            ? '/inventory-v2/main-stock/export-balance'
            : '/inventory-v2/sub-stock/export-balance';
        $params = $this->request->getQueryParams();
        return $this->redirect(array_merge([$route], $params));
    }

    /**
     * Stream Excel ของรายการยอดคงเหลือ (ใช้ร่วมกันระหว่าง main/sub)
     * @param array $rows ผลลัพธ์จาก buildBalanceContext()['rows']
     * @param string $filenamePrefix prefix ของชื่อไฟล์
     */
    public static function streamBalanceXlsx(array $rows, string $filenamePrefix = 'balance-by-warehouse'): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ยอดคงเหลือตามคลัง');

        $sheet->setCellValue('A1', 'รายการวัสดุคงเหลือตามคลัง');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $headers = ['ลำดับ', 'คลัง', 'รหัส', 'ชื่อวัสดุ', 'ประเภท', 'หน่วย', 'จำนวนคงเหลือ', 'มูลค่า (บาท)', 'Min', 'Max', 'สถานะ'];
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

        $rowNum = 4;
        foreach ($rows as $i => $r) {
            $status = $r['below_min'] ? 'ต่ำกว่า Min' : ($r['below_max'] ? 'ต่ำกว่า Max' : 'พอดี');
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $r['warehouse_name']);
            $sheet->setCellValue('C' . $rowNum, $r['item_code']);
            $sheet->setCellValue('D' . $rowNum, $r['item_name']);
            $sheet->setCellValue('E' . $rowNum, $r['category_title']);
            $sheet->setCellValue('F' . $rowNum, $r['unit_name']);
            $sheet->setCellValue('G' . $rowNum, $r['balance_qty']);
            $sheet->setCellValue('H' . $rowNum, $r['value']);
            $sheet->setCellValue('I' . $rowNum, $r['min_qty'] !== null ? $r['min_qty'] : '-');
            $sheet->setCellValue('J' . $rowNum, $r['max_qty'] !== null ? $r['max_qty'] : '-');
            $sheet->setCellValue('K' . $rowNum, $status);
            $rowNum++;
        }

        $sheet->getStyle('G4:H' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        if (!empty($rows)) {
            $sheet->getStyle('I4:J' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $filename = $filenamePrefix . '-' . date('Ymd-His') . '.xlsx';
        $tempPath = Yii::getAlias('@runtime') . '/balance_' . uniqid('', true) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        Yii::$app->response->sendFile($tempPath, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false,
        ])->on(Response::EVENT_AFTER_SEND, function () use ($tempPath) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        });
    }

    /**
     * รายงานวัสดุไม่พอจ่าย: จากยอดที่ขอเบิก (ใบ PENDING + APPROVED) เทียบยอดคงเหลือในคลังหลัก
     * แสดงแต่ละรายการ ต้องซื้อเพิ่มเท่าไหร่ (shortfall) — รวมรายการที่ยังไม่เคยรับเข้าคลัง (ยอดคงเหลือ 0) เพื่อออกใบสั่งซื้อ
     * Filter: ประเภทวัสดุ, คลังย่อยที่ขอเบิก, คลังหลักที่รอจ่าย
     */
    public function actionInsufficientToDisburse()
    {
        $mainWarehouseId = $this->request->get('main_warehouse_id') !== null && $this->request->get('main_warehouse_id') !== ''
            ? (int) $this->request->get('main_warehouse_id') : null;
        $subWarehouseId = $this->request->get('sub_warehouse_id') !== null && $this->request->get('sub_warehouse_id') !== ''
            ? (int) $this->request->get('sub_warehouse_id') : null;
        $categoryId = $this->request->get('category_id') !== null && $this->request->get('category_id') !== ''
            ? (string) $this->request->get('category_id') : null;

        $data = $this->getInsufficientToDisburseRows($mainWarehouseId, $subWarehouseId,$categoryId);
        $rows = $data['rows'];
        $listMain = $data['listMain'];
        $listSub = $data['listSub'];

        $categories = ['' => '-- ทุกประเภท --'] + \yii\helpers\ArrayHelper::map(
            Categorise::find()->where(['name' => 'asset_type', 'group_id' => 'MATER'])->orderBy('title')->all(),
            'code',
            'title'
        );
        $mainWarehouses = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map($listMain, 'id', 'warehouse_name');
        $subWarehouses = ['' => '-- ทุกคลังย่อยที่ขอเบิก --'] + \yii\helpers\ArrayHelper::map($listSub, 'id', 'warehouse_name');

        $this->view->params['active'] = 'report-balance';
        return $this->render('insufficient-to-disburse', [
            'rows' => $rows,
            'mainWarehouseId' => $mainWarehouseId,
            'subWarehouseId' => $subWarehouseId,
            'categoryId' => $categoryId,
            'mainWarehouses' => $mainWarehouses,
            'subWarehouses' => $subWarehouses,
            'categories' => $categories,
        ]);
    }

    /**
     * Export รายงานวัสดุไม่พอจ่ายเป็น Excel (ใช้ filter เดียวกับหน้ารายงาน)
     */
    public function actionExportInsufficientToDisburse()
    {
        $mainWarehouseId = $this->request->get('main_warehouse_id') !== null && $this->request->get('main_warehouse_id') !== ''
            ? (int) $this->request->get('main_warehouse_id') : null;
        $subWarehouseId = $this->request->get('sub_warehouse_id') !== null && $this->request->get('sub_warehouse_id') !== ''
            ? (int) $this->request->get('sub_warehouse_id') : null;

        $data = $this->getInsufficientToDisburseRows($mainWarehouseId, $subWarehouseId);
        $rows = $data['rows'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('วัสดุไม่พอจ่าย');

        $sheet->setCellValue('A1', 'รายงานวัสดุไม่พอจ่าย');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'จากรายการที่ขอเบิก (ใบรออนุมัติ + อนุมัติแล้ว) เทียบยอดคงเหลือในคลังหลัก — ต้องซื้อเพิ่ม');
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setSize(10);

        $headers = ['ลำดับ', 'คลังหลักที่รอจ่าย', 'รหัส', 'ชื่อวัสดุ', 'ประเภท', 'หน่วย', 'จำนวนที่ขอเบิก', 'ยอดคงเหลือในคลัง', 'ต้องซื้อเพิ่ม'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $col++;
        }
        $sheet->getStyle('A4:I4')->getFont()->setBold(true);
        $sheet->getStyle('A4:I4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFF3CD');

        $rowNum = 5;
        foreach ($rows as $i => $r) {
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $r['main_warehouse_name']);
            $sheet->setCellValue('C' . $rowNum, $r['item_code']);
            $sheet->setCellValue('D' . $rowNum, $r['item_name']);
            $sheet->setCellValue('E' . $rowNum, $r['category_title']);
            $sheet->setCellValue('F' . $rowNum, $r['unit_name']);
            $sheet->setCellValue('G' . $rowNum, $r['requested_qty']);
            $sheet->setCellValue('H' . $rowNum, $r['balance_qty']);
            $sheet->setCellValue('I' . $rowNum, $r['shortfall']);
            $rowNum++;
        }

        $sheet->getStyle('G5:I' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        $filename = 'insufficient-to-disburse-' . date('Ymd-His') . '.xlsx';
        $tempPath = Yii::getAlias('@runtime') . '/insufficient_' . uniqid('', true) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        Yii::$app->response->sendFile($tempPath, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false,
        ])->on(Response::EVENT_AFTER_SEND, function ($event) use ($tempPath) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        });
    }

    /**
     * ดึงข้อมูลรายงานวัสดุไม่พอจ่าย (ใช้ทั้งหน้าแสดงและ export Excel)
     * @return array { rows: array, listMain: Warehouse[] }
     */
    // --- เพิ่ม $categoryId = null ใน signature ---
    protected function getInsufficientToDisburseRows($mainWarehouseId, $subWarehouseId, $categoryId = null)
    {
        $listMain = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $listSub = Warehouse::find()
            ->where(['warehouse_type' => 'SUB'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $mainWarehouseIds = array_column($listMain, 'id');
        if (empty($mainWarehouseIds)) {
            $mainWarehouseIds = [-1];
        }

        $warehouseIds = $mainWarehouseId ? [$mainWarehouseId] : $mainWarehouseIds;

        $reqSub = (new Query())
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where([
                'so.order_type' => 'OUT',
                'so.source_type' => 'REQUEST',
                'so.status' => [StockOrder::STATUS_PENDING, StockOrder::STATUS_APPROVED],
            ])
            ->andWhere(['so.main_warehouse_id' => $warehouseIds]);
        if ($subWarehouseId !== null) {
            $reqSub->andWhere(['so.sub_warehouse_id' => $subWarehouseId]);
        }
        if ($mainWarehouseId) {
            $reqSub->select(['so.main_warehouse_id', 'sd.item_code', 'SUM(sd.qty) AS requested_qty'])->groupBy(['so.main_warehouse_id', 'sd.item_code']);
        } else {
            $reqSub->select(['sd.item_code', 'SUM(sd.qty) AS requested_qty'])->groupBy('sd.item_code');
        }

        $balSub = (new Query())
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $warehouseIds]);
        if ($mainWarehouseId) {
            $balSub->select(['warehouse_id AS main_warehouse_id', 'item_code', 'SUM(balance_qty) AS balance_qty'])->groupBy(['warehouse_id', 'item_code']);
        } else {
            $balSub->select(['item_code', 'SUM(balance_qty) AS balance_qty'])->groupBy('item_code');
        }

        if ($mainWarehouseId) {
            $query = (new Query())
                ->select([
                    'req.item_code',
                    'req.main_warehouse_id',
                    'req.requested_qty',
                    'balance_qty' => new Expression('COALESCE(bal.balance_qty, 0)'),
                    'shortfall' => new Expression('req.requested_qty - COALESCE(bal.balance_qty, 0)'),
                    'item_name' => new Expression("COALESCE(i.title, req.item_code)"),
                    'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                ])
                ->from(['req' => $reqSub])
                ->leftJoin(['bal' => $balSub], 'bal.main_warehouse_id = req.main_warehouse_id AND bal.item_code = req.item_code')
                ->leftJoin(['i' => StockItem::tableName()], 'i.code = req.item_code')
                ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
                ->andWhere(new Expression('req.requested_qty > COALESCE(bal.balance_qty, 0)'));
        } else {
            $query = (new Query())
                ->select([
                    'req.item_code',
                    'main_warehouse_id' => new Expression('NULL'),
                    'req.requested_qty',
                    'balance_qty' => new Expression('COALESCE(bal.balance_qty, 0)'),
                    'shortfall' => new Expression('req.requested_qty - COALESCE(bal.balance_qty, 0)'),
                    'item_name' => new Expression("COALESCE(i.title, req.item_code)"),
                    'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
                ])
                ->from(['req' => $reqSub])
                ->leftJoin(['bal' => $balSub], 'bal.item_code = req.item_code')
                ->leftJoin(['i' => StockItem::tableName()], 'i.code = req.item_code')
                ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
                ->andWhere(new Expression('req.requested_qty > COALESCE(bal.balance_qty, 0)'));
        }

        // --- เพิ่มเงื่อนไขค้นหาด้วย $categoryId ---
        // ใช้ andFilterWhere() เพื่อให้ถ้าค่า $categoryId ว่างเปล่า หรือ null ระบบจะไม่นำไปต่อท้ายเงื่อนไข WHERE
        $query->andFilterWhere(['i.category_id' => $categoryId]);

        $query->orderBy(['shortfall' => SORT_DESC]);
        $rows = $query->all();

        $mainNames = [];
        foreach ($listMain as $w) {
            $mainNames[$w->id] = $w->warehouse_name;
        }
        foreach ($rows as &$r) {
            $r['main_warehouse_name'] = isset($r['main_warehouse_id']) && $r['main_warehouse_id'] !== null
                ? ($mainNames[$r['main_warehouse_id']] ?? (string) $r['main_warehouse_id'])
                : 'ทุกคลังหลัก';
            $r['balance_qty'] = (float) ($r['balance_qty'] ?? 0);
            $r['requested_qty'] = (float) $r['requested_qty'];
            $r['shortfall'] = (float) $r['shortfall'];
            $item = StockItem::findOne($r['item_code']);
            $r['unit_name'] = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : '-';
        }
        unset($r);

        return ['rows' => $rows, 'listMain' => $listMain, 'listSub' => $listSub];
    }

    /**
     * รวมยอดจาก stock_monthly_report ตามประเภทวัสดุ (category)
     */
    protected function aggregateByCategory($year, $month, $warehouseId = null)
    {
        $query = (new Query())
            ->select([
                new Expression("COALESCE(cat.code, i.category_id, 'OTHER') AS category_code"),
                new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ') AS category_title"),
                'SUM(r.opening_qty) AS opening_qty',
                'SUM(r.opening_value) AS opening_value',
                'SUM(r.in_qty) AS in_qty',
                'SUM(r.in_value) AS in_value',
                'SUM(r.adjust_in_qty) AS adjust_in_qty',
                'SUM(r.adjust_in_value) AS adjust_in_value',
                'SUM(r.adjust_out_qty) AS adjust_out_qty',
                'SUM(r.adjust_out_value) AS adjust_out_value',
                'SUM(r.out_sub_qty) AS out_sub_qty',
                'SUM(r.out_sub_value) AS out_sub_value',
                'SUM(r.out_hosp_qty) AS out_hosp_qty',
                'SUM(r.out_hosp_value) AS out_hosp_value',
                'SUM(r.total_out_qty) AS total_out_qty',
                'SUM(r.total_out_value) AS total_out_value',
                'SUM(r.closing_qty) AS closing_qty',
                'SUM(r.closing_value) AS closing_value',
            ])
            ->from(['r' => StockMonthlyReport::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = r.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where([
                'r.report_year' => $year,
                'r.report_month' => $month,
            ])
            ->groupBy(new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"));

        if ($warehouseId !== null && $warehouseId !== '') {
            $query->andWhere(['r.warehouse_id' => $warehouseId]);
        }

        $raw = $query->all();
        $categories = Categorise::find()
            ->where(['name' => 'asset_type', 'category_id' => 4])
            ->orderBy(['code' => SORT_ASC])
            ->indexBy('code')
            ->all();

        $out = [];
        foreach ($raw as $r) {
            $code = $r['category_code'] ?? 'OTHER';
            $title = isset($categories[$code])
                ? '(' . $categories[$code]->code . ')' . $categories[$code]->title
                : '(' . $code . ') ' . ($r['category_title'] ?? '');
            $out[] = [
                'category_code' => $code,
                'category_label' => $title,
                'opening_qty' => (float) $r['opening_qty'],
                'opening_value' => (float) $r['opening_value'],
                'in_qty' => (float) $r['in_qty'],
                'in_value' => (float) $r['in_value'],
                'adjust_in_qty' => (float) ($r['adjust_in_qty'] ?? 0),
                'adjust_in_value' => (float) ($r['adjust_in_value'] ?? 0),
                'adjust_out_qty' => (float) ($r['adjust_out_qty'] ?? 0),
                'adjust_out_value' => (float) ($r['adjust_out_value'] ?? 0),
                'out_sub_qty' => (float) $r['out_sub_qty'],
                'out_sub_value' => (float) $r['out_sub_value'],
                'out_hosp_qty' => (float) $r['out_hosp_qty'],
                'out_hosp_value' => (float) $r['out_hosp_value'],
                'total_out_qty' => (float) $r['total_out_qty'],
                'total_out_value' => (float) $r['total_out_value'],
                'closing_qty' => (float) $r['closing_qty'],
                'closing_value' => (float) $r['closing_value'],
            ];
        }
        usort($out, function ($a, $b) {
            return strcmp($a['category_code'], $b['category_code']);
        });
        return $out;
    }

    /**
     * ปิดเดือน: คำนวณและบันทึกลง stock_monthly_report
     * ส่ง warehouse_id เป็นตัวเลข = ปิดเฉพาะคลังนั้น, ส่ง "all" หรือไม่ส่ง = ปิดรวมทุกคลังหลัก
     */
    public function actionCloseMonth()
    {
        $this->response->format = Response::FORMAT_JSON;
        $year = (int) $this->request->post('year', date('Y'));
        $month = (int) $this->request->post('month', (int) date('n'));
        $warehouseIdParam = $this->request->post('warehouse_id');

        $warehouseIds = [];
        if ($warehouseIdParam === 'all' || $warehouseIdParam === '' || $warehouseIdParam === null) {
            $warehouseIds = Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->select('id')
                ->column();
        } else {
            $wid = (int) $warehouseIdParam;
            if ($wid <= 0) {
                return ['success' => false, 'message' => 'กรุณาเลือกคลังหรือเลือกปิดรวมทุกคลัง'];
            }
            $warehouseIds = [$wid];
        }

        if (empty($warehouseIds)) {
            return ['success' => false, 'message' => 'ไม่พบคลังหลักในระบบ'];
        }

        $totalCount = 0;
        foreach ($warehouseIds as $warehouseId) {
            // ถ้ายังไม่เคยปิดงวดก่อนหน้า → ยอดยกมาคำนวณสะสมจากต้น, บันทึกเฉพาะงวดนี้
            $result = self::closeMonthFromStart((int) $warehouseId, $year, $month);
            $totalCount += $result['count'];
        }

        return [
            'success' => true,
            'message' => 'ปิดเดือนเรียบร้อย',
            'count' => $totalCount,
            'warehouses_count' => count($warehouseIds),
        ];
    }

    /**
     * แปลง warehouse_id param → รายการ id คลังหลักที่จะประมวลผล (ใช้ร่วม close / preview)
     * @return array{ids: int[], error: ?string}
     */
    protected function resolveCloseWarehouseIds($warehouseIdParam): array
    {
        if ($warehouseIdParam === 'all' || $warehouseIdParam === '' || $warehouseIdParam === null) {
            $ids = Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->select('id')
                ->column();
            if (empty($ids)) {
                return ['ids' => [], 'error' => 'ไม่พบคลังหลักในระบบ'];
            }
            return ['ids' => array_map('intval', $ids), 'error' => null];
        }
        $wid = (int) $warehouseIdParam;
        if ($wid <= 0) {
            return ['ids' => [], 'error' => 'กรุณาเลือกคลังหรือเลือกปิดรวมทุกคลัง'];
        }
        return ['ids' => [$wid], 'error' => null];
    }

    /**
     * ตัวอย่างก่อนปิดเดือน: คำนวณยอด (ไม่บันทึก) แล้วคืน summary + ตารางตามประเภท + รายการที่ต้องตรวจสอบ
     * ให้เจ้าหน้าที่บัญชียืนยันความถูกต้องก่อนกดปิดเดือนจริง
     */
    public function actionCloseMonthPreview()
    {
        $this->response->format = Response::FORMAT_JSON;
        $year = (int) $this->request->post('year', date('Y'));
        $month = (int) $this->request->post('month', (int) date('n'));
        $warehouseIdParam = $this->request->post('warehouse_id');

        $resolved = $this->resolveCloseWarehouseIds($warehouseIdParam);
        if ($resolved['error'] !== null) {
            return ['success' => false, 'message' => $resolved['error']];
        }
        $warehouseIds = $resolved['ids'];

        // คำนวณ row ต่อ item ต่อคลัง (แหล่งเดียวกับที่ปิดเดือนจริงจะบันทึก)
        // opening มาจาก chain (buildOpeningForMonth) เพื่อให้ preview = ผลของ auto-chain ตอนกดปิดจริง
        // — ถ้ามีงวดก่อนยังไม่ปิด จะคำนวณยอดยกมาต่อเนื่องมาให้ ไม่ใช่ 0
        $allRows = [];
        $chainedMonths = 0;
        foreach ($warehouseIds as $wid) {
            // นับงวดก่อนหน้าที่ยังไม่ปิด (จะถูก auto-chain ตอนกดปิดจริง) เพื่อเตือนผู้ใช้
            [$py, $pm] = [$year, $month - 1];
            if ($pm < 1) { $pm += 12; $py--; }
            [$sy, $sm] = self::firstStockOrderMonth($wid);
            if ($sy !== null) {
                $curOrd = $py * 12 + $pm;
                $ty = $sy; $tm = $sm;
                while (($ty * 12 + $tm) <= $curOrd) {
                    if (empty(self::snapshotClosingMap($wid, $ty, $tm))) {
                        $chainedMonths++;
                    }
                    $tm++;
                    if ($tm > 12) { $tm = 1; $ty++; }
                }
            }
            $opening = self::buildOpeningForMonth($wid, $year, $month);
            foreach (self::computeMonthlyRows($wid, $year, $month, $opening) as $row) {
                $allRows[] = $row;
            }
        }

        $catRows = $this->aggregateRowsByCategory($allRows);

        // ── สรุปยอดรวม ──
        $sumOpening = $sumIn = $sumOutSub = $sumOutHosp = $sumOut = $sumClosing = 0.0;
        foreach ($catRows as $c) {
            $sumOpening += $c['opening_value'];
            $sumIn += $c['in_value'];
            $sumOutSub += $c['out_sub_value'];
            $sumOutHosp += $c['out_hosp_value'];
            $sumOut += $c['total_out_value'];
            $sumClosing += $c['closing_value'];
        }

        // ── รายการที่ต้องตรวจสอบ ──
        // (1) ยอดยกไปติดลบ — เก็บราย (item, คลัง) เพื่อคลิกดูประวัติได้ตรงคลัง
        // (2) จ่ายออกแต่ราคาทุน = 0 (มูลค่าอาจต่ำกว่าจริง) — รวมราย item
        $whNameMap = [];
        foreach (Warehouse::find()->select(['id', 'warehouse_name'])->where(['id' => $warehouseIds])->asArray()->all() as $w) {
            $whNameMap[(int) $w['id']] = (string) $w['warehouse_name'];
        }

        $negativeRows = [];
        $zeroCostCodes = [];
        $outQtyByItem = [];
        foreach ($allRows as $r) {
            $code = (string) $r['item_code'];
            if ((float) $r['closing_value'] < -0.005) {
                $negativeRows[] = [
                    'item_code' => $code,
                    'warehouse_id' => (int) $r['warehouse_id'],
                    'value' => round((float) $r['closing_value'], 2),
                ];
            }
            if ((float) $r['total_out_qty'] > 0.005 && abs((float) $r['total_out_value']) < 0.005) {
                $zeroCostCodes[$code] = true;
            }
            $outQtyByItem[$code] = ($outQtyByItem[$code] ?? 0) + (float) $r['total_out_qty'];
        }

        $warnCodes = array_values(array_unique(array_merge(
            array_map(fn($n) => $n['item_code'], $negativeRows),
            array_keys($zeroCostCodes)
        )));
        $itemTitles = [];
        if (!empty($warnCodes)) {
            foreach (StockItem::find()->select(['code', 'title'])->where(['code' => $warnCodes])->asArray()->all() as $it) {
                $itemTitles[(string) $it['code']] = (string) ($it['title'] ?? '');
            }
        }

        $multiWarehouse = count($warehouseIds) > 1;
        $negatives = array_map(function ($n) use ($itemTitles, $whNameMap, $multiWarehouse) {
            return [
                'item_code' => $n['item_code'],
                'item_name' => $itemTitles[$n['item_code']] ?? $n['item_code'],
                'warehouse_id' => $n['warehouse_id'],
                'warehouse_name' => $multiWarehouse ? ($whNameMap[$n['warehouse_id']] ?? '') : '',
                'value' => $n['value'],
            ];
        }, $negativeRows);
        $zeroCost = [];
        foreach (array_keys($zeroCostCodes) as $code) {
            $zeroCost[] = [
                'item_code' => $code,
                'item_name' => $itemTitles[$code] ?? $code,
                'qty' => round($outQtyByItem[$code] ?? 0, 2),
            ];
        }
        usort($negatives, fn($a, $b) => $a['value'] <=> $b['value']); // ติดลบมากสุดก่อน
        usort($zeroCost, fn($a, $b) => strcmp($a['item_code'], $b['item_code']));

        // ── งวดนี้เคยปิดไปแล้วหรือยัง (กดยืนยันจะเขียนทับ) ──
        $existing = (new Query())
            ->from(StockMonthlyReport::tableName())
            ->where(['report_year' => $year, 'report_month' => $month]);
        if (count($warehouseIds) === 1) {
            $existing->andWhere(['warehouse_id' => $warehouseIds[0]]);
        }
        $alreadyClosed = $existing->exists();

        $monthNames = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];
        $periodLabel = ($monthNames[$month] ?? '') . ' ' . ($year + 543);
        if (count($warehouseIds) === 1) {
            $w = Warehouse::findOne($warehouseIds[0]);
            $warehouseLabel = $w ? $w->warehouse_name : (string) $warehouseIds[0];
        } else {
            $warehouseLabel = 'ทุกคลังหลัก (' . count($warehouseIds) . ' คลัง)';
        }

        return [
            'success' => true,
            'meta' => [
                'period_label' => $periodLabel,
                'warehouse_label' => $warehouseLabel,
                'warehouses_count' => count($warehouseIds),
                'already_closed' => $alreadyClosed,
                'chained_months' => $chainedMonths,
            ],
            'summary' => [
                'row_count' => count($allRows),
                'item_count' => count(array_unique(array_map(fn($r) => (string) $r['item_code'], $allRows))),
                'opening_value' => round($sumOpening, 2),
                'in_value' => round($sumIn, 2),
                'out_sub_value' => round($sumOutSub, 2),
                'out_hosp_value' => round($sumOutHosp, 2),
                'total_out_value' => round($sumOut, 2),
                'closing_value' => round($sumClosing, 2),
            ],
            'rows' => $catRows,
            'warnings' => [
                'negatives' => $negatives,
                'zero_cost' => $zeroCost,
            ],
        ];
    }

    /**
     * ยกเลิกการปิดเดือน: ลบ row ใน stock_monthly_report ของงวด+คลังที่เลือก
     * ทำเป็น 2 เฟส — เรียกครั้งแรก (ไม่มี confirmed) คืนจำนวนแถวที่จะลบ + งวดถัดไปที่ปิดแล้ว (ยอดยกมาผูกกัน)
     * ให้ยืนยันก่อน แล้วเรียกซ้ำพร้อม confirmed=1 จึงลบจริง (destructive irreversible)
     */
    public function actionCancelClose()
    {
        $this->response->format = Response::FORMAT_JSON;
        $year = (int) $this->request->post('year', date('Y'));
        $month = (int) $this->request->post('month', (int) date('n'));
        $warehouseIdParam = $this->request->post('warehouse_id');
        $confirmed = (string) $this->request->post('confirmed', '') === '1';

        $resolved = $this->resolveCloseWarehouseIds($warehouseIdParam);
        if ($resolved['error'] !== null) {
            return ['success' => false, 'message' => $resolved['error']];
        }
        $warehouseIds = $resolved['ids'];

        $filter = ['report_year' => $year, 'report_month' => $month, 'warehouse_id' => $warehouseIds];
        $rowCount = (int) (new Query())->from(StockMonthlyReport::tableName())->where($filter)->count();

        if ($rowCount === 0) {
            return ['success' => false, 'message' => 'งวดนี้ยังไม่มีข้อมูลปิดเดือน ไม่มีอะไรให้ยกเลิก'];
        }

        $monthNames = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];

        // งวดถัดไปที่ปิดแล้ว (report_year*12+report_month > งวดนี้) — ยอดยกมาผูกกับยอดยกไปของงวดนี้
        $thisOrd = $year * 12 + $month;
        $laterRows = (new Query())
            ->select(['report_year', 'report_month'])
            ->distinct()
            ->from(StockMonthlyReport::tableName())
            ->where(['warehouse_id' => $warehouseIds])
            ->andWhere(new Expression('report_year * 12 + report_month > :ord', [':ord' => $thisOrd]))
            ->orderBy(['report_year' => SORT_ASC, 'report_month' => SORT_ASC])
            ->all();
        $laterClosed = array_map(function ($r) use ($monthNames) {
            $y = (int) $r['report_year'];
            $mo = (int) $r['report_month'];
            return ['year' => $y, 'month' => $mo, 'label' => ($monthNames[$mo] ?? '') . ' ' . ($y + 543)];
        }, $laterRows);

        $periodLabel = ($monthNames[$month] ?? '') . ' ' . ($year + 543);
        if (count($warehouseIds) === 1) {
            $w = Warehouse::findOne($warehouseIds[0]);
            $warehouseLabel = $w ? $w->warehouse_name : (string) $warehouseIds[0];
        } else {
            $warehouseLabel = 'ทุกคลังหลัก (' . count($warehouseIds) . ' คลัง)';
        }

        // เฟส 1 — คืนข้อมูลให้ยืนยัน ยังไม่ลบ
        if (!$confirmed) {
            return [
                'success' => true,
                'confirmed' => false,
                'row_count' => $rowCount,
                'period_label' => $periodLabel,
                'warehouse_label' => $warehouseLabel,
                'warehouses_count' => count($warehouseIds),
                'later_closed' => $laterClosed,
            ];
        }

        // เฟส 2 — ลบจริง
        $deleted = (int) StockMonthlyReport::deleteAll($filter);
        return [
            'success' => true,
            'confirmed' => true,
            'deleted' => $deleted,
            'later_closed' => $laterClosed,
        ];
    }

    /**
     * รวมยอด row ต่อ item (in-memory จาก computeMonthlyRows) ตามประเภทวัสดุ
     * ใช้ label/order รูปแบบเดียวกับ aggregateByCategory เพื่อให้ preview = รายงานจริง
     * @param array<int, array<string, mixed>> $rows
     */
    protected function aggregateRowsByCategory(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }
        $itemCodes = array_values(array_unique(array_map(fn($r) => (string) $r['item_code'], $rows)));

        $catMap = (new Query())
            ->select([
                'code' => 'i.code',
                'category_code' => new Expression("COALESCE(cat.code, i.category_id, 'OTHER')"),
                'category_title' => new Expression("COALESCE(cat.title, i.category_id, 'อื่นๆ')"),
            ])
            ->from(['i' => StockItem::tableName()])
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where(['i.code' => $itemCodes])
            ->indexBy('code')
            ->all();

        $categories = Categorise::find()
            ->where(['name' => 'asset_type', 'category_id' => 4])
            ->orderBy(['code' => SORT_ASC])
            ->indexBy('code')
            ->all();

        $fields = [
            'opening_qty', 'opening_value', 'in_qty', 'in_value',
            'adjust_in_qty', 'adjust_in_value', 'adjust_out_qty', 'adjust_out_value',
            'out_sub_qty', 'out_sub_value', 'out_hosp_qty', 'out_hosp_value',
            'total_out_qty', 'total_out_value', 'closing_qty', 'closing_value',
        ];

        $agg = [];
        foreach ($rows as $r) {
            $code = (string) $r['item_code'];
            $catCode = $catMap[$code]['category_code'] ?? 'OTHER';
            $catTitle = $catMap[$code]['category_title'] ?? 'อื่นๆ';
            if (!isset($agg[$catCode])) {
                $label = isset($categories[$catCode])
                    ? '(' . $categories[$catCode]->code . ')' . $categories[$catCode]->title
                    : '(' . $catCode . ') ' . $catTitle;
                $agg[$catCode] = ['category_code' => $catCode, 'category_label' => $label];
                foreach ($fields as $f) {
                    $agg[$catCode][$f] = 0.0;
                }
            }
            foreach ($fields as $f) {
                $agg[$catCode][$f] += (float) ($r[$f] ?? 0);
            }
        }

        $out = array_values($agg);
        usort($out, fn($a, $b) => strcmp($a['category_code'], $b['category_code']));
        return $out;
    }

    /**
     * คำนวณยอดรายเดือนต่อ item สำหรับคลังเดียว โดย "ไม่บันทึก" ลง DB
     * ใช้ร่วมกันระหว่าง preview (actionCloseMonthPreview) และการปิดเดือนจริง (closeMonthForWarehouse)
     * เพื่อรับประกันว่า "ตัวอย่างที่เห็น = ข้อมูลที่จะบันทึก" (WYSIWYG)
     *
     * @return array<int, array<string, mixed>> แต่ละ element = 1 row ของ stock_monthly_report (ยังไม่มี created_at/by)
     */
    public static function computeMonthlyRows($warehouseId, $year, $month, ?array $openingOverride = null)
    {
        $subIds = self::getDisburseSubWarehouseIds();
        $dateStart = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $lastDay = (int) date('t', strtotime($dateStart));
        $dateEnd = sprintf('%04d-%02d-%02d 23:59:59', $year, $month, $lastDay);

        // ยอดยกมา = ยอดยกไปของงวดก่อน
        // - $openingOverride != null → ใช้ opening ที่ chain คำนวณมาให้ (in-memory) — ใช้ตอน preview / auto-chain
        //   รูปแบบ: [item_code => ['closing_qty' => float, 'closing_value' => float]]
        // - null → อ่านจาก snapshot งวดก่อนใน stock_monthly_report (พฤติกรรมเดิม, ใช้โดย backfill ที่ปิดเรียงอยู่แล้ว)
        if ($openingOverride !== null) {
            $prevClosing = $openingOverride;
        } else {
            $prevMonth = $month - 1;
            $prevYear = $year;
            if ($prevMonth < 1) {
                $prevMonth += 12;
                $prevYear--;
            }
            $prevClosing = (new Query())
                ->select(['item_code', 'closing_qty', 'closing_value'])
                ->from(StockMonthlyReport::tableName())
                ->where([
                    'report_year' => $prevYear,
                    'report_month' => $prevMonth,
                    'warehouse_id' => $warehouseId,
                ])
                ->indexBy('item_code')
                ->all();
        }

        $itemCodes = array_keys($prevClosing);

        $inRows = (new Query())
            ->select([
                'sd.item_code',
                'SUM(sd.qty) AS in_qty',
                'SUM(sd.qty * COALESCE(sd.unit_price, 0)) AS in_value',
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.order_type' => StockOrder::ORDER_TYPE_IN])
            ->andWhere(['so.main_warehouse_id' => $warehouseId])
            ->andWhere(['between', 'so.order_date', $dateStart, $dateEnd])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->groupBy('sd.item_code')
            ->all();
        foreach ($inRows as $row) {
            $itemCodes[] = $row['item_code'];
        }
        $itemCodes = array_unique($itemCodes);

        // ราคาทุนต่อหน่วยต่อ lot — ใช้ unit_price จาก IN detail ล่าสุดของ (item_code, lot_number) ในคลังหลักนี้
        // เพื่อให้ "มูลค่าจ่ายออก" คำนวณตาม lot ที่จ่ายจริง (ไม่ขึ้นกับ sd.unit_price ของ OUT row)
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
                    ->andWhere(['so_l.main_warehouse_id' => $warehouseId])
                    ->groupBy(['sd_l.item_code', 'sd_l.lot_number'])],
                'latest.item_code = sd_in.item_code AND latest.lot_number = sd_in.lot_number AND latest.mid = sd_in.id'
            );

        $outRows = (new Query())
            ->select([
                'item_code' => 'sd.item_code',
                'sub_warehouse_id' => 'so.sub_warehouse_id',
                'qty' => 'sd.qty',
                'lot_number' => 'sd.lot_number',
                'in_unit_price' => new Expression('COALESCE(in_lot.unit_price, sd.unit_price, 0)'),
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->leftJoin(['in_lot' => $latestInPrice], 'in_lot.item_code = sd.item_code AND in_lot.lot_number = sd.lot_number')
            ->where(['so.order_type' => StockOrder::ORDER_TYPE_OUT])
            ->andWhere(['so.main_warehouse_id' => $warehouseId])
            ->andWhere(['between', 'so.order_date', $dateStart, $dateEnd])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->all();

        $outSub = [];
        $outHosp = [];
        foreach ($outRows as $row) {
            $code = $row['item_code'];
            $q = (float) $row['qty'];
            $v = $q * (float) ($row['in_unit_price'] ?? 0);
            if (!isset($outSub[$code])) {
                $outSub[$code] = ['qty' => 0, 'value' => 0];
            }
            if (!isset($outHosp[$code])) {
                $outHosp[$code] = ['qty' => 0, 'value' => 0];
            }
            $isSub = in_array((int) $row['sub_warehouse_id'], $subIds, true);
            if ($isSub) {
                $outSub[$code]['qty'] += $q;
                $outSub[$code]['value'] += $v;
            } else {
                $outHosp[$code]['qty'] += $q;
                $outHosp[$code]['value'] += $v;
            }
            $itemCodes[] = $code;
        }
        $itemCodes = array_unique($itemCodes);

        // ADJUST: ราคาทุนใช้ IN ล่าสุดของ item (cross-lot) เพราะ lot_number ของ ADJUST = 'ADJUST'
        // ไม่ตรงกับ lot จริง — fallback ระดับ item เพื่อให้ได้ราคาประมาณการที่สมเหตุสมผล
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
                    ->andWhere(['so_l.main_warehouse_id' => $warehouseId])
                    ->groupBy(['sd_l.item_code'])],
                'latest_item.item_code = sd_in.item_code AND latest_item.mid = sd_in.id'
            );

        $adjustRows = (new Query())
            ->select([
                'item_code' => 'sd.item_code',
                'qty' => 'sd.qty',
                'unit_price' => new Expression('COALESCE(in_item.unit_price, 0)'),
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->leftJoin(['in_item' => $latestInPriceByItem], 'in_item.item_code = sd.item_code')
            ->where(['so.order_type' => StockOrder::ORDER_TYPE_ADJUST])
            ->andWhere(['so.main_warehouse_id' => $warehouseId])
            ->andWhere(['between', 'so.order_date', $dateStart, $dateEnd])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->all();

        $adjustIn = [];
        $adjustOut = [];
        foreach ($adjustRows as $row) {
            $code = $row['item_code'];
            $q = (float) $row['qty'];
            $price = (float) $row['unit_price'];
            if (!isset($adjustIn[$code])) {
                $adjustIn[$code] = ['qty' => 0, 'value' => 0];
            }
            if (!isset($adjustOut[$code])) {
                $adjustOut[$code] = ['qty' => 0, 'value' => 0];
            }
            if ($q >= 0) {
                $adjustIn[$code]['qty'] += $q;
                $adjustIn[$code]['value'] += $q * $price;
            } else {
                $adjustOut[$code]['qty'] += -$q;
                $adjustOut[$code]['value'] += -$q * $price;
            }
            $itemCodes[] = $code;
        }
        $itemCodes = array_unique($itemCodes);

        $inMap = [];
        foreach ($inRows as $row) {
            $inMap[$row['item_code']] = [
                'in_qty' => (float) $row['in_qty'],
                'in_value' => (float) ($row['in_value'] ?? 0),
            ];
        }

        $items = StockItem::find()->where(['item_code' => $itemCodes])->indexBy('item_code')->all();

        $rows = [];
        foreach ($itemCodes as $itemCode) {
            $prev = $prevClosing[$itemCode] ?? null;
            $openingQty = $prev ? (float) $prev['closing_qty'] : 0;
            $openingValue = $prev ? (float) $prev['closing_value'] : 0;

            $in = $inMap[$itemCode] ?? ['in_qty' => 0, 'in_value' => 0];
            $inQty = $in['in_qty'];
            $inValue = $in['in_value'];

            $sub = $outSub[$itemCode] ?? ['qty' => 0, 'value' => 0];
            $hosp = $outHosp[$itemCode] ?? ['qty' => 0, 'value' => 0];
            $outSubQty = $sub['qty'];
            $outSubValue = $sub['value'];
            $outHospQty = $hosp['qty'];
            $outHospValue = $hosp['value'];
            $totalOutQty = $outSubQty + $outHospQty;
            $totalOutValue = $outSubValue + $outHospValue;

            $adjIn = $adjustIn[$itemCode] ?? ['qty' => 0, 'value' => 0];
            $adjOut = $adjustOut[$itemCode] ?? ['qty' => 0, 'value' => 0];
            $adjustInQty = $adjIn['qty'];
            $adjustInValue = $adjIn['value'];
            $adjustOutQty = $adjOut['qty'];
            $adjustOutValue = $adjOut['value'];

            $closingQty = $openingQty + $inQty + $adjustInQty - $totalOutQty - $adjustOutQty;
            $closingValue = $openingValue + $inValue + $adjustInValue - $totalOutValue - $adjustOutValue;

            $item = $items[$itemCode] ?? null;
            $unitName = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : null;

            $rows[] = [
                'report_year' => $year,
                'report_month' => $month,
                'warehouse_id' => $warehouseId,
                'item_code' => $itemCode,
                'unit_name' => $unitName,
                'opening_qty' => $openingQty,
                'opening_value' => $openingValue,
                'in_qty' => $inQty,
                'in_value' => $inValue,
                'adjust_in_qty' => $adjustInQty,
                'adjust_in_value' => $adjustInValue,
                'adjust_out_qty' => $adjustOutQty,
                'adjust_out_value' => $adjustOutValue,
                'out_sub_qty' => $outSubQty,
                'out_sub_value' => $outSubValue,
                'out_hosp_qty' => $outHospQty,
                'out_hosp_value' => $outHospValue,
                'total_out_qty' => $totalOutQty,
                'total_out_value' => $totalOutValue,
                'closing_qty' => $closingQty,
                'closing_value' => $closingValue,
            ];
        }

        return $rows;
    }

    /**
     * ปิดเดือนสำหรับคลังเดียว (งวดเดียว): คำนวณ (opening จาก snapshot งวดก่อนใน DB) แล้วเขียนทับ
     * เปิดเป็น public static เพื่อให้ console command (backfill) เรียกใช้ได้นอก request context
     * NOTE: backfill ปิดเรียงลำดับอยู่แล้ว จึงใช้ตัวนี้ได้; ฝั่งเว็บใช้ closeMonthFromStart (opening สะสมจากต้น)
     */
    public static function closeMonthForWarehouse($warehouseId, $year, $month)
    {
        $rows = self::computeMonthlyRows($warehouseId, $year, $month);
        self::persistMonthlyRows($warehouseId, $year, $month, $rows);
        return ['count' => count($rows)];
    }

    /**
     * เขียนทับ snapshot ของงวด+คลังด้วย rows ที่คำนวณมา
     * @param array<int, array<string, mixed>> $rows ผลจาก computeMonthlyRows
     */
    protected static function persistMonthlyRows($warehouseId, $year, $month, array $rows): void
    {
        StockMonthlyReport::deleteAll([
            'report_year' => $year,
            'report_month' => $month,
            'warehouse_id' => $warehouseId,
        ]);

        $createdAt = date('Y-m-d H:i:s');
        $createdBy = Yii::$app->has('user', true) ? (Yii::$app->user->id ?? null) : null;

        foreach ($rows as $row) {
            $r = new StockMonthlyReport();
            $r->setAttributes($row, false);
            $r->created_at = $createdAt;
            $r->created_by = $createdBy;
            $r->save(false);
        }
    }

    /** ปี/เดือนแรกที่มี stock_order ในคลังนี้ (จุดเริ่มของ chain) — @return array{0:?int,1:?int} */
    protected static function firstStockOrderMonth($warehouseId): array
    {
        $firstDate = (new Query())
            ->select('MIN(order_date)')
            ->from(StockOrder::tableName())
            ->where(['main_warehouse_id' => $warehouseId])
            ->scalar();
        if (!$firstDate) {
            return [null, null];
        }
        $ts = strtotime($firstDate);
        return [(int) date('Y', $ts), (int) date('n', $ts)];
    }

    /** map [item_code => ['closing_qty'=>, 'closing_value'=>]] จาก snapshot งวดใน DB */
    protected static function snapshotClosingMap($warehouseId, $year, $month): array
    {
        return (new Query())
            ->select(['item_code', 'closing_qty', 'closing_value'])
            ->from(StockMonthlyReport::tableName())
            ->where(['report_year' => $year, 'report_month' => $month, 'warehouse_id' => $warehouseId])
            ->indexBy('item_code')
            ->all();
    }

    /** map [item_code => ['closing_qty'=>, 'closing_value'=>]] จาก rows ที่คำนวณ (in-memory) */
    protected static function closingMapFromRows(array $rows): array
    {
        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r['item_code']] = [
                'closing_qty' => (float) $r['closing_qty'],
                'closing_value' => (float) $r['closing_value'],
            ];
        }
        return $map;
    }

    /**
     * ยอดยกมาของงวด (year, month) = ยอดยกไปของงวดก่อน — คำนวณแบบ chain โดยไม่เขียน DB
     * - ถ้างวดก่อนมี snapshot ใน DB → เชื่อค่านั้น (หยุด chain)
     * - ถ้าไม่มี → คำนวณงวดก่อนใน memory (recursive) ย้อนไปจนถึงงวดแรกที่มี stock_order
     * ใช้ตอน preview เพื่อให้ opening ตรงกับที่ auto-chain จะบันทึกจริง
     * @return array<string, array{closing_qty: float, closing_value: float}>
     */
    public static function buildOpeningForMonth($warehouseId, $year, $month): array
    {
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth += 12;
            $prevYear--;
        }

        [$startYear, $startMonth] = self::firstStockOrderMonth($warehouseId);
        // ไม่มี order เลย หรือ งวดก่อนอยู่ก่อนงวดแรกสุด → ยอดยกมา = 0
        if ($startYear === null || ($prevYear * 12 + $prevMonth) < ($startYear * 12 + $startMonth)) {
            return [];
        }

        $snapshot = self::snapshotClosingMap($warehouseId, $prevYear, $prevMonth);
        if (!empty($snapshot)) {
            return $snapshot;
        }

        // งวดก่อนยังไม่ปิด → คำนวณ chain ต่อ
        $prevOpening = self::buildOpeningForMonth($warehouseId, $prevYear, $prevMonth);
        $prevRows = self::computeMonthlyRows($warehouseId, $prevYear, $prevMonth, $prevOpening);
        return self::closingMapFromRows($prevRows);
    }

    /**
     * ปิดเดือน (ฝั่งเว็บ): บันทึกเฉพาะงวดเป้าหมายงวดเดียว
     * - ถ้างวดก่อนหน้ายังไม่ปิด → ยอดยกมาคำนวณสะสมจากต้นถึงงวดนี้ (buildOpeningForMonth, in-memory ไม่เขียน snapshot งวดกลาง)
     * - ถ้างวดก่อนหน้าปิดไว้แล้ว → ยอดยกมาดึงจาก snapshot งวดที่ปิด (ปกติ)
     * @return array{count: int}
     */
    public static function closeMonthFromStart($warehouseId, $targetYear, $targetMonth): array
    {
        $opening = self::buildOpeningForMonth($warehouseId, $targetYear, $targetMonth);
        $rows = self::computeMonthlyRows($warehouseId, $targetYear, $targetMonth, $opening);
        self::persistMonthlyRows($warehouseId, $targetYear, $targetMonth, $rows);
        return ['count' => count($rows)];
    }

    /**
     * Export รายงานสรุปเป็น Excel
     */
    public function actionExportExcel()
    {
        $year = (int) ($this->request->get('year') ?: date('Y'));
        $month = (int) ($this->request->get('month') ?: (int) date('n'));
        $warehouseId = $this->request->get('warehouse_id') ? (int) $this->request->get('warehouse_id') : null;

        $rows = $this->aggregateByCategory($year, $month, $warehouseId);
        $itemRows = $this->getRowsByItem($year, $month, $warehouseId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สรุปวัสดุคงคลัง');

        $summaryWidths = [
            'A' => 8,
            'B' => 30,
            'C' => 27,
            'D' => 32.29,
            'E' => 23.71,
            'F' => 35.57,
            'G' => 39.14,
            'H' => 23.71,
            'I' => 27,
        ];
        foreach ($summaryWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->mergeCells('F1:H1');
        $sheet->setCellValue('F1', 'สรุปงานวัสดุคงคลัง');
        $sheet->setCellValue('F2', 'เดือน ');
        $sheet->setCellValue('F3', 'รายงาน ณ วันที่');
        $sheet->setCellValue('G3', $this->formatThaiMonthDateRange($year, $month));

        $sheet->setCellValue('A4', 'ที่');
        $sheet->setCellValue('B4', 'รายการ');
        $sheet->setCellValue('C4', 'สินค้าคงเหลือ');
        $sheet->setCellValue('D4', 'ซื้อระหว่างเดือน');
        $sheet->setCellValue('E4', 'รวม');
        $sheet->setCellValue('F4', 'สินค้าที่ใช้ไป');
        $sheet->setCellValue('F5', 'จ่ายส่วนของ รพ.สต.');
        $sheet->setCellValue('G5', 'จ่ายส่วนของโรงพยาบาล');
        $sheet->setCellValue('H5', 'รวม');
        $sheet->setCellValue('I4', 'สินค้าคงเหลือ');
        foreach (['A', 'B', 'C', 'D', 'E', 'I'] as $column) {
            $sheet->mergeCells($column . '4:' . $column . '5');
        }
        $sheet->mergeCells('F4:H4');

        $rowNum = 6;
        $tot = [
            'opening' => 0,
            'in' => 0,
            'out_sub' => 0,
            'out_hosp' => 0,
            'total_out' => 0,
            'closing' => 0,
        ];
        foreach ($rows as $i => $r) {
            $openingValue = (float) $r['opening_value'];
            $inValue = (float) $r['in_value'];
            $outSubValue = (float) $r['out_sub_value'];
            $outHospValue = (float) $r['out_hosp_value'];
            $totalOutValue = (float) $r['total_out_value'];
            $closingValue = (float) $r['closing_value'];

            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $this->formatMaterialSummaryCategoryLabel($r['category_label']));
            $sheet->setCellValue('C' . $rowNum, $openingValue);
            $sheet->setCellValue('D' . $rowNum, $inValue);
            $sheet->setCellValue('E' . $rowNum, $openingValue + $inValue);
            $sheet->setCellValue('F' . $rowNum, $outSubValue);
            $sheet->setCellValue('G' . $rowNum, $outHospValue);
            $sheet->setCellValue('H' . $rowNum, $totalOutValue);
            $sheet->setCellValue('I' . $rowNum, $closingValue);

            $tot['opening'] += $openingValue;
            $tot['in'] += $inValue;
            $tot['out_sub'] += $outSubValue;
            $tot['out_hosp'] += $outHospValue;
            $tot['total_out'] += $totalOutValue;
            $tot['closing'] += $closingValue;
            $rowNum++;
        }

        $summaryTotalRow = $rowNum;
        $sheet->setCellValue('A' . $summaryTotalRow, '');
        $sheet->setCellValue('B' . $summaryTotalRow, 'รวม');
        $sheet->setCellValue('C' . $summaryTotalRow, $tot['opening']);
        $sheet->setCellValue('D' . $summaryTotalRow, $tot['in']);
        $sheet->setCellValue('E' . $summaryTotalRow, $tot['opening'] + $tot['in']);
        $sheet->setCellValue('F' . $summaryTotalRow, $tot['out_sub']);
        $sheet->setCellValue('G' . $summaryTotalRow, $tot['out_hosp']);
        $sheet->setCellValue('H' . $summaryTotalRow, $tot['total_out']);
        $sheet->setCellValue('I' . $summaryTotalRow, $tot['closing']);

        for ($i = 1; $i <= $summaryTotalRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight($i <= 5 ? 18 : 16.5);
        }
        $sheet->getStyle('A1:I' . $summaryTotalRow)->getFont()
            ->setName('TH Sarabun New')
            ->setSize(10);
        $sheet->getStyle('A1:I' . $summaryTotalRow)->getAlignment()
            ->setShrinkToFit(true);
        $sheet->getStyle('F1:H1')->getFont()->setSize(13)->setBold(true);
        $sheet->getStyle('F2:H3')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A4:I5')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A' . $summaryTotalRow . ':I' . $summaryTotalRow)->getFont()->setBold(true);
        $sheet->getStyle('A1:I5')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A6:A' . $summaryTotalRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B6:B' . max(6, $summaryTotalRow - 1))->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B' . $summaryTotalRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('C6:I' . $summaryTotalRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('C6:I' . $summaryTotalRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A4:I' . $summaryTotalRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $itemSheet = $spreadsheet->createSheet();
        $itemSheet->setTitle('สรุปรายการ');

        $itemWidths = [
            'A' => 12,
            'B' => 10,
            'C' => 40,
            'D' => 25,
            'E' => 9,
            'F' => 13,
            'G' => 13,
            'H' => 13,
            'I' => 13,
            'J' => 13,
            'K' => 13,
            'L' => 13,
            'M' => 13,
        ];
        foreach ($itemWidths as $column => $width) {
            $itemSheet->getColumnDimension($column)->setWidth($width);
        }

        $itemSheet->setCellValue('A1', 'วดป.ที่รายงาน');
        $itemSheet->setCellValue('B1', $this->formatThaiCurrentDate());
        $itemFormulaEndRow = max(3, count($itemRows) + 2);
        foreach (range('G', 'M') as $column) {
            $itemSheet->setCellValue($column . '1', '=SUBTOTAL(9,' . $column . '3:' . $column . $itemFormulaEndRow . ')');
        }

        $itemHeaders = [
            'ที่',
            'รหัส',
            'รายการสินค้า',
            'ประเภท',
            'หน่วย',
            'จำนวนคงเหลือ',
            'มูลค่าคงเหลือ',
            'จำนวนรับใหม่',
            'มูลค่ารับใหม่',
            'จำนวนจ่ายใหม่',
            'มูลค่าจ่ายใหม่',
            'จำนวนคงเหลือ',
            'มูลค่าคงเหลือ',
        ];
        $itemColumn = 'A';
        foreach ($itemHeaders as $header) {
            $itemSheet->setCellValue($itemColumn . '2', $header);
            $itemColumn++;
        }

        $itemRowNum = 3;
        foreach ($itemRows as $i => $r) {
            $itemSheet->setCellValue('A' . $itemRowNum, $i + 1);
            $itemSheet->setCellValue('B' . $itemRowNum, $r['item_code']);
            $itemSheet->setCellValue('C' . $itemRowNum, $r['item_name']);
            $itemSheet->setCellValue('D' . $itemRowNum, $r['category_title']);
            $itemSheet->setCellValue('E' . $itemRowNum, $r['unit_name'] ?? '');
            $itemSheet->setCellValue('F' . $itemRowNum, (float) $r['opening_qty']);
            $itemSheet->setCellValue('G' . $itemRowNum, (float) $r['opening_value']);
            $itemSheet->setCellValue('H' . $itemRowNum, (float) $r['in_qty']);
            $itemSheet->setCellValue('I' . $itemRowNum, (float) $r['in_value']);
            $itemSheet->setCellValue('J' . $itemRowNum, (float) $r['total_out_qty']);
            $itemSheet->setCellValue('K' . $itemRowNum, (float) $r['total_out_value']);
            $itemSheet->setCellValue('L' . $itemRowNum, (float) $r['closing_qty']);
            $itemSheet->setCellValue('M' . $itemRowNum, (float) $r['closing_value']);
            $itemRowNum++;
        }
        $lastItemRow = max(2, $itemRowNum - 1);
        for ($i = 1; $i <= $lastItemRow; $i++) {
            $itemSheet->getRowDimension($i)->setRowHeight($i <= 2 ? 18 : 16.5);
        }
        $itemSheet->getStyle('A1:M' . $lastItemRow)->getFont()
            ->setName('TH Sarabun New')
            ->setSize(9);
        $itemSheet->getStyle('A1:M' . $lastItemRow)->getAlignment()
            ->setShrinkToFit(true);
        $itemSheet->getStyle('A1:M2')->getFont()->setSize(10)->setBold(true);
        $itemSheet->getStyle('A1:M2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        if ($lastItemRow >= 3) {
            $itemSheet->getStyle('A3:A' . $lastItemRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $itemSheet->getStyle('B3:E' . $lastItemRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $itemSheet->getStyle('F3:M' . $lastItemRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $itemSheet->getStyle('F3:M' . $lastItemRow)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $itemSheet->getStyle('G1:M1')->getNumberFormat()->setFormatCode('#,##0.00');
        $itemSheet->getStyle('A1:M' . $lastItemRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $itemSheet->getStyle('A1:M' . $lastItemRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = $this->formatMaterialSummaryExportFilename($year, $month);
        $fallbackFilename = 'material-summary-' . $year . '-' . $month . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fallbackFilename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * รายงานแยกรายการ (ระดับรายการสินค้า): ลำดับ, รหัสสินค้า, รายการสินค้า, ประเภทวัสดุ, ยอดยกมา(จำนวน/มูลค่า), รับเข้า, จ่ายออก, คงเหลือสิ้นเดือน
     */
    public function actionMaterialByItem()
    {
        $year = (int) ($this->request->get('year') ?: date('Y'));
        $month = (int) ($this->request->get('month') ?: (int) date('n'));
        $warehouseId = $this->request->get('warehouse_id') ? (int) $this->request->get('warehouse_id') : null;

        $listWarehouse = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $warehouses = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map($listWarehouse, 'id', 'warehouse_name');

        $rows = $this->getRowsByItem($year, $month, $warehouseId);
        $hasData = !empty($rows);

        return $this->render('material-by-item', [
            'year' => $year,
            'month' => $month,
            'warehouseId' => $warehouseId,
            'warehouses' => $warehouses,
            'rows' => $rows,
            'hasData' => $hasData,
        ]);
    }

    /**
     * ดึงรายงานระดับรายการ (ไม่รวมตาม category) จาก stock_monthly_report
     */
    protected function getRowsByItem($year, $month, $warehouseId = null)
    {
        $query = (new Query())
            ->select([
                'r.item_code',
                'item_name' => 'i.title',
                'item_data_json' => 'i.data_json',
                new Expression("COALESCE(cat.title, i.category_id, '') AS category_title"),
                'r.opening_qty',
                'r.opening_value',
                'r.in_qty',
                'r.in_value',
                'r.total_out_qty',
                'r.total_out_value',
                'r.closing_qty',
                'r.closing_value',
            ])
            ->from(['r' => StockMonthlyReport::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = r.item_code')
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where([
                'r.report_year' => $year,
                'r.report_month' => $month,
            ])
            ->orderBy([new Expression('COALESCE(cat.code, i.category_id)'), 'r.item_code' => SORT_ASC]);

        if ($warehouseId !== null && $warehouseId !== '') {
            $query->andWhere(['r.warehouse_id' => $warehouseId]);
        }

        $rows = $query->all();
        foreach ($rows as &$row) {
            $row['unit_name'] = $this->extractStockItemUnitName($row['item_data_json'] ?? null);
            unset($row['item_data_json']);
        }
        unset($row);

        return $rows;
    }

    protected function extractStockItemUnitName($dataJson)
    {
        $data = json_decode((string) $dataJson, true);
        if (!is_array($data)) {
            return '';
        }

        foreach (['unit_name', 'unit'] as $key) {
            $value = trim((string) ($data[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    protected function formatMaterialSummaryCategoryLabel($label)
    {
        $label = trim((string) $label);
        $withoutCode = trim((string) preg_replace('/^\([^)]+\)\s*/u', '', $label));

        return $withoutCode !== '' ? $withoutCode : $label;
    }

    protected function formatMaterialSummaryExportFilename($year, $month)
    {
        $monthName = $this->getThaiMonthName($month, false);
        $budgetYear = (int) $year + 543;

        return 'สรุปรายงานวัสดุคงคลัง_' . $monthName . '_' . $budgetYear . '.xlsx';
    }

    protected function formatThaiMonthDateRange($year, $month)
    {
        $lastDay = (int) date('t', mktime(0, 0, 0, (int) $month, 1, (int) $year));

        return '1 - ' . $lastDay . ' ' . $this->getThaiMonthName($month, true) . ' ' . ((int) $year + 543);
    }

    protected function formatThaiCurrentDate()
    {
        return date('d/m/') . (date('Y') + 543);
    }

    protected function getThaiMonthName($month, $short = false)
    {
        $fullMonthNames = [
            1 => 'มกราคม',
            2 => 'กุมภาพันธ์',
            3 => 'มีนาคม',
            4 => 'เมษายน',
            5 => 'พฤษภาคม',
            6 => 'มิถุนายน',
            7 => 'กรกฎาคม',
            8 => 'สิงหาคม',
            9 => 'กันยายน',
            10 => 'ตุลาคม',
            11 => 'พฤศจิกายน',
            12 => 'ธันวาคม',
        ];
        $shortMonthNames = [
            1 => 'ม.ค.',
            2 => 'ก.พ.',
            3 => 'มี.ค.',
            4 => 'เม.ย.',
            5 => 'พ.ค.',
            6 => 'มิ.ย.',
            7 => 'ก.ค.',
            8 => 'ส.ค.',
            9 => 'ก.ย.',
            10 => 'ต.ค.',
            11 => 'พ.ย.',
            12 => 'ธ.ค.',
        ];

        $month = (int) $month;

        return $short
            ? ($shortMonthNames[$month] ?? (string) $month)
            : ($fullMonthNames[$month] ?? (string) $month);
    }

    /**
     * Export รายงานแยกรายการเป็น Excel
     */
    public function actionExportExcelByItem()
    {
        $year = (int) ($this->request->get('year') ?: date('Y'));
        $month = (int) ($this->request->get('month') ?: (int) date('n'));
        $warehouseId = $this->request->get('warehouse_id') ? (int) $this->request->get('warehouse_id') : null;

        $rows = $this->getRowsByItem($year, $month, $warehouseId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('รายงานแยกรายการ');

        $title = 'รายงานวัสดุคงคลังแยกรายการ  เดือน ' . $month . '/' . ($year + 543);
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $headers = ['ลำดับ', 'รหัสสินค้า', 'รายการสินค้า', 'ประเภทวัสดุ', 'ยอดยกมา(จำนวน)', 'ยอดยกมา(มูลค่า)', 'รับเข้า(จำนวน)', 'รับเข้า(มูลค่า)', 'จ่ายออก(จำนวน)', 'จ่ายออก(มูลค่า)', 'คงเหลือสิ้นเดือน(จำนวน)', 'คงเหลือสิ้นเดือน(มูลค่า)'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }
        if (strlen($col) > 1) {
            $lastCol = chr(ord('A') + count($headers) - 1);
            $sheet->getStyle('A3:' . $lastCol . '3')->getFont()->setBold(true);
            $sheet->getStyle('A3:' . $lastCol . '3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E0E0E0');
        } else {
            $sheet->getStyle('A3:L3')->getFont()->setBold(true);
            $sheet->getStyle('A3:L3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E0E0E0');
        }

        $rowNum = 4;
        foreach ($rows as $i => $r) {
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $r['item_code']);
            $sheet->setCellValue('C' . $rowNum, $r['item_name']);
            $sheet->setCellValue('D' . $rowNum, $r['category_title']);
            $sheet->setCellValue('E' . $rowNum, $r['opening_qty']);
            $sheet->setCellValue('F' . $rowNum, $r['opening_value']);
            $sheet->setCellValue('G' . $rowNum, $r['in_qty']);
            $sheet->setCellValue('H' . $rowNum, $r['in_value']);
            $sheet->setCellValue('I' . $rowNum, $r['total_out_qty']);
            $sheet->setCellValue('J' . $rowNum, $r['total_out_value']);
            $sheet->setCellValue('K' . $rowNum, $r['closing_qty']);
            $sheet->setCellValue('L' . $rowNum, $r['closing_value']);
            $rowNum++;
        }

        if (!empty($rows)) {
            $tot = [
                'opening_qty' => array_sum(array_column($rows, 'opening_qty')),
                'opening_value' => array_sum(array_column($rows, 'opening_value')),
                'in_qty' => array_sum(array_column($rows, 'in_qty')),
                'in_value' => array_sum(array_column($rows, 'in_value')),
                'total_out_qty' => array_sum(array_column($rows, 'total_out_qty')),
                'total_out_value' => array_sum(array_column($rows, 'total_out_value')),
                'closing_qty' => array_sum(array_column($rows, 'closing_qty')),
                'closing_value' => array_sum(array_column($rows, 'closing_value')),
            ];
            $sheet->setCellValue('A' . $rowNum, '');
            $sheet->setCellValue('B' . $rowNum, 'รวมทั้งหมด');
            $sheet->setCellValue('C' . $rowNum, '');
            $sheet->setCellValue('D' . $rowNum, '');
            $sheet->setCellValue('E' . $rowNum, $tot['opening_qty']);
            $sheet->setCellValue('F' . $rowNum, $tot['opening_value']);
            $sheet->setCellValue('G' . $rowNum, $tot['in_qty']);
            $sheet->setCellValue('H' . $rowNum, $tot['in_value']);
            $sheet->setCellValue('I' . $rowNum, $tot['total_out_qty']);
            $sheet->setCellValue('J' . $rowNum, $tot['total_out_value']);
            $sheet->setCellValue('K' . $rowNum, $tot['closing_qty']);
            $sheet->setCellValue('L' . $rowNum, $tot['closing_value']);
            $sheet->getStyle('A' . $rowNum . ':L' . $rowNum)->getFont()->setBold(true);
            $sheet->getStyle('B' . $rowNum . ':L' . $rowNum)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFF59D');
        }

        foreach (range('E', 'L') as $c) {
            $sheet->getStyle($c . '4:' . $c . ($rowNum))->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $filename = 'material-by-item-' . $year . '-' . $month . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * รายงาน "ประวัติจ่ายวัสดุ × เดือน"
     * — Pivot: rows = item, columns = 12 เดือนของปี พ.ศ., cells = qty / value
     * — Filter: ปี พ.ศ., คลังต้นทาง (main), คลังปลายทาง (sub), category, ค้นหา item
     * — มูลค่า = qty × IN-lot unit_price (ตาม lot_number)
     */
    public function actionDisbursementByMonth()
    {
        $thaiYear = (int) ($this->request->get('year') ?: \app\components\AppHelper::YearBudget());
        $mainWarehouseId = $this->request->get('main_warehouse_id') !== null && $this->request->get('main_warehouse_id') !== ''
            ? (int) $this->request->get('main_warehouse_id') : null;
        $subWarehouseId = $this->request->get('sub_warehouse_id') !== null && $this->request->get('sub_warehouse_id') !== ''
            ? (int) $this->request->get('sub_warehouse_id') : null;
        $categoryId = trim((string) $this->request->get('category_id', ''));
        $search = trim((string) $this->request->get('q', ''));

        $listMain = Warehouse::find()->where(['warehouse_type' => 'MAIN'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->orderBy(['warehouse_name' => SORT_ASC])->all();
        $listSub = Warehouse::find()->where(['warehouse_type' => 'SUB'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->orderBy(['warehouse_name' => SORT_ASC])->all();

        $mainWarehouses = ['' => '-- ทุกคลังหลัก --'] + \yii\helpers\ArrayHelper::map($listMain, 'id', 'warehouse_name');
        $subWarehouses = ['' => '-- ทุกคลังปลายทาง --'] + \yii\helpers\ArrayHelper::map($listSub, 'id', 'warehouse_name');
        $categories = ['' => '-- ทุกประเภท --'] + \yii\helpers\ArrayHelper::map(
            Categorise::find()->where(['name' => 'asset_type', 'group_id' => 'MATER'])->orderBy('title')->all(),
            'code', 'title'
        );

        $yearOptions = [];
        $currentBE = (int) \app\components\AppHelper::YearBudget();
        for ($y = $currentBE; $y >= $currentBE - 3; $y--) {
            $yearOptions[$y] = $y;
        }

        $data = $this->getDisbursementByMonthData($thaiYear, $mainWarehouseId, $subWarehouseId, $categoryId, $search);

        return $this->render('disbursement-by-month', [
            'year' => $thaiYear,
            'mainWarehouseId' => $mainWarehouseId,
            'subWarehouseId' => $subWarehouseId,
            'categoryId' => $categoryId,
            'search' => $search,
            'mainWarehouses' => $mainWarehouses,
            'subWarehouses' => $subWarehouses,
            'categories' => $categories,
            'yearOptions' => $yearOptions,
            'rows' => $data['rows'],
            'monthOrder' => $data['monthOrder'],
            'monthTotals' => $data['monthTotals'],
            'grandQty' => $data['grandQty'],
            'grandValue' => $data['grandValue'],
        ]);
    }

    /**
     * Pivot data รายเดือน — qty + value ต่อ item × เดือน
     * @return array{rows: array, monthTotals: array, grandQty: float, grandValue: float}
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

    protected function getDisbursementByMonthData($thaiYear, $mainWarehouseId, $subWarehouseId, $categoryId, $search)
    {
        [$fromDate, $toDate] = $this->getFiscalYearDateRange($thaiYear);
        $monthOrder = $this->getFiscalMonthOrder();

        $mainIds = $mainWarehouseId
            ? [$mainWarehouseId]
            : Warehouse::find()->select('id')->where(['warehouse_type' => 'MAIN'])
                ->andWhere(['or', ['delete' => null], ['delete' => '']])->column();
        if (empty($mainIds)) {
            return ['rows' => [], 'monthTotals' => [], 'grandQty' => 0, 'grandValue' => 0];
        }

        // ราคาทุน IN lot ล่าสุดต่อ (item_code, lot_number, main_warehouse) — same pattern เดียวกับ close-month
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
                    ->andWhere(['so_l.main_warehouse_id' => $mainIds])
                    ->groupBy(['sd_l.item_code', 'sd_l.lot_number'])],
                'latest.item_code = sd_in.item_code AND latest.lot_number = sd_in.lot_number AND latest.mid = sd_in.id'
            );

        $query = (new Query())
            ->select([
                'item_code' => 'sd.item_code',
                'item_name' => new Expression('MAX(i.title)'),
                'category_code' => new Expression("MAX(COALESCE(cat.code, i.category_id, 'OTHER'))"),
                'category_title' => new Expression("MAX(COALESCE(cat.title, i.category_id, 'อื่นๆ'))"),
                'm' => new Expression('MONTH(so.order_date)'),
                'fiscal_month' => new Expression('CASE WHEN MONTH(so.order_date) >= 10 THEN MONTH(so.order_date) - 9 ELSE MONTH(so.order_date) + 3 END'),
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
            ->andWhere(['so.main_warehouse_id' => $mainIds])
            ->andWhere(['between', 'so.order_date', $fromDate, $toDate]);

        if ($subWarehouseId !== null) {
            $query->andWhere(['so.sub_warehouse_id' => $subWarehouseId]);
        } else {
            $query->andWhere(['is not', 'so.sub_warehouse_id', null]);
        }
        if ($categoryId !== '') {
            $query->andWhere(['i.category_id' => $categoryId]);
        }
        if ($search !== '') {
            $query->andWhere(['or',
                ['like', 'sd.item_code', $search],
                ['like', 'i.title', $search],
            ]);
        }

        $query->groupBy(['sd.item_code', new Expression('MONTH(so.order_date)')])
            ->orderBy(new Expression('CASE WHEN MONTH(so.order_date) >= 10 THEN MONTH(so.order_date) - 9 ELSE MONTH(so.order_date) + 3 END ASC, sd.item_code ASC'));

        $rawRows = $query->all();

        // Pivot: [item_code => ['name', 'category', 'unit', 'monthly' => [1..12 => ['qty','value']], 'total_qty', 'total_value']]
        $items = [];
        $monthTotals = array_fill(1, 12, ['qty' => 0.0, 'value' => 0.0]);
        $grandQty = 0.0;
        $grandValue = 0.0;

        foreach ($rawRows as $r) {
            $code = (string) $r['item_code'];
            $m = (int) $r['m'];
            $q = (float) $r['qty'];
            $v = (float) $r['value'];
            if ($m < 1 || $m > 12) continue;

            if (!isset($items[$code])) {
                $items[$code] = [
                    'item_code' => $code,
                    'item_name' => (string) $r['item_name'],
                    'category_code' => (string) $r['category_code'],
                    'category_title' => (string) $r['category_title'],
                    'unit_name' => '',
                    'monthly' => array_fill(1, 12, ['qty' => 0.0, 'value' => 0.0]),
                    'total_qty' => 0.0,
                    'total_value' => 0.0,
                ];
            }
            $items[$code]['monthly'][$m] = ['qty' => $q, 'value' => $v];
            $items[$code]['total_qty'] += $q;
            $items[$code]['total_value'] += $v;
            $monthTotals[$m]['qty'] += $q;
            $monthTotals[$m]['value'] += $v;
            $grandQty += $q;
            $grandValue += $v;
        }

        // resolve unit_name PHP-side (categorise table ไม่มีคอลัมน์ unit; เก็บใน data_json)
        if (!empty($items)) {
            $stockItems = StockItem::find()
                ->where(['code' => array_keys($items)])
                ->indexBy('code')
                ->all();
            foreach ($items as $code => &$row) {
                $si = $stockItems[$code] ?? null;
                if ($si && method_exists($si, 'getUnitName')) {
                    $u = (string) $si->getUnitName();
                    $row['unit_name'] = $u !== '' ? $u : '-';
                }
            }
            unset($row);
        }

        // sort by total_value desc, fallback by item_code
        usort($items, function ($a, $b) {
            $cmp = $b['total_value'] <=> $a['total_value'];
            return $cmp !== 0 ? $cmp : strcmp($a['item_code'], $b['item_code']);
        });

        return [
            'rows' => array_values($items),
            'monthOrder' => $monthOrder,
            'monthTotals' => $monthTotals,
            'grandQty' => $grandQty,
            'grandValue' => $grandValue,
        ];
    }

    /**
     * Drill-down: รายการเอกสารใบเบิกในเดือนนั้น สำหรับ 1 item × 1 sub (optional) × ปี พ.ศ.
     */
    public function actionDisbursementDetail($item_code, $year, $month, $main_warehouse_id = null, $sub_warehouse_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $thaiYear = (int) $year;
        $month = max(1, min(12, (int) $month));
        [$fromDate, $toDate] = $this->getFiscalMonthDateRange($thaiYear, $month);

        $mainIds = ($main_warehouse_id !== null && $main_warehouse_id !== '')
            ? [(int) $main_warehouse_id]
            : Warehouse::find()->select('id')->where(['warehouse_type' => 'MAIN'])
                ->andWhere(['or', ['delete' => null], ['delete' => '']])->column();
        if (empty($mainIds)) {
            return ['rows' => [], 'total_qty' => 0, 'total_value' => 0];
        }

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
                    ->andWhere(['so_l.main_warehouse_id' => $mainIds])
                    ->groupBy(['sd_l.item_code', 'sd_l.lot_number'])],
                'latest.item_code = sd_in.item_code AND latest.lot_number = sd_in.lot_number AND latest.mid = sd_in.id'
            );

        $q = (new Query())
            ->select([
                'order_no' => 'so.order_no',
                'order_date' => 'so.order_date',
                'main_warehouse_id' => 'so.main_warehouse_id',
                'sub_warehouse_id' => 'so.sub_warehouse_id',
                'qty' => 'sd.qty',
                'lot_number' => 'sd.lot_number',
                'unit_price' => new Expression('COALESCE(in_lot.unit_price, sd.unit_price, 0)'),
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->leftJoin(['in_lot' => $latestInPrice], 'in_lot.item_code = sd.item_code AND in_lot.lot_number = sd.lot_number')
            ->where(['so.order_type' => 'OUT'])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['sd.item_code' => (string) $item_code])
            ->andWhere(['so.main_warehouse_id' => $mainIds])
            ->andWhere(['between', 'so.order_date', $fromDate, $toDate]);

        if ($sub_warehouse_id !== null && $sub_warehouse_id !== '') {
            $q->andWhere(['so.sub_warehouse_id' => (int) $sub_warehouse_id]);
        } else {
            $q->andWhere(['is not', 'so.sub_warehouse_id', null]);
        }

        $q->orderBy(['so.order_date' => SORT_ASC]);
        $rows = $q->all();

        // resolve warehouse names
        $whIds = array_unique(array_filter(array_merge(
            array_column($rows, 'main_warehouse_id'),
            array_column($rows, 'sub_warehouse_id')
        )));
        $whMap = empty($whIds) ? [] : \yii\helpers\ArrayHelper::map(
            Warehouse::find()->where(['id' => $whIds])->all(), 'id', 'warehouse_name'
        );

        $totalQty = 0;
        $totalValue = 0;
        $out = [];
        foreach ($rows as $r) {
            $qty = (float) $r['qty'];
            $price = (float) $r['unit_price'];
            $val = $qty * $price;
            $totalQty += $qty;
            $totalValue += $val;
            $out[] = [
                'order_no' => (string) $r['order_no'],
                'order_date' => date('d/m/', strtotime($r['order_date'])) . ((int) date('Y', strtotime($r['order_date'])) + 543),
                'main_warehouse' => $whMap[$r['main_warehouse_id']] ?? '-',
                'sub_warehouse' => $whMap[$r['sub_warehouse_id']] ?? '-',
                'lot_number' => (string) ($r['lot_number'] ?? '-'),
                'qty' => round($qty, 2),
                'unit_price' => round($price, 2),
                'value' => round($val, 2),
            ];
        }

        return [
            'rows' => $out,
            'total_qty' => round($totalQty, 2),
            'total_value' => round($totalValue, 2),
        ];
    }

    /**
     * Export "ประวัติจ่ายวัสดุ × เดือน" เป็น Excel (pivot)
     */
    public function actionExportDisbursementByMonth()
    {
        $thaiYear = (int) ($this->request->get('year') ?: \app\components\AppHelper::YearBudget());
        $mainWarehouseId = $this->request->get('main_warehouse_id') !== null && $this->request->get('main_warehouse_id') !== ''
            ? (int) $this->request->get('main_warehouse_id') : null;
        $subWarehouseId = $this->request->get('sub_warehouse_id') !== null && $this->request->get('sub_warehouse_id') !== ''
            ? (int) $this->request->get('sub_warehouse_id') : null;
        $categoryId = trim((string) $this->request->get('category_id', ''));
        $search = trim((string) $this->request->get('q', ''));

        $data = $this->getDisbursementByMonthData($thaiYear, $mainWarehouseId, $subWarehouseId, $categoryId, $search);
        $rows = $data['rows'];
        $monthTotals = $data['monthTotals'];
        $monthOrder = $data['monthOrder'];
        $monthLabels = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('จ่ายวัสดุรายเดือน');

        $sheet->setCellValue('A1', 'ประวัติจ่ายวัสดุ × เดือน  ปี ' . $thaiYear);
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // header row 1 (group): item info | month labels (merged across qty+value) | รวม
        $sheet->setCellValue('A3', 'ลำดับ'); $sheet->mergeCells('A3:A4');
        $sheet->setCellValue('B3', 'รหัส'); $sheet->mergeCells('B3:B4');
        $sheet->setCellValue('C3', 'รายการ'); $sheet->mergeCells('C3:C4');
        $sheet->setCellValue('D3', 'ประเภท'); $sheet->mergeCells('D3:D4');
        $sheet->setCellValue('E3', 'หน่วย'); $sheet->mergeCells('E3:E4');

        $startCol = 6; // F
        foreach ($monthOrder as $idx => $m) {
            $c1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + $idx * 2);
            $c2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + $idx * 2 + 1);
            $sheet->setCellValue($c1 . '3', $monthLabels[$m - 1]);
            $sheet->mergeCells($c1 . '3:' . $c2 . '3');
            $sheet->setCellValue($c1 . '4', 'จำนวน');
            $sheet->setCellValue($c2 . '4', 'มูลค่า');
        }
        $totalCol1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + 24);
        $totalCol2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + 25);
        $sheet->setCellValue($totalCol1 . '3', 'รวม');
        $sheet->mergeCells($totalCol1 . '3:' . $totalCol2 . '3');
        $sheet->setCellValue($totalCol1 . '4', 'จำนวน');
        $sheet->setCellValue($totalCol2 . '4', 'มูลค่า');

        $sheet->getStyle('A3:' . $totalCol2 . '4')->getFont()->setBold(true);
        $sheet->getStyle('A3:' . $totalCol2 . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A3:' . $totalCol2 . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 5;
        foreach ($rows as $i => $r) {
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $r['item_code']);
            $sheet->setCellValue('C' . $rowNum, $r['item_name']);
            $sheet->setCellValue('D' . $rowNum, $r['category_title']);
            $sheet->setCellValue('E' . $rowNum, $r['unit_name']);
            foreach ($monthOrder as $idx => $m) {
                $c1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + $idx * 2);
                $c2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + $idx * 2 + 1);
                $sheet->setCellValue($c1 . $rowNum, $r['monthly'][$m]['qty']);
                $sheet->setCellValue($c2 . $rowNum, $r['monthly'][$m]['value']);
            }
            $sheet->setCellValue($totalCol1 . $rowNum, $r['total_qty']);
            $sheet->setCellValue($totalCol2 . $rowNum, $r['total_value']);
            $rowNum++;
        }

        // footer total
        if (!empty($rows)) {
            $sheet->setCellValue('A' . $rowNum, '');
            $sheet->setCellValue('B' . $rowNum, 'รวมทั้งหมด');
            foreach ($monthOrder as $idx => $m) {
                $c1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + $idx * 2);
                $c2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol + $idx * 2 + 1);
                $sheet->setCellValue($c1 . $rowNum, $monthTotals[$m]['qty']);
                $sheet->setCellValue($c2 . $rowNum, $monthTotals[$m]['value']);
            }
            $sheet->setCellValue($totalCol1 . $rowNum, $data['grandQty']);
            $sheet->setCellValue($totalCol2 . $rowNum, $data['grandValue']);
            $sheet->getStyle('A' . $rowNum . ':' . $totalCol2 . $rowNum)->getFont()->setBold(true);
            $sheet->getStyle('A' . $rowNum . ':' . $totalCol2 . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF59D');
        }

        // number format
        $firstDataCell = 'F5';
        $lastDataCell = $totalCol2 . ($rowNum);
        $sheet->getStyle($firstDataCell . ':' . $lastDataCell)->getNumberFormat()->setFormatCode('#,##0.00');

        $filename = 'disbursement-by-month-' . $thaiYear . '.xlsx';
        $tempPath = Yii::getAlias('@runtime') . '/disb_' . uniqid('', true) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        Yii::$app->response->sendFile($tempPath, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false,
        ])->on(Response::EVENT_AFTER_SEND, function ($event) use ($tempPath) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        });
    }
}
