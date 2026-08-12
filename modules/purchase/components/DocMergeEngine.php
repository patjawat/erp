<?php

namespace app\modules\purchase\components;

use Yii;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\hr\models\Employees;
use app\modules\purchase\models\Bond;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\Doc;
use app\modules\purchase\models\DocTemplate;
use app\modules\purchase\models\Order;
use app\modules\pdfTemplate\services\FieldValueResolver;

/**
 * แทนค่าจากฐานข้อมูลลงในข้อความแม่แบบ
 *
 * ใช้ FieldValueResolver ของโมดูล pdfTemplate มาแก้ dot path ไม่ได้เขียนตัวแก้ใหม่
 * เพราะกฎการเดินทีละ segment และการคืนค่าว่างเมื่อทางตันเป็นเรื่องเดียวกัน
 * ถ้าเขียนซ้ำแล้ววันหนึ่งกฎเปลี่ยน สองที่จะให้ผลไม่เหมือนกัน
 *
 * ไวยากรณ์ที่รองรับ
 *
 *   {{org.company_name}}   ค่าเดี่ยว — ชื่อ namespace ต้องอยู่ในชุดที่รู้จัก
 *   {{#items}}...{{/items}} บล็อกวนซ้ำต่อ 1 รายการพัสดุ ใช้ {{item.xxx}} ข้างใน
 *
 * แท็กที่ไม่ได้ขึ้นต้นด้วย namespace ที่รู้จักจะถูกปล่อยไว้เฉย ๆ ไม่ถูกลบ
 * เพราะ {{emblem}} ต้องรอ DocRenderer แทนทีหลัง (ตราครุฑบนจอเป็น URL
 * แต่ใน mPDF ต้องเป็น path ในเครื่อง จึงแทนที่ชั้นเรนเดอร์ไม่ใช่ชั้นนี้)
 *
 * ค่าที่หาไม่ได้ถูกแทนด้วยจุดไข่ปลา ไม่ใช่ช่องว่าง — ช่องว่างบนกระดาษราชการอ่านได้
 * ว่า "ตกหล่น" ส่วนจุดไข่ปลาอ่านได้ว่า "รอกรอกด้วยปากกา" ซึ่งตรงกับความจริงมากกว่า
 */
class DocMergeEngine
{
    /** ความยาวจุดไข่ปลาที่ใช้แทนค่าที่หาไม่ได้ */
    const DOTS = '....................';

    /** namespace ที่ยอมแทนค่า — นอกเหนือจากนี้ปล่อยผ่านให้ชั้นอื่นจัดการ */
    const NAMESPACES = ['org', 'doc', 'ref', 'user'];

    /**
     * แทนค่าทั้งฉบับ
     *
     * ทำบล็อกวนซ้ำก่อนค่าเดี่ยว เพราะข้างในบล็อกมี {{item.xxx}} ที่ต้องถูกคัดลอก
     * ไปตามจำนวนรายการก่อน ถ้าแทนค่าเดี่ยวก่อน {{item.xxx}} ในแม่แบบจะกลายเป็น
     * จุดไข่ปลาไปแล้วตั้งแต่รอบแรก แล้ววนซ้ำได้แต่แถวจุดไข่ปลาเหมือนกันทุกแถว
     */
    public static function merge(?string $html, array $payload): string
    {
        $html = (string) $html;
        if ($html === '') {
            return '';
        }

        foreach (self::BLOCKS as $block => $var) {
            $html = self::mergeBlocks($html, $block, $var, $payload[$block] ?? []);
        }

        return self::mergeScalars($html, $payload);
    }

    /**
     * บล็อกวนซ้ำที่รองรับ — ชื่อบล็อก => ชื่อตัวแปรที่ใช้ข้างใน
     *
     * committee กับ committee_detail เป็นคนละชุดกรรมการ (ตรวจรับพัสดุ กับ
     * กำหนดรายละเอียดคุณลักษณะ) แต่ใช้ชื่อตัวแปร member เหมือนกัน เพราะรูปร่าง
     * ข้อมูลเหมือนกันและแม่แบบหนึ่งฉบับใช้ชุดเดียวเท่านั้น
     *
     * ลำดับใน array นี้ไม่สำคัญ เพราะรูปแบบ {{#committee}} ต้องมี }} ต่อท้ายทันที
     * จึงไม่ไปจับ {{#committee_detail}} โดยบังเอิญ
     */
    const BLOCKS = [
        'items' => 'item',
        'committee' => 'member',
        'committee_detail' => 'member',
    ];

