<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\i18n\Formatter;

/**
 * ThaiDateHelper - คลาสช่วยจัดการรูปแบบวันที่แบบไทย
 */
class ThaiDateHelper extends Component
{
    /**
     * แปลงเป็นรูปแบบ '1 - 3 ม.ค. 2568'
     * สามารถใช้กับวันที่เดียวหรือช่วงวันที่ (start date - end date)
     * 
     * @param string|int|\DateTime $startDate วันที่เริ่มต้น
     * @param string|int|\DateTime|null $endDate วันที่สิ้นสุด (ถ้าไม่มีจะแสดงเป็นวันเดียว)
     * @param string $dateFormat รูปแบบวันที่ที่ต้องการ
     * @return string วันที่ในรูปแบบไทย
     */
    public static function formatThaiDateRange($startDate, $endDate = null, $dateFormat = 'short')
    {
        // ตรวจสอบค่าว่าง
        if (empty($startDate)) {
            return '';
        }

        // แปลงวันที่เป็น timestamp
        $startTimestamp = static::parseToTimestamp($startDate);
        
        // ถ้าไม่มีวันที่สิ้นสุด = แสดงเฉพาะวันที่เดียว
        if (empty($endDate)) {
            return static::formatThaiDate($startTimestamp, $dateFormat);
        }
        
        $endTimestamp = static::parseToTimestamp($endDate);
        
        // ดึงข้อมูลวันที่แยกเป็นส่วนต่างๆ
        $startDay = date('j', $startTimestamp);
        $startMonth = date('n', $startTimestamp);
        $startYear = date('Y', $startTimestamp);
        
        $endDay = date('j', $endTimestamp);
        $endMonth = date('n', $endTimestamp);
        $endYear = date('Y', $endTimestamp);
        
        // แปลงปีเป็นพุทธศักราช
        $startYearThai = $startYear + 543;
        $endYearThai = $endYear + 543;
        
        // ชื่อเดือนภาษาไทยแบบย่อ
        $thaiMonths = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];
        
        // กรณีเป็นวันเดียวกัน
        if ($startDay == $endDay && $startMonth == $endMonth && $startYear == $endYear) {
            return $startDay . ' ' . $thaiMonths[$startMonth] . ' ' . $startYearThai;
        }
        
        // กรณีเดือนเดียวกันและปีเดียวกัน
        if ($startMonth == $endMonth && $startYear == $endYear) {
            return $startDay . ' - ' . $endDay . ' ' . $thaiMonths[$startMonth] . ' ' . $startYearThai;
        }
        
        // กรณีปีเดียวกันแต่คนละเดือน
        if ($startYear == $endYear) {
            return $startDay . ' ' . $thaiMonths[$startMonth] . ' - ' . 
                   $endDay . ' ' . $thaiMonths[$endMonth] . ' ' . $startYearThai;
        }
        
        // กรณีคนละปี
        return $startDay . ' ' . $thaiMonths[$startMonth] . ' ' . $startYearThai . ' - ' . 
               $endDay . ' ' . $thaiMonths[$endMonth] . ' ' . $endYearThai;
    }

    /**
     * แปลงวันที่เป็นรูปแบบไทย
     * 
     * @param string|int|\DateTime $date วันที่ที่ต้องการแปลง
     * @param string $format รูปแบบที่ต้องการ (short, medium, long)
     * @return string วันที่ในรูปแบบไทย
     */
    public static function formatThaiDate($date, $format = 'short')
    {
        if (empty($date)) {
            return '';
        }
        
        $timestamp = static::parseToTimestamp($date);
        
        // แปลงปีเป็นพุทธศักราช
        $yearThai = date('Y', $timestamp) + 543;
        
        // ชื่อเดือนภาษาไทย
        $thaiMonthsShort = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];
        
        $thaiMonthsFull = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];
        
        $day = date('j', $timestamp);
        $month = date('n', $timestamp);
        
        switch ($format) {
            case 'short':
                return $day . ' ' . $thaiMonthsShort[$month] . ' ' . $yearThai;
            case 'medium':
                return $day . ' ' . $thaiMonthsFull[$month] . ' ' . $yearThai;
            case 'long':
                $thaiDays = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
                $dayOfWeek = date('w', $timestamp);
                return 'วัน' . $thaiDays[$dayOfWeek] . 'ที่ ' . $day . ' ' . $thaiMonthsFull[$month] . ' พ.ศ. ' . $yearThai;
            default:
                return $day . ' ' . $thaiMonthsShort[$month] . ' ' . $yearThai;
        }
    }
    
    /**
     * แปลงวันที่เป็น timestamp
     * 
     * @param string|int|\DateTime $date
     * @return int timestamp
     */
    protected static function parseToTimestamp($date)
    {
        if ($date instanceof \DateTime) {
            return $date->getTimestamp();
        }
        
        if (is_numeric($date)) {
            return (int) $date;
        }
        
        return strtotime($date);
    }
}