<?php

namespace app\modules\purchase\components;

use app\modules\purchase\models\DocTemplate;

/**
 * ทะเบียนเอกสารพิมพ์ชุดเดิมของงานจัดซื้อ (web/msword/purchase_1..12)
 *
 * ทำไมต้องมีคลาสนี้
 *
 * รายชื่อเอกสาร 13 รายการเคยเขียนฝังไว้ในไฟล์ view เดียว (order/document.php)
 * พอหน้าการ์ดต้องแสดงรายการเดียวกันด้วย ก็จะกลายเป็นสองที่ที่ต้องแก้ให้ตรงกันทุกครั้ง
 * ที่มีเอกสารถูกแปลงเป็นแม่แบบ HTML — ที่ไหนลืมแก้ ผู้ใช้จะเห็นรายการไม่เท่ากันสองหน้า
 *
 * แต่ละรายการมีสองสถานะ
 *
 *   converted  มีแม่แบบ HTML แล้ว (จับคู่ด้วย purchase_doc_template.legacy_key)
 *              -> เปิดหน้าแก้ไขบนกระดาษ A4 แก้ได้ก่อนพริ้นท์
 *   legacy     ยังไม่แปลง -> ใช้ /ms-word/<key> เดิม คือได้ไฟล์ .docx ไปเปิด Word เอง
 *
 * มีสองสถานะเพื่อให้แปลงเอกสารได้ทีละใบโดยของเดิมไม่พัง และผู้ใช้ยังเห็นเอกสารครบ
 * ทุกใบตั้งแต่วันแรก ไม่ต้องรอให้แปลงครบ 13 ใบก่อนจึงจะมีอะไรให้ใช้
 */
class LegacyDocCatalog
{
    /**
     * รายชื่อเอกสารตามลำดับที่งานพัสดุใช้จริง
     *
     * ลำดับและถ้อยคำยกมาจาก order/document.php ของเดิมทั้งหมด ไม่ได้จัดใหม่
     * เพราะเป็นลำดับขั้นตอนการจัดซื้อที่ผู้ใช้จำตำแหน่งไว้แล้ว
     *
     * หมายเหตุที่ยกมาจากของเดิมโดยไม่แก้: "คำสั่งจังหวัด...?" กับ "ขอความเห็นชอบและ
     * รายงานผล" ชี้ไป purchase_2 ไฟล์เดียวกันทั้งคู่ ซึ่งน่าจะเป็นความพลาดเดิม
     * แต่การเปลี่ยนว่าปุ่มไหนเปิดไฟล์ไหนคือเปลี่ยนพฤติกรรมที่ใช้อยู่ทุกวัน
     * ต้องให้งานพัสดุยืนยันก่อน จึงคงไว้ตามเดิม
     *
     * @return array<int, array{key:string, label:string, size:string}>
     */
    public static function documents(): array
    {
        return [
            ['key' => 'purchase_3', 'label' => 'ขออนุมัติจัดซื้อจัดจ้าง', 'size' => 'modal-xl'],
            ['key' => 'purchase_1', 'label' => 'ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด', 'size' => 'modal-md'],
            ['key' => 'purchase_2', 'label' => 'คำสั่งจังหวัด...?', 'size' => 'modal-xl'],
            ['key' => 'purchase_2', 'label' => 'ขอความเห็นชอบและรายงานผล', 'size' => 'modal-md'],
            ['key' => 'purchase_4', 'label' => 'รายการคุณลักษณะพัสดุ', 'size' => 'modal-xl'],
            ['key' => 'purchase_5', 'label' => 'บันทึกข้อความรายงานการขอซื้อ', 'size' => 'modal-md'],
            ['key' => 'purchase_6', 'label' => 'รายงานผลการพิจารณาและขออนุมัติสั่งซื้อสั่งจ้าง', 'size' => 'modal-md'],
            ['key' => 'purchase_7', 'label' => 'ประกาศผู้ชนะการเสนอราคา', 'size' => 'modal-md'],
            ['key' => 'purchase_8', 'label' => 'ใบสั่งซื้อ/สั่งจ้าง', 'size' => 'modal-xl'],
            ['key' => 'purchase_9', 'label' => 'ใบตรวจรับการจัดซื้อ/จัดจ้าง', 'size' => 'modal-xl'],
            ['key' => 'purchase_10', 'label' => 'รายงานผลการตรวจรับ', 'size' => 'modal-md'],
            ['key' => 'purchase_11', 'label' => 'แบบแสดงความบริสุทธิ์ใจ', 'size' => 'modal-xl'],
            ['key' => 'purchase_12', 'label' => 'ขออนุมัติจ่ายเงินบำรุง', 'size' => 'modal-md'],
        ];
    }

    /**
     * รายชื่อเอกสารพร้อมสถานะและปลายทางที่ควรไป
     *
     * @return array<int, array{
     *     key:string, label:string, size:string,
     *     converted:bool, template:?DocTemplate
     * }>
     */
    public static function resolved(): array
    {
        $templates = DocTemplate::byLegacyKey();

        $out = [];
        foreach (self::documents() as $doc) {
            $template = $templates[$doc['key']] ?? null;
            $out[] = $doc + [
                'converted' => $template !== null,
                'template' => $template,
            ];
        }

        return $out;
    }

    /** จำนวนที่แปลงแล้ว / ทั้งหมด — ใช้ขึ้นป้ายบอกความคืบหน้า */
    public static function progress(): array
    {
        $all = self::resolved();
        $done = 0;
        foreach ($all as $doc) {
            if ($doc['converted']) {
                $done++;
            }
        }

        return ['done' => $done, 'total' => count($all)];
    }
}