    /**
     * แทนบล็อก {{#items}}...{{/items}}
     *
     * เมื่อไม่มีรายการเลย ยังต้องเหลือแถวจุดไข่ปลาไว้หนึ่งแถว ไม่ใช่ลบบล็อกทิ้ง
     * เพราะตารางที่มีแต่หัวตารางกับบรรทัดรวมเงินทำให้คนอ่านคิดว่าระบบพิมพ์ตกไป
     * ส่วนแถวว่างที่มีเส้นให้เขียนคือสิ่งที่งานพัสดุใช้จริงเวลายังไม่รู้รายการ
     */
    private static function mergeBlocks(string $html, string $block, string $var, array $rows): string
    {
        $pattern = '/\{\{#' . preg_quote($block, '/') . '\}\}(.*?)\{\{\/' . preg_quote($block, '/') . '\}\}/su';

        return preg_replace_callback(
            $pattern,
            static function (array $m) use ($var, $rows): string {
                $body = $m[1];
                if (empty($rows)) {
                    // เก็บชื่อคีย์ที่แม่แบบขอไว้ แล้วสร้างแถวจุดไข่ปลาให้ครบทุกช่อง
                    preg_match_all('/\{\{\s*' . preg_quote($var, '/') . '\.([a-zA-Z0-9_]+)\s*\}\}/u', $body, $keys);
                    $rows = [self::blankRow($keys[1] ?? [])];
                }

                $out = '';
                foreach ($rows as $row) {
                    $out .= preg_replace_callback(
                        '/\{\{\s*' . preg_quote($var, '/') . '\.([a-zA-Z0-9_]+)\s*\}\}/u',
                        static function (array $mm) use ($row): string {
                            $value = $row[$mm[1]] ?? null;

                            return ($value === null || $value === '')
                                ? self::DOTS
                                : self::escape((string) $value);
                        },
                        $body
                    );
                }

                return $out;
            },
            $html
        );
    }

