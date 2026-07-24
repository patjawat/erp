<?php

namespace app\modules\medsop\components;

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/**
 * ตัวช่วยจัดการข้อความแบบ Rich Text ของ MedSOP
 *
 * ฟิลด์ objective / scope / step.description / step.caution เดิมเก็บเป็น "ข้อความล้วน"
 * และแสดงผลด้วย nl2br(Html::encode()). เมื่อฟอร์มสร้างเอกสารเปิดให้จัดรูปแบบข้อความได้
 * ค่าที่บันทึกจะกลายเป็น HTML จำกัดชนิดแท็ก (bold, italic, underline, list)
 *
 * - sanitize() ใช้ตอน "บันทึก" เพื่อกรอง HTML ให้เหลือเฉพาะแท็กที่อนุญาต กัน stored XSS
 * - render()   ใช้ตอน "แสดงผล": ถ้าเป็น HTML ให้ purify, ถ้าเป็นข้อความล้วน (record เก่า)
 *   ให้ nl2br(encode) เหมือนเดิม เพื่อไม่ให้เอกสารเก่าเสียบรรทัด
 */
class RichText
{
    /** แท็กที่อนุญาตให้เก็บและแสดงผล (ไม่มี attribute ใด ๆ) */
    public const ALLOWED_TAGS = 'p,br,ul,ol,li,strong,em,b,i,u';

    /** ตรวจว่าค่ามีแท็ก HTML ที่เรารองรับหรือไม่ (ใช้แยก record ใหม่/เก่า) */
    private const HTML_PROBE = '/<(?:p|br|ul|ol|li|strong|em|b|i|u)\b[^>]*>/i';

    /**
     * กรอง HTML ที่ผู้ใช้ส่งมาให้เหลือเฉพาะแท็กที่อนุญาต ใช้ก่อนบันทึกลงฐานข้อมูล
     * คืน '' เมื่อเนื้อหาว่างเปล่า/เหลือแต่แท็กว่าง เพื่อให้ validation แบบ required ทำงานถูกต้อง
     */
    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $clean = HtmlPurifier::process($html, [
            'HTML.Allowed' => self::ALLOWED_TAGS,
            'AutoFormat.RemoveEmpty' => true,
            'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
        ]);

        // เหลือแต่ช่องว่าง/แท็กว่าง = ถือว่าไม่มีเนื้อหา
        return trim(strip_tags((string) $clean)) === '' ? '' : trim((string) $clean);
    }

    /**
     * แปลงค่าที่เก็บไว้ให้เป็น HTML ปลอดภัยสำหรับแสดงผล
     * - record ใหม่ (มีแท็ก) : purify ตาม allow-list
     * - record เก่า (ข้อความล้วน) : nl2br(Html::encode()) เพื่อคงบรรทัดเดิม
     */
    public static function render(?string $value): string
    {
        $value = (string) $value;
        if (trim($value) === '') {
            return '';
        }

        if (preg_match(self::HTML_PROBE, $value)) {
            return HtmlPurifier::process($value, [
                'HTML.Allowed' => self::ALLOWED_TAGS,
                'AutoFormat.RemoveEmpty' => true,
            ]);
        }

        return nl2br(Html::encode($value));
    }
}
