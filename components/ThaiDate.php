<?php

namespace app\components;

use Yii;
use DateTime;
use IntlDateFormatter;

class ThaiDate
{
    /**
     * แปลงวันที่เป็นรูปแบบไทย
     *
     * @param string $date วันที่ในรูปแบบ `Y-m-d H:i:s` หรือ `Y-m-d`
     * @param bool $showTime แสดงเวลาหรือไม่ (true = แสดง, false = ไม่แสดง)
     * @param bool $shortMonth ใช้ชื่อเดือนแบบย่อหรือไม่ (true = "มี.ค.", false = "มีนาคม")
     * @return string วันที่ที่ถูกแปลงแล้ว
     */
    public static function toThaiDate($date, $showTime = true, $shortMonth = false)
    {
        if (empty($date)) {
            return '-';
        }

        $dateTime = new DateTime($date);
        $year = $dateTime->format('Y') + 543; // แปลง ค.ศ. เป็น พ.ศ.

        // กำหนดรูปแบบวันที่
        $pattern = $shortMonth ? "d MMM $year" : "d MMMM $year";
        
        // ถ้าต้องการแสดงเวลา ให้เพิ่มเวลาเข้าไป
        if ($showTime) {
            $pattern .= ' HH:mm น.'; // แสดงเวลาแบบ 24 ชั่วโมง
        }

        // ใช้ IntlDateFormatter เพื่อจัดรูปแบบวันที่ไทย
        $formatter = new IntlDateFormatter(
            'th_TH',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Asia/Bangkok',
            IntlDateFormatter::GREGORIAN,
            $pattern
        );

        return $formatter->format($dateTime);
    }
}