    /**
     * แทนค่าเดี่ยว {{namespace.path}}
     *
     * ส่วน path รับอักขระอะไรก็ได้ที่ไม่ใช่ปีกกาและช่องว่าง ไม่ได้จำกัดเป็น a-z0-9_
     * เพราะถ้าจำกัด แท็กที่พิมพ์ผิดเป็นภาษาไทย (ซึ่งเกิดง่ายมากเวลาแก้แม่แบบแล้ว
     * สลับแป้นไม่ทัน) จะไม่เข้าเงื่อนไขเลยแล้วถูกพิมพ์ลงกระดาษเป็น {{ref.ไม่มีจริง}}
     * ขณะที่แท็กพิมพ์ผิดเป็นอังกฤษกลายเป็นจุดไข่ปลา — พฤติกรรมสองแบบกับความผิด
     * ชนิดเดียวกันทำให้ผู้ใช้เดาไม่ถูกว่าระบบทำอะไร
     */
    private static function mergeScalars(string $html, array $payload): string
    {
        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\.([^{}\s]+?)\s*\}\}/u',
            static function (array $m) use ($payload): string {
                [$full, $ns, $path] = $m;

                // แท็กนอกชุด namespace ที่รู้จักต้องกลับไปเหมือนเดิมทุกตัวอักษร
                if (!in_array($ns, self::NAMESPACES, true)) {
                    return $full;
                }

                $bag = $payload[$ns] ?? [];

                // แยก "ข้อมูลว่าง" ออกจาก "แท็กพิมพ์ผิด" — บนกระดาษได้จุดไข่ปลาเหมือนกัน
                // แต่กรณีหลังเขียน log ไว้ เพราะเป็นความผิดที่ต้องไปแก้แม่แบบ ไม่ใช่
                // เรื่องที่รอผู้ใช้กรอกด้วยปากกา ถ้าไม่บอกไว้เลยจะไม่มีใครรู้ว่าแม่แบบมีแท็กเสีย
                $first = explode('.', $path)[0];
                if (is_array($bag) && !array_key_exists($first, $bag)) {
                    Yii::warning(
                        'DocMergeEngine: แม่แบบอ้างแท็กที่ไม่มีในชุดข้อมูล — {{' . $ns . '.' . $path . '}}',
                        __METHOD__
                    );
                }

                $value = FieldValueResolver::resolve($bag, $path);

                return ($value === '') ? self::DOTS : self::escape($value);
            },
            $html
        );
    }

    /**
     * หนีอักขระ HTML ของค่าที่แทนลงไป
     *
     * ค่าจากฐานข้อมูลเป็นข้อความล้วน ถ้าชื่อผู้ขายมี & หรือ < อยู่ (เกิดขึ้นจริงกับ
     * ชื่อห้าง "เอ แอนด์ บี") แล้วปล่อยลงไปตรง ๆ HTMLPurifier ตอนบันทึกจะตัดทิ้ง
     * ไปทั้งท่อน ทำให้ชื่อผู้ขายหายจากเอกสารโดยไม่มีใครรู้
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * แถวเปล่าที่มีแต่จุดไข่ปลา ใช้เมื่อเรื่องต้นทางยังไม่มีข้อมูลในบล็อกนั้น
     *
     * สร้างคีย์ตามที่แม่แบบขอจริง ไม่ใช้ชุดคีย์คงที่ เพราะแต่ละบล็อกใช้คีย์ต่างกัน
     * (รายการพัสดุใช้ qty/unit ส่วนคณะกรรมการใช้ name/role) ถ้าใช้ชุดคงที่ ช่องที่
     * ไม่อยู่ในชุดจะกลายเป็นจุดไข่ปลายาวเท่ากันหมดโดยไม่สนบริบท
     *
     * @param string[] $keys
     */
    private static function blankRow(array $keys): array
    {
        $short = ['no', 'qty', 'unit'];

        $row = [];
        foreach ($keys as $key) {
            $row[$key] = in_array($key, $short, true) ? '......' : self::DOTS;
        }
        if (array_key_exists('no', $row)) {
            $row['no'] = '1';
        }

        return $row;
    }

    // ────────────────────────────────────────────────────────────────────────
    //  การประกอบ payload
    // ────────────────────────────────────────────────────────────────────────

    /**
     * ประกอบข้อมูลทั้งชุดสำหรับเอกสาร 1 ฉบับ
     *
     * @param Doc $doc เอกสารที่กำลังสร้าง (ใช้เลขที่/วันที่/ปีงบ)
     * @param object|null $ref เรื่องต้นทางตาม ref_type — null เมื่อไม่ผูกเรื่อง
     */
    public static function payload(Doc $doc, $ref = null): array
    {
        return [
            'org' => self::orgPayload(),
            'doc' => self::docPayload($doc),
            'user' => self::userPayload(),
            'ref' => self::refPayload($doc->ref_type, $ref),
            'items' => self::itemsPayload($doc->ref_type, $ref),
            'committee' => self::committeePayload($ref, 'ListCommittee'),
            'committee_detail' => self::committeePayload($ref, 'ListCommitteeDetail'),
        ];
    }

    /**
     * รายชื่อคณะกรรมการสำหรับบล็อก {{#committee}} / {{#committee_detail}}
     *
     * โครงสร้างของ ERP นี้เก็บกรรมการเป็นแถวลูกในตาราง orders เดียวกัน แยกด้วย
     * คอลัมน์ name (committee = กรรมการตรวจรับ, committee_detail = กรรมการกำหนด
     * รายละเอียด) ค่าที่ต้องใช้อยู่ใน data_json ของแถวลูกนั้น
     *
     * @param object|null $ref
     */
    private static function committeePayload($ref, string $method): array
    {
        if ($ref === null || !method_exists($ref, $method)) {
            return [];
        }

        $out = [];
        try {
            foreach ($ref->$method() as $i => $row) {
                $data = is_array($row->data_json)
                    ? $row->data_json
                    : (json_decode((string) $row->data_json, true) ?: []);

                $out[] = [
                    'no' => (string) ($i + 1),
                    'name' => (string) ($data['emp_fullname'] ?? ''),
                    'position' => (string) ($data['emp_position'] ?? ''),
                    'role' => (string) ($data['committee_name'] ?? ''),
                ];
            }
        } catch (\Throwable $e) {
            Yii::warning('DocMergeEngine: อ่านรายชื่อกรรมการไม่สำเร็จ — ' . $e->getMessage(), __METHOD__);
        }

        return $out;
    }

    private static function orgPayload(): array
    {
        $info = SiteHelper::getInfo();

        $directorFullname = '';
        try {
            $directorFullname = SiteHelper::viewDirector()['fullname'] ?? '';
        } catch (\Throwable $e) {
            // ยังไม่ได้ตั้งค่าผู้อำนวยการในระบบ — ปล่อยให้กลายเป็นจุดไข่ปลาบนกระดาษ
        }

        return [
            'company_name' => $info['company_name'] ?? '',
            // ชื่อพร้อมที่อยู่ ใช้ในช่อง "ส่วนราชการ" ของบันทึกข้อความชุดเดิม
            // (ตรงกับ ${org_name_full} ของ TemplateProcessor)
            'company_full' => trim(($info['company_name'] ?? '') . ' ' . ($info['address'] ?? '')),
            'address' => $info['address'] ?? '',
            'province' => $info['province'] ?? '',
            'phone' => $info['phone'] ?? '',
            'doc_number' => $info['doc_number'] ?? '',
            'director_fullname' => $directorFullname ?: ($info['director_name'] ?? ''),
            'director_position' => $info['director_position'] ?? '',
            'leader_fullname' => $info['leader_fullname'] ?? '',
            'leader_position' => $info['leader_position'] ?? '',
        ];
    }

    private static function docPayload(Doc $doc): array
    {
        return [
            'doc_no' => (string) $doc->doc_no,
            'title' => (string) $doc->title,
            'thai_year' => (string) $doc->thai_year,
            'date_thai' => self::thaiDate($doc->doc_date),
        ];
    }

    private static function userPayload(): array
    {
        try {
            $employee = Employees::findOne(['user_id' => Yii::$app->user->id]);
            if ($employee === null) {
                return ['fullname' => '', 'position' => '', 'department' => ''];
            }

            return [
                'fullname' => (string) $employee->fullname,
                // ชื่อตำแหน่งของ ERP นี้ประกอบจากสองคีย์ใน data_json เสมอ
                // (ชื่อสายงาน + ระดับ) ตามที่ Order::getUserReq() ทำอยู่
                'position' => trim(($employee->data_json['position_name_text'] ?? '')
                    . ($employee->data_json['position_level_text'] ?? '')),
                'department' => (string) $employee->departmentName(),
            ];
        } catch (\Throwable $e) {
            return ['fullname' => '', 'position' => '', 'department' => ''];
        }
    }

    /** ค่าจากเรื่องต้นทาง — คีย์ชุดเดียวกันทุก ref_type เพื่อให้แม่แบบสลับไปใช้ได้ */
    private static function refPayload(string $refType, $ref): array
    {
        $empty = [
            'doc_no' => '', 'title' => '', 'budget' => '', 'budget_text' => '',
            'vendor_name' => '', 'item_count' => '', 'date_thai' => '',
        ];

        if ($ref === null) {
            return $empty;
        }

        try {
            switch ($refType) {
                case DocTemplate::REF_ORDER:
                    return self::orderPayload($ref);
                case DocTemplate::REF_CONTRACT:
                    return self::contractPayload($ref);
                case DocTemplate::REF_BOND:
                    return self::bondPayload($ref);
            }
        } catch (\Throwable $e) {
            Yii::warning('DocMergeEngine: ประกอบค่าจากเรื่องต้นทางไม่สำเร็จ — ' . $e->getMessage(), __METHOD__);
        }

        return $empty;
    }

    private static function orderPayload(Order $order): array
    {
        $budget = (float) $order->SumPo();
        $items = $order->ListOrderItems();

        return [
            'doc_no' => (string) ($order->pr_number ?: $order->po_number),
            'pr_number' => (string) $order->pr_number,
            'po_number' => (string) $order->po_number,
            'title' => self::orderTitle($order, $items),
            'budget' => number_format($budget, 2),
            'budget_text' => self::bahtText($budget),
            'vendor_name' => (string) ($order->vendor->title ?? ''),
            'item_count' => (string) count($items),
            'date_thai' => self::thaiDate($order->data_json['pr_create_date'] ?? null),
            'department' => (string) ($order->getUserReq()['department'] ?? ''),
            'requester' => (string) ($order->getUserReq()['fullname'] ?? ''),
            'product_type' => (string) ($order->data_json['product_type_name'] ?? ''),

            // สามค่านี้เพิ่มเพื่อให้แม่แบบที่แปลงมาจากเอกสารพิมพ์ชุดเดิมใช้ค่าเดียวกับ
            // ที่ชุดเดิมใช้ได้ตรง ๆ — ของเดิมอ่านจาก viewLeaderUser() (หัวหน้าที่ระบุใน
            // data_json.leader1 ของใบขอซื้อ) ไม่ใช่หัวหน้าจากหน้าตั้งค่าหน่วยงาน
            // ถ้าใช้ org.leader_* แทน จะได้ชื่อคนละคนกับที่พิมพ์อยู่ทุกวันนี้
            'leader_name' => (string) ($order->viewLeaderUser()['fullname'] ?? ''),
            'leader_position' => (string) ($order->viewLeaderUser()['position_name'] ?? ''),
            'comment' => (string) ($order->data_json['comment'] ?? ''),
        ] + self::orderExtras($order, $budget);
    }

    /**
     * ค่าที่เอกสารชุดเดิมใช้ ดึงจาก data_json ของใบขอซื้อและจากทะเบียนผู้ขาย
     *
     * แยกออกมาเป็นเมธอดของตัวเองเพราะมีหลายสิบคีย์ ถ้ายัดรวมใน orderPayload
     * จะอ่านไม่ออกว่าคีย์ไหนเป็นค่าหลักที่ทุกเอกสารใช้ กับคีย์ไหนมีไว้เพื่อเอกสาร
     * เฉพาะใบ ทุกคีย์ที่นี่มาจากการสำรวจ data_json ของใบขอซื้อจริงในระบบ
     * ไม่ได้เดาจากชื่อตัวแปรในไฟล์ .docx
     */
    private static function orderExtras(Order $order, float $budget): array
    {
        $d = is_array($order->data_json)
            ? $order->data_json
            : (json_decode((string) $order->data_json, true) ?: []);

        $vendor = $order->vendor;
        $vj = [];
        if ($vendor) {
            $vj = is_array($vendor->data_json)
                ? $vendor->data_json
                : (json_decode((string) $vendor->data_json, true) ?: []);
        }

        $get = static fn (string $key): string => (string) ($d[$key] ?? '');

        return [
            // ── เลขที่เอกสารในสายงานจัดซื้อ ──
            'pq_number' => (string) $order->pq_number,
            'gr_number' => (string) $order->gr_number,
            'qr_number' => $get('qr_number'),

            // ── วันที่ต่าง ๆ ──
            'po_date' => self::thaiDate($get('po_date') ?: null),
            'gr_date' => self::thaiDate(substr($get('gr_date'), 0, 10) ?: null),
            'due_date' => self::thaiDate($get('due_date') ?: null),
            'delivery_date' => self::thaiDate($get('delivery_date') ?: null),
            'warranty_date' => self::thaiDate($get('warranty_date') ?: null),
            'signing_date' => self::thaiDate($get('signing_date') ?: null),
            'credit_days' => $get('credit_days'),

            // ── ชนิดพัสดุและแหล่งเงิน ──
            'order_type' => $get('order_type_name'),
            'budget_type' => $get('pq_budget_type_name'),
            'budget_group' => $get('pq_budget_group_name'),
            'purchase_type' => $get('pq_purchase_type_name'),
            'method_get' => $get('pq_method_get_name'),
            'consideration' => $get('pq_consideration'),
            'condition_name' => $get('pq_condition_name'),
            'income_reason' => $get('pq_income_reason'),
            'thai_year' => (string) $order->thai_year,

            // ── ผลการตรวจรับ ──
            'item_checker' => $get('order_item_checker'),
            'fine' => $get('fine'),

            // ── ผู้ขาย (ค่าในใบขอซื้อมาก่อน ถ้าว่างจึงใช้จากทะเบียนผู้ขาย) ──
            'vendor_address' => $get('vendor_address') ?: (string) ($vj['address'] ?? ''),
            'vendor_phone' => $get('vendor_phone') ?: (string) ($vj['phone'] ?? ''),
            'vendor_tax' => $get('vendor_tax') ?: (string) $order->vendor_id,
            'vendor_account_no' => $get('account_number') ?: (string) ($vj['account_number'] ?? ''),
            'vendor_account_name' => $get('account_name') ?: (string) ($vj['account_name'] ?? ''),
            'vendor_bank' => (string) ($vj['bank_name'] ?? ''),
            'vendor_contact' => $get('contact_name') ?: (string) ($vj['contact_name'] ?? ''),
            'vendor_contact_position' => $get('contact_position') ?: (string) ($vj['contact_position'] ?? ''),

            // ── ยอดเงิน ──
            'total_price' => number_format((float) ($d['total_price'] ?? $budget), 2),
            'total_price_text' => self::bahtText((float) ($d['total_price'] ?? $budget)),
        ];
    }

    /**
     * ชื่อรายการที่จะขึ้นในช่อง "รายการพัสดุ/งานจ้าง"
     *
     * ใบขอซื้อของ ERP นี้ไม่มีช่องชื่อเรื่องของตัวเอง จึงต้องประกอบจากรายการลูก
     * รายการเดียวใช้ชื่อรายการนั้นตรง ๆ หลายรายการใช้ชื่อรายการแรกแล้วบอกว่า
     * มีอีกกี่รายการ — ดีกว่าเอาชื่อทุกรายการมาต่อกันจนล้นช่องในตาราง
     */
    private static function orderTitle(Order $order, array $items): string
    {
        if (!empty($order->data_json['product_type_name'])) {
            $type = (string) $order->data_json['product_type_name'];
        } else {
            $type = '';
        }

        if (empty($items)) {
            return $type;
        }

        $first = (string) ($items[0]->product->title ?? '');
        if (count($items) === 1) {
            return $first !== '' ? $first : $type;
        }

        return $first . ' และรายการอื่นอีก ' . (count($items) - 1) . ' รายการ';
    }

    private static function contractPayload(Contract $contract): array
    {
        $budget = (float) $contract->budget;

        return [
            'doc_no' => (string) ($contract->contract_no ?: $contract->doc_no),
            'contract_no' => (string) $contract->contract_no,
            'title' => (string) $contract->title,
            'budget' => number_format($budget, 2),
            'budget_text' => self::bahtText($budget),
            'vendor_name' => (string) $contract->partyName(),
            'item_count' => '',
            'date_thai' => self::thaiDate($contract->sign_date),
            'start_date' => self::thaiDate($contract->start_date),
            'end_date' => self::thaiDate($contract->end_date),
            'contract_type' => (string) $contract->typeName(),
        ];
    }

    private static function bondPayload(Bond $bond): array
    {
        $amount = (float) $bond->amount;

        return [
            'doc_no' => (string) $bond->doc_no,
            'title' => (string) $bond->title,
            'budget' => number_format($amount, 2),
            'budget_text' => self::bahtText($amount),
            'vendor_name' => (string) $bond->partyName(),
            'item_count' => '',
            'date_thai' => self::thaiDate($bond->place_date),
            'expiry_date' => self::thaiDate($bond->expiry_date),
            'bond_type' => (string) $bond->typeName(),
            'bond_form' => (string) $bond->bondFormName(),
            'issuer' => (string) $bond->issuer,
        ];
    }

    /** รายการพัสดุสำหรับบล็อก {{#items}} — มีเฉพาะเมื่อผูกกับใบขอซื้อ */
    private static function itemsPayload(string $refType, $ref): array
    {
        if ($refType !== DocTemplate::REF_ORDER || $ref === null) {
            return [];
        }

        $out = [];
        try {
            foreach ($ref->ListOrderItems() as $i => $item) {
                $qty = (float) $item->qty;
                $price = (float) $item->price;

                $out[] = [
                    'no' => (string) ($i + 1),
                    'name' => (string) ($item->product->title ?? ''),
                    'qty' => rtrim(rtrim(number_format($qty, 2), '0'), '.'),
                    'unit' => (string) ($item->product->data_json['unit'] ?? ''),
                    'price' => number_format($price, 2),
                    'amount' => number_format($qty * $price, 2),
                ];
            }
        } catch (\Throwable $e) {
            Yii::warning('DocMergeEngine: อ่านรายการพัสดุไม่สำเร็จ — ' . $e->getMessage(), __METHOD__);
        }

        return $out;
    }

    /**
     * จำนวนเงินเป็นตัวหนังสือแบบเต็มรูป เช่น "หนึ่งหมื่นสองพันบาทถ้วน"
     *
     * ทำไมไม่เรียก AppHelper::convertNumberToWords() ที่มีอยู่แล้ว
     *
     * ตัวนั้นอ่านหลักหน่วยที่เป็น 1 ผิดเมื่อมีหลักสูงกว่าตามมา — เงื่อนไขของมันเช็ค
     * ว่า $string ยังว่างอยู่หรือไม่ ทั้งที่วนจากหลักหน่วยขึ้นไป ตอนถึงหลักหน่วย
     * $string ย่อมว่างเสมอ ผลคือ 21 ได้ "ยี่สิบหนึ่ง" แทน "ยี่สิบเอ็ด" และ 101 ได้
     * "หนึ่งร้อยหนึ่ง" แทน "หนึ่งร้อยเอ็ด" นอกจากนั้นยังตัดเศษสตางค์ทิ้งด้วย intval
     * และไม่เติมคำว่า "บาท" ให้
     *
     * เลือกเขียนใหม่ในโมดูลนี้ ไม่ไปแก้ AppHelper เพราะตัวนั้นถูกเรียกจากงานสัญญา
     * และเอกสารพิมพ์ชุดเดิมด้วย การแก้จึงเปลี่ยนผลลัพธ์ของเอกสารนอกขอบเขตงานนี้
     * (ข้อบกพร่องนั้นถูกแจ้งไว้แยกเพื่อให้ตัดสินใจแก้ทั้งระบบต่างหาก)
     */
    public static function bahtText(float $amount): string
    {
        $negative = $amount < 0;
        $amount = round(abs($amount), 2);

        $baht = (int) floor($amount);
        // คูณด้วย 100 แล้วปัดทันที ไม่ปล่อยให้ floating point ทำให้ 0.07 กลายเป็น 6 สตางค์
        $satang = (int) round(($amount - $baht) * 100);
        if ($satang === 100) {
            $baht++;
            $satang = 0;
        }

        $text = self::thaiInteger($baht) . 'บาท';
        $text .= $satang > 0
            ? self::thaiInteger($satang) . 'สตางค์'
            : 'ถ้วน';

        return ($negative ? 'ลบ' : '') . $text;
    }

    /**
     * จำนวนเต็มเป็นตัวหนังสือไทย
     *
     * วนจากหลักซ้ายไปขวาโดยรู้ตำแหน่งของแต่ละหลักล่วงหน้า (ไม่ใช่วนจากขวาแล้วเดา)
     * เพราะกฎ "เอ็ด" ต้องรู้ว่ามีหลักสูงกว่าอยู่หรือไม่ ซึ่งวนจากขวาจะยังไม่รู้
     * ส่วนที่เกินหลักล้านวนซ้ำตัวเองเพื่อรองรับวงเงินหลายล้านตามแบบการอ่านของไทย
     */
    private static function thaiInteger(int $number): string
    {
        if ($number === 0) {
            return 'ศูนย์';
        }

        if ($number >= 1000000) {
            $remainder = $number % 1000000;

            return self::thaiInteger(intdiv($number, 1000000)) . 'ล้าน'
                . ($remainder > 0 ? self::thaiInteger($remainder) : '');
        }

        $units = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
        $places = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน'];

        $digits = str_split((string) $number);
        $length = count($digits);
        $out = '';

        foreach ($digits as $index => $digit) {
            $digit = (int) $digit;
            if ($digit === 0) {
                continue;
            }

            $place = $length - $index - 1;

            if ($place === 0 && $digit === 1 && $length > 1) {
                // 21 = ยี่สิบเอ็ด แต่ 1 = หนึ่ง — ต่างกันที่มีหลักสูงกว่าหรือไม่
                $out .= 'เอ็ด';
            } elseif ($place === 1 && $digit === 1) {
                $out .= 'สิบ';
            } elseif ($place === 1 && $digit === 2) {
                $out .= 'ยี่สิบ';
            } else {
                $out .= $units[$digit] . $places[$place];
            }
        }

        return $out;
    }

    /**
     * วันที่แบบไทยเต็มรูป เช่น 12 สิงหาคม 2569
     *
     * ใช้ 'long' (ชื่อเดือนเต็ม) ไม่ใช่ 'medium' (ส.ค.) เพราะระเบียบงานสารบรรณ
     * กำหนดให้เขียนชื่อเดือนเต็มในหนังสือราชการ ถ้าหน่วยงานต้องการแบบย่อ
     * แก้ที่นี่ที่เดียวแล้วเอกสารทุกฉบับเปลี่ยนตาม
     */
    public static function thaiDate(?string $date): string
    {
        if (empty($date) || $date === '0000-00-00') {
            return '';
        }

        try {
            return (string) Yii::$app->thaiFormatter->asDate($date, 'long');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    //  รายการฟิลด์สำหรับหน้าจัดการแม่แบบ
    // ────────────────────────────────────────────────────────────────────────

    /**
     * แท็กที่ใช้ได้ พร้อมคำอธิบาย — แสดงเป็นรายการให้คลิกแทรกในหน้าแก้แม่แบบ
     *
     * ประกาศไว้ที่เดียวกับตัวแทนค่า ถ้าเพิ่มคีย์ใน payload แล้วลืมเพิ่มที่นี่
     * ผู้ใช้จะไม่รู้ว่ามีแท็กนั้นให้ใช้ ซึ่งเท่ากับไม่มี
     */
    public static function fieldCatalog(string $refType = DocTemplate::REF_ORDER): array
    {
        $common = [
            'หน่วยงาน' => [
                'org.company_name' => 'ชื่อหน่วยงาน',
                'org.company_full' => 'ชื่อหน่วยงาน + ที่อยู่',
                'org.address' => 'ที่อยู่',
                'org.province' => 'จังหวัด',
                'org.phone' => 'โทรศัพท์',
                'org.director_fullname' => 'ชื่อผู้อำนวยการ',
                'org.director_position' => 'ตำแหน่งผู้อำนวยการ',
                'org.leader_fullname' => 'ชื่อหัวหน้ากลุ่มงาน',
                'org.leader_position' => 'ตำแหน่งหัวหน้ากลุ่มงาน',
            ],
            'ตัวเอกสาร' => [
                'doc.doc_no' => 'เลขที่หนังสือ',
                'doc.date_thai' => 'วันที่หนังสือ (ไทย)',
                'doc.thai_year' => 'ปีงบประมาณ',
                'doc.title' => 'ชื่อเอกสาร',
                'emblem' => 'ตราครุฑ (ขนาดตามที่เลือกบนหน้าแก้ไข)',
            ],
            'ผู้จัดทำ' => [
                'user.fullname' => 'ชื่อผู้จัดทำ',
                'user.position' => 'ตำแหน่งผู้จัดทำ',
                'user.department' => 'หน่วยงานผู้จัดทำ',
            ],
        ];

        switch ($refType) {
            case DocTemplate::REF_ORDER:
                $common['เรื่องต้นทาง (ใบขอซื้อ)'] = [
                    'ref.doc_no' => 'เลขที่ใบขอซื้อ/ใบสั่งซื้อ',
                    'ref.pr_number' => 'เลขที่ใบขอซื้อ (PR)',
                    'ref.po_number' => 'เลขที่ใบสั่งซื้อ (PO)',
                    'ref.title' => 'รายการพัสดุ (สรุป)',
                    'ref.budget' => 'วงเงิน (ตัวเลข)',
                    'ref.budget_text' => 'วงเงิน (ตัวหนังสือ)',
                    'ref.vendor_name' => 'ชื่อผู้ขาย',
                    'ref.item_count' => 'จำนวนรายการ',
                    'ref.date_thai' => 'วันที่ขอซื้อ (ไทย)',
                    'ref.department' => 'หน่วยงานที่ขอ',
                    'ref.requester' => 'ผู้ขอซื้อ',
                    'ref.product_type' => 'ประเภทพัสดุ',
                    'ref.leader_name' => 'หัวหน้าผู้ขออนุมัติ',
                    'ref.leader_position' => 'ตำแหน่งหัวหน้าผู้ขออนุมัติ',
                    'ref.comment' => 'เหตุผลที่ขอซื้อ',
                ];
                $common['เรื่องต้นทาง (เลขที่/วันที่เพิ่มเติม)'] = [
                    'ref.pq_number' => 'เลขทะเบียนคุม',
                    'ref.gr_number' => 'เลขที่ใบส่งของ/ตรวจรับ',
                    'ref.qr_number' => 'เลขที่ใบเสนอราคา',
                    'ref.po_date' => 'วันที่ใบสั่งซื้อ (ไทย)',
                    'ref.gr_date' => 'วันที่ตรวจรับ (ไทย)',
                    'ref.due_date' => 'วันครบกำหนดส่งมอบ (ไทย)',
                    'ref.delivery_date' => 'วันส่งมอบ (ไทย)',
                    'ref.warranty_date' => 'วันสิ้นสุดรับประกัน (ไทย)',
                    'ref.signing_date' => 'วันลงนาม (ไทย)',
                    'ref.credit_days' => 'จำนวนวันที่ให้ส่งมอบ',
                ];
                $common['เรื่องต้นทาง (วิธีจัดซื้อ/แหล่งเงิน)'] = [
                    'ref.order_type' => 'ชนิดพัสดุ (เช่น วัสดุบริโภค)',
                    'ref.budget_type' => 'แหล่งเงิน (เช่น เงินบำรุง)',
                    'ref.budget_group' => 'หมวดงบ',
                    'ref.purchase_type' => 'วิธีจัดซื้อ (เช่น เฉพาะเจาะจง)',
                    'ref.method_get' => 'ซื้อ/จ้าง',
                    'ref.consideration' => 'เกณฑ์การพิจารณา',
                    'ref.condition_name' => 'เงื่อนไขวงเงิน',
                    'ref.income_reason' => 'เหตุผลความจำเป็น',
                    'ref.thai_year' => 'ปีงบประมาณของใบขอซื้อ',
                    'ref.item_checker' => 'ผลการตรวจรับ',
                    'ref.fine' => 'ค่าปรับ',
                ];
                $common['เรื่องต้นทาง (ผู้ขาย)'] = [
                    'ref.vendor_address' => 'ที่อยู่ผู้ขาย',
                    'ref.vendor_phone' => 'โทรศัพท์ผู้ขาย',
                    'ref.vendor_tax' => 'เลขประจำตัวผู้เสียภาษี',
                    'ref.vendor_account_no' => 'เลขที่บัญชีธนาคาร',
                    'ref.vendor_account_name' => 'ชื่อบัญชี',
                    'ref.vendor_bank' => 'ธนาคาร',
                    'ref.vendor_contact' => 'ผู้ติดต่อ',
                    'ref.vendor_contact_position' => 'ตำแหน่งผู้ติดต่อ',
                    'ref.total_price' => 'ยอดรวมตามใบสั่งซื้อ (ตัวเลข)',
                    'ref.total_price_text' => 'ยอดรวมตามใบสั่งซื้อ (ตัวหนังสือ)',
                ];
                $common['ตารางรายการพัสดุ'] = [
                    '#items' => 'เปิดบล็อกวนซ้ำ — วางเป็น {{#items}} ก่อนแถวตาราง',
                    '/items' => 'ปิดบล็อกวนซ้ำ — วางเป็น {{/items}} หลังแถวตาราง',
                    'item.no' => 'ลำดับ',
                    'item.name' => 'ชื่อรายการ',
                    'item.qty' => 'จำนวน',
                    'item.unit' => 'หน่วยนับ',
                    'item.price' => 'ราคาต่อหน่วย',
                    'item.amount' => 'รวมเงิน',
                ];
                $common['ตารางคณะกรรมการ'] = [
                    '#committee' => 'เปิดบล็อก กก.ตรวจรับพัสดุ — {{#committee}}',
                    '/committee' => 'ปิดบล็อก กก.ตรวจรับพัสดุ — {{/committee}}',
                    '#committee_detail' => 'เปิดบล็อก กก.กำหนดรายละเอียด — {{#committee_detail}}',
                    '/committee_detail' => 'ปิดบล็อก กก.กำหนดรายละเอียด — {{/committee_detail}}',
                    'member.no' => 'ลำดับ',
                    'member.name' => 'ชื่อกรรมการ',
                    'member.position' => 'ตำแหน่งในหน่วยงาน',
                    'member.role' => 'ตำแหน่งในคณะกรรมการ',
                ];
                break;

            case DocTemplate::REF_CONTRACT:
                $common['เรื่องต้นทาง (สัญญา)'] = [
                    'ref.doc_no' => 'เลขที่สัญญา',
                    'ref.contract_no' => 'เลขที่สัญญาตามที่ออกจริง',
                    'ref.title' => 'ชื่องานตามสัญญา',
                    'ref.budget' => 'วงเงินตามสัญญา (ตัวเลข)',
                    'ref.budget_text' => 'วงเงินตามสัญญา (ตัวหนังสือ)',
                    'ref.vendor_name' => 'ชื่อคู่สัญญา',
                    'ref.contract_type' => 'ประเภทสัญญา',
                    'ref.date_thai' => 'วันลงนาม (ไทย)',
                    'ref.start_date' => 'วันเริ่มสัญญา (ไทย)',
                    'ref.end_date' => 'วันสิ้นสุดสัญญา (ไทย)',
                ];
                break;

            case DocTemplate::REF_BOND:
                $common['เรื่องต้นทาง (หลักประกัน)'] = [
                    'ref.doc_no' => 'เลขที่หลักประกัน',
                    'ref.title' => 'รายการ/โครงการที่ค้ำ',
                    'ref.budget' => 'วงเงินหลักประกัน (ตัวเลข)',
                    'ref.budget_text' => 'วงเงินหลักประกัน (ตัวหนังสือ)',
                    'ref.vendor_name' => 'ผู้วางหลักประกัน',
                    'ref.bond_type' => 'ประเภทหลักประกัน',
                    'ref.bond_form' => 'รูปแบบหลักประกัน',
                    'ref.issuer' => 'ธนาคาร/ผู้ออกหลักฐาน',
                    'ref.date_thai' => 'วันที่วาง (ไทย)',
                    'ref.expiry_date' => 'วันสิ้นอายุ (ไทย)',
                ];
                break;
        }

        return $common;
    }
}
