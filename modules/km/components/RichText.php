<?php

namespace app\modules\km\components;

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/**
 * Rich text สำหรับช่องเนื้อหากิจกรรม KM (สรุปย่อ / วัตถุประสงค์ / รายละเอียด)
 * ให้จัดรูปแบบแบบ Word ได้ (ตัวหนา เอียง ขีดเส้นใต้ หัวข้อ รายการ ตาราง)
 *
 * - sanitize(): กรอง HTML ก่อนบันทึกด้วย HtmlPurifier (กัน XSS + จำกัดแท็ก)
 * - render():   แสดงผล — ถ้าเป็น HTML ก็ purify แล้วแสดง ; ถ้าเป็นข้อความเก่า (plain) ก็ nl2br
 *
 * แนวเดียวกับ app\modules\jd\components\RichText (สำเนาต่อโมดูลตามคอนเวนชันโปรเจกต์)
 */
class RichText
{
    public const ALLOWED_TAGS = 'p,br,h4,h5,ul,ol,li,strong,em,b,i,u,s,blockquote,a[href|target|rel],table[class],caption,thead,tbody,tfoot,tr,th[colspan|rowspan|scope],td[colspan|rowspan]';
    private const HTML_PROBE = '/<(?:p|br|h4|h5|ul|ol|li|strong|em|b|i|u|s|blockquote|a|table|caption|thead|tbody|tfoot|tr|th|td)\b[^>]*>/i';

    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $clean = HtmlPurifier::process($html, [
            'HTML.Allowed' => self::ALLOWED_TAGS,
            'HTML.TargetBlank' => true,
            'AutoFormat.RemoveEmpty' => true,
            'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
        ]);

        // ถ้าเหลือแต่แท็กว่าง ไม่มีข้อความจริง ให้ถือว่าว่าง
        return trim(strip_tags((string) $clean)) === '' ? '' : trim((string) $clean);
    }

    public static function render(?string $value): string
    {
        $value = (string) $value;
        if (trim($value) === '') {
            return '';
        }
        if (preg_match(self::HTML_PROBE, $value)) {
            return HtmlPurifier::process($value, [
                'HTML.Allowed' => self::ALLOWED_TAGS,
                'HTML.TargetBlank' => true,
                'AutoFormat.RemoveEmpty' => true,
            ]);
        }
        return nl2br(Html::encode($value));
    }
}
