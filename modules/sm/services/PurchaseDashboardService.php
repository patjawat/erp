<?php

namespace app\modules\sm\services;

use Yii;

/**
 * รวม query สำหรับหน้าภาพรวมงานพัสดุ (/sm)
 *
 * แนวคิด: ทุกตัวเลขบนหน้าต้องตรงกับข้อมูลจริงในตาราง orders
 *  - หมวดพัสดุจำแนกจาก data_json.order_type_name (วัสดุ / ครุภัณฑ์ / งานจ้าง / ยา-เวชภัณฑ์)
 *  - "ขอซื้อรายเดือน" ยึดวันที่ pr_create_date, "ตรวจรับรายเดือน" ยึดวันที่ gr_date (status >= 5)
 *  - มูลค่าใช้ผลรวม order_item (price * qty) เป็นฐานเดียวทั้งหน้า
 *  - เทียบแผนจากตาราง plan_order (budget_total + month_1..12) เฉพาะเงินบำรุง
 *
 * รหัสสถานะจริง (categorise name='order_status'):
 *   1 ขอซื้อ(PR) · 2 ผอ.อนุมัติ · 3 ทะเบียนคุม · 4 ใบสั่งซื้อ
 *   5 ตรวจรับวัสดุ · 6 วัสดุเข้าคลัง · 7 ส่งการเงิน · 8 ยกเลิก
 */
