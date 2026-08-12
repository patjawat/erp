<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * แปลงเอกสารพิมพ์ชุดเดิมที่เหลือเป็นแม่แบบ HTML ที่แก้บนจอได้
 *
 * ถ้อยคำทุกฉบับถอดมาจากไฟล์ใน web/msword/purchase_*.docx ตัวจริง (อ่านจาก
 * word/document.xml) แล้วแทนตัวแปรของ TemplateProcessor ด้วยแท็กของ
 * DocMergeEngine ทีละตัว ตารางรายการพัสดุใช้บล็อก {{#items}} ตารางกรรมการใช้
 * {{#committee}} (กก.ตรวจรับ) หรือ {{#committee_detail}} (กก.กำหนดรายละเอียด)
 *
 * ค่าที่ไม่มีแหล่งข้อมูลในระบบจะปล่อยเป็นจุดไข่ปลาให้กรอกด้วยปากกาหรือแก้บนจอ
 * ไม่สร้างแท็กลอย ๆ ที่ไม่มีอะไรมาเติม เพราะแท็กที่เติมไม่ได้จะกลายเป็นจุดไข่ปลา
 * อยู่ดี แต่หลอกให้คนอ่านแม่แบบคิดว่าระบบจะเติมให้
 *
 * สิ่งที่แก้จากของเดิมโดยเจตนา — ทุกข้อเป็นข้อผิดพลาดที่ติดมากับไฟล์ .docx
 * ถ้าคัดลอกมาตรง ๆ จะพาความผิดพลาดมาด้วย และแก้บนหน้าจัดการแม่แบบได้ทีหลัง
 *
 *   purchase_2   สะกดผิด "ผู้อำนายการ" -> "ผู้อำนวยการ", "ตาที่" -> "ตามที่"
 *   purchase_10  ชื่อผู้ขาย "ร้านเทคนิค โอ.เอ" เขียนฝังไว้ในไฟล์ -> {{ref.vendor_name}}
 *                "จังหวัดเลย" ฝังไว้ -> จังหวัด{{org.province}}
 *                ข้อความซ้ำ "(...) นั้น (...) นั้น" -> เหลือครั้งเดียว
 *   purchase_12  มี "${26 เมษายน 2566}" คือวันที่ถูกพิมพ์ไว้ในรูปตัวแปร
 *                -> {{ref.date_thai}} (วันที่ขอซื้อของใบนั้น)
 *   purchase_5   "การจ้งก่อสร้าง" -> "การจ้างก่อสร้าง", "จ้อง" -> "ต้อง",
 *                "อนุมติ"/"จ่าเงิน" -> "อนุมัติ"/"จ่ายเงิน"
 *
 * สิ่งที่ยังไม่แตะและต้องให้งานพัสดุตัดสิน
 *
 *   - ชื่อในเมนูของ purchase_6 คือ "รายงานผลการพิจารณาและขออนุมัติสั่งซื้อสั่งจ้าง"
 *     แต่เนื้อในไฟล์เป็น "รายงานผลการตรวจรับพัสดุ" ซึ่งซ้ำกับ purchase_10
 *     ที่นี่ใช้ชื่อตามเมนู (เพราะผู้ใช้จำตำแหน่งปุ่มไว้) และคงเนื้อตามไฟล์
 *   - ข้อกฎหมายและหนังสือเวียนที่อ้างในทุกฉบับยกมาตามเดิม ยังไม่ได้ตรวจว่าฉบับที่
 *     อ้างยังใช้อยู่หรือถูกยกเลิกไปแล้ว
 */
final class m260813_000001_seed_legacy_doc_templates extends Migration
{
    private const REVIEW = 'แปลงจากไฟล์ .docx ชุดเดิม ถ้อยคำและข้อกฎหมายยกมาตามเดิม'
        . ' โปรดตรวจว่าระเบียบและหนังสือเวียนที่อ้างยังใช้อยู่ก่อนออกหนังสือจริง';

    public function safeUp(): void
    {
        foreach (self::templates() as $row) {
            $this->insert('{{%purchase_doc_template}}', $row + [
                'category' => 'buy',
                'ref_type' => 'order',
                'orientation' => 'portrait',
                'font_size' => 16,
                'margin_json' => json_encode(['top' => 25, 'right' => 20, 'bottom' => 20, 'left' => 30]),
                'note' => self::REVIEW,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function safeDown(): void
    {
        foreach (self::templates() as $row) {
            $this->delete('{{%purchase_doc_template}}', ['code' => $row['code']]);
        }
    }

    private static function templates(): array
    {
        return [
            ['code' => 'legacy_purchase_1', 'legacy_key' => 'purchase_1', 'sort_order' => 30,
                'name' => 'ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด', 'emblem' => '1.5',
                'body_html' => self::doc1()],
            ['code' => 'legacy_purchase_2', 'legacy_key' => 'purchase_2', 'sort_order' => 40,
                'name' => 'ขอความเห็นชอบและรายงานผล', 'emblem' => '1.5',
                'body_html' => self::doc2()],
            ['code' => 'legacy_purchase_4', 'legacy_key' => 'purchase_4', 'sort_order' => 50,
                'name' => 'รายการคุณลักษณะพัสดุ (ราคากลาง)', 'emblem' => 'none',
                'body_html' => self::doc4()],
            ['code' => 'legacy_purchase_5', 'legacy_key' => 'purchase_5', 'sort_order' => 60,
                'name' => 'บันทึกข้อความรายงานการขอซื้อ', 'emblem' => '1.5',
                'body_html' => self::doc5()],
            ['code' => 'legacy_purchase_6', 'legacy_key' => 'purchase_6', 'sort_order' => 70,
                'name' => 'รายงานผลการพิจารณาและขออนุมัติสั่งซื้อสั่งจ้าง', 'emblem' => '1.5',
                'body_html' => self::doc6()],
            ['code' => 'legacy_purchase_7', 'legacy_key' => 'purchase_7', 'sort_order' => 80,
                'name' => 'ประกาศผู้ชนะการเสนอราคา', 'emblem' => '3.0',
                'body_html' => self::doc7()],
            ['code' => 'legacy_purchase_8', 'legacy_key' => 'purchase_8', 'sort_order' => 90,
                'name' => 'ใบสั่งซื้อ/สั่งจ้าง', 'emblem' => 'none',
                'body_html' => self::doc8()],
            ['code' => 'legacy_purchase_9', 'legacy_key' => 'purchase_9', 'sort_order' => 100,
                'name' => 'ใบตรวจรับการจัดซื้อ/จัดจ้าง', 'emblem' => 'none',
                'body_html' => self::doc9()],
            ['code' => 'legacy_purchase_10', 'legacy_key' => 'purchase_10', 'sort_order' => 110,
                'name' => 'รายงานผลการตรวจรับ', 'emblem' => '1.5',
                'body_html' => self::doc10()],
            ['code' => 'legacy_purchase_11', 'legacy_key' => 'purchase_11', 'sort_order' => 120,
                'name' => 'แบบแสดงความบริสุทธิ์ใจ', 'emblem' => 'none',
                'body_html' => self::doc11()],
            ['code' => 'legacy_purchase_12', 'legacy_key' => 'purchase_12', 'sort_order' => 130,
                'name' => 'ขออนุมัติจ่ายเงินบำรุง', 'emblem' => '1.5',
                'body_html' => self::doc12()],
        ];
    }

    /** หัวบันทึกข้อความมาตรฐาน ใช้ซ้ำหลายฉบับ */
    private static function memoHead(string $subject, string $to): string
    {
        return <<<HTML
<table class="d-masthead">
    <tr>
        <td class="d-masthead-side">{{emblem}}</td>
        <td class="d-masthead-title"><p class="d-title">บันทึกข้อความ</p></td>
        <td class="d-masthead-side"></td>
    </tr>
</table>

<table class="d-head">
    <tr>
        <td class="d-lbl">ส่วนราชการ</td>
        <td class="d-val" colspan="3">{{org.company_full}}</td>
    </tr>
    <tr>
        <td class="d-lbl">ที่</td>
        <td class="d-val">{{doc.doc_no}}</td>
        <td class="d-lbl-sm">วันที่</td>
        <td class="d-val">{{doc.date_thai}}</td>
    </tr>
    <tr>
        <td class="d-lbl">เรื่อง</td>
        <td class="d-val" colspan="3">{$subject}</td>
    </tr>
</table>

<p class="d-to">เรียน&nbsp;&nbsp;{$to}</p>
HTML;
    }

    /** ช่องลงนาม 1 ช่อง จัดชิดขวา */
    private static function sign(string $line, string $name, string ...$positions): string
    {
        $pos = '';
        foreach ($positions as $p) {
            $pos .= '            <p class="d-sign-pos">' . $p . "</p>\n";
        }

        return <<<HTML
<table class="d-sign">
    <tr>
        <td class="d-sign-cell"></td>
        <td class="d-sign-cell">
            <p class="d-sign-line">{$line}</p>
            <p class="d-sign-name">( {$name} )</p>
{$pos}        </td>
    </tr>
</table>
HTML;
    }

    private static function doc1(): string
    {
        $head = self::memoHead(
            'ขออนุมัติแต่งตั้งคณะกรรมการกำหนดรายละเอียดคุณลักษณะเฉพาะ',
            'ผู้อำนวยการ{{org.company_name}}'
        );
        $s1 = self::sign('', '{{user.fullname}}', '{{user.position}}');
        $s2 = self::sign('', '{{org.leader_fullname}}', '{{org.leader_position}}', 'หัวหน้าเจ้าหน้าที่');
        $s3 = self::sign('', '{{org.director_fullname}}',
            'ผู้อำนวยการ{{org.company_name}}', 'ปฏิบัติราชการแทน ปลัดกระทรวงสาธารณสุข');

        return <<<HTML
{$head}

<p class="d-body">ด้วย{{org.company_name}} มีความประสงค์จะดำเนินการซื้อ {{ref.order_type}}
ประจำปีงบประมาณ {{ref.thai_year}} วงเงินงบประมาณ <strong>{{ref.budget}} บาท</strong>
({{ref.budget_text}})</p>

<p class="d-body">ดังนั้น เพื่อให้การจัดทำรายละเอียดคุณลักษณะเฉพาะของพัสดุดังกล่าว เป็นไปตามระเบียบ
กระทรวงสาธารณสุข ว่าด้วยวิธีปฏิบัติเกี่ยวกับการจัดซื้อจัดจ้างและการพัสดุโดยใช้เงินบริจาคของหน่วยบริการ
สาธารณสุข พ.ศ. ๒๕๖๑ ข้อ ๑๓ ในการซื้อหรือจ้างที่มิใช่การจ้างก่อสร้าง ให้ผู้อำนวยการแต่งตั้งคณะกรรมการ
ขึ้นมาคณะหนึ่งหรือจะให้เจ้าหน้าที่หรือบุคคลใดบุคคลหนึ่งรับผิดชอบในการจัดทำร่างขอบเขตของงาน
หรือรายละเอียดคุณลักษณะเฉพาะของพัสดุที่จะซื้อหรือจ้าง รวมทั้งกำหนดหลักเกณฑ์การพิจารณาคัดเลือก
ข้อเสนอด้วย จึงเห็นสมควรแต่งตั้งคณะกรรมการจัดทำราคากลางของพัสดุที่จะซื้อ ประกอบด้วย</p>

<p class="d-caption">คณะกรรมการกำหนดรายละเอียดคุณลักษณะเฉพาะครุภัณฑ์และราคากลาง</p>

<table class="d-items">
    <tr class="d-items-head">
        <td class="d-c-no">ที่</td>
        <td class="d-c-name">ชื่อ-สกุล</td>
        <td class="d-c-name">ตำแหน่ง</td>
        <td class="d-c-name">หน้าที่ในคณะกรรมการ</td>
    </tr>
    {{#committee_detail}}
    <tr>
        <td class="d-c-no">{{member.no}}</td>
        <td class="d-c-name">{{member.name}}</td>
        <td class="d-c-name">{{member.position}}</td>
        <td class="d-c-name">{{member.role}}</td>
    </tr>
    {{/committee_detail}}
</table>

<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณา หากเห็นชอบขอได้โปรดลงนามในคำสั่งแต่งตั้งต่อไปด้วย
จะเป็นพระคุณยิ่ง</p>

{$s1}

<p class="d-caption">ความเห็นหัวหน้าเจ้าหน้าที่</p>
<p class="d-approve">เพื่อโปรดให้ความเห็นชอบ</p>

{$s2}

{$s3}
HTML;
    }

    private static function doc2(): string
    {
        $head = self::memoHead(
            'ขอความเห็นชอบรายละเอียดคุณลักษณะเฉพาะและรายงานผล',
            'ผู้อำนวยการ{{org.company_name}}'
        );
        $s1 = self::sign('', '..................................................', 'หัวหน้าเจ้าหน้าที่');
        $s2 = self::sign('', '{{org.director_fullname}}', '{{org.director_position}}',
            'ผู้อำนวยการ{{org.company_name}}',
            'ปฏิบัติราชการแทน ผู้ว่าราชการจังหวัด{{org.province}}');

        return <<<HTML
{$head}

<p class="d-body">ตามคำสั่งจังหวัดเลขที่ .................................... ลงวันที่ {{doc.date_thai}}
คณะกรรมการได้ดำเนินการกำหนดคุณลักษณะเฉพาะครุภัณฑ์และหลักเกณฑ์การพิจารณาคัดเลือก
เพื่อใช้ประกอบดำเนินการจัดซื้อต่อไป รายละเอียดตามเอกสารที่แนบมาพร้อมหนังสือนี้</p>

<p class="d-body">เรียน ผู้อำนวยการ{{org.company_name}}</p>

<p class="d-list">- เพื่อโปรดพิจารณาเห็นชอบ</p>
<p class="d-list">- เห็นควรให้ใช้คุณลักษณะเฉพาะครุภัณฑ์ ตามที่คณะกรรมการฯ เสนอ
เพื่อประกอบการจัดซื้อต่อไป</p>

{$s1}

{$s2}
HTML;
    }

    private static function doc4(): string
    {
        return <<<'HTML'
<p class="d-title">รายละเอียดขอบเขตงานหรือรายละเอียดคุณลักษณะของพัสดุที่จะซื้อหรือจ้าง</p>
<p class="d-caption">สำหรับการจัดซื้อ/จัดจ้าง {{ref.order_type}}</p>

<table class="d-items">
    <tr class="d-items-head">
        <td class="d-c-no">ลำดับ</td>
        <td class="d-c-name">รายละเอียดขอบเขตงานหรือรายละเอียดคุณลักษณะของพัสดุที่จะซื้อหรือจ้าง</td>
        <td class="d-c-qty">จำนวน</td>
        <td class="d-c-unit">หน่วย</td>
        <td class="d-c-price">ราคา/หน่วย</td>
        <td class="d-c-amount">ราคารวม</td>
    </tr>
    {{#items}}
    <tr>
        <td class="d-c-no">{{item.no}}</td>
        <td class="d-c-name">{{item.name}}</td>
        <td class="d-c-qty">{{item.qty}}</td>
        <td class="d-c-unit">{{item.unit}}</td>
        <td class="d-c-price">{{item.price}}</td>
        <td class="d-c-amount">{{item.amount}}</td>
    </tr>
    {{/items}}
    <tr>
        <td class="d-c-total" colspan="5">รวมเป็นเงินทั้งสิ้น</td>
        <td class="d-c-amount">{{ref.budget}}</td>
    </tr>
</table>

<p class="d-caption">แหล่งที่มาของราคากลาง</p>

<p class="d-list">(&nbsp;&nbsp;&nbsp;) ราคาที่ได้มาจากการคำนวณตามหลักเกณฑ์ที่คณะกรรมการราคากลางกำหนด</p>
<p class="d-list">(&nbsp;&nbsp;&nbsp;) ราคาที่ได้มาจากฐานข้อมูลราคาอ้างอิงของพัสดุที่กรมบัญชีกลางจัดทำ</p>
<p class="d-list">(&nbsp;&nbsp;&nbsp;) ราคามาตรฐานที่สำนักงบประมาณหรือหน่วยงานกลางอื่นกำหนด</p>
<p class="d-list">(&nbsp;&nbsp;&nbsp;) ราคาที่ได้มาจากการสืบราคาท้องตลาด จำนวน .................. รายการ</p>
<p class="d-list">(&nbsp;&nbsp;&nbsp;) ราคาที่เคยซื้อหรือจ้างครั้งหลังสุด ภายในระยะเวลาสองปีงบประมาณ</p>

<p class="d-body">ตามใบสั่งซื้อสั่งจ้าง/หนังสือข้อตกลงซื้อขาย/สัญญาซื้อขาย เลขที่ {{ref.po_number}}
ลงวันที่ {{ref.po_date}}</p>
HTML;
    }

    private static function doc5(): string
    {
        $head = self::memoHead('รายงานขอซื้อขอจ้าง', 'ปลัดกระทรวงสาธารณสุข');
        $s1 = self::sign('ลงชื่อ ...............................................เจ้าหน้าที่',
            '{{user.fullname}}', 'ตำแหน่ง {{user.position}}');
        $s2 = self::sign('', '{{org.leader_fullname}}', '{{org.leader_position}}', 'หัวหน้าเจ้าหน้าที่');
        $s3 = self::sign('', '{{org.director_fullname}}',
            'ผู้อำนวยการ{{org.company_name}}', 'ปฏิบัติราชการแทน ปลัดกระทรวงสาธารณสุข');

        return <<<HTML
{$head}

<p class="d-body">ด้วย {{org.company_name}} มีความประสงค์จะขออนุมัติจัดซื้อ {{ref.order_type}}
โดยใช้{{ref.budget_type}} ซึ่งมีรายละเอียดต่อไปนี้</p>

<p class="d-caption">๑. เหตุผลความจำเป็นที่ต้องจัดซื้อ</p>
<p class="d-body">{{ref.income_reason}}</p>
<p class="d-body">รายการที่ขอซื้อคือ {{ref.title}} รวมเป็นเงิน {{ref.budget}} บาท
({{ref.budget_text}})</p>

<p class="d-caption">๒. รายละเอียดของพัสดุ</p>

<table class="d-items">
    <tr class="d-items-head">
        <td class="d-c-no">ที่</td>
        <td class="d-c-name">รายการ</td>
        <td class="d-c-qty">จำนวน</td>
        <td class="d-c-unit">หน่วย</td>
        <td class="d-c-price">ราคาต่อหน่วย</td>
        <td class="d-c-amount">รวมเงิน</td>
    </tr>
    {{#items}}
    <tr>
        <td class="d-c-no">{{item.no}}</td>
        <td class="d-c-name">{{item.name}}</td>
        <td class="d-c-qty">{{item.qty}}</td>
        <td class="d-c-unit">{{item.unit}}</td>
        <td class="d-c-price">{{item.price}}</td>
        <td class="d-c-amount">{{item.amount}}</td>
    </tr>
    {{/items}}
</table>

<p class="d-caption">๓. ราคากลางของพัสดุที่จะซื้อ</p>
<p class="d-body">{{ref.title}} โดยใช้{{ref.budget_type}} ตามรายละเอียดคุณลักษณะเฉพาะรายการที่แนบ</p>

<p class="d-caption">๔. วงเงินที่จะซื้อหรือจ้าง</p>
<p class="d-body">{{ref.budget_type}} จำนวน {{ref.budget}} บาท ({{ref.budget_text}})</p>

<p class="d-caption">๕. กำหนดเวลาที่ต้องการใช้พัสดุนั้น หรือให้งานนั้นแล้วเสร็จ</p>
<p class="d-body">กำหนดเวลาการส่งมอบพัสดุ หรือให้งานแล้วเสร็จภายใน {{ref.credit_days}} วัน
นับถัดจากวันลงนามในสัญญา</p>

<p class="d-caption">๖. วิธีที่จะซื้อ และเหตุผลที่ต้องซื้อ</p>
<p class="d-body">ดำเนินการโดยวิธี{{ref.purchase_type}} ตาม</p>
<p class="d-list">๖.๑ ระเบียบกระทรวงสาธารณสุขว่าด้วยวิธีการปฏิบัติเกี่ยวกับการจัดซื้อจัดจ้างและการพัสดุ
โดยใช้เงินบริจาคของหน่วยบริการในสังกัดกระทรวงสาธารณสุข พ.ศ. ๒๕๖๑ ข้อ ๒๒ กรณีวงเงินในการจัดซื้อ
จัดจ้างครั้งหนึ่งไม่เกิน ๕๐๐,๐๐๐ บาท ให้เจ้าหน้าที่เจรจาตกลงราคากับผู้ประกอบการที่มีอาชีพขายหรือ
รับจ้างนั้นโดยตรง แล้วดำเนินการตามข้อ ๒๑ วรรคหนึ่ง (๒)</p>
<p class="d-list">๖.๒ คำสั่งสำนักงานปลัดกระทรวงสาธารณสุข ที่ ๒๐๐๙/๒๕๖๓ ลงวันที่ ๑๗ สิงหาคม ๒๕๖๓
เรื่อง มอบอำนาจการอนุมัติการรับบริจาคทรัพย์สินที่เป็นอสังหาริมทรัพย์ การอนุมัติจ่ายเงินหรืออนุมัติ
ก่อหนี้ผูกพันเงินบริจาคให้ผู้ว่าราชการจังหวัดและหัวหน้าหน่วยบริการปฏิบัติราชการแทน
ปลัดกระทรวงสาธารณสุข ข้อ ๒ มอบอำนาจให้หัวหน้าหน่วยบริการเกี่ยวกับการอนุมัติจ่ายเงินหรือก่อหนี้
ผูกพันเงินบริจาคที่ได้มาจากการบริจาคตามระเบียบกระทรวงสาธารณสุข ว่าด้วยเงินบริจาคและทรัพย์สิน
บริจาคของหน่วยบริการ พ.ศ. ๒๕๖๑</p>

<p class="d-caption">๗. หลักเกณฑ์การพิจารณาคัดเลือกข้อเสนอ</p>
<p class="d-body">การพิจารณาคัดเลือกข้อเสนอโดยใช้{{ref.consideration}}</p>

<p class="d-caption">๘. การขออนุมัติแต่งตั้งคณะกรรมการต่าง ๆ</p>
<p class="d-body">คณะกรรมการตรวจรับพัสดุ</p>

<table class="d-items">
    <tr class="d-items-head">
        <td class="d-c-no">ที่</td>
        <td class="d-c-name">ชื่อ-สกุล</td>
        <td class="d-c-name">ตำแหน่ง</td>
        <td class="d-c-name">หน้าที่ในคณะกรรมการ</td>
    </tr>
    {{#committee}}
    <tr>
        <td class="d-c-no">{{member.no}}</td>
        <td class="d-c-name">{{member.name}}</td>
        <td class="d-c-name">{{member.position}}</td>
        <td class="d-c-name">{{member.role}}</td>
    </tr>
    {{/committee}}
</table>

<p class="d-body">อำนาจและหน้าที่ ทำการตรวจรับพัสดุให้เป็นไปตามเงื่อนไขของสัญญาหรือข้อตกลง</p>

<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณา หากเห็นชอบขอได้โปรดอนุมัติให้ดำเนินการ
ตามรายละเอียดในรายงานขอซื้อดังกล่าวข้างต้น</p>

{$s1}

<p class="d-caption">ความเห็นหัวหน้าเจ้าหน้าที่</p>
<p class="d-approve">เห็นควรอนุมัติ</p>

{$s2}

<p class="d-approve">อนุมัติ</p>

{$s3}
HTML;
    }

    private static function doc6(): string
    {
        $head = self::memoHead(
            'รายงานผลการตรวจรับพัสดุรายการ {{ref.title}}',
            'ผู้ว่าราชการจังหวัด{{org.province}}'
        );
        $s1 = self::sign('ลงชื่อ ...............................................เจ้าหน้าที่',
            '{{user.fullname}}', 'ตำแหน่ง {{user.position}}');
        $s2 = self::sign('', '{{org.leader_fullname}}', '{{org.leader_position}}', 'หัวหน้าเจ้าหน้าที่');
        $s3 = self::sign('', '{{org.director_fullname}}',
            'ผู้อำนวยการ{{org.company_name}}',
            'ปฏิบัติราชการแทน ผู้ว่าราชการจังหวัด{{org.province}}');

        return <<<HTML
{$head}

<p class="d-caption">๑. เรื่องเดิม</p>
<p class="d-body">จังหวัด{{org.province}} โดย{{org.company_name}} ได้ตกลงซื้อ{{ref.title}}
โดยใช้{{ref.budget_type}} เป็นเงิน {{ref.budget}} บาท ({{ref.budget_text}})
ใบสั่งซื้อเลขที่ {{ref.po_number}} ลงวันที่ {{ref.po_date}} ครบกำหนดส่งมอบวันที่ {{ref.due_date}}</p>

<p class="d-caption">๒. ข้อกฎหมายและระเบียบที่เกี่ยวข้อง</p>
<p class="d-list">๒.๑ ระเบียบกระทรวงสาธารณสุขว่าด้วยวิธีปฏิบัติเกี่ยวกับการจัดซื้อจัดจ้างและการพัสดุ
โดยใช้{{ref.budget_type}}ของหน่วยบริการในสังกัดกระทรวงสาธารณสุข พ.ศ. ๒๕๖๑ ข้อ ๗๓ สรุปความว่า
"การบริหารสัญญาและการตรวจรับพัสดุ ในระเบียบนี้ให้เป็นไปตามกฎหมายว่าด้วยการจัดซื้อจัดจ้างและ
การบริหารพัสดุภาครัฐ"</p>
<p class="d-list">๒.๒ ระเบียบกระทรวงการคลังว่าด้วยการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. ๒๕๖๐
ข้อ ๑๗๕ (๔) ความว่า "เมื่อตรวจถูกต้องครบถ้วนแล้ว ให้รับพัสดุไว้และถือว่าผู้ขายหรือผู้รับจ้างได้ส่งมอบ
พัสดุถูกต้องครบถ้วนตั้งแต่วันที่ผู้ขายหรือผู้รับจ้างนำพัสดุนั้นมาส่ง แล้วมอบแก่เจ้าหน้าที่พร้อมกับทำ
ใบตรวจรับโดยลงชื่อไว้เป็นหลักฐาน อย่างน้อย ๒ ฉบับ มอบแก่ผู้ขายหรือผู้รับจ้าง ๑ ฉบับ และเจ้าหน้าที่
๑ ฉบับ เพื่อดำเนินการเบิกจ่ายตามระเบียบของหน่วยงานของรัฐและรายงานให้หัวหน้าหน่วยงานของรัฐทราบ"</p>

<p class="d-caption">๓. ข้อเท็จจริง</p>
<p class="d-body">คณะกรรมการตรวจรับพัสดุได้ดำเนินการตรวจรับพัสดุ ในวันที่ {{ref.gr_date}}
ซึ่งมีผลการตรวจรับปรากฏว่า{{ref.item_checker}} ตามสัญญา ความละเอียดตามใบตรวจรับการจัดซื้อ/จัดจ้าง
ฉบับลงวันที่ {{ref.gr_date}}</p>
<p class="d-body">ผู้ขายส่งมอบพัสดุภายในกำหนดสัญญา จึงเบิกจ่ายเงินให้ผู้ขาย เป็นเงินจำนวน
{{ref.budget}} บาท ({{ref.budget_text}})</p>

<p class="d-caption">๔. ข้อพิจารณา</p>
<p class="d-body">งานพัสดุ กลุ่มงานบริหารทั่วไป {{org.company_name}} พิจารณาแล้วเห็นว่า
ผู้ขายส่งมอบพัสดุครบถ้วนตามสัญญา และคณะกรรมการตรวจรับพัสดุมีมติเป็นเอกฉันท์ตรวจรับไว้ถูกต้อง
ครบถ้วนแล้ว โดยจะได้มอบใบตรวจรับการจัดซื้อจัดจ้างให้ผู้เกี่ยวข้องเพื่อดำเนินการเบิกจ่ายเงินตามระเบียบ
ของหน่วยงานของรัฐ ดังนั้น เพื่อให้ชอบด้วยระเบียบกระทรวงสาธารณสุขว่าด้วยวิธีปฏิบัติเกี่ยวกับการจัดซื้อ
จัดจ้างและการพัสดุโดยใช้เงินบริจาคของหน่วยบริการในสังกัดกระทรวงสาธารณสุข พ.ศ. ๒๕๖๑ ข้อ ๗๓
จึงเสนอรายงานมาเพื่อทราบ</p>

<p class="d-caption">๕. ข้อเสนอ</p>
<p class="d-body">จึงเรียนมาเพื่อโปรดทราบ</p>

{$s1}

<p class="d-caption">ความเห็นหัวหน้าเจ้าหน้าที่</p>
<p class="d-approve">เพื่อโปรดทราบ</p>

{$s2}

<p class="d-approve">ทราบ</p>

{$s3}
HTML;
    }

    private static function doc7(): string
    {
        $s = self::sign('', '{{org.director_fullname}}',
            'ผู้อำนวยการ{{org.company_name}}', 'ปฏิบัติราชการแทน ปลัดกระทรวงสาธารณสุข');

        return <<<HTML
<table class="d-masthead">
    <tr>
        <td class="d-masthead-side"></td>
        <td class="d-masthead-title">{{emblem}}</td>
        <td class="d-masthead-side"></td>
    </tr>
</table>

<p class="d-title">ประกาศสำนักงานปลัดกระทรวงสาธารณสุข</p>
<p class="d-caption">เรื่อง ประกาศผู้ชนะการเสนอราคา ซื้อ{{ref.order_type}}</p>
<p class="d-caption">---------------------------</p>

<p class="d-body">ตาม {{org.company_name}} ได้มีโครงการ {{ref.title}} ด้วย{{ref.budget_type}}
โดยวิธี{{ref.purchase_type}} นั้น</p>

<p class="d-body">ผู้ได้รับการคัดเลือก ได้แก่ <strong>{{ref.vendor_name}}</strong>
โดยเสนอราคาเป็นเงินทั้งสิ้น <strong>{{ref.budget}} บาท</strong> ({{ref.budget_text}})
รวมภาษีมูลค่าเพิ่มและภาษีอื่น ค่าขนส่ง ค่าจดทะเบียน และค่าใช้จ่ายอื่น ๆ ทั้งปวง</p>

<p class="d-body">ประกาศ ณ วันที่ {{doc.date_thai}}</p>

{$s}
HTML;
    }

    private static function doc8(): string
    {
        return <<<'HTML'
<p class="d-title">ใบสั่งซื้อ/สั่งจ้าง</p>

<table class="d-detail">
    <tr>
        <td class="d-detail-lbl">ผู้ขาย/ผู้รับจ้าง</td>
        <td class="d-detail-val">{{ref.vendor_name}}</td>
        <td class="d-detail-lbl">ใบสั่งซื้อ/สั่งจ้างเลขที่</td>
        <td class="d-detail-val">{{ref.po_number}}</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">ที่อยู่</td>
        <td class="d-detail-val">{{ref.vendor_address}}</td>
        <td class="d-detail-lbl">วันที่</td>
        <td class="d-detail-val">{{ref.po_date}}</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">โทรศัพท์</td>
        <td class="d-detail-val">{{ref.vendor_phone}}</td>
        <td class="d-detail-lbl">ส่วนราชการ</td>
        <td class="d-detail-val">{{org.company_name}}</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">เลขประจำตัวผู้เสียภาษี</td>
        <td class="d-detail-val">{{ref.vendor_tax}}</td>
        <td class="d-detail-lbl">ที่อยู่</td>
        <td class="d-detail-val">{{org.address}}</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">เลขที่บัญชีเงินฝากธนาคาร</td>
        <td class="d-detail-val">{{ref.vendor_account_no}}</td>
        <td class="d-detail-lbl">โทรศัพท์</td>
        <td class="d-detail-val">{{org.phone}}</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">ชื่อบัญชี / ธนาคาร</td>
        <td class="d-detail-val">{{ref.vendor_account_name}} / {{ref.vendor_bank}}</td>
        <td class="d-detail-lbl">ใบเสนอราคาเลขที่</td>
        <td class="d-detail-val">{{ref.qr_number}}</td>
    </tr>
</table>

<p class="d-body">ตามที่ {{ref.vendor_name}} ได้เสนอราคาตามใบเสนอราคาเลขที่ {{ref.qr_number}}
ลงวันที่ {{ref.po_date}} ไว้ต่อจังหวัด{{org.province}} โดย{{org.company_name}}
ซึ่งได้รับราคาและตกลงซื้อ/จ้าง ตามรายการดังต่อไปนี้</p>

<table class="d-items">
    <tr class="d-items-head">
        <td class="d-c-no">ลำดับ</td>
        <td class="d-c-name">รายการ</td>
        <td class="d-c-qty">จำนวน</td>
        <td class="d-c-unit">หน่วย</td>
        <td class="d-c-price">ราคาต่อหน่วย (บาท)</td>
        <td class="d-c-amount">จำนวนเงิน (บาท)</td>
    </tr>
    {{#items}}
    <tr>
        <td class="d-c-no">{{item.no}}</td>
        <td class="d-c-name">{{item.name}}</td>
        <td class="d-c-qty">{{item.qty}}</td>
        <td class="d-c-unit">{{item.unit}}</td>
        <td class="d-c-price">{{item.price}}</td>
        <td class="d-c-amount">{{item.amount}}</td>
    </tr>
    {{/items}}
    <tr>
        <td class="d-c-total" colspan="5">รวมเป็นเงินทั้งสิ้น</td>
        <td class="d-c-amount">{{ref.total_price}}</td>
    </tr>
</table>

<p class="d-body">({{ref.total_price_text}})</p>

<p class="d-caption">การซื้อ/สั่งจ้าง อยู่ภายใต้เงื่อนไขต่อไปนี้</p>

<p class="d-list">๑. กำหนดส่งมอบภายใน {{ref.credit_days}} วัน นับถัดจากวันที่ผู้รับจ้างได้รับใบสั่งซื้อ</p>
<p class="d-list">๒. ครบกำหนดส่งมอบวันที่ {{ref.due_date}}</p>
<p class="d-list">๓. สถานที่ส่งมอบ {{org.company_name}}</p>
<p class="d-list">๔. ระยะเวลารับประกัน ถึงวันที่ {{ref.warranty_date}}</p>
<p class="d-list">๕. สงวนสิทธิ์ค่าปรับกรณีส่งมอบเกินกำหนด โดยคิดค่าปรับเป็นรายวันในอัตราร้อยละ ๐.๒
ของราคาสิ่งของที่ยังไม่ได้รับมอบ แต่จะต้องไม่ต่ำกว่าวันละ ๑๐๐.๐๐ บาท</p>
<p class="d-list">๖. ส่วนราชการสงวนสิทธิ์ที่จะไม่รับมอบถ้าปรากฏว่าสินค้านั้นมีลักษณะไม่ตรงตามรายการที่ระบุไว้
ในใบสั่งซื้อ กรณีนี้ผู้รับจ้างจะต้องดำเนินการเปลี่ยนใหม่ให้ถูกต้องตามใบสั่งซื้อทุกประการ</p>
<p class="d-list">๗. กรณีงานจ้าง ผู้รับจ้างจะต้องไม่เอางานทั้งหมดหรือแต่บางส่วนแห่งสัญญานี้ไปจ้างช่วงอีกทอดหนึ่ง
เว้นแต่การจ้างช่วงงานแต่บางส่วนที่ได้รับอนุญาตเป็นหนังสือจากผู้ว่าจ้างแล้ว การที่ผู้ว่าจ้างได้อนุญาตให้
จ้างช่วงงานแต่บางส่วนดังกล่าวนั้น ไม่เป็นเหตุให้ผู้รับจ้างหลุดพ้นจากความรับผิดหรือพันธะหน้าที่ตามสัญญานี้
และผู้รับจ้างจะยังคงต้องรับผิดในความผิดและความประมาทเลินเล่อของผู้รับจ้างช่วง หรือของตัวแทนหรือ
ลูกจ้างของผู้รับจ้างช่วงนั้นทุกประการ กรณีผู้รับจ้างไปจ้างช่วงงานแต่บางส่วนโดยฝ่าฝืนความในวรรคหนึ่ง
ผู้รับจ้างต้องชำระค่าปรับให้แก่ผู้ว่าจ้างเป็นจำนวนเงินในอัตราร้อยละ ๑๐ (สิบ) ของวงเงินของงานที่จ้างช่วง
ตามสัญญา ทั้งนี้ ไม่ตัดสิทธิผู้ว่าจ้างในการบอกเลิกสัญญา</p>
<p class="d-list">๘. การประเมินผลการปฏิบัติงานของผู้ประกอบการ หน่วยงานของรัฐสามารถนำผลการปฏิบัติงาน
แล้วเสร็จตามสัญญาหรือข้อตกลงของคู่สัญญาเพื่อนำมาประเมินผลการปฏิบัติงานของผู้ประกอบการ</p>

<p class="d-caption">หมายเหตุ</p>
<p class="d-list">๑. การติดอากรแสตมป์ให้เป็นไปตามประมวลกฎหมายรัษฎากร หากต้องการให้ใบสั่งซื้อมีผลตามกฎหมาย</p>
<p class="d-list">๒. ใบสั่งซื้อสั่งจ้างนี้อ้างอิงตามเลขที่โครงการจัดซื้อจัดจ้าง {{ref.order_type}}
โดยวิธี{{ref.purchase_type}} ตามประกาศ {{org.company_name}} วันที่ {{ref.date_thai}}</p>

<table class="d-sign">
    <tr>
        <td class="d-sign-cell">
            <p class="d-sign-line">ลงชื่อ ..................................................... ผู้สั่งซื้อ/จ้าง</p>
            <p class="d-sign-name">( {{org.director_fullname}} )</p>
            <p class="d-sign-pos">ตำแหน่ง ผู้อำนวยการ{{org.company_name}}</p>
            <p class="d-sign-pos">ปฏิบัติราชการแทน ผู้ว่าราชการจังหวัด{{org.province}}</p>
            <p class="d-sign-pos">{{ref.date_thai}}</p>
        </td>
        <td class="d-sign-cell">
            <p class="d-sign-line">ลงชื่อ ..................................................... ผู้รับใบสั่งซื้อ/จ้าง</p>
            <p class="d-sign-name">( {{ref.vendor_contact}} )</p>
            <p class="d-sign-pos">ตำแหน่ง {{ref.vendor_contact_position}}</p>
            <p class="d-sign-pos">{{ref.date_thai}}</p>
        </td>
    </tr>
</table>
HTML;
    }

    private static function doc9(): string
    {
        return <<<'HTML'
<p class="d-title">ใบตรวจรับการจัดซื้อ/จัดจ้าง</p>

<table class="d-head">
    <tr>
        <td class="d-lbl">วันที่</td>
        <td class="d-val" colspan="3">{{doc.date_thai}}</td>
    </tr>
</table>

<p class="d-body">ตามใบสั่งซื้อเลขที่ {{ref.po_number}} ลงวันที่ {{ref.po_date}}
จังหวัด{{org.province}} โดย{{org.company_name}} ได้ตกลงซื้อกับ{{ref.vendor_name}}
สำหรับโครงการซื้อ{{ref.order_type}} รายการ {{ref.title}} โดยใช้{{ref.budget_type}}
เป็นจำนวนเงินทั้งสิ้น {{ref.total_price}} บาท ({{ref.total_price_text}})</p>

<p class="d-body">คณะกรรมการตรวจรับพัสดุ ได้ตรวจรับงานแล้ว ผลปรากฏดังนี้</p>

<table class="d-detail">
    <tr>
        <td class="d-detail-lbl">๑. ผลการตรวจรับ</td>
        <td class="d-detail-val">{{ref.item_checker}}</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">๒. ค่าปรับ</td>
        <td class="d-detail-val">{{ref.fine}}</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">๓. การเบิกจ่ายเงิน</td>
        <td class="d-detail-val">เบิกจ่ายเงิน เป็นจำนวนเงินทั้งสิ้น {{ref.total_price}} บาท</td>
    </tr>
</table>

<p class="d-caption">คณะกรรมการตรวจรับพัสดุ</p>

<table class="d-items">
    <tr class="d-items-head">
        <td class="d-c-no">ที่</td>
        <td class="d-c-name">ลงชื่อ</td>
        <td class="d-c-name">ชื่อ-สกุล</td>
        <td class="d-c-name">หน้าที่</td>
    </tr>
    {{#committee}}
    <tr>
        <td class="d-c-no">{{member.no}}</td>
        <td class="d-c-name">......................................</td>
        <td class="d-c-name">{{member.name}}</td>
        <td class="d-c-name">{{member.role}}</td>
    </tr>
    {{/committee}}
</table>
HTML;
    }

    private static function doc10(): string
    {
        $head = self::memoHead(
            'รายงานผลการตรวจรับ{{ref.order_type}}',
            'ผู้ว่าราชการจังหวัด{{org.province}}'
        );
        $s2 = self::sign('', '{{org.leader_fullname}}', '{{org.leader_position}}');
        $s3 = self::sign('', '{{org.director_fullname}}',
            'ผู้อำนวยการ{{org.company_name}}',
            'ปฏิบัติราชการแทน ผู้ว่าราชการจังหวัด{{org.province}}');

        return <<<HTML
{$head}

<p class="d-caption">๑. เรื่องเดิม</p>
<p class="d-body">จังหวัด{{org.province}} โดย{{org.company_name}} ได้ตกลงซื้อ{{ref.order_type}}
โดยวิธี{{ref.purchase_type}} ตามรายงานผลการพิจารณาและขออนุมัติสั่งซื้อสั่งจ้าง ฉบับลงวันที่
{{ref.date_thai}} และใบสั่งซื้อสั่งจ้างเลขที่ {{ref.po_number}} ลงวันที่ {{ref.po_date}}
ตกลงซื้อกับ {{ref.vendor_name}} ในราคา {{ref.budget}} บาท ({{ref.budget_text}}) นั้น</p>

<p class="d-caption">๒. ข้อกฎหมายและระเบียบที่เกี่ยวข้อง</p>
<p class="d-list">๒.๑ ระเบียบกระทรวงการคลังว่าด้วยการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. ๒๕๖๐
ข้อ ๑๗๕ (๔) ความว่า "เมื่อตรวจถูกต้องครบถ้วนแล้ว ให้รับพัสดุไว้และถือว่าผู้ขายหรือผู้รับจ้างได้ส่งมอบ
พัสดุถูกต้องครบถ้วนตั้งแต่วันที่ผู้ขายหรือผู้รับจ้างนำพัสดุนั้นมาส่ง แล้วมอบแก่เจ้าหน้าที่พร้อมกับทำ
ใบตรวจรับโดยลงชื่อไว้เป็นหลักฐานอย่างน้อย ๒ ฉบับ มอบแก่ผู้ขายหรือผู้รับจ้าง ๑ ฉบับ และเจ้าหน้าที่
๑ ฉบับ เพื่อดำเนินการเบิกจ่ายเงินตามระเบียบของหน่วยงานของรัฐและรายงานให้หัวหน้าหน่วยงานของรัฐทราบ"</p>
<p class="d-list">๒.๒ หนังสือคณะกรรมการวินิจฉัยปัญหาการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ กรมบัญชีกลาง
ด่วนที่สุด ที่ กค (กวจ) ๐๔๐๕.๒/ว ๗๘ ลงวันที่ ๓๑ มกราคม ๒๕๖๕ ว่าด้วยการจัดซื้อพัสดุที่ผลิตในประเทศ
และการจัดทำรายงานผลการใช้พัสดุที่ผลิตภายในประเทศ</p>

<p class="d-caption">๓. ข้อเท็จจริง</p>
<p class="d-body">ผู้ขาย {{ref.vendor_name}} ขอส่งมอบพัสดุตามใบส่งของเลขที่ {{ref.gr_number}}
ลงวันที่ {{ref.gr_date}} และคณะกรรมการตรวจรับพัสดุได้ดำเนินการตรวจรับพัสดุในวันที่ {{ref.gr_date}}
ซึ่งมีผลการตรวจรับปรากฏว่า{{ref.item_checker}}ตามสัญญา ความละเอียดตามใบตรวจรับการจัดซื้อ/จัดจ้าง
ฉบับลงวันที่ {{ref.gr_date}} และปรากฏว่าพัสดุที่ส่งมอบเป็นพัสดุที่ผลิตภายในประเทศในอัตราร้อยละ ๑๐๐
ตามรายงานผลการใช้พัสดุที่ผลิตภายในประเทศ</p>
<p class="d-body">ผู้ขายส่งมอบพัสดุภายในกำหนดสัญญา จึงเบิกจ่ายเงินให้ผู้ขาย เป็นเงินจำนวน
{{ref.budget}} บาท ({{ref.budget_text}})</p>

<p class="d-caption">๔. ข้อพิจารณา</p>
<p class="d-body">งานพัสดุ กลุ่มงานบริหารทั่วไป {{org.company_name}} พิจารณาแล้วเห็นว่า
ผู้ขายส่งมอบพัสดุตามสัญญา ซึ่งคณะกรรมการตรวจรับพัสดุได้ตรวจรับไว้ถูกต้องครบถ้วนแล้ว โดยจะได้
ส่งมอบใบตรวจรับการจัดซื้อ/จัดจ้างให้ผู้เกี่ยวข้องเพื่อดำเนินการเบิกจ่ายเงินตามระเบียบฯ เพื่อให้เป็นไป
ตามระเบียบกระทรวงการคลังว่าด้วยการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. ๒๕๖๐ ข้อ ๑๗๕ (๔)
จึงเสนอรายงานมาเพื่อทราบ</p>

<p class="d-caption">๕. ข้อเสนอ</p>
<p class="d-body">จึงเรียนมาเพื่อโปรดทราบ และมอบผู้เกี่ยวข้องดำเนินการเบิกจ่ายเงินตามระเบียบต่อไป</p>

<p class="d-caption">คณะกรรมการตรวจรับพัสดุ</p>

<table class="d-items">
    <tr class="d-items-head">
        <td class="d-c-no">ที่</td>
        <td class="d-c-name">ลงชื่อ</td>
        <td class="d-c-name">ชื่อ-สกุล</td>
        <td class="d-c-name">หน้าที่</td>
    </tr>
    {{#committee}}
    <tr>
        <td class="d-c-no">{{member.no}}</td>
        <td class="d-c-name">......................................</td>
        <td class="d-c-name">{{member.name}}</td>
        <td class="d-c-name">{{member.role}}</td>
    </tr>
    {{/committee}}
</table>

{$s2}

<p class="d-approve">ทราบ</p>

{$s3}
HTML;
    }

    private static function doc11(): string
    {
        return <<<'HTML'
<p class="d-title">แบบแสดงความบริสุทธิ์ใจในการจัดซื้อจัดจ้างทุกวิธีของหน่วยงาน</p>
<p class="d-caption">ในการเปิดเผยข้อมูลความขัดแย้งทางผลประโยชน์ของหัวหน้าเจ้าหน้าที่ เจ้าหน้าที่
คณะกรรมการตรวจรับพัสดุ และผู้ตรวจรับพัสดุ</p>

<table class="d-detail">
    <tr>
        <td class="d-detail-lbl">ข้าพเจ้า</td>
        <td class="d-detail-val">{{org.leader_fullname}} (หัวหน้าเจ้าหน้าที่)</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">ข้าพเจ้า</td>
        <td class="d-detail-val">{{user.fullname}} (เจ้าหน้าที่)</td>
    </tr>
    {{#committee}}
    <tr>
        <td class="d-detail-lbl">ข้าพเจ้า</td>
        <td class="d-detail-val">{{member.name}} ({{member.role}})</td>
    </tr>
    {{/committee}}
</table>

<p class="d-body">ขอให้คำรับรองว่าไม่มีความเกี่ยวข้องหรือมีส่วนได้ส่วนเสีย ไม่ว่าโดยตรงหรือโดยอ้อม
หรือผลประโยชน์ใด ๆ ที่ก่อให้เกิดความขัดแย้งทางผลประโยชน์กับผู้ขาย ผู้รับจ้าง ผู้เสนองาน
ผู้ชนะการประมูล หรือผู้มีส่วนเกี่ยวข้องที่เข้ามามีนิติสัมพันธ์ และวางตัวเป็นกลางในการดำเนินการ
เกี่ยวกับการพัสดุ ปฏิบัติหน้าที่ด้วยจิตสำนึก ด้วยความโปร่งใส สามารถให้ผู้เกี่ยวข้องตรวจสอบได้ทุกเวลา
มุ่งประโยชน์ส่วนรวมเป็นสำคัญ ตามที่ระบุไว้ในประกาศสำนักงานปลัดกระทรวงสาธารณสุขว่าด้วย
แนวทางปฏิบัติงานเพื่อตรวจสอบบุคลากรในหน่วยงานด้านการจัดซื้อจัดจ้าง พ.ศ. ๒๕๖๐</p>

<p class="d-body">หากปรากฏว่าเกิดความขัดแย้งทางผลประโยชน์ระหว่างข้าพเจ้ากับผู้ขาย ผู้รับจ้าง
ผู้เสนองาน ผู้ชนะการประมูล หรือผู้มีส่วนเกี่ยวข้องที่เข้ามามีนิติสัมพันธ์ ข้าพเจ้าจะรายงานให้ทราบ
โดยทันที</p>

<table class="d-sign">
    <tr>
        <td class="d-sign-cell">
            <p class="d-sign-line">ลงนาม .........................................................</p>
            <p class="d-sign-name">( {{org.leader_fullname}} )</p>
            <p class="d-sign-pos">(หัวหน้าเจ้าหน้าที่)</p>
        </td>
        <td class="d-sign-cell">
            <p class="d-sign-line">ลงนาม .........................................................</p>
            <p class="d-sign-name">( {{user.fullname}} )</p>
            <p class="d-sign-pos">(เจ้าหน้าที่)</p>
        </td>
    </tr>
</table>

{{#committee}}
<table class="d-sign">
    <tr>
        <td class="d-sign-cell"></td>
        <td class="d-sign-cell">
            <p class="d-sign-line">ลงนาม .........................................................</p>
            <p class="d-sign-name">( {{member.name}} )</p>
            <p class="d-sign-pos">( {{member.role}} )</p>
        </td>
    </tr>
</table>
{{/committee}}
HTML;
    }

    private static function doc12(): string
    {
        $head = self::memoHead('ขออนุมัติจ่าย{{ref.budget_type}}', 'ผู้อำนวยการ{{org.company_name}}');
        $s1 = self::sign('', '{{user.fullname}}', '{{user.position}}');
        $s2 = self::sign('', '{{org.leader_fullname}}', '{{org.leader_position}}');
        $s3 = self::sign('', '{{org.director_fullname}}', 'ผู้อำนวยการ{{org.company_name}}');

        return <<<HTML
{$head}

<p class="d-body">ด้วย{{org.company_name}} ได้รับอนุมัติให้ดำเนินการจัดซื้อ {{ref.order_type}}
ตามบันทึกของโรงพยาบาลฯ ลงวันที่ {{ref.date_thai}} ซึ่งบัดนี้เจ้าหน้าที่ได้ดำเนินการเสร็จเรียบร้อยแล้ว
ดังรายละเอียดที่แนบมาพร้อมนี้</p>

<p class="d-body">อาศัยอำนาจตามคำสั่งสำนักงานปลัดกระทรวงสาธารณสุข ที่ ๒๐๐๙/๒๕๖๓
ลงวันที่ ๑๗ สิงหาคม ๒๕๖๓ เรื่อง มอบอำนาจการอนุมัติการรับบริจาคทรัพย์สินที่เป็นอสังหาริมทรัพย์
การอนุมัติจ่ายเงินหรืออนุมัติก่อหนี้ผูกพันเงินบริจาคให้ผู้ว่าราชการจังหวัดและหัวหน้าหน่วยบริการ
ปฏิบัติราชการแทนปลัดกระทรวงสาธารณสุข ข้อ ๒ มอบอำนาจให้หัวหน้าหน่วยบริการเกี่ยวกับการอนุมัติ
จ่ายเงินหรือก่อหนี้ผูกพันเงินบริจาคที่ได้มาจากการบริจาคตามระเบียบกระทรวงสาธารณสุข ว่าด้วย
เงินบริจาคและทรัพย์สินบริจาคของหน่วยบริการ พ.ศ. ๒๕๖๑</p>

<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณาอนุมัติจ่าย{{ref.budget_type}} เพื่อจ่ายให้
<strong>{{ref.vendor_name}}</strong> เป็นเงินจำนวน <strong>{{ref.budget}} บาท</strong>
({{ref.budget_text}})</p>

{$s1}

<p class="d-caption">ความเห็นของหัวหน้าฝ่ายบริหารงานทั่วไป</p>
<p class="d-approve">ได้ตรวจสอบหลักฐานถูกต้องแล้ว เห็นควรอนุมัติจ่ายเงินต่อไป</p>

{$s2}

<p class="d-approve">อนุมัติ</p>

{$s3}
HTML;
    }
}
