<?php

namespace app\modules\inventoryV2\services;

use app\models\Categorise;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockMonthlyReport;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * คำนวณแผนจัดซื้อวัสดุประจำปี ตามแบบฟอร์ม "แผนการจัดวัสดุ" ของโรงพยาบาล
 *
 * สูตรทั้งหมดถอดมาจากไฟล์ต้นแบบ HOS-68-แผนจัดซื้อวัสดุ ปี 2568.xlsx
 * - ใช้จริงของปีฐาน  = ผลรวมจำนวนจ่ายออกทั้งปีงบ (ปีฐาน = ปีที่จะจัดซื้อ - 1)
 * - ใช้ย้อนหลังปีก่อน = ปีถัดไปลบด้วย growth% ต่อชั้น (ปั้นย้อนจากปีจริง)
 * - ประมาณการใช้     = ใช้จริงปีฐาน + growth%
 * - ยอดคงคลัง        = ยอดคงเหลือ ณ สิ้นเดือนที่ 12 ของปีฐาน (30 ก.ย.)
 * - ประมาณการจัดซื้อ = max(ประมาณการใช้ - ยอดคงคลัง, 0)
 * - ราคาต่อหน่วย     = มูลค่ารับเข้าทั้งปี / จำนวนรับเข้าทั้งปี (ถ่วงน้ำหนัก)
 * - แผนรายไตรมาส    = ประมาณการจัดซื้อ แบ่ง 4 ไตรมาส เศษลงไตรมาสต้น
 *
 * จำนวนทุกช่องปัดเป็นจำนวนเต็มหน่วยนับ (สั่งซื้อเศษหน่วยไม่ได้) ต่างจากไฟล์ต้นแบบ
 * ที่ปล่อยทศนิยมไว้ — มูลค่าเงินยังคงทศนิยม 2 ตำแหน่งตามเดิม
 */
class MaterialPlanForecastService
{
    /** อัตราเพิ่ม/ลดตั้งต้น (%) ตามที่ใช้ในแบบฟอร์มเดิม */
    public const DEFAULT_GROWTH_PCT = 5.0;

    /** จำนวนปีย้อนหลังที่แสดงในแบบฟอร์ม */
    public const HISTORY_YEARS = 3;

    /** ที่มาของยอดคงคลังในการคำนวณรอบล่าสุด: closed_month | rolled_back | none */
    protected string $balanceSource = 'none';

    /** ความครบถ้วนของข้อมูลปีฐานในการคำนวณรอบล่าสุด */
    protected array $coverage = ['months' => 12, 'factor' => 1.0, 'last_date' => null];

    // ------------------------------------------------------------------
    // ส่วนคำนวณล้วน (ไม่แตะฐานข้อมูล — ทดสอบได้ตรง ๆ)
    // ------------------------------------------------------------------

    /**
     * ปีงบประมาณไทยของวันที่ที่กำหนด (เริ่ม 1 ต.ค.)
     */
    public static function thaiFiscalYear(?\DateTimeImmutable $now = null): int
    {
        $now = $now ?: new \DateTimeImmutable('now', new \DateTimeZone('Asia/Bangkok'));

        return (int) $now->format('Y') + ((int) $now->format('n') >= 10 ? 544 : 543);
    }

    /**
     * ปีฐานที่ใช้ดึงยอดใช้จริง = ปีก่อนหน้าปีที่จะจัดซื้อ
     */
    public static function baseFiscalYear(int $planFiscalYear): int
    {
        return $planFiscalYear - 1;
    }

    /**
     * ช่วงวันที่ของปีงบประมาณไทย (1 ต.ค. ปีก่อน ถึง 30 ก.ย.)
     *
     * @return array{0: string, 1: string} [เริ่ม, สิ้นสุด] รูปแบบ Y-m-d H:i:s
     */
    public static function fiscalRange(int $fiscalYear): array
    {
        $endYear = $fiscalYear - 543;

        return [
            ($endYear - 1) . '-10-01 00:00:00',
            $endYear . '-09-30 23:59:59',
        ];
    }

    /**
     * ปริมาณใช้ย้อนหลังตามแบบฟอร์ม: ปีจริงอยู่ท้ายสุด ปีก่อนหน้าปั้นย้อนลงชั้นละ growth%
     *
     * @return array<int, float> key = ปีงบ (พ.ศ.), เรียงจากเก่าไปใหม่
     */
    public static function historyUsage(int $baseFiscalYear, float $actualUsage, float $growthPct, int $years = self::HISTORY_YEARS): array
    {
        $factor = 1 - ($growthPct / 100);
        $history = [$baseFiscalYear => (int) round($actualUsage)];
        $value = $actualUsage;

        for ($back = 1; $back < $years; $back++) {
            $value = $value * $factor;
            $history[$baseFiscalYear - $back] = (int) round($value);
        }

        ksort($history);

        return $history;
    }