class PurchaseDashboardService
{
    /** เดือนเรียงตามปีงบประมาณ ต.ค. -> ก.ย. */
    public const FISCAL_MONTHS = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
    public const MONTH_LABELS = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];

    /** เงินบำรุง = รหัส 4 ใน categorise name='budget_type' */
    public const BUDGET_TYPE_REVENUE = '4';

    /** สถานะที่ถือว่า "ตรวจรับแล้ว" */
    public const STATUS_RECEIVED = 5;

    /** สถานะที่ถือว่า "เข้าคลังแล้ว" */
    public const STATUS_STOCKED = 6;

    /** ป้ายสถานะ (ตาม categorise order_status) + Bootstrap CSS var สำหรับ pipeline */
    public const STATUS_LABELS = [
        1 => ['label' => 'ขอซื้อ (PR)', 'cssvar' => '--bs-gray-500'],
        2 => ['label' => 'ผอ.อนุมัติ', 'cssvar' => '--bs-cyan'],
        3 => ['label' => 'ลงทะเบียนคุม', 'cssvar' => '--bs-indigo'],
        4 => ['label' => 'ออกใบสั่งซื้อ', 'cssvar' => '--bs-orange'],
        5 => ['label' => 'ตรวจรับวัสดุ', 'cssvar' => '--bs-pink'],
        6 => ['label' => 'เข้าคลัง', 'cssvar' => '--bs-green'],
        7 => ['label' => 'ส่งการเงิน', 'cssvar' => '--bs-teal'],
    ];

    /** วันที่อ้างอิงสำหรับคำนวณอายุงาน (ค้างกี่วัน) */
    public function today(): string
    {
        return date('Y-m-d');
    }

    /** นิยาม 4 หมวด: key => ป้าย + Bootstrap CSS var (ไม่ hardcode สี ตาม DESIGN.md) */
    public const CATEGORIES = [
        'material' => ['label' => 'วัสดุ', 'cssvar' => '--bs-indigo'],
        'asset'    => ['label' => 'ครุภัณฑ์', 'cssvar' => '--bs-teal'],
        'wage'     => ['label' => 'งานจ้าง', 'cssvar' => '--bs-orange'],
        'drug'     => ['label' => 'ยา/เวชภัณฑ์', 'cssvar' => '--bs-pink'],
    ];

    /** รายชื่อ CSS var ของ 4 หมวด เรียงตามลำดับ (ให้ JS resolve เป็นสีจริงสำหรับกราฟ) */
    public static function categoryCssVars(): array
    {
        return array_map(fn($c) => $c['cssvar'], array_values(self::CATEGORIES));
    }

    public int $year;

    public function __construct($year)
    {
        $this->year = (int) $year;
    }

    /** นิพจน์ SQL จำแนกหมวดจาก order_type_name (o = alias ตาราง orders หลัก) */
    private function categoryCase(string $alias = 'o'): string
    {
        $col = "JSON_UNQUOTE(JSON_EXTRACT($alias.data_json, '$.order_type_name'))";
        return "CASE
            WHEN $col LIKE 'ยา%' OR $col LIKE '%เวชภัณฑ์%' THEN 'drug'
            WHEN $col LIKE 'ครุภัณฑ์%' THEN 'asset'
            WHEN $col LIKE 'จ้าง%' THEN 'wage'
            ELSE 'material'
        END";
    }

    /**
     * ยอดรายเดือนแยกหมวด สำหรับกราฟแท่ง
     * @param string $stage 'pr' = ขอซื้อ (ยึด pr_create_date), 'gr' = ตรวจรับ (ยึด gr_date, status>=5)
     * @return array [ catKey => [12 ค่าเรียงตามปีงบ], ... ]
     */
    public function monthlyByCategory(string $stage): array
    {
        [$dateExpr, $stageWhere] = $this->stageDate($stage);

        $cat = $this->categoryCase();
        $receipts = ($stage === 'gr' && $this->receiptsReady())
            ? "UNION ALL SELECT $cat AS c, MONTH(r.receive_date) AS m, ri.amount AS v " . str_replace(':yr', ':yr2', $this->receiptFrom())
            : '';
        $sql = "SELECT c AS cat, m AS mth, SUM(v) AS total FROM (
                    SELECT $cat AS c, MONTH($dateExpr) AS m, (i.price * i.qty) AS v
                    FROM orders o
                    JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                    WHERE o.name = 'order' AND o.thai_year = :yr AND o.status <> 8 $stageWhere
                    $receipts
                ) t
                WHERE m IS NOT NULL
                GROUP BY c, m";

        $params = [':yr' => $this->year];
        if ($receipts) {
            $params[':yr2'] = $this->year;
        }
        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        // เตรียมโครง 0 ทุกหมวด/ทุกเดือน
        $out = [];
        foreach (array_keys(self::CATEGORIES) as $k) {
            $out[$k] = array_fill(0, 12, 0.0);
        }
        $idx = array_flip(self::FISCAL_MONTHS); // month => ตำแหน่งในปีงบ
        foreach ($rows as $r) {
            $k = $r['cat'];
            $pos = $idx[(int) $r['mth']] ?? null;
            if ($k !== null && $pos !== null && isset($out[$k])) {
                $out[$k][$pos] = (float) $r['total'];
            }
        }
        return $out;
    }

    // ── ตรวจรับรายงวด (สัญญาที่ออกใบสั่งซื้อเต็มวงเงินแต่ตรวจรับรายเดือน) ─────────────
    // ใบแบบนี้ "ตรวจรับ" ต้องนับจากงวดตรวจรับจริง (purchase_contract_receipt) ตามวันตรวจรับของแต่ละงวด
    // และตัดยอดทั้งใบออกจากฝั่ง orders ไม่ให้นับซ้ำ ส่วน "ขอซื้อ" ยังนับเต็มวงเงิน (ภาระผูกพัน)

    private ?bool $receiptsReady = null;

    /** ตารางงวดตรวจรับมีแล้วหรือยัง (ฐานที่ยังไม่รัน migration m260925_100000 ให้ทำงานแบบเดิม) */
    private function receiptsReady(): bool
    {
        if ($this->receiptsReady === null) {
            $this->receiptsReady = Yii::$app->db->getTableSchema('purchase_contract_receipt', true) !== null;
        }
        return $this->receiptsReady;
    }

    /** เงื่อนไขตัดใบที่ตรวจรับรายงวดออกจากยอดตรวจรับทั้งใบ */
    private function notInstallment(string $alias = 'o'): string
    {
        if (!$this->receiptsReady()) {
            return '';
        }
        return " AND NOT EXISTS (SELECT 1 FROM purchase_contract pc
                    WHERE pc.order_id = $alias.id AND pc.deleted_at IS NULL
                      AND pc.billing_mode IN ('fixed', 'unit_price'))";
    }

    /**
     * ชุดแถวงวดที่ตรวจรับแล้วของปีงบนี้ (ปีงบของงวด ไม่ใช่ของใบ) — o = ใบสั่งซื้อ, r = งวด, ri = รายการในงวด
     * มูลค่า = ผลรวมรายการ (qty × unit_price) ฐานเดียวกับ order_item.price × qty ของทั้งหน้า
     */
    private function receiptFrom(): string
    {
        return "FROM purchase_contract_receipt r
                JOIN orders o ON o.id = r.order_id AND o.name = 'order'
                JOIN purchase_contract_receipt_item ri ON ri.receipt_id = r.id
                WHERE r.deleted_at IS NULL AND r.status IN ('received', 'sent_finance')
                  AND r.thai_year = :yr AND o.status <> 8";
    }

    /** ยอดงวดที่นับเป็น "เข้าคลัง": งานจ้าง/บริการนับทันที; พัสดุ (วัสดุ/ยา) นับเมื่อมีใบรับเข้าคลังผูกงวดแล้ว */
    private function receiptStockedCase(): string
    {
        return "CASE WHEN JSON_EXTRACT(r.data_json, '$.stock_order_id') IS NOT NULL
                    OR o.category_id = 'M25'
                    OR JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.order_type_name')) LIKE 'จ้าง%'
                THEN ri.amount ELSE 0 END";
    }

    /**
     * นิพจน์วันที่ + เงื่อนไขสถานะ ตามมุมมอง
     * @return array [dateExpr, stageWhere]
     */
    private function stageDate(string $stage): array
    {
        if ($stage === 'gr') {
            return [
                "JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.gr_date'))",
                'AND o.status >= ' . self::STATUS_RECEIVED . $this->notInstallment(),
            ];
        }
        // ขอซื้อ: วันที่ทำใบขอซื้อ (fallback order_date แล้วค่อย created_at)
        return [
            "COALESCE(
                NULLIF(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.pr_create_date')), ''), 'null'),
                NULLIF(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.order_date')), ''), 'null'),
                DATE(o.created_at)
            )",
            '',
        ];
    }

    /**
     * ยอดรายเดือนแยกประเภทพัสดุย่อย (order_type_name) ภายในแต่ละหมวด สำหรับกราฟแท่งแบบเจาะหมวด
     * ประเภทย่อยที่มูลค่าทั้งปีน้อยกว่าอันดับ $top รวมเป็น "อื่นๆ" กันกราฟสีเยอะจนอ่านไม่ออก
     * @return array [ catKey => [ ['name' => ประเภทย่อย, 'data' => [12 ค่าเรียงตามปีงบ]], ... ], ... ]
     */
    public function monthlyBySubType(string $stage, int $top = 8): array
    {
        [$dateExpr, $stageWhere] = $this->stageDate($stage);
        $cat = $this->categoryCase();
        $subExpr = "COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.order_type_name')), ''), '(ไม่ระบุ)')";

        $receipts = ($stage === 'gr' && $this->receiptsReady())
            ? "UNION ALL SELECT $cat AS c, $subExpr AS s, MONTH(r.receive_date) AS m, ri.amount AS v " . str_replace(':yr', ':yr2', $this->receiptFrom())
            : '';
        $sql = "SELECT c AS cat, s AS subtype, m AS mth, SUM(v) AS total FROM (
                    SELECT $cat AS c, $subExpr AS s, MONTH($dateExpr) AS m, (i.price * i.qty) AS v
                    FROM orders o
                    JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                    WHERE o.name = 'order' AND o.thai_year = :yr AND o.status <> 8 $stageWhere
                    $receipts
                ) t
                WHERE m IS NOT NULL
                GROUP BY c, s, m";
        $params = [':yr' => $this->year];
        if ($receipts) {
            $params[':yr2'] = $this->year;
        }
        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $idx = array_flip(self::FISCAL_MONTHS);
        $bySub = []; // catKey => subtype => [12]
        foreach ($rows as $r) {
            $pos = $idx[(int) $r['mth']] ?? null;
            if ($pos === null || !isset(self::CATEGORIES[$r['cat']])) {
                continue;
            }
            $bySub[$r['cat']][$r['subtype']] ??= array_fill(0, 12, 0.0);
            $bySub[$r['cat']][$r['subtype']][$pos] += (float) $r['total'];
        }

        $out = [];
        foreach (array_keys(self::CATEGORIES) as $k) {
            $subs = $bySub[$k] ?? [];
            uasort($subs, fn($a, $b) => array_sum($b) <=> array_sum($a));
            $series = [];
            $other = array_fill(0, 12, 0.0);
            $n = 0;
            foreach ($subs as $name => $data) {
                if ($n++ < $top) {
                    $series[] = ['name' => $name, 'data' => array_map(fn($v) => round($v, 2), $data)];
                } else {
                    foreach ($data as $p => $v) {
                        $other[$p] += $v;
                    }
                }
            }
            if (array_sum($other) > 0) {
                $series[] = ['name' => 'อื่นๆ', 'data' => array_map(fn($v) => round($v, 2), $other)];
            }
            $out[$k] = $series;
        }
        return $out;
    }

    /** แปลงผลจาก monthlyByCategory เป็น series พร้อมสำหรับ ApexCharts */
    public function chartSeries(string $stage): array
    {
        $data = $this->monthlyByCategory($stage);
        $series = [];
        foreach (self::CATEGORIES as $k => $meta) {
            $series[] = ['name' => $meta['label'], 'data' => array_map(fn($v) => round($v, 2), $data[$k])];
        }
        return $series;
    }

    /** มูลค่าขอซื้อรวมทั้งปี แยกหมวด (ทุกใบที่ไม่ถูกยกเลิก) */
    public function actualByCategory(): array
    {
        $cat = $this->categoryCase();
        $sql = "SELECT $cat AS cat, SUM(i.price * i.qty) AS total, COUNT(DISTINCT o.id) AS cnt
                FROM orders o
                JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                WHERE o.name = 'order' AND o.thai_year = :yr AND o.status <> 8
                GROUP BY cat";
        $rows = Yii::$app->db->createCommand($sql, [':yr' => $this->year])->queryAll();
        $out = [];
        foreach (array_keys(self::CATEGORIES) as $k) {
            $out[$k] = ['total' => 0.0, 'cnt' => 0];
        }
        foreach ($rows as $r) {
            if (isset($out[$r['cat']])) {
                $out[$r['cat']] = ['total' => (float) $r['total'], 'cnt' => (int) $r['cnt']];
            }
        }
        return $out;
    }

    /** มูลค่าตรวจรับ (status >= 5) แยกหมวด */
    public function receivedByCategory(): array
    {
        $cat = $this->categoryCase();
        $sql = "SELECT $cat AS cat, SUM(i.price * i.qty) AS total, COUNT(DISTINCT o.id) AS cnt
                FROM orders o
                JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                WHERE o.name = 'order' AND o.thai_year = :yr
                  AND o.status >= " . self::STATUS_RECEIVED . " AND o.status <> 8" . $this->notInstallment() . "
                GROUP BY cat";
        $rows = Yii::$app->db->createCommand($sql, [':yr' => $this->year])->queryAll();
        if ($this->receiptsReady()) {
            // ใบตรวจรับรายงวด: นับยอดงวดที่ตรวจรับแล้วในปีงบนี้ + นับใบละ 1
            $rows = array_merge($rows, Yii::$app->db->createCommand(
                "SELECT $cat AS cat, SUM(ri.amount) AS total, COUNT(DISTINCT o.id) AS cnt " . $this->receiptFrom() . " GROUP BY cat",
                [':yr' => $this->year]
            )->queryAll());
        }
        $out = [];
        foreach (array_keys(self::CATEGORIES) as $k) {
            $out[$k] = ['total' => 0.0, 'cnt' => 0];
        }
        foreach ($rows as $r) {
            if (isset($out[$r['cat']])) {
                $out[$r['cat']]['total'] += (float) $r['total'];
                $out[$r['cat']]['cnt'] += (int) $r['cnt'];
            }
        }
        return $out;
    }

    /**
     * แตกรายละเอียดตามประเภทพัสดุย่อย (order_type_name) จัดกลุ่มใต้ 4 หมวดหลัก
     * แต่ละแถว: ขอซื้อ / ตรวจรับ / เข้าคลัง / ค้างเข้าคลัง
     * @return array [ catKey => ['label','color','rows'=>[...],'totals'=>[...]], ... ]
     */
    public function bySubType(?int $month = null): array
    {
        $cat = $this->categoryCase();
        $subExpr = "COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.order_type_name')), ''), '(ไม่ระบุ)')";
        $recv = self::STATUS_RECEIVED;
        $stock = self::STATUS_STOCKED;

        if ($month !== null && in_array($month, self::FISCAL_MONTHS, true)) {
            // รายเดือน: ขอซื้อยึดเดือนของ pr_create_date, ตรวจรับ/เข้าคลังยึดเดือนของ gr_date
            $prMonth = "MONTH(COALESCE(
                NULLIF(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.pr_create_date')), ''), 'null'),
                NULLIF(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.order_date')), ''), 'null'),
                DATE(o.created_at)
            )) = $month";
            $grMonth = "MONTH(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.gr_date'))) = $month";
            $orderedCond = $prMonth;
            $recvCond = "o.status >= $recv AND $grMonth";
            $stockCond = "o.status >= $stock AND $grMonth";
        } else {
            // ทั้งปี: ยึดสถานะปัจจุบันแบบสะสม
            $orderedCond = '1 = 1';
            $recvCond = "o.status >= $recv";
            $stockCond = "o.status >= $stock";
        }
        // ใบตรวจรับรายงวด: ตรวจรับ/เข้าคลังนับจากงวดแทนทั้งใบ (เติมด้านล่าง)
        $recvCond .= $this->notInstallment();
        $stockCond .= $this->notInstallment();

        $sql = "SELECT $cat AS cat, $subExpr AS subtype,
                    COUNT(DISTINCT CASE WHEN $orderedCond THEN o.id END) AS cnt,
                    SUM(CASE WHEN $orderedCond THEN i.price * i.qty ELSE 0 END) AS ordered,
                    SUM(CASE WHEN $recvCond THEN i.price * i.qty ELSE 0 END) AS received,
                    SUM(CASE WHEN $stockCond THEN i.price * i.qty ELSE 0 END) AS stocked
                FROM orders o
                JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                WHERE o.name = 'order' AND o.thai_year = :yr AND o.status <> 8
                GROUP BY cat, subtype
                ORDER BY ordered DESC";
        $rows = Yii::$app->db->createCommand($sql, [':yr' => $this->year])->queryAll();

        if ($this->receiptsReady()) {
            // งวดที่ตรวจรับแล้ว: งานจ้าง/บริการไม่มีขั้นรับเข้าคลัง จึงนับเป็น "เข้าคลัง" ด้วย ไม่ให้ค้างเข้าคลังผิด ๆ
            $monthCond = ($month !== null && in_array($month, self::FISCAL_MONTHS, true))
                ? ' AND MONTH(r.receive_date) = ' . (int) $month : '';
            $byKey = [];
            foreach ($rows as $idx => $r) {
                $byKey[$r['cat'] . '|' . $r['subtype']] = $idx;
            }
            $stockedCase = $this->receiptStockedCase();
            $extra = Yii::$app->db->createCommand(
                "SELECT $cat AS cat, $subExpr AS subtype, SUM(ri.amount) AS amt, SUM($stockedCase) AS stocked_amt "
                    . $this->receiptFrom() . $monthCond . " GROUP BY cat, subtype",
                [':yr' => $this->year]
            )->queryAll();
            foreach ($extra as $e) {
                $key = $e['cat'] . '|' . $e['subtype'];
                if (!isset($byKey[$key])) {
                    $rows[] = ['cat' => $e['cat'], 'subtype' => $e['subtype'], 'cnt' => 0, 'ordered' => 0, 'received' => 0, 'stocked' => 0];
                    $byKey[$key] = count($rows) - 1;
                }
                $rows[$byKey[$key]]['received'] += (float) $e['amt'];
                $rows[$byKey[$key]]['stocked'] += (float) $e['stocked_amt'];
            }
        }

        $out = [];
        foreach (self::CATEGORIES as $k => $meta) {
            $out[$k] = [
                'label' => $meta['label'],
                'cssvar' => $meta['cssvar'],
                'rows' => [],
                'totals' => ['cnt' => 0, 'ordered' => 0.0, 'received' => 0.0, 'stocked' => 0.0, 'pending' => 0.0],
            ];
        }
        foreach ($rows as $r) {
            $k = $r['cat'];
            if (!isset($out[$k])) {
                continue;
            }
            $ordered = (float) $r['ordered'];
            $received = (float) $r['received'];
            $stocked = (float) $r['stocked'];
            $pending = $received - $stocked;
            // ข้ามประเภทที่ไม่มีความเคลื่อนไหวเลย (สำคัญเมื่อกรองรายเดือน)
            if ($ordered == 0.0 && $received == 0.0 && $stocked == 0.0) {
                continue;
            }
            $out[$k]['rows'][] = [
                'subtype' => $r['subtype'],
                'cnt' => (int) $r['cnt'],
                'ordered' => $ordered,
                'received' => $received,
                'stocked' => $stocked,
                'pending' => $pending,
            ];
            $out[$k]['totals']['cnt'] += (int) $r['cnt'];
            $out[$k]['totals']['ordered'] += $ordered;
            $out[$k]['totals']['received'] += $received;
            $out[$k]['totals']['stocked'] += $stocked;
            $out[$k]['totals']['pending'] += $pending;
        }
        return $out;
    }

    /**
     * กระทบยอด "ตรวจรับ" (status>=5) กับ "เข้าคลัง" (status>=6)
     * แกนเวลาใช้ gr_date (วันตรวจรับ) เพราะไม่มีฟิลด์วันเข้าคลังแยก
     * @return array totals + monthly[stocked|pending] เรียงตามปีงบ
     */
    public function reconcile(): array
    {
        // ใบตรวจรับรายงวดไม่นับทั้งใบ (ปิดสัญญาแล้วใบเป็นสถานะ 7 เต็มวงเงิน) — ใช้ยอดงวดด้านล่างแทน
        $received = $this->sumByStatusRaw('o.status >= ' . self::STATUS_RECEIVED . ' AND o.status <> 8' . $this->notInstallment())['price'];
        $stocked  = $this->sumByStatusRaw('o.status >= ' . self::STATUS_STOCKED . ' AND o.status <> 8' . $this->notInstallment())['price'];

        $dateExpr = "JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.gr_date'))";
        $sql = "SELECT MONTH($dateExpr) AS mth,
                    SUM(CASE WHEN o.status >= " . self::STATUS_STOCKED . " THEN i.price * i.qty ELSE 0 END) AS stocked,
                    SUM(CASE WHEN o.status = " . self::STATUS_RECEIVED . " THEN i.price * i.qty ELSE 0 END) AS pending
                FROM orders o
                JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                WHERE o.name = 'order' AND o.thai_year = :yr
                  AND o.status >= " . self::STATUS_RECEIVED . " AND o.status <> 8" . $this->notInstallment() . "
                GROUP BY mth HAVING mth IS NOT NULL";
        $rows = Yii::$app->db->createCommand($sql, [':yr' => $this->year])->queryAll();

        if ($this->receiptsReady()) {
            // งวดที่ตรวจรับแล้ว: เข้าคลังเมื่อผูกใบรับเข้าแล้ว (งานจ้างนับทันที) ค้างเข้าคลัง = ส่วนที่เหลือ
            $stockedCase = $this->receiptStockedCase();
            $extra = Yii::$app->db->createCommand(
                "SELECT MONTH(r.receive_date) AS mth, SUM($stockedCase) AS stocked, SUM(ri.amount) - SUM($stockedCase) AS pending "
                    . $this->receiptFrom() . " GROUP BY mth",
                [':yr' => $this->year]
            )->queryAll();
            foreach ($extra as $e) {
                $received += (float) $e['stocked'] + (float) $e['pending'];
                $stocked += (float) $e['stocked'];
            }
            $rows = array_merge($rows, $extra);
        }
        $pending  = $received - $stocked; // ตรวจรับแล้วแต่ยังไม่เข้าคลัง

        $stockedM = array_fill(0, 12, 0.0);
        $pendingM = array_fill(0, 12, 0.0);
        $idx = array_flip(self::FISCAL_MONTHS);
        foreach ($rows as $r) {
            $pos = $idx[(int) $r['mth']] ?? null;
            if ($pos !== null) {
                $stockedM[$pos] += (float) $r['stocked'];
                $pendingM[$pos] += (float) $r['pending'];
            }
        }

        return [
            'received' => $received,
            'stocked' => $stocked,
            'pending' => $pending,
            'pct' => $received > 0 ? round(($stocked / $received) * 100, 1) : null,
            'monthly' => ['stocked' => $stockedM, 'pending' => $pendingM],
        ];
    }

    /** การ์ด KPI ด้านบน */
    public function kpi(): array
    {
        // ขอซื้อทั้งหมด (ไม่รวมยกเลิก)
        $pr = $this->sumByStatus('<> 8');
        // อยู่ระหว่างดำเนินการ (ยังไม่ตรวจรับ): status 1-4 หรือยังไม่ตั้งสถานะ
        $inProgress = $this->sumByStatusRaw("(o.status IS NULL OR (o.status BETWEEN 1 AND 4))");
        // ตรวจรับแล้ว (ใบตรวจรับรายงวดนับยอดงวดที่ตรวจรับแล้วแทนทั้งใบ)
        $received = $this->sumByStatusRaw('o.status >= ' . self::STATUS_RECEIVED . ' AND o.status <> 8' . $this->notInstallment());
        if ($this->receiptsReady()) {
            $r = Yii::$app->db->createCommand(
                'SELECT COUNT(DISTINCT o.id) AS cnt, IFNULL(SUM(ri.amount), 0) AS price ' . $this->receiptFrom(),
                [':yr' => $this->year]
            )->queryOne();
            $received['total'] += (int) $r['cnt'];
            $received['price'] += (float) $r['price'];
        }

        $plan = $this->planRevenueTotal();
        $planUsedPct = $plan > 0 ? round(($pr['price'] / $plan) * 100, 1) : null;

        return [
            'pr' => $pr,
            'inProgress' => $inProgress,
            'received' => $received,
            'planTotal' => $plan,
            'planUsedPct' => $planUsedPct,
        ];
    }

    private function sumByStatus(string $statusCond): array
    {
        return $this->sumByStatusRaw("o.status $statusCond");
    }

    private function sumByStatusRaw(string $where): array
    {
        $sql = "SELECT COUNT(DISTINCT o.id) AS cnt, IFNULL(SUM(i.price * i.qty), 0) AS price
                FROM orders o
                JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                WHERE o.name = 'order' AND o.thai_year = :yr AND ($where)";
        $r = Yii::$app->db->createCommand($sql, [':yr' => $this->year])->queryOne();
        return ['total' => (int) $r['cnt'], 'price' => (float) $r['price']];
    }

    /** มูลค่าขอซื้อแยกตามประเภทเงิน (categorise budget_type) */
    public function actualByBudgetType(): array
    {
        $sql = "SELECT b.title, IFNULL(SUM(i.price * i.qty), 0) AS total
                FROM categorise b
                LEFT JOIN orders o
                    ON JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.pq_budget_type')) = b.code
                    AND o.name = 'order' AND o.thai_year = :yr AND o.status <> 8
                LEFT JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                WHERE b.name = 'budget_type' AND b.code <> 8
                GROUP BY b.code, b.title
                ORDER BY total DESC";
        return Yii::$app->db->createCommand($sql, [':yr' => $this->year])->queryAll();
    }

    /** ยอดแผนเงินบำรุงรวมทั้งปี จาก plan_order */
    public function planRevenueTotal(): float
    {
        if (!$this->tableExists('plan_order')) {
            return 0.0;
        }
        $sql = "SELECT IFNULL(SUM(budget_total), 0) FROM plan_order
                WHERE thai_year = :yr AND deleted_at IS NULL
                AND (plan_budget_type_id = :bt OR plan_budget_type_id IS NULL)";
        return (float) Yii::$app->db->createCommand($sql, [
            ':yr' => $this->year,
            ':bt' => self::BUDGET_TYPE_REVENUE,
        ])->queryScalar();
    }

    /**
     * รายการแผน (plan_item) ที่นับเป็นหมวด ยา/เวชภัณฑ์ — อยู่ใต้ 2.3 ค่าวัสดุ แต่ฝั่งจัดซื้อแยกเป็นหมวดของตัวเอง
     * P93 วัสดุเภสัชกรรม นับเป็นวัสดุ (ผู้ใช้ยืนยัน 2026-09-25)
     */
    public const PLAN_DRUG_ITEMS = ['P92'];

    /**
     * แผนรายเดือนแยกหมวด — ยอดจาก month_1..12 (เดือนปฏิทิน: month_10 = ต.ค.)
     * ไม่ใช้ budget_total เพราะแบบฟอร์มแผนกรอกเป็นรายเดือน และ budget_total ไม่ได้ถูกคำนวณเก็บไว้
     *
     * จำแนกหมวดผ่าน plan_item -> plan_category (ห้ามใช้ plan_type_id/plan_category_id บน plan_order ที่ปนเปื้อน)
     * นับเฉพาะแผนที่เป็นงานจัดซื้อจัดจ้าง — เงินเดือน/ค่าสาธารณูปโภค/ค่าใช้จ่ายอื่นไม่เกี่ยวกับพัสดุ จึงตัดออก
     * @return array [ catKey => [12 ค่าเรียงตามปีงบ], ... ]
     */
    public function planMonthlyByCategory(): array
    {
        $out = [];
        foreach (array_keys(self::CATEGORIES) as $k) {
            $out[$k] = array_fill(0, 12, 0.0);
        }
        if (!$this->tableExists('plan_order')) {
            return $out;
        }
        $drug = "'" . implode("','", self::PLAN_DRUG_ITEMS) . "'";
        $catExpr = "CASE
            WHEN p.plan_item_id IN ($drug) THEN 'drug'
            WHEN pc.code = 'OPS_03' THEN 'material'
            WHEN pc.code IN ('INV_01', 'INV_03') THEN 'asset'
            WHEN pc.code = 'OPS_05' AND (pi.title LIKE 'ค่าจ้าง%' OR pi.title LIKE 'ค่าซ่อม%') THEN 'wage'
        END";
        $months = [];
        foreach (self::FISCAL_MONTHS as $m) {
            $months[] = "IFNULL(SUM(p.month_$m), 0) AS m$m";
        }
        $sql = "SELECT $catExpr AS cat, " . implode(', ', $months) . "
                FROM plan_order p
                JOIN categorise pi ON pi.name = 'plan_item' AND pi.code = p.plan_item_id
                LEFT JOIN categorise pc ON pc.name = 'plan_category' AND pc.code = pi.category_id
                WHERE p.thai_year = :yr AND p.deleted_at IS NULL
                GROUP BY cat";
        $rows = Yii::$app->db->createCommand($sql, [':yr' => $this->year])->queryAll();
        foreach ($rows as $r) {
            if (!isset($out[$r['cat']])) {
                continue;
            }
            foreach (self::FISCAL_MONTHS as $pos => $m) {
                $out[$r['cat']][$pos] = (float) $r["m$m"];
            }
        }
        return $out;
    }

    /** แผนทั้งปีแยกหมวด (ผลรวมแผนรายเดือน) */
    public function planByCategory(): array
    {
        return array_map('array_sum', $this->planMonthlyByCategory());
    }

    /**
     * ตารางเทียบแผนรายหมวด + ธงเตือนเกินแผน
     * @return array [ ['key','label','color','plan','actual','remaining','pct','over'], ... , รวม ]
     */
    public function planComparison(): array
    {
        $actual = $this->actualByCategory();     // ขอซื้อ (ผูกพัน)
        $received = $this->receivedByCategory(); // ตรวจรับ (รับจริง)
        $plan = $this->planByCategory();
        $rows = [];
        $sumPlan = 0.0;
        $sumActual = 0.0;
        $sumReceived = 0.0;
        foreach (self::CATEGORIES as $k => $meta) {
            $p = $plan[$k] ?? 0.0;
            $a = $actual[$k]['total'] ?? 0.0;
            $rc = $received[$k]['total'] ?? 0.0;
            $sumPlan += $p;
            $sumActual += $a;
            $sumReceived += $rc;
            $rows[] = [
                'key' => $k,
                'label' => $meta['label'],
                'cssvar' => $meta['cssvar'],
                'plan' => $p,
                'actual' => $a,
                'received' => $rc,
                'remaining' => $p - $a,
                'pct' => $p > 0 ? round(($a / $p) * 100, 1) : null,
                'over' => $p > 0 && $a > $p,
            ];
        }
        return [
            'rows' => $rows,
            'total' => [
                'plan' => $sumPlan,
                'actual' => $sumActual,
                'received' => $sumReceived,
                'remaining' => $sumPlan - $sumActual,
                'pct' => $sumPlan > 0 ? round(($sumActual / $sumPlan) * 100, 1) : null,
                'over' => $sumPlan > 0 && $sumActual > $sumPlan,
            ],
            'hasPlan' => $sumPlan > 0,
        ];
    }

    /** รายการเตือน: หมวดที่ขอซื้อเกินแผน */
    public function overPlanAlerts(): array
    {
        $cmp = $this->planComparison();
        if (!$cmp['hasPlan']) {
            return [];
        }
        $alerts = [];
        foreach ($cmp['rows'] as $r) {
            if ($r['over']) {
                $alerts[] = $r;
            }
        }
        return $alerts;
    }

    /**
     * ท่อสถานะ (pipeline funnel) — จำนวน + มูลค่า ต่อสถานะ 1-7
     * ใช้ให้เห็นว่างานไปกองอยู่ที่ขั้นไหน
     */
    public function pipeline(): array
    {
        $sql = "SELECT o.status, COUNT(DISTINCT o.id) AS cnt, IFNULL(SUM(i.price * i.qty), 0) AS price
                FROM orders o
                JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                WHERE o.name = 'order' AND o.thai_year = :yr AND o.status BETWEEN 1 AND 7
                GROUP BY o.status";
        $rows = Yii::$app->db->createCommand($sql, [':yr' => $this->year])->queryAll();
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['status']] = ['cnt' => (int) $r['cnt'], 'price' => (float) $r['price']];
        }
        $out = [];
        foreach (self::STATUS_LABELS as $st => $meta) {
            $out[] = [
                'status' => $st,
                'label' => $meta['label'],
                'cssvar' => $meta['cssvar'],
                'cnt' => $map[$st]['cnt'] ?? 0,
                'price' => $map[$st]['price'] ?? 0.0,
            ];
        }
        return $out;
    }

    /**
     * แผงเฝ้าระวัง: ใบที่ "ตรวจรับแล้วแต่ยังไม่เข้าคลัง" (status = 5) เรียงตามค้างนานสุด
     * คืนทั้งรายการ + สรุปตามช่วงอายุ เพื่อชี้เป้าใบที่มีปัญหาโดยไม่ต้องไปค้นเอง
     */
    public function pendingStockWatchlist(int $limit = 10): array
    {
        $today = $this->today();
        $sql = "SELECT o.id, o.pr_number,
                    JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.order_type_name')) AS otn,
                    JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.department')) AS department,
                    JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.gr_number')) AS gr_number,
                    DATE(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.gr_date'))) AS gr_date,
                    DATEDIFF(:today, JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.gr_date'))) AS days,
                    SUM(i.price * i.qty) AS value
                FROM orders o
                JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                WHERE o.name = 'order' AND o.thai_year = :yr AND o.status = " . self::STATUS_RECEIVED . "
                GROUP BY o.id
                ORDER BY days DESC, value DESC";
        $rows = Yii::$app->db->createCommand($sql, [':yr' => $this->year, ':today' => $today])->queryAll();

        $buckets = ['gt60' => 0, 'd31_60' => 0, 'le30' => 0];
        $totalValue = 0.0;
        foreach ($rows as $r) {
            $d = (int) $r['days'];
            $totalValue += (float) $r['value'];
            if ($d > 60) {
                $buckets['gt60']++;
            } elseif ($d >= 31) {
                $buckets['d31_60']++;
            } else {
                $buckets['le30']++;
            }
        }

        return [
            'count' => count($rows),
            'totalValue' => $totalValue,
            'buckets' => $buckets,
            'items' => array_slice($rows, 0, $limit),
        ];
    }

    /**
     * รายการใบ "ตรวจรับแล้วยังไม่เข้าคลัง" (status = 5) สำหรับ drill-down
     * กรองตามประเภทย่อย (order_type_name) หรือหมวดหลัก และตามเดือน (gr_date) ถ้าระบุ
     *
     * @param string|null $subtype ชื่อประเภทย่อยแบบตรงตัว หรือ '(ไม่ระบุ)'
     * @param string|null $catKey  key หมวดหลัก (material/asset/wage/drug) ใช้เมื่อไม่ได้ส่ง subtype
     * @param int|null $month       เดือน (10-9) ยึด gr_date
     */
    public function pendingStockItems(?string $subtype = null, ?string $catKey = null, ?int $month = null): array
    {
        $today = $this->today();
        $params = [':yr' => $this->year, ':today' => $today];
        $where = "o.name = 'order' AND o.thai_year = :yr AND o.status = " . self::STATUS_RECEIVED;

        if ($subtype !== null && $subtype !== '') {
            $where .= " AND COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.order_type_name')), ''), '(ไม่ระบุ)') = :subtype";
            $params[':subtype'] = $subtype;
        } elseif ($catKey !== null && isset(self::CATEGORIES[$catKey])) {
            $where .= ' AND (' . $this->categoryCase() . ") = :catKey";
            $params[':catKey'] = $catKey;
        }
        if ($month !== null && in_array($month, self::FISCAL_MONTHS, true)) {
            $where .= " AND MONTH(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.gr_date'))) = " . (int) $month;
        }

        $sql = "SELECT o.id, o.pr_number,
                    JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.order_type_name')) AS otn,
                    JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.department')) AS department,
                    JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.gr_number')) AS gr_number,
                    DATE(JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.gr_date'))) AS gr_date,
                    DATEDIFF(:today, JSON_UNQUOTE(JSON_EXTRACT(o.data_json, '$.gr_date'))) AS days,
                    SUM(i.price * i.qty) AS value
                FROM orders o
                JOIN orders i ON i.category_id = o.id AND i.name = 'order_item'
                WHERE $where
                GROUP BY o.id
                ORDER BY days DESC, value DESC";
        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $totalValue = 0.0;
        foreach ($rows as $r) {
            $totalValue += (float) $r['value'];
        }
        return ['items' => $rows, 'count' => count($rows), 'totalValue' => $totalValue];
    }

    private function tableExists(string $table): bool
    {
        try {
            return in_array($table, Yii::$app->db->schema->getTableNames(), true);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
