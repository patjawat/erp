<?php

namespace app\modules\finance\components;

/**
 * แปลงจำนวนเงินเป็นข้อความภาษาไทย (บาท/สตางค์) สำหรับรายงานราชการ เช่น แบบ 407
 */
class BahtText
{
    private const DIGITS = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    private const PLACES = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน'];

    public static function convert($number): string
    {
        $number = number_format((float) $number, 2, '.', '');
        [$baht, $satang] = explode('.', $number);
        $baht = ltrim($baht, '0');

        $text = ($baht === '' ? 'ศูนย์' : self::readInteger($baht)) . 'บาท';
        if ($satang === '00') {
            $text .= 'ถ้วน';
        } else {
            $text .= self::readInteger(ltrim($satang, '0') ?: '0') . 'สตางค์';
        }
        return $text;
    }

    private static function readInteger(string $num): string
    {
        $num = ltrim($num, '0');
        if ($num === '') {
            return '';
        }
        $len = strlen($num);
        // จำนวนเกินหลักล้าน ตัดเป็นชุดละ 6 หลักแล้วต่อด้วย "ล้าน"
        if ($len > 6) {
            return self::readInteger(substr($num, 0, $len - 6)) . 'ล้าน' . self::readInteger(substr($num, $len - 6));
        }
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $d = (int) $num[$i];
            $place = $len - $i - 1;
            if ($d === 0) {
                continue;
            }
            if ($place === 1 && $d === 1) {
                $result .= 'สิบ';
            } elseif ($place === 1 && $d === 2) {
                $result .= 'ยี่สิบ';
            } elseif ($place === 0 && $d === 1 && $len > 1) {
                $result .= 'เอ็ด';
            } else {
                $result .= self::DIGITS[$d] . self::PLACES[$place];
            }
        }
        return $result;
    }
}