    /**
     * จำนวนเดือนของปีงบที่มีข้อมูลจริงครบทั้งเดือน
     *
     * นับเฉพาะเดือนที่จบไปแล้ว ณ วันที่มีข้อมูลล่าสุด เดือนที่ยังเก็บไม่ครบไม่นับ
     * เพื่อไม่ให้อัตราการใช้ต่อเดือนถูกเจือจาง เช่นข้อมูลถึง 3 ส.ค. ก็ยังนับได้ 10 เดือน (ต.ค.-ก.ค.)
     *
     * @param string|null $lastMovementDate วันที่ทำรายการล่าสุดในปีงบนั้น
     */
    public static function monthsCovered(int $fiscalYear, ?string $lastMovementDate): int
    {
        [$start, $end] = self::fiscalRange($fiscalYear);
        if ($lastMovementDate === null || trim($lastMovementDate) === '') {
            return 12;
        }

        // เทียบระดับวัน ไม่ใช่ระดับเวลา เพราะ order_date ส่วนใหญ่บันทึกเป็นเที่ยงคืน
        // ถ้าเทียบทั้ง timestamp รายการวันสุดท้ายของเดือนจะทำให้เดือนนั้นตกไป
        $cutoff = min(
            (new \DateTimeImmutable($lastMovementDate))->setTime(0, 0),
            (new \DateTimeImmutable($end))->setTime(0, 0)
        );
        $month = (new \DateTimeImmutable($start))->setTime(0, 0);
        $covered = 0;

        for ($index = 0; $index < 12; $index++) {
            $monthEnd = $month->modify('last day of this month')->setTime(0, 0);
            if ($monthEnd > $cutoff) {
                break;
            }
            $covered++;
            $month = $month->modify('first day of next month');
        }

        return max(1, min(12, $covered));
    }

    /**
     * ปรับยอดใช้ที่เก็บได้ไม่ครบปีให้เป็นอัตราเต็ม 12 เดือน
     *
     * เช่นเก็บได้ 6 เดือนใช้ไป 60 หน่วย อัตราเต็มปีคือ 120 หน่วย
     */
    public static function annualizeUsage(float $usage, int $monthsCovered): float
    {
        if ($monthsCovered <= 0 || $monthsCovered >= 12) {
            return $usage;
        }

        return $usage * (12 / $monthsCovered);
    }

    /**
     * ปริมาณที่หน่วยงานควรตั้งงบ = ปรับยอดใช้จริงเป็นเต็มปี แล้วบวกเผื่อ
     *
     * ไม่หักยอดคงคลังของหน่วยงาน เพราะหน่วยงานตั้งงบตามปริมาณที่จะใช้
     * การหักคงคลังเป็นขั้นของพัสดุตอนตัดสินใจซื้อจริง (ดู planQty) หักสองที่จะกดงบต่ำเกินจริง
     */
    public static function departmentForecastQty(float $actualUsage, int $monthsCovered, float $growthPct): int
    {
        return self::forecastUsage(self::annualizeUsage($actualUsage, $monthsCovered), $growthPct);
    }

    /**
     * ประมาณการใช้ปีที่จะจัดซื้อ = ใช้จริงปีฐาน + growth% ปัดเป็นจำนวนเต็มหน่วย
     */
    public static function forecastUsage(float $actualUsage, float $growthPct): int
    {
        return (int) round($actualUsage * (1 + ($growthPct / 100)));
    }

    /**
     * ประมาณการจัดซื้อ = ประมาณการใช้ - ยอดคงคลัง (ไม่ต่ำกว่าศูนย์)
     *
     * ปัดขึ้นเพราะสั่งซื้อเศษหน่วยไม่ได้ และแผนต้องไม่ต่ำกว่าปริมาณที่คาดว่าจะใช้
     */
    public static function planQty(float $forecastQty, float $openingQty): int
    {
        return (int) ceil(max($forecastQty - $openingQty, 0));
    }

    /**
     * แบ่งปริมาณจัดซื้อออกเป็น 4 ไตรมาสเป็นจำนวนเต็ม เศษที่หารไม่ลงตัวใส่ไตรมาสต้น
     * ผลรวมทั้ง 4 ไตรมาสจึงเท่ากับปริมาณจัดซื้อพอดี เช่น 229 → 58/57/57/57
     *
     * @return array<int, int> 4 ค่า
     */
    public static function splitQuarters(float $planQty): array
    {
        $total = (int) ceil(max($planQty, 0));
        $base = intdiv($total, 4);
        $remainder = $total % 4;

        $quarters = [];
        for ($index = 0; $index < 4; $index++) {
            $quarters[] = $base + ($index < $remainder ? 1 : 0);
        }

        return $quarters;
    }

