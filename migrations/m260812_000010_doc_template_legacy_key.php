<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ผูกแม่แบบ HTML เข้ากับเอกสารพิมพ์ชุดเดิม + แปลงใบแรก
 *
 * เจตนา
 *
 * เมนู "พิมพ์เอกสาร" ในหน้ารายการจัดซื้อจัดจ้างมีเอกสารให้เลือก 13 ใบ ซึ่งทุกใบเป็น
 * ไฟล์ .docx ใน web/msword แล้วพรีวิวด้วย Google Docs Viewer ที่ต้องเข้าถึงไฟล์จาก
 * อินเทอร์เน็ตได้ — ERP รันในอินทราเน็ตจึงขึ้น "ไม่มีตัวอย่างที่ใช้ได้" เสมอ
 * ผู้ใช้ได้แต่ดาวน์โหลดไปเปิด Word เอง แก้บนจอไม่ได้
 *
 * legacy_key คือสะพานให้เมนูเดิมนั้นชี้มาที่แม่แบบ HTML ใบเดียวกันได้ ค่าของมันคือ
 * ชื่อ action เดิม (purchase_1 ... purchase_12) หน้าเมนูจะเช็คว่าเอกสารใบไหนมีแม่แบบ
 * HTML แล้ว ถ้ามีก็พาไปหน้าแก้ไข ถ้ายังไม่มีก็ปล่อยให้ใช้ทางเดิมต่อไป
 *
 * ทำแบบนี้เพื่อให้แปลงเอกสารได้ทีละใบโดยของเดิมไม่พังระหว่างทาง — ไม่ต้องรอให้ครบ
 * 13 ใบก่อนจึงจะได้ใช้ ใบที่แปลงแล้วก็ใช้ของใหม่ได้เลย
 *
 * ใบแรกที่แปลง: purchase_3 "ขออนุมัติจัดซื้อ/จ้าง" ถอดถ้อยคำมาจากไฟล์
 * web/msword/purchase_3.docx ตรงตัว โดยแทน ${...} ของ TemplateProcessor
 * ด้วย {{...}} ของ DocMergeEngine ตามตารางนี้
 *
 *   ${org_name}          -> {{org.company_name}}
 *   ${doc_number}        -> {{doc.doc_no}}       (เดิมดึงจากค่าตั้งค่าหน่วยงาน
 *                                                 ที่นี่เป็นเลขที่ของเอกสารใบนั้นเอง)
 *   ${date}              -> {{doc.date_thai}}
 *   ${director_fullname} -> {{org.director_fullname}}
 *   ${director_position} -> {{org.director_position}}
 *   ${department}        -> {{ref.department}}
 *   ${comment}           -> {{ref.comment}}
 *   ${price_amount}      -> {{ref.budget}} บาท
 *   ${emp_name}          -> {{ref.leader_name}}
 *   ${emp_position}      -> {{ref.leader_position}}
 *   ${n} ${item_name} ${qty} ${unit} -> บล็อก {{#items}}
 *
 * จุดที่ตั้งใจไม่เหมือนของเดิม: ตารางรายการของเดิมไม่มีแถวหัวตาราง แต่ยัดคำว่า
 * "จำนวน" เป็นช่องหนึ่งในทุกแถว ที่นี่ใช้แถวหัวตาราง (ที่ / รายการ / จำนวน / หน่วย)
 * แทน เนื้อหาเท่ากันแต่อ่านง่ายกว่าและไม่ต้องพิมพ์คำเดิมซ้ำทุกบรรทัด
 */
final class m260812_000010_doc_template_legacy_key extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%purchase_doc_template}}',
            'legacy_key',
            $this->string(30)->null()->after('code')
                ->comment('ชื่อ action ของเอกสารพิมพ์ชุดเดิม เช่น purchase_3 — ว่าง = ไม่ผูกกับชุดเดิม')
        );
        $this->createIndex('idx-purchase_doc_template-legacy', '{{%purchase_doc_template}}', 'legacy_key');

        $this->insert('{{%purchase_doc_template}}', [
            'code' => 'legacy_purchase_3',
            'legacy_key' => 'purchase_3',
            'name' => 'ขออนุมัติจัดซื้อ/จ้าง',
            'category' => 'buy',
            'ref_type' => 'order',
            'body_html' => self::bodyHtml(),
            'orientation' => 'portrait',
            'emblem' => '1.5',
            'font_size' => 16,
            'margin_json' => json_encode(['top' => 25, 'right' => 20, 'bottom' => 20, 'left' => 30]),
            'law_ref' => null,
            'note' => 'แปลงจาก web/msword/purchase_3.docx ถ้อยคำตรงตามของเดิม'
                . ' — ของเดิมยังใช้งานได้ปกติที่เมนูเดียวกัน',
            'active' => 1,
            'sort_order' => 20,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function safeDown(): void
    {
        $this->delete('{{%purchase_doc_template}}', ['code' => 'legacy_purchase_3']);
        $this->dropIndex('idx-purchase_doc_template-legacy', '{{%purchase_doc_template}}');
        $this->dropColumn('{{%purchase_doc_template}}', 'legacy_key');
    }

    private static function bodyHtml(): string
    {
        return <<<'HTML'
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
        <td class="d-val" colspan="3">{{org.company_name}}</td>
    </tr>
    <tr>
        <td class="d-lbl">ที่</td>
        <td class="d-val">{{doc.doc_no}}</td>
        <td class="d-lbl-sm">วันที่</td>
        <td class="d-val">{{doc.date_thai}}</td>
    </tr>
    <tr>
        <td class="d-lbl">เรื่อง</td>
        <td class="d-val" colspan="3">ขออนุมัติจัดซื้อ/จ้าง</td>
    </tr>
</table>

<p class="d-to">เรียน&nbsp;&nbsp;ผู้อำนวยการ{{org.director_fullname}}</p>

<p class="d-body">เนื่องด้วยงาน/ฝ่าย{{ref.department}} มีความประสงค์ ขอซื้อ ขอจ้าง
รายการดังต่อไปนี้</p>

<table class="d-items">
    <tr class="d-items-head">
        <td class="d-c-no">ที่</td>
        <td class="d-c-name">รายการ</td>
        <td class="d-c-qty">จำนวน</td>
        <td class="d-c-unit">หน่วย</td>
    </tr>
    {{#items}}
    <tr>
        <td class="d-c-no">{{item.no}}</td>
        <td class="d-c-name">{{item.name}}</td>
        <td class="d-c-qty">{{item.qty}}</td>
        <td class="d-c-unit">{{item.unit}}</td>
    </tr>
    {{/items}}
</table>

<p class="d-body">(เหตุผล) {{ref.comment}}</p>

<p class="d-body">เป็นเงิน <strong>{{ref.budget}} บาท</strong> ({{ref.budget_text}})</p>

<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณา</p>

<table class="d-sign">
    <tr>
        <td class="d-sign-cell"></td>
        <td class="d-sign-cell">
            <p class="d-sign-line">ลงชื่อ ....................................................ผู้ขออนุมัติ</p>
            <p class="d-sign-name">( {{ref.leader_name}} )</p>
            <p class="d-sign-pos">ตำแหน่ง {{ref.leader_position}}</p>
        </td>
    </tr>
</table>

<p class="d-approve">( ) อนุมัติ&nbsp;&nbsp;&nbsp;&nbsp;( ) ไม่อนุมัติ</p>

<table class="d-sign">
    <tr>
        <td class="d-sign-cell"></td>
        <td class="d-sign-cell">
            <p class="d-sign-line">ลงชื่อ ....................................................ผู้อนุมัติ</p>
            <p class="d-sign-name">( {{org.director_fullname}} )</p>
            <p class="d-sign-pos">{{org.director_position}}</p>
            <p class="d-sign-pos">{{org.company_name}}</p>
        </td>
    </tr>
</table>
HTML;
    }
}
