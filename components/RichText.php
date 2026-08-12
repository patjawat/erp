<?php

namespace app\components;

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/**
 * ตัวช่วยจัดการข้อความแบบ Rich Text ระดับแอป
 *
 * ฟิลด์ข้อความยาวที่เปิดให้จัดเป็นข้อ/หัวข้อย่อยได้ จะเก็บเป็น HTML จำกัดชนิดแท็ก
 * ส่วนข้อมูลเดิมที่บันทึกไว้เป็นข้อความล้วนยังแสดงผลได้ถูกต้องโดยไม่เสียบรรทัด
 *
 * - sanitize() ใช้ตอน "บันทึก" เพื่อกรอง HTML ให้เหลือเฉพาะแท็กที่อนุญาต กัน stored XSS
 * - render()   ใช้ตอน "แสดงผล": ถ้าเป็น HTML ให้ purify, ถ้าเป็นข้อความล้วนให้ nl2br(encode)
 *
 * ใช้คู่กับ RichTextAsset ที่ครอบ textarea[data-richtext] ด้วยแถบเครื่องมือฝั่งหน้าเว็บ
 */
class RichText
{
    /** แท็กที่อนุญาตให้เก็บและแสดงผล (ไม่มี attribute ใด ๆ) */
    public const ALLOWED_TAGS = 'p,br,ul,ol,li,strong,em,b,i,u';

    /** ตรวจว่าค่ามีแท็ก HTML ที่เรารองรับหรือไม่ (ใช้แยกข้อมูลใหม่/เก่า) */
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
     * - ข้อมูลใหม่ (มีแท็ก) : purify ตาม allow-list
     * - ข้อมูลเก่า (ข้อความล้วน) : nl2br(Html::encode()) เพื่อคงบรรทัดเดิม
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

    /** ข้อความล้วนของค่า ใช้ตอนแสดงในตาราง/หัวข้อที่ไม่ต้องการแท็ก */
    public static function plain(?string $value, int $limit = 0): string
    {
        $text = html_entity_decode(strip_tags(str_replace(['</li>', '</p>', '<br>', '<br />'], ' ', (string) $value)), ENT_QUOTES, 'UTF-8');
        // &nbsp; ที่ตัวแก้ไขข้อความใส่มาไม่ถูก \s จับ จึงต้องแปลงเป็นช่องว่างปกติก่อน
        $text = trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $text)));
        return $limit > 0 && mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) . '…' : $text;
    }

    /** ค่าว่างหรือไม่ หลังตัดแท็กออกแล้ว */
    public static function isEmpty(?string $value): bool
    {
        return self::plain($value) === '';
    }
}