    /**
     * ชื่อไตรมาสตามปีงบประมาณ (แก้หัวไตรมาสแรกที่พิมพ์ผิดในไฟล์ต้นแบบเป็น ต.ค.-ธ.ค.)
     *
     * @return array<int, string>
     */
    public static function quarterLabels(): array
    {
        return ['ต.ค.-ธ.ค.', 'ม.ค.-มี.ค.', 'เม.ย.-มิ.ย.', 'ก.ค.-ก.ย.'];
    }

    /**
     * รับค่าปีงบจาก query string ให้เป็น พ.ศ. เสมอ ว่างเปล่าคืนปีถัดไปจากปีงบปัจจุบัน
     */
    public static function normalizeFiscalYear($value): int
    {
        $year = (int) $value;
        if ($year <= 0) {
            return self::thaiFiscalYear() + 1;
        }

        return $year < 2400 ? $year + 543 : $year;
    }

    /**
     * จำกัด growth% ให้อยู่ในช่วงที่สมเหตุสมผล
     */
    public static function normalizeGrowthPct($value): float
    {
        if ($value === null || $value === '') {
            return self::DEFAULT_GROWTH_PCT;
        }

        return max(-100.0, min(500.0, round((float) $value, 2)));
    }

    // ------------------------------------------------------------------
    // ส่วนดึงข้อมูล
    // ------------------------------------------------------------------

    /**
     * รายการวัสดุพร้อมตัวเลขแผน เรียงตามหมวดแล้วชื่อรายการ
     *
     * @param array $filter fiscal_year, warehouse_id, dept_warehouse_id, category_id, q, growth_pct
     * @return array<int, array>
     */
    public function buildRows(array $filter): array
    {
        $planYear = (int) $filter['fiscal_year'];
        $baseYear = self::baseFiscalYear($planYear);
        $growthPct = (float) $filter['growth_pct'];
        $warehouseId = $filter['warehouse_id'] ?? null;
        $deptWarehouseId = $filter['dept_warehouse_id'] ?? null;

        $usageMap = $this->usageMap($baseYear, $warehouseId, $deptWarehouseId);
        if ($usageMap === []) {
            return [];
        }

        $itemCodes = array_keys($usageMap);
        $items = $this->itemMap($itemCodes, $filter['category_id'] ?? '', $filter['q'] ?? '');
        if ($items === []) {
            return [];
        }

        $receiptMap = $this->receiptMap($baseYear, $warehouseId, array_keys($items));
        $fallbackPriceMap = $this->latestReceiptPriceMap(array_keys($items));
        $closing = $this->closingBalanceMap($baseYear, $deptWarehouseId ?: $warehouseId, array_keys($items));
        $balanceMap = $closing['map'];
        $this->balanceSource = $closing['source'];
        $this->coverage = $this->resolveCoverage($baseYear);

        $monthsCovered = $this->coverage['months'];

        $rows = [];
        foreach ($items as $code => $item) {
            $actualUsage = (float) ($usageMap[$code] ?? 0);
            // ปีฐานที่ข้อมูลยังไม่ครบ 12 เดือน ต้องปรับเป็นอัตราเต็มปีก่อน ไม่งั้นแผนจะต่ำกว่าความจริง
            $annualUsage = self::annualizeUsage($actualUsage, $monthsCovered);
            $forecastQty = self::forecastUsage($annualUsage, $growthPct);
            $openingQty = (float) ($balanceMap[$code] ?? 0);
            $planQty = self::planQty($forecastQty, $openingQty);
            [$unitPrice, $priceSource] = $this->resolveUnitPrice($code, $receiptMap, $fallbackPriceMap);
            $quarters = self::splitQuarters($planQty);

            $rows[] = [
                'item_code' => $code,
                'item_name' => $item['item_name'],
                'category_id' => $item['category_id'],
                'category_title' => $item['category_title'],
                'unit_name' => $item['unit_name'],
                'history' => self::historyUsage($baseYear, $annualUsage, $growthPct),
                'actual_usage' => round($actualUsage, 2),
                'annual_usage' => (int) round($annualUsage),
                'forecast_qty' => $forecastQty,
                'opening_qty' => round($openingQty, 2),
                'plan_qty' => $planQty,
                'unit_price' => $unitPrice,
                'price_source' => $priceSource,
                'plan_value' => round($planQty * $unitPrice, 2),
                'quarters' => $quarters,
                'quarter_values' => array_map(static fn ($qty) => round($qty * $unitPrice, 2), $quarters),
            ];
        }

        usort($rows, static function ($a, $b) {
            return [$a['category_title'], $a['item_name']] <=> [$b['category_title'], $b['item_name']];
        });

        $seq = 1;
        foreach ($rows as &$row) {
            $row['seq'] = $seq++;
        }
        unset($row);

        return $rows;
    }

