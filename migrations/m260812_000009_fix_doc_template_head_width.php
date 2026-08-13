<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * แก้ป้ายหัวเอกสารที่พิมพ์ออกมาเป็นตัวอักษรเรียงลงทีละบรรทัด
 *
 * อาการที่พบตอนสั่งพิมพ์จริง: ช่อง "ส่วนราชการ" "ที่" "วันที่" "เรื่อง" ถูกบีบจนเหลือ
 * ความกว้างเท่าตัวอักษรเดียว ข้อความจึงตกบรรทัดทุกตัวอักษรกลายเป็นแนวตั้ง
 *
 * สาเหตุอยู่ที่ CSS (.d-lbl ใช้ width:1% คู่กับ white-space:nowrap ซึ่งเบราว์เซอร์
 * ยอมให้แต่ mPDF ไม่รู้จัก nowrap แล้วเชื่อ width:1% ตรง ๆ) แก้ที่
 * DocRenderer::sheetCss() เป็นความกว้างสัดส่วนจริงแล้ว
 *
 * ส่วนที่ต้องแก้ในฐานข้อมูลคือช่องป้าย "วันที่" ที่อยู่กลางแถวเดียวกับ "ที่" ซึ่งต้องแคบ
 * กว่าป้ายอื่น ไม่งั้นสองช่องป้ายกินความกว้างรวมกัน 40% เหลือที่ให้ค่าน้อยเกินไป
 * จึงเปลี่ยน class ของช่องนั้นเป็น d-lbl-sm
 *
 * ใช้ UPDATE แบบเจาะจงไม่ใช่ลบแล้ว seed ใหม่ เพราะแม่แบบถูกอ้างด้วย
 * purchase_doc.template_id อยู่แล้ว การลบทิ้งจะทำให้เอกสารที่ออกไปแล้วชี้ไปที่
 * แม่แบบที่ไม่มีอยู่ และปุ่มรีเซ็ตของเอกสารเหล่านั้นใช้ไม่ได้อีก
 */
final class m260812_000009_fix_doc_template_head_width extends Migration
{
    private const FROM = '<td class="d-lbl">วันที่</td>';
    private const TO = '<td class="d-lbl-sm">วันที่</td>';

    public function safeUp(): void
    {
        $this->swap(self::FROM, self::TO);
    }

    public function safeDown(): void
    {
        $this->swap(self::TO, self::FROM);
    }

    /**
     * สลับข้อความใน body_html ของแม่แบบ ว.804
     *
     * ทำผ่าน REPLACE ของ MySQL ไม่ดึงมาต่อใน PHP เพื่อไม่ต้องกังวลว่าใครแก้เนื้อ
     * แม่แบบส่วนอื่นไปแล้วหรือยัง — แถวไหนไม่มีข้อความต้นทางก็ไม่ถูกแตะ
     */
    private function swap(string $from, string $to): void
    {
        $this->execute(
            'UPDATE {{%purchase_doc_template}} SET body_html = REPLACE(body_html, :from, :to)'
            . ' WHERE code = :code AND body_html LIKE :like',
            [
                ':from' => $from,
                ':to' => $to,
                ':code' => 'w804_memo_buy',
                ':like' => '%' . $from . '%',
            ]
        );
    }
}
