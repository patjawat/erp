<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * แม่แบบตั้งต้น — บันทึกข้อความขออนุมัติจัดซื้อโดยวิธีเฉพาะเจาะจง (อ้าง ว 804)
 *
 * ข้อความในแม่แบบนี้เป็นค่าตั้งต้นให้งานพัสดุแก้ต่อได้จากหน้าจัดการแม่แบบ ไม่ใช่
 * ข้อความที่ผ่านการตรวจทานจากงานพัสดุของโรงพยาบาลแล้ว จุดที่ต้องเทียบกับฉบับจริง
 * ก่อนนำไปใช้ออกหนังสือ
 *
 *   - เลขและวันที่ของหนังสือเวียนที่อ้างในย่อหน้าแรก (กค (กวจ) 0405.2/ว 804
 *     ลว. 12 พ.ย. 2568) เป็นชุดเดียวกับที่ seed ไว้ใน purchase_bond_policy
 *     ซึ่งยังรอการยืนยันจากงานพัสดุอยู่เช่นกัน
 *   - เพดาน 50,000 บาท ในชื่อเรื่องและในย่อหน้าแรกเป็นตัวเลขคงที่ในข้อความ
 *     ไม่ได้ผูกกับตาราง purchase_bond_policy เพราะเป็นเกณฑ์คนละเรื่องกัน
 *     (เกณฑ์วิธีจัดซื้อ ไม่ใช่เกณฑ์การวางหลักประกัน) ถ้าเพดานเปลี่ยน ต้องมาแก้
 *     ข้อความในแม่แบบนี้
 *
 * merge tag ที่ใช้ในแม่แบบ ดูรายการเต็มได้ที่ DocMergeEngine::fieldCatalog()
 * ค่าที่หาไม่ได้จะถูกแทนด้วยจุดไข่ปลาให้ผู้ใช้เห็นว่าต้องกรอกเอง ไม่ปล่อยเป็นช่องว่าง
 * เพราะช่องว่างบนกระดาษราชการดูเหมือนตกหล่นมากกว่าเหมือนรอเติม
 */
final class m260812_000008_seed_purchase_doc_template extends Migration
{
    private const REVIEW_NOTE = 'ข้อความตั้งต้นของระบบ ยังไม่ผ่านการตรวจทานจากงานพัสดุ'
        . ' — โปรดเทียบเลขและวันที่หนังสือเวียน (ว 804) รวมถึงเพดานวงเงิน 50,000 บาท'
        . ' กับฉบับที่ใช้อยู่จริงก่อนนำไปออกหนังสือ';