    /**
     * ประมาณการใช้วัสดุของหน่วยงานหนึ่ง สำหรับให้หน่วยงานดึงไปตั้งงบในโมดูลแผนงบประมาณ
     *
     * "หน่วยงาน" ยึดคลังปลายทางที่ของถูกจ่ายไปถึง (warehouses.department) ไม่ใช่แผนกของคนกดเบิก
     * เพราะของไปถึงที่ไหนคือที่นั่นใช้จริง และข้อมูลครบกว่า
     *
     * ไม่หักยอดคงคลังของหน่วยงาน เพราะหน่วยงานตั้งงบตามปริมาณที่จะใช้
     * ส่วนการหักคงคลังเป็นขั้นของพัสดุตอนตัดสินใจซื้อจริง หักสองที่จะกดงบต่ำเกินจริง
     *
     * @param int $organizationId รหัสหน่วยงานในผังองค์กร (ตาราง tree)
     * @return array{
     *     items: array<int, array>, months_covered: int, factor: float,
     *     base_year: int, warehouse_ids: array<int, int>, unmapped: bool
     * }
     */
    public function forecastForOrganization(
        int $organizationId,
        int $planFiscalYear,
        float $growthPct = self::DEFAULT_GROWTH_PCT,
        array $childOrganizationIds = [],
        string $categoryId = ''
    ): array {
        $baseYear = self::baseFiscalYear($planFiscalYear);
        $organizationIds = array_values(array_unique(array_merge([$organizationId], array_map('intval', $childOrganizationIds))));
        $warehouseIds = self::warehouseIdsForOrganizations($organizationIds);

        $empty = [
            'items' => [],
            'months_covered' => 12,
            'factor' => 1.0,
            'base_year' => $baseYear,
            'warehouse_ids' => $warehouseIds,
            'unmapped' => $warehouseIds === [],
        ];
        if ($warehouseIds === []) {
            return $empty;
        }

        $usageMap = $this->usageMap($baseYear, null, $warehouseIds);
        if ($usageMap === []) {
            return $empty;
        }

        $items = $this->itemMap(array_keys($usageMap), $categoryId);
        if ($items === []) {
            return $empty;
        }

        $receiptMap = $this->receiptMap($baseYear, null, array_keys($items));
        $fallbackPriceMap = $this->latestReceiptPriceMap(array_keys($items));
        $coverage = $this->resolveCoverage($baseYear);

        $rows = [];
        foreach ($items as $code => $item) {
            $actualUsage = (float) ($usageMap[$code] ?? 0);
            $forecastQty = self::departmentForecastQty($actualUsage, $coverage['months'], $growthPct);
            if ($forecastQty <= 0) {
                continue;
            }
            [$unitPrice] = $this->resolveUnitPrice($code, $receiptMap, $fallbackPriceMap);

            $rows[] = [
                'code' => $code,
                'name' => $item['item_name'],
                'unit_name' => $item['unit_name'],
                'category_title' => $item['category_title'],
                'actual_qty' => round($actualUsage, 2),
                'qty_year' => $forecastQty,
                'last_price' => $unitPrice,
                'per_month' => round($forecastQty / 12, 2),
                'total_price' => round($forecastQty * $unitPrice, 2),
            ];
        }

        usort($rows, static fn ($a, $b) => $b['total_price'] <=> $a['total_price']);

        return [
            'items' => $rows,
            'months_covered' => $coverage['months'],
            'factor' => $coverage['factor'],
            'base_year' => $baseYear,
            'warehouse_ids' => $warehouseIds,
            'unmapped' => false,
        ];
    }

    /**
     * คลังของหน่วยงานตามผังองค์กร — คลังย่อยและ รพ.สต. ที่ผูก department ไว้กับหน่วยงานนั้น
     *
     * @return array<int, int>
     */
    public static function warehouseIdsForOrganizations(array $organizationIds): array
    {
        $organizationIds = array_values(array_filter(array_map('intval', $organizationIds)));
        if ($organizationIds === []) {
            return [];
        }

        return array_map('intval', Warehouse::find()
            ->select('id')
            ->where(['warehouse_type' => Warehouse::SUB_STOCK_TYPES])
            ->andWhere(['department' => $organizationIds])
            ->column());
    }