    public function safeUp(): void
    {
        $this->insert('{{%purchase_doc_template}}', [
            'code' => 'w804_memo_buy',
            'name' => 'บันทึกข้อความขอจัดซื้อ (ว.804)',
            'category' => 'buy',
            'ref_type' => 'order',
            'body_html' => self::bodyHtml(),
            'orientation' => 'portrait',
            'emblem' => '1.5',
            'font_size' => 14,
            'margin_json' => json_encode(['top' => 25, 'right' => 20, 'bottom' => 20, 'left' => 30]),
            'law_ref' => 'หนังสือกระทรวงการคลัง ที่ กค (กวจ) 0405.2/ว 804 ลงวันที่ 12 พฤศจิกายน 2568',
            'note' => self::REVIEW_NOTE,
            'active' => 1,
            'sort_order' => 10,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function safeDown(): void
    {
        $this->delete('{{%purchase_doc_template}}', ['code' => 'w804_memo_buy']);
    }

    /**
     * เนื้อเอกสารเป็น HTML
     *
     * ใช้ nowdoc ไม่ใช่ heredoc เพื่อให้ {{...}} และเครื่องหมาย $ ที่อาจโผล่มา
     * ในข้อความภายหลังไม่ถูก PHP ตีความ
     *
     * ข้อจำกัดที่กำหนดรูปแบบของ HTML ชุดนี้ — ต้องเรนเดอร์ได้เหมือนกันทั้งสามที่
     * คือบนจอ (contenteditable), mPDF (พิมพ์) และ PhpWord (ส่งออก Word) จึงใช้
     * เฉพาะ table/p/span/strong และคุมระยะด้วย class ไม่ใช่ inline style
     * ไม่ใช้ float เพราะ PhpWord แปลงไม่ได้ ตราครุฑกับหัวเรื่องจึงอยู่ในตาราง
     * สามช่องที่ช่องซ้ายกับขวากว้างเท่ากัน หัวเรื่องจึงอยู่กลางหน้ากระดาษพอดี
     */
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
        <td class="d-val" colspan="3">ขออนุมัติจัดซื้อโดยวิธีเฉพาะเจาะจง (วงเงินไม่เกิน 50,000 บาท)</td>
    </tr>
</table>

<p class="d-to">เรียน&nbsp;&nbsp;ผู้อำนวยการ{{org.company_name}}</p>

<p class="d-body">ด้วย{{org.company_name}} มีความจำเป็นต้อง<strong>จัดซื้อ</strong>
เพื่อสนับสนุนการปฏิบัติราชการ ประจำปีงบประมาณ พ.ศ. {{doc.thai_year}}
วงเงิน <strong>{{ref.budget}} บาท</strong> ({{ref.budget_text}})
ซึ่งมีวงเงินไม่เกิน 50,000 บาท สามารถใช้กระบวนการจัดซื้อจัดจ้างอย่างง่ายได้ตาม
<strong>หนังสือกระทรวงการคลัง ที่ กค (กวจ) 0405.2/ว 804 ลงวันที่ 12 พฤศจิกายน 2568</strong>
โดยเจ้าหน้าที่พิจารณาเลือกผู้ประกอบการที่มีอาชีพขายพัสดุนั้นและเจรจาตกลงราคาโดยตรง</p>

<p class="d-caption">รายละเอียดการจัดซื้อจัดจ้าง</p>

<table class="d-detail">
    <tr>
        <td class="d-detail-lbl">1. รายการพัสดุ/งานจ้าง</td>
        <td class="d-detail-val">{{ref.title}}</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">2. จำนวนรายการ</td>
        <td class="d-detail-val">{{ref.item_count}} รายการ</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">3. วงเงินที่ขอซื้อ</td>
        <td class="d-detail-val">{{ref.budget}} บาท ({{ref.budget_text}})</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">4. ผู้ประกอบการที่ตกลงราคา</td>
        <td class="d-detail-val">{{ref.vendor_name}}</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">5. เหตุผลที่เลือก</td>
        <td class="d-detail-val">เป็นผู้มีอาชีพขายพัสดุดังกล่าวโดยตรง เสนอราคาอยู่ในวงเงินที่ได้รับ และเคยส่งมอบงานได้ตามกำหนด</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">6. กำหนดส่งมอบ</td>
        <td class="d-detail-val">ภายใน ................ วัน นับถัดจากวันที่ผู้ขายได้รับใบสั่งซื้อ</td>
    </tr>
    <tr>
        <td class="d-detail-lbl">7. แหล่งเงิน</td>
        <td class="d-detail-val">เงินบำรุงโรงพยาบาล</td>
    </tr>
</table>

<p class="d-caption">รายการพัสดุที่ขอซื้อ</p>

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
    <tr>
        <td class="d-c-total" colspan="5">รวมเป็นเงินทั้งสิ้น</td>
        <td class="d-c-amount">{{ref.budget}}</td>
    </tr>
</table>

<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณา หากเห็นสมควรขอได้โปรด</p>

<p class="d-list">1. อนุมัติให้จัดซื้อพัสดุตามรายการข้างต้น ในวงเงิน {{ref.budget}} บาท โดยวิธีเฉพาะเจาะจง</p>
<p class="d-list">2. อนุมัติแต่งตั้งผู้ตรวจรับพัสดุตามรายชื่อที่เสนอ</p>
<p class="d-list">3. ลงนามในใบสั่งซื้อพัสดุรายการดังกล่าว</p>

<table class="d-sign">
    <tr>
        <td class="d-sign-cell"></td>
        <td class="d-sign-cell">
            <p class="d-sign-line">(ลงชื่อ) ..................................................</p>
            <p class="d-sign-name">( {{user.fullname}} )</p>
            <p class="d-sign-pos">{{user.position}}</p>
            <p class="d-sign-pos">เจ้าหน้าที่</p>
        </td>
    </tr>
</table>

<table class="d-sign">
    <tr>
        <td class="d-sign-cell"></td>
        <td class="d-sign-cell">
            <p class="d-sign-line">(ลงชื่อ) ..................................................</p>
            <p class="d-sign-name">( .................................................. )</p>
            <p class="d-sign-pos">หัวหน้าเจ้าหน้าที่</p>
        </td>
    </tr>
</table>

<p class="d-caption">คำสั่ง / ความเห็นของผู้มีอำนาจอนุมัติ</p>

<p class="d-approve">&#9744; อนุมัติ&nbsp;&nbsp;&nbsp;&nbsp;&#9744; ไม่อนุมัติ&nbsp;&nbsp;&nbsp;&nbsp;&#9744; อื่น ๆ ....................................................................</p>

<table class="d-sign">
    <tr>
        <td class="d-sign-cell"></td>
        <td class="d-sign-cell">
            <p class="d-sign-line">(ลงชื่อ) ..................................................</p>
            <p class="d-sign-name">( {{org.director_fullname}} )</p>
            <p class="d-sign-pos">{{org.director_position}}</p>
        </td>
    </tr>
</table>
HTML;
    }
}