    /**
     * ค้นวัสดุจากทะเบียนทั้งหมด สำหรับเพิ่มรายการที่ไม่มีความเคลื่อนไหวเข้าแผนเอง
     *
     * @return array<int, array{item_code: string, item_name: string, category_title: string, unit_name: string, unit_price: float}>
     */
    public function searchItems(string $q, int $limit = 20): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }

        $query = (new Query())
            ->select([
                'item_code' => 'i.code',
                'item_name' => 'i.title',
                'category_id' => 'i.category_id',
                'item_data_json' => 'i.data_json',
                'category_title' => new Expression("COALESCE(cat.title, i.category_id, '')"),
            ])
            ->from(['i' => StockItem::tableName()])
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where([
                'i.name' => 'asset_item',
                'i.group_id' => 'MATER',
                'i.active' => 1,
            ])
            ->andWhere(['or', ['like', 'i.code', $q], ['like', 'i.title', $q]])
            ->orderBy(['i.title' => SORT_ASC])
            ->limit(max(1, min(50, $limit)));

        $found = $query->all();
        if ($found === []) {
            return [];
        }

        $priceMap = $this->latestReceiptPriceMap(ArrayHelper::getColumn($found, 'item_code'));

        return array_map(function ($row) use ($priceMap) {
            $code = (string) $row['item_code'];

            return [
                'item_code' => $code,
                'item_name' => trim((string) $row['item_name']),
                'category_title' => trim((string) $row['category_title']) !== ''
                    ? (string) $row['category_title']
                    : 'ไม่ระบุหมวด',
                'unit_name' => self::firstNonEmpty(self::decodeJson($row['item_data_json'] ?? null), ['unit_name', 'unit']),
                'unit_price' => round((float) ($priceMap[$code] ?? 0), 2),
            ];
        }, $found);
    }

    /**
     * แถวของวัสดุที่ผู้ใช้เพิ่มเข้าแผนเอง — ไม่มีประวัติการใช้ ตัวเลขจึงเริ่มที่ศูนย์
     * ให้ผู้ใช้กรอกปริมาณเอง ราคาดึงจากใบรับเข้าครั้งล่าสุดถ้ามี
     *
     * @return array<int, array>
     */
    public function buildManualRows(array $itemCodes, array $filter): array
    {
        $itemCodes = array_values(array_unique(array_filter(array_map('strval', $itemCodes))));
        if ($itemCodes === []) {
            return [];
        }

        $baseYear = self::baseFiscalYear((int) $filter['fiscal_year']);
        $items = $this->itemMap($itemCodes);
        if ($items === []) {
            return [];
        }

        $fallbackPriceMap = $this->latestReceiptPriceMap(array_keys($items));
        $balanceMap = $this->closingBalanceMap(
            $baseYear,
            $filter['dept_warehouse_id'] ?? ($filter['warehouse_id'] ?? null),
            array_keys($items)
        )['map'];

        $rows = [];
        foreach ($items as $code => $item) {
            $unitPrice = round((float) ($fallbackPriceMap[$code] ?? 0), 2);

            $rows[] = [
                'item_code' => $code,
                'item_name' => $item['item_name'],
                'category_id' => $item['category_id'],
                'category_title' => $item['category_title'],
                'unit_name' => $item['unit_name'],
                'history' => self::historyUsage($baseYear, 0.0, (float) $filter['growth_pct']),
                'actual_usage' => 0.0,
                'annual_usage' => 0,
                'forecast_qty' => 0,
                'opening_qty' => round((float) ($balanceMap[$code] ?? 0), 2),
                'plan_qty' => 0,
                'unit_price' => $unitPrice,
                'price_source' => $unitPrice > 0 ? 'latest' : 'none',
                'plan_value' => 0.0,
                'quarters' => [0, 0, 0, 0],
                'quarter_values' => [0.0, 0.0, 0.0, 0.0],
                'is_manual' => true,
            ];
        }

        return $rows;
    }

    /**
     * ที่มาของยอดคงคลังที่ใช้ในการคำนวณรอบล่าสุด (ไว้บอกผู้ใช้บนหน้าจอ)
     */
    public function getBalanceSource(): string
    {
        return $this->balanceSource;
    }

    /**
     * ความครบถ้วนของข้อมูลปีฐาน: จำนวนเดือนที่มีข้อมูล ตัวคูณเต็มปี และวันที่ข้อมูลล่าสุด
     *
     * @return array{months: int, factor: float, last_date: string|null}
     */
    public function getCoverage(): array
    {
        return $this->coverage;
    }

    /**
     * ดูว่าปีฐานเก็บข้อมูลได้ถึงเมื่อไหร่ แล้วคิดเป็นกี่เดือนเต็ม
     *
     * อ่านจากรายการจ่ายทั้งระบบ ไม่กรองตามหน่วยงาน เพราะความครบถ้วนของข้อมูล
     * เป็นคุณสมบัติของระบบ ไม่ใช่ของหน่วยงานที่อาจเบิกห่าง ๆ
     *
     * @return array{months: int, factor: float, last_date: string|null}
     */
    protected function resolveCoverage(int $fiscalYear): array
    {
        [$start, $end] = self::fiscalRange($fiscalYear);

        $lastDate = (new Query())
            ->from(StockOrder::tableName())
            ->where(['order_type' => StockOrder::ORDER_TYPE_OUT])
            ->andWhere(['status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['between', 'order_date', $start, $end])
            ->max('order_date');

        $months = self::monthsCovered($fiscalYear, $lastDate !== null ? (string) $lastDate : null);

        return [
            'months' => $months,
            'factor' => $months >= 12 ? 1.0 : round(12 / $months, 4),
            'last_date' => $lastDate !== null ? (string) $lastDate : null,
        ];
    }

    /**
     * ยอดรวมสำหรับการ์ดสรุปหัวรายงาน
     */
    public function summarize(array $rows): array
    {
        $summary = [
            'item_count' => count($rows),
            'purchase_count' => 0,
            'no_price_count' => 0,
            'plan_value' => 0.0,
            'quarter_values' => [0.0, 0.0, 0.0, 0.0],
        ];

        foreach ($rows as $row) {
            if ($row['plan_qty'] > 0) {
                $summary['purchase_count']++;
            }
            if ($row['unit_price'] <= 0) {
                $summary['no_price_count']++;
            }
            $summary['plan_value'] += $row['plan_value'];
            foreach ($row['quarter_values'] as $index => $value) {
                $summary['quarter_values'][$index] += $value;
            }
        }

        $summary['plan_value'] = round($summary['plan_value'], 2);
        $summary['quarter_values'] = array_map(static fn ($v) => round($v, 2), $summary['quarter_values']);

        return $summary;
    }

    /**
     * ยอดจ่ายออกรวมทั้งปีงบ แยกรายวัสดุ (เฉพาะรายการที่มีความเคลื่อนไหวจริง)
     *
     * อ่านจากบัญชีเดินสะพัด (stock_order + stock_detail) ไม่ใช่รายงานปิดเดือน
     * เพราะต้องกรองตามหน่วยงานปลายทางได้ และต้องใช้ได้แม้ยังไม่ปิดเดือน
     *
     * @param int|array<int, int>|null $deptWarehouseId คลังปลายทาง หนึ่งคลังหรือหลายคลังของหน่วยงานเดียวกัน
     * @return array<string, float>
     */
    protected function usageMap(int $fiscalYear, $warehouseId = null, $deptWarehouseId = null): array
    {
        [$start, $end] = self::fiscalRange($fiscalYear);

        $query = (new Query())
            ->select([
                'item_code' => 'sd.item_code',
                'usage_qty' => new Expression('SUM(COALESCE(sd.qty, 0))'),
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.order_type' => StockOrder::ORDER_TYPE_OUT])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['between', 'so.order_date', $start, $end])
            ->groupBy(['sd.item_code'])
            ->having(['>', new Expression('SUM(COALESCE(sd.qty, 0))'), 0]);

        if ($warehouseId) {
            $query->andWhere(['so.main_warehouse_id' => (int) $warehouseId]);
        }
        if (is_array($deptWarehouseId)) {
            if ($deptWarehouseId === []) {
                return [];
            }
            $query->andWhere(['so.sub_warehouse_id' => array_map('intval', $deptWarehouseId)]);
        } elseif ($deptWarehouseId) {
            $query->andWhere(['so.sub_warehouse_id' => (int) $deptWarehouseId]);
        }

        return array_map('floatval', ArrayHelper::map($query->all(), 'item_code', 'usage_qty'));
    }

    /**
     * ทะเบียนวัสดุ (เฉพาะกลุ่มวัสดุสิ้นเปลืองที่ยังใช้งาน) พร้อมหมวดและหน่วยนับ
     *
     * @return array<string, array>
     */
    protected function itemMap(array $itemCodes, string $categoryId = '', string $q = ''): array
    {
        if ($itemCodes === []) {
            return [];
        }

        $query = (new Query())
            ->select([
                'item_code' => 'i.code',
                'item_name' => 'i.title',
                'category_id' => 'i.category_id',
                'item_data_json' => 'i.data_json',
                'category_title' => new Expression("COALESCE(cat.title, i.category_id, '')"),
            ])
            ->from(['i' => StockItem::tableName()])
            ->leftJoin(['cat' => Categorise::tableName()], "cat.code = i.category_id AND cat.name = 'asset_type'")
            ->where([
                'i.name' => 'asset_item',
                'i.group_id' => 'MATER',
                'i.active' => 1,
            ])
            ->andWhere(['i.code' => $itemCodes]);

        if ($categoryId !== '') {
            $query->andWhere(['i.category_id' => $categoryId]);
        }
        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'i.code', $q],
                ['like', 'i.title', $q],
                ['like', 'cat.title', $q],
            ]);
        }

        $map = [];
        foreach ($query->all() as $row) {
            $dataJson = self::decodeJson($row['item_data_json'] ?? null);
            $map[(string) $row['item_code']] = [
                // ชื่อในทะเบียนบางรายการมี tab/ช่องว่างนำหน้าติดมาจากการนำเข้าข้อมูลเดิม
                'item_name' => trim((string) $row['item_name']),
                'category_id' => (string) $row['category_id'],
                'category_title' => trim((string) $row['category_title']) !== ''
                    ? (string) $row['category_title']
                    : 'ไม่ระบุหมวด',
                'unit_name' => self::firstNonEmpty($dataJson, ['unit_name', 'unit']),
            ];
        }

        return $map;
    }

    /**
     * จำนวนและมูลค่ารับเข้าทั้งปีงบ ใช้หาราคาเฉลี่ยถ่วงน้ำหนัก
     *
     * @return array<string, array{qty: float, value: float}>
     */
    protected function receiptMap(int $fiscalYear, $warehouseId, array $itemCodes): array
    {
        if ($itemCodes === []) {
            return [];
        }

        [$start, $end] = self::fiscalRange($fiscalYear);

        $query = (new Query())
            ->select([
                'item_code' => 'sd.item_code',
                'in_qty' => new Expression('SUM(COALESCE(sd.qty, 0))'),
                'in_value' => new Expression('SUM(COALESCE(sd.qty, 0) * COALESCE(sd.unit_price, 0))'),
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.order_type' => StockOrder::ORDER_TYPE_IN])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['between', 'so.order_date', $start, $end])
            ->andWhere(['sd.item_code' => $itemCodes])
            ->groupBy(['sd.item_code']);

        if ($warehouseId) {
            $query->andWhere(['so.main_warehouse_id' => (int) $warehouseId]);
        }

        $map = [];
        foreach ($query->all() as $row) {
            $map[(string) $row['item_code']] = [
                'qty' => (float) $row['in_qty'],
                'value' => (float) $row['in_value'],
            ];
        }

        return $map;
    }

    /**
     * ราคารับเข้าครั้งล่าสุดของแต่ละวัสดุ ใช้เป็นราคาสำรองเมื่อปีฐานไม่มีการรับเข้า
     *
     * @return array<string, float>
     */
    protected function latestReceiptPriceMap(array $itemCodes): array
    {
        if ($itemCodes === []) {
            return [];
        }

        $rows = (new Query())
            ->select([
                'item_code' => 'sd.item_code',
                'unit_price' => 'sd.unit_price',
                'order_date' => 'so.order_date',
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.order_type' => StockOrder::ORDER_TYPE_IN])
            ->andWhere(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['sd.item_code' => $itemCodes])
            ->andWhere(['>', 'sd.unit_price', 0])
            ->orderBy(['so.order_date' => SORT_ASC, 'sd.id' => SORT_ASC])
            ->all();

        // เรียงเก่าไปใหม่แล้วเขียนทับ ค่าที่ค้างอยู่ท้ายสุดจึงเป็นราคาล่าสุด
        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['item_code']] = (float) $row['unit_price'];
        }

        return $map;
    }

    /**
     * ยอดคงเหลือ ณ สิ้นเดือนที่ 12 ของปีงบ (30 ก.ย.) ตามช่อง "ยอดคงคลัง" ในแบบฟอร์ม
     *
     * ใช้ยอดปิดเดือนของเดือน ก.ย. ถ้าปิดเดือนนั้นแล้ว (เป็นยอดที่บัญชีรับรอง)
     * ถ้ายังไม่ปิดให้ย้อนยอดคงคลังปัจจุบันกลับด้วยความเคลื่อนไหวหลังสิ้นปีงบ
     * ซึ่งได้ค่าเท่ากับยอดปัจจุบันเมื่อปีงบยังไม่สิ้นสุด
     *
     * @return array{map: array<string, float>, source: string}
     */
    protected function closingBalanceMap(int $fiscalYear, $warehouseId, array $itemCodes): array
    {
        if ($itemCodes === []) {
            return ['map' => [], 'source' => 'none'];
        }

        $warehouseIds = $this->targetWarehouseIds($warehouseId);
        $endYear = $fiscalYear - 543;

        $closed = (new Query())
            ->select([
                'item_code' => 'r.item_code',
                'closing_qty' => new Expression('SUM(COALESCE(r.closing_qty, 0))'),
            ])
            ->from(['r' => StockMonthlyReport::tableName()])
            ->where([
                'r.report_year' => $endYear,
                'r.report_month' => 9,
                'r.warehouse_id' => $warehouseIds,
            ])
            ->andWhere(['r.item_code' => $itemCodes])
            ->groupBy(['r.item_code'])
            ->all();

        if ($closed !== []) {
            return [
                'map' => array_map('floatval', ArrayHelper::map($closed, 'item_code', 'closing_qty')),
                'source' => 'closed_month',
            ];
        }

        return [
            'map' => $this->rolledBackBalanceMap($fiscalYear, $warehouseIds, $itemCodes),
            'source' => 'rolled_back',
        ];
    }

    /**
     * ยอดคงคลังปัจจุบันหักความเคลื่อนไหวที่เกิดหลังสิ้นปีงบออก
     *
     * @return array<string, float>
     */
    protected function rolledBackBalanceMap(int $fiscalYear, array $warehouseIds, array $itemCodes): array
    {
        $balance = (new Query())
            ->select([
                'item_code' => 'b.item_code',
                'balance_qty' => new Expression('SUM(COALESCE(b.balance_qty, 0))'),
            ])
            ->from(['b' => StockBalance::tableName()])
            ->where(['b.item_code' => $itemCodes, 'b.warehouse_id' => $warehouseIds])
            ->groupBy(['b.item_code'])
            ->all();

        $map = array_map('floatval', ArrayHelper::map($balance, 'item_code', 'balance_qty'));

        [, $fiscalEnd] = self::fiscalRange($fiscalYear);
        $movedAfter = (new Query())
            ->select([
                'item_code' => 'sd.item_code',
                'net_qty' => new Expression(
                    "SUM(CASE WHEN so.order_type = 'OUT' THEN -COALESCE(sd.qty, 0) ELSE COALESCE(sd.qty, 0) END)"
                ),
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where(['so.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['so.main_warehouse_id' => $warehouseIds])
            ->andWhere(['>', 'so.order_date', $fiscalEnd])
            ->andWhere(['sd.item_code' => $itemCodes])
            ->groupBy(['sd.item_code'])
            ->all();

        foreach ($movedAfter as $row) {
            $code = (string) $row['item_code'];
            $map[$code] = ($map[$code] ?? 0) - (float) $row['net_qty'];
        }

        return array_map(static fn ($qty) => max($qty, 0.0), $map);
    }

    /**
     * คลังที่นับยอดคงเหลือ — คลังที่เลือก หรือคลังหลักทั้งหมดถ้าไม่ได้เลือก
     *
     * @return array<int, int>
     */
    protected function targetWarehouseIds($warehouseId): array
    {
        if ($warehouseId) {
            return [(int) $warehouseId];
        }

        return array_map('intval', Warehouse::find()
            ->select('id')
            ->where(['warehouse_type' => 'MAIN'])
            ->column());
    }

    /**
     * ราคาต่อหน่วย: เฉลี่ยถ่วงน้ำหนักจากการรับเข้าปีฐาน ถ้าปีนั้นไม่รับเข้าเลยให้ถอยไปราคาล่าสุด
     *
     * @return array{0: float, 1: string} [ราคา, ที่มา]
     */
    protected function resolveUnitPrice(string $itemCode, array $receiptMap, array $fallbackPriceMap): array
    {
        $receipt = $receiptMap[$itemCode] ?? null;
        if ($receipt !== null && $receipt['qty'] > 0) {
            return [round($receipt['value'] / $receipt['qty'], 2), 'average'];
        }

        if (isset($fallbackPriceMap[$itemCode]) && $fallbackPriceMap[$itemCode] > 0) {
            return [round($fallbackPriceMap[$itemCode], 2), 'latest'];
        }

        return [0.0, 'none'];
    }

    /**
     * รายชื่อคลังหลักสำหรับตัวกรอง
     */
    public static function mainWarehouseOptions(): array
    {
        return ['' => '-- ทุกคลังหลัก --'] + ArrayHelper::map(
            Warehouse::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->orderBy(['warehouse_name' => SORT_ASC])
                ->all(),
            'id',
            'warehouse_name'
        );
    }

    /**
     * รายชื่อหน่วยงาน (คลังย่อยและ รพ.สต. ที่รับของจากคลังหลัก) สำหรับตัวกรอง
     */
    public static function departmentOptions(): array
    {
        return ['' => '-- ทุกหน่วยงาน --'] + ArrayHelper::map(
            Warehouse::find()
                ->where(['warehouse_type' => Warehouse::SUB_STOCK_TYPES])
                ->orderBy(['warehouse_name' => SORT_ASC])
                ->all(),
            'id',
            'warehouse_name'
        );
    }

    /**
     * หมวดวัสดุที่มีวัสดุใช้งานอยู่จริง สำหรับตัวกรอง
     */
    public static function categoryOptions(): array
    {
        $rows = (new Query())
            ->select(['code' => 'cat.code', 'title' => 'cat.title'])
            ->from(['cat' => Categorise::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.category_id = cat.code')
            ->where([
                'cat.name' => 'asset_type',
                'i.name' => 'asset_item',
                'i.group_id' => 'MATER',
                'i.active' => 1,
            ])
            ->groupBy(['cat.code', 'cat.title'])
            ->orderBy(['cat.title' => SORT_ASC])
            ->all();

        return ['' => '-- ทุกหมวดวัสดุ --'] + ArrayHelper::map($rows, 'code', 'title');
    }

    protected static function decodeJson($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected static function firstNonEmpty(array $data, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && trim((string) $data[$key]) !== '') {
                return trim((string) $data[$key]);
            }
        }

        return '';
    }
}
