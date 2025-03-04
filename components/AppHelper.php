<?php

namespace app\components;

use Yii;
use DateTime;
use app\models\Visit;
use yii\helpers\Html;
use app\models\Profile;
use yii\base\Component;
use app\models\Hospcode;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use yii\helpers\BaseFileHelper;
use app\modules\usermanager\models\User;

// รวม function ตที่ใช้งานบ่อยๆ
class AppHelper extends Component
{
    private $title;

    // คำนวน วัน เวลา ที่ผ่านมา
    //  public static function Duration($end){
    //     return round(abs(strtotime(date('Y-m-d 00:00:00')) - strtotime($model->created_at))/60/60/24);
    // }

    // บันทึกเลขบตรประชาชนลง Database
    public static function SaveCid($data)
    {
        $cid = '';
        foreach (explode('-', $data) as $key => $val) {
            $cid .= $val;
        }
        return $cid;
    }

    // แปลงเป็นตัวเลข
    public static function formatNumber($input)
    {
        // ตัวอย่างการใช้งาน
        // echo formatNumber("2,590.00"); // แสดงผล: 2590
        // echo formatNumber("2590");     // แสดงผล: 2590
        // ตรวจสอบว่ามีคอมม่าหรือจุดทศนิยมในสตริงหรือไม่
        if (strpos($input, ',') !== false || strpos($input, '.') !== false) {
            // ลบคอมม่าออก
            $output = str_replace(',', '', $input);
            // ลบจุดทศนิยม (ถ้ามี)
            $output = explode('.', $output)[0];
            return $output;
        }
        // หากไม่มีคอมม่าหรือจุดทศนิยม ให้แสดงผลตามค่าเดิม
        return $input;
    }
    //แปลงรูปแบบ format 'Y-m-d'
    public static function convertToYMD($date)
    {
        // แยกวันที่ออกเป็นวัน เดือน ปี
        $dateParts = explode("/", $date);
        // ตรวจสอบรูปแบบวันที่
        if (count($dateParts) !== 3) {
            return 'Invalid date format.'.$date;
        }
        $day = $dateParts[0];
        $month = $dateParts[1];
        $year = $dateParts[2];
        // ตรวจสอบว่าปีเป็น พ.ศ. หรือ ค.ศ.
        if ($year > 2500) {
            $year = $year - 543;  // แปลง พ.ศ. เป็น ค.ศ.
        }
        // แปลงเป็นรูปแบบ y-m-d
        return sprintf('%d-%02d-%02d', $year, $month, $day);
    }

    // นับวันหยุด
    public static function CalDay($dateStart, $dateEnd)
    {
        $me = UserHelper::GetEmployee();
        // นับวันหยุดไม่รวมเสาร์-อาทิตย์
        $sqlsatsunDays = "WITH RECURSIVE date_range AS (
                        SELECT :date_start AS date
                        UNION ALL
                        SELECT DATE_ADD(date, INTERVAL 1 DAY)
                        FROM date_range
                        WHERE date < :date_end
                        )
                        SELECT count(date) as count_days FROM date_range
                        WHERE DAYNAME(date) IN('Saturday','Sunday');";

// นับจำนวนวันเสาร์-อาทิตย์
$sqlSundays = 'SELECT (WEEK(:date_end, 1) - WEEK(:date_start, 1)) * 2 -- ลบเสาร์-อาทิตย์
                        - CASE 
                            WHEN DAYOFWEEK(:date_start) = 7 THEN 1 -- ถ้าวันแรกเป็นเสาร์ ให้ลบ 1
                            WHEN DAYOFWEEK(:date_end) = 7 THEN 1 -- ถ้าวันสุดท้ายเป็นเสาร์ ให้ลบ 1
                            ELSE 0
                            END 
                        - CASE
                            WHEN DAYOFWEEK(:date_end) = 1 THEN 1 -- ถ้าวันสุดท้ายเป็นอาทิตย์ ให้ลบอีก 1
                            ELSE 0
                            END AS date_count;';
                            
                            // หาจำนวนวันหยุด
                            $sqlHoliday = "SELECT count(id) FROM `calendar` WHERE name = 'holiday' AND date_start BETWEEN :date_start AND :date_end";
                            // ตารางปฏิทินวันหยุดกรณีที่เป็นพยาบาลหรือมีขึ้นเวร
                             $sqlHolidayMe = "SELECT count(id) FROM `calendar` WHERE name = 'off' AND date_start BETWEEN :date_start AND :date_end";
                             //นับวัน Off
                             $sqlDayOff = "SELECT count(id) FROM `calendar` WHERE name = 'off' AND emp_id =  :emp_id AND MONTH(date_end) = MONTH(:date_end);";
                             $countDayOff = Yii::$app->db->createCommand($sqlDayOff)
                             ->bindValue(':emp_id', $me->id)
                             ->bindValue(':date_end', $dateEnd)
                             ->queryScalar();
                            
                            // นับจำนวนวันทั้งหมด
                            $sqlAllDays = "WITH RECURSIVE date_range AS (SELECT :date_start AS date UNION ALL SELECT DATE_ADD(date, INTERVAL 1 DAY) FROM date_range WHERE date < :date_end ) SELECT count(date) as count_days FROM date_range;"; 
                            $countAllDays = Yii::$app->db->createCommand($sqlAllDays)->bindValue(':date_start', $dateStart)->bindValue(':date_end', $dateEnd)->queryScalar();
        $satsunDays = Yii::$app->db->createCommand($sqlsatsunDays)->bindValue(':date_start', $dateStart)->bindValue(':date_end', $dateEnd)->queryScalar();
        // $sunDay = Yii::$app->db->createCommand($sqlSundays)->bindValue(':date_start', $dateStart)->bindValue(':date_end', $dateEnd)->queryScalar();
        $holiday = Yii::$app->db->createCommand($sqlHoliday)->bindValue(':date_start', $dateStart)->bindValue(':date_end', $dateEnd)->queryScalar();
        //  $holidayMe =   Yii::$app->db->createCommand($sqlHolidayMe)->bindValue(':date_start', $dateStart)->bindValue(':date_end', $dateEnd)->queryScalar();
        return [
            'allDays' => $countAllDays,
            'satsunDays' => $satsunDays,
            'dayOff' => $countDayOff,
            // 'sunDay' => $sunDay,
            'holiday' => $holiday,
            //  'holidy_me' =>  $holidayMe
        ];
    }

    // หาปีงบประมาณไทย
    public static function YearBudget($date = null)
    {
        if ($date) {
            return Yii::$app
                ->db
                ->createCommand('SELECT IF(MONTH(:date)>9,YEAR(:date)+1,YEAR(:date)) + 543 AS year_bud')
                ->bindValue(':date', $date)
                ->queryScalar();
            // ->getRawSql();
        } else {
            return Yii::$app->db->createCommand('SELECT IF(MONTH(NOW())>9,YEAR(NOW())+1,YEAR(NOW())) + 543 AS year_bud')->queryScalar();
        }
    }

    // แปลงปีงบประมาณไทยเป็น แบบ ค.ศ. ปกติ
    public static function ThaiToGregorian($date = null)
    {
        return Yii::$app
            ->db
            ->createCommand("SELECT LAST_DAY(CONCAT((IF(MONTH(:date)>9,YEAR(:date)-1,YEAR(:date)) - 543),'-',DATE_FORMAT(:date, '%m-%d'))) AS year_bud")
            ->bindValue(':date', $date)
            ->queryScalar();

        // ->getRawSql();
    }

    // สร้าง Directory
    public static function CreateDir($folderName)
    {
        if ($folderName != null) {
            BaseFileHelper::createDirectory($folderName, 0777);
        }
        return;
    }

    // แปลง ค.ศ. เป็น พ.ศ.

    public static function convertToThai($date)
    {
        if ($date !== null) {
            $dateTime = new DateTime($date);
            $year = $dateTime->format('Y') + 543;
            return $dateTime->format("d/m/{$year}");
        }
        return null;
    }

    // แปลง พ.ศ. เป็น ค.ศ.
    public static function convertToGregorian($date)
    {
        if ($date !== null || $date !== '__/__/____') {
            list($day, $month, $year) = explode('/', $date);
            $y = ($year - 543);
            return "{$y}-{$month}-{$day}";
        }
        return null;
    }

    // แปลงตัวเลขเป็นตัวหนังสือ
    public static function convertNumberToWords($number)
    {
        $units = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
        $positions = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];

        if ($number == 0) {
            return 'ศูนย์';
        }

        if ($number < 0) {
            return 'ลบ ' . self::convertNumberToWords(abs($number));
        }

        $string = '';
        $position = 0;

        while ($number > 0) {
            $digit = (int) $number % 10;

            if ($position == 0 && $digit == 1 && $string != '') {
                $string = 'เอ็ด' . $string;
            } elseif ($position == 1 && $digit == 1) {
                $string = 'สิบ' . $string;
            } elseif ($position == 1 && $digit == 2) {
                $string = 'ยี่สิบ' . $string;
            } elseif ($digit != 0) {
                $string = $units[$digit] . $positions[$position] . $string;
            }

            $number = intval($number / 10);
            $position++;
        }

        return $string;
    }

    // แปลงตัวเลขอาราบิก เป็น ตัวเลขไทย
    public static function thainumDigit($num)
    {
        return str_replace(array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'),
            array('o', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙'),
            $num);
    }

    public static function viewProgressBar($val)
    {
        switch (true) {
            case $val <= 20:
                $color = 'bg-danger';
                break;

            case $val <= 40:
                $color = 'bg-warning';
                break;

            case $val <= 60:
                $color = 'bg-primary';
                break;

            default:
                $color = 'bg-success';
                break;
        }
        if ($val) {
            return '<div class="d-flex align-items-center justify-content-between">
        <div class="progress w-50" style="height: 5px;">
               <div class="progress-bar ' . $color . '" role="progressbar" aria-label="Example with label" style="width: ' . $val . '%;" aria-valuenow="14" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <span class="badge rounded-pill bg-primary text-white shadow">' . $val . '%</span>
        </div>';
        } else {
            return '<div class="progress w-50" style="height: 5px;">
         <div class="progress-bar" role="progressbar" aria-label="Example with label" style="width:0%;" aria-valuenow="14" aria-valuemin="0" aria-valuemax="100"></div>
  </div>';
        }
    }

    public static function cidFormat($text = '', $pattern = '', $ex = '')
    {
        $cid = ($text == '') ? '0000000000000' : $text;
        $pattern = ($pattern == '') ? '_-____-_____-__-_' : $pattern;
        $p = explode('-', $pattern);
        $ex = ($ex == '') ? '-' : $ex;
        $first = 0;
        $last = 0;
        for ($i = 0; $i <= count($p) - 1; $i++) {
            $first = $first + $last;
            $last = strlen($p[$i]);
            $returnText[$i] = substr($cid, $first, $last);
        }

        return implode($ex, $returnText);
    }

    // ใช้คำนวนวันเวลาที่ผ่านมา
    public static function timeDifference($dateTime)
    {
        $currentDateTime = new DateTime();
        $targetDateTime = new DateTime($dateTime);

        $interval = $currentDateTime->diff($targetDateTime);

        if ($interval->y >= 1) {
            return $interval->y . ' ปี';
        } elseif ($interval->m >= 1) {
            return $interval->m . ' เดือน';
        } elseif ($interval->d >= 7) {
            return floor($interval->d / 7) . ' สัปดาห์';
        } elseif ($interval->d >= 1) {
            return $interval->d . ' วัน';
        } elseif ($interval->h >= 1) {
            return $interval->h . ' ชั่วโมง';
        } else {
            return $interval->i . ' นาที';
        }
    }

    public static function CompareDate($array)
    {
        if (!is_array($array)) {
            return;
        }

        if ((!array_key_exists('begin', $array)) || empty($array['begin'])) {
            return;
        }

        if ((!array_key_exists('end', $array)) || empty($array['end'])) {
            return;
        }

        $begin_time = strtotime($array['begin']);
        $end_time = strtotime($array['end']);

        $amount_time = $end_time - $begin_time;

        $list = array(
            'day' => array('วัน', '86400'),
            'hour' => array('ชั่วโมง', '3600'),
            'munite' => array('นาที', '60'),
            'second' => array('วินาที', '1')
        );

        foreach ($list as $value):
            $result = floor($amount_time / $value[1]);
            if ($result > 0) {
                $return[] = $result;
                $return[] = $value[0];
            }

            $amount_time = $amount_time % $value[1];
        endforeach;

        return implode(' ', $return) . ' ผ่านมา';
    }

    //  หาชื่อเดือน
    public static function getMonthName($monthNumber)
    {
        // สร้างอาร์เรย์ที่เก็บชื่อเดือน
        $months = [
            1 => 'มกราคม',
            2 => 'กุมภาพันธ์',
            3 => 'มีนาคม',
            4 => 'เมษายน',
            5 => 'พฤษภาคม',
            6 => 'มิถุนายน',
            7 => 'กรกฎาคม',
            8 => 'สิงหาคม',
            9 => 'กันยายน',
            10 => 'ตุลาคม',
            11 => 'พฤศจิกายน',
            12 => 'ธันวาคม'
        ];

        // ตรวจสอบว่าเดือนถูกต้องหรือไม่
        if ($monthNumber >= 1 && $monthNumber <= 12) {
            return $months[$monthNumber];  // คืนค่าชื่อเดือน
        } else {
            return 'เดือนไม่ถูกต้อง';  // หากตัวเลขไม่ใช่เดือนที่ถูกต้อง
        }
    }

    // แปลง วัน เดือน ปี เป็น ไทย
    public static function ThaiDate($datetime, $format, $clock)
    {
        if ($datetime) {
            list($date, $time) = explode(' ', $datetime);
            // list($H,$i,$s) = split(':',$time);
            list($Y, $m, $d) = explode('-', $date);
            $Y = $Y + 543;

            $month = array(
                '0' => array('01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฏาคม', '08' => 'สิงหาคม', '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤษจิกายน', '12' => 'ธันวาคม'),
                '1' => array('01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.', '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.', '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.')
            );
            if ($clock == false)
                return $d . ' ' . $month[$format][$m] . ' ' . $Y;
            else
                return $d . ' ' . $month[$format][$m] . ' ' . $time;
        }
    }

    public static function Duration($begin, $end)
    {
        $remain = intval(strtotime($end) - strtotime($begin));
        $wan = floor($remain / 86400);
        $l_wan = $remain % 86400;
        $hour = floor($l_wan / 3600);
        $l_hour = $l_wan % 3600;
        $minute = floor($l_hour / 60);
        $second = $l_hour % 60;
        // return ($wan > 0 ? "ผ่านมาแล้ว " . $wan . " วัน " : " วันนี้ ") . ($hour > 0 ? $hour . " ชั่วโมง " : null) . ($minute > 0 ?  $minute. " นาที " : null) . ($second > 0 ? $second . " วินาที" : null);
        // ($wan > 0 ? "ผ่านมาแล้ว " . $wan . " วัน " : " วันนี้ ");
        if ($wan <= 7) {
            return ($wan > 0 ? '<i class="bi bi-calendar2-check-fill"></i> ผ่านมาแล้ว ' . $wan . ' วัน ' : ('<i class="bi bi-clock-history"></i> วันนี้ ') . Yii::$app->formatter->asDateTime($begin, 'php:H:i:s'));
        } else {
            return $begin;
        }
    }

    // แปลงวันที่ลง Databas
    public static function DateToDb($date)
    {
        try {
            if ($date != '__/__/____') {
                $dmy = explode('/', $date);  // แยก วัน/เดือน/ปี
                $year = (int) $dmy[2];  // กำหนดเป็น int เพื่อการคำนวณ
                $year = $year - 543;  // ปี พ.ศ.-543
                return $year . '-' . $dmy[1] . '-' . $dmy[0];  // ได้รูปแบบ 2016-05-20
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            return null;
        }
    }

    // แปลงวันที่จาก database
    public static function DateFormDb($date)
    {
        try {
            $dmy = explode('-', $date);  // แยก วัน/เดือน/ปี
            $year = (int) $dmy[0];  // กำหนดเป็น int เพื่อการคำนวณ
            $year = $year + 543;  // ปี พ.ศ.-543
            return $dmy[2] . '/' . $dmy[1] . '/' . $year;  // ได้รูปแบบ 20/10/2566
        } catch (\Throwable $th) {
            return null;
        }
    }

    // public static function Age($date,$year = null)
    // {
    //    try {
    //       $getDate = self::DateToDb($date);
    //       $currentDate = date('Y-m-d'); //วันที่ปัจจุบัน
    //       $diff = abs(strtotime($currentDate) - strtotime($getDate));
    //       $years = floor($diff / (365 * 60 * 60 * 24));
    //       $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
    //       $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
    //       // printf("%d ปี, %d เดือน, %d วัน\n", $years, $months, $days);
    //       // return $years . ' ปี';
    //       if($year){
    //          return $years;
    //       }else{
    //          return  $years.' ปี '.$months.' เดือน '.$days.' วัน';
    //       }
    //    } catch (\Throwable $th) {
    //       return null;
    //    }
    // }

    // คำนวนจาก mysql
    public static function Age($date, $year = null)
    {
        // try {
        $getDate = self::DateToDb($date);
        $currentDate = date('Y-m-d');  // วันที่ปัจจุบัน
        $sql = 'SELECT TIMESTAMPDIFF(YEAR, :birthday, NOW()) AS years,
         TIMESTAMPDIFF(MONTH, :birthday, NOW()) % 12 AS months,
         FLOOR(TIMESTAMPDIFF(DAY, :birthday, NOW()) % 30.4375) AS days;';
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindParam(':birthday', $getDate)
            ->queryOne();

        return [
            'full' => $query['years'] . ' ปี ' . $query['months'] . ' เดือน ' . $query['days'] . ' วัน',
            'year' => $query['years'],
            'month' => $query['months'],
            'day' => $query['days'],
        ];
        // if ($year) {
        //     return $query['years'];
        // } else {
        //     return $query['years'] . ' ปี ' . $query['months'] . ' เดือน ' . $query['days'] . ' วัน';
        // }
        // } catch (\Throwable $th) {
        //    return null;
        // }
    }

    // CountDown นับถอยหลัง
    public static function CountDown($date, $year = null)
    {
        // try {
        $getDate = self::DateToDb($date);
        $currentDate = date('Y-m-d');  // วันที่ปัจจุบัน
        $sql = 'SELECT TIMESTAMPDIFF(YEAR, NOW(), :birthday) AS years,
         TIMESTAMPDIFF(MONTH, NOW(), :birthday) % 12 AS months,
         FLOOR(TIMESTAMPDIFF(DAY, NOW(),:birthday) % 30.4375) AS days;';
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindParam(':birthday', $getDate)
            ->queryOne();

        if ($year) {
            return $query['years'];
            // return 'xx';
        } else {
            return $query['years'] . ' ปี ' . $query['months'] . ' เดือน ' . $query['days'] . ' วัน';
        }
        // } catch (\Throwable $th) {
        //    return null;
        // }
    }

    public static function CategoriseByName($name = null)
    {
        if ($name) {
            return Categorise::find()->where(['name' => $name])->all();
        } else {
            return null;
        }
    }

    public static function CategoriseById($id = null)
    {
        if ($id) {
            return Categorise::find()->where(['id' => $id])->one();
        } else {
            return null;
        }
    }

    public static function prefixName()
    {
        return ArrayHelper::map(self::CategoriseByName('prefix'), 'title', 'title');
    }

    public static function showActive($active)
    {
        return $active == 1 ? '<i class="fa-regular fa-circle-check text-primary"></i>' : '<i class="fa-solid fa-circle-minus text-danger"></i>';
    }

    public static function InitailPatient() {}

    public static function checkFileType($type)
    {
        switch ($type) {
            case 'doc':
                return 'office';
                break;

            case 'docx':
                return 'gdocs';
                break;
            case 'xls':
                return 'office';
                break;
            case 'xlsx':
                return 'office';
                break;
            case 'pdf':
                return 'pdf';
                break;
            case 'mp4':
                return 'video';
                break;
            case 'tif':
                return 'gdocs';
                break;

            default:
                return 'image';
                break;
        }
    }

    // หาค่า BMI

    public static function getBMI($weight, $height)
    {
        if (!empty($weight) && !empty($height)) {
            $cal = $weight / (($height / 100) ** 2);
            $bmi = number_format($cal, 2);

            if ($bmi <= 18.5) {
                return [
                    'bmi' => $bmi,
                    'criterion' => '< 18.50',
                    'point' => 'น้ำหนักน้อย / ผอม',
                    'risk' => 'มากกว่าคนปกติ',
                    'progress' => 20,
                    'color' => 'bg-info'
                ];
            } else if ($bmi > 18.5 && $bmi < 22.9) {
                return [
                    'bmi' => $bmi,
                    'criterion' => 'ระหว่าง 18.50 - 22.90',
                    'point' => 'ปกติ (สุขภาพดี)',
                    'risk' => 'เท่าคนปกติ',
                    'progress' => 40,
                    'color' => 'bg-primary'
                ];
            } else if ($bmi > 23 && $bmi < 24.9) {
                return [
                    'bmi' => $bmi,
                    'criterion' => 'ระหว่าง 23 - 24.90',
                    'point' => 'ท้วม / โรคอ้วนระดับ 1',
                    'risk' => 'อันตรายระดับ 1',
                    'progress' => 60,
                    'color' => 'bg-warning'
                ];
            } else if ($bmi > 25 && $bmi < 29.9) {
                return [
                    'bmi' => $bmi,
                    'criterion' => 'ระหว่าง 25 - 25.90',
                    'point' => 'อ้วน / โรคอ้วนระดับ 2',
                    'risk' => 'อันตรายระดับ 2',
                    'progress' => 80,
                    'color' => 'bg-danger'
                ];
            } else if ($bmi > 30) {
                return [
                    'bmi' => $bmi,
                    'criterion' => '> 30',
                    'point' => 'อ้วนมาก / โรคอ้วนระดับ 3',
                    'risk' => 'อันตรายระดับ 3',
                    'progress' => 100,
                    'color' => 'bg-danger'
                ];
            }
        } else {
            return [
                'bmi' => 0,
                'criterion' => '',
                'point' => '',
                'risk' => '',
                'progress' => 0,
                'color' => ''
            ];
        }
    }

    // Button Helper

    public static function BtnSave($msg = null)
    {
        $msg = $msg ? $msg : '<i class="fa-regular fa-circle-check"></i> บันทึก';
        return Html::submitButton($msg, ['class' => 'btn btn-primary']);
    }

    public static function Btn($arr = [])
    {
        $title = '<i class="bi bi-plus"></i> สร้างใหม่';
        $url = ['create'];
        $modal = false;
        $pjax = 0;
        $size = '';
        $class = 'btn btn-outline-primary';

        if (array_key_exists('title', $arr)) {
            $title = $arr['title'];
        }

        if (array_key_exists('type', $arr)) {
            if ($arr['type'] == 'update') {
                $title = '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข';
            }
        }
        if (array_key_exists('url', $arr)) {
            $url = $arr['url'];
        }

        if (array_key_exists('size', $arr)) {
            $size = 'modal-' . $arr['size'];
        }

        if (array_key_exists('class', $arr)) {
            $class = $arr['class'];
        }

        if (array_key_exists('modal', $arr)) {
            if ($arr['modal'] == true) {
                $class = $class . ' open-modal';
                $pjax = 1;
            } else {
                $pjax = 0;
            }
        }

        return Html::a($title, $url, ['class' => $class, 'data' => [
            'pjax' => $pjax,
            'size' => $size
        ]]);
    }

    public static function MsgWarning($msg = null)
    {
        return '<i class="fa-solid fa-circle-exclamation text-danger me-1"></i>' . $msg;
    }

    public static function GetDepreciation($year, $price, $Drate)
    {
        $data = [];
        // $Drate = ( $Drate / $year ) * 2;
        $priceCurrent = $price;
        for ($i = 1; $i <= $year; $i++) {
            $del = (($priceCurrent * $Drate) / 100);
            array_push($data, [
                $priceCurrent,
                $Drate,
                $del,
                $priceCurrent - $del
            ]);
            $priceCurrent = $priceCurrent - $del;
        }
        return $data;
    }

    public static function GetDepreciationByDay($price, $Drate, $year, $imported)
    {
        $imported = date('Y-m-d', strtotime($imported . '+' . $year . ' years'));
        $A = $price - (($price * $Drate) / 100);
        $ReducePerYear = ($price * (($A / $price) ** ((date('Y') - date('Y', strtotime($imported)))))) - ($price * (($A / $price) ** ((date('Y') - date('Y', strtotime($imported))) + 1)));
        if (date('L') == 1) {
            $ReducePerDay = $ReducePerYear / (date('z', mktime(0, 0, 0, 12, 31, date('Y'))) + 1);
        } else {
            $ReducePerDay = $ReducePerYear / date('z', mktime(0, 0, 0, 12, 31, date('Y')));
        }

        $beforeDay = ($price * (($A / $price) ** ((date('Y') - date('Y', strtotime($imported)))))) - ($ReducePerDay * (date('z') - 1));
        $result = ($price * (($A / $price) ** ((date('Y') - date('Y', strtotime($imported)))))) - ($ReducePerDay * date('z'));
        return [
            $price,
            $beforeDay,
            $Drate,
            $ReducePerDay * date('t'),
            $result
        ];
    }

    public static function GetDepreciationByMonth()
    {
        // แสดงจำนวนวันใน 5 ปี
        $sql1 = "SELECT DATEDIFF(DATE_FORMAT(NOW() + INTERVAL 5 YEAR,'%Y-%m-%d'),NOW());";
        $querys1 = Yii::$app->db->createCommand($sql1)->queryScalar();
        // แสดงวันทั้งหมดในเดือน
        $sql2 = "select 
      DATE_FORMAT(m1, '%Y-%m-%d') as month_year,
      DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month

      from
      (
      select ('2020-01-01' - INTERVAL DAYOFMONTH('2020-01-01')-1 DAY) 
      +INTERVAL m MONTH as m1
      from
      (
      select @rownum:=@rownum+1 as m from
      (select 1 union select 2 union select 3 union select 4) t1,
      (select 1 union select 2 union select 3 union select 4) t2,
      (select 1 union select 2 union select 3 union select 4) t3,
      (select 1 union select 2 union select 3 union select 4) t4,
      (select @rownum:=-1) t0
      ) d1
      ) d2 
      where m1<= DATE_FORMAT('2020-01-01' + INTERVAL 3 YEAR,'%Y-%m-%d')
      order by m1";
        $querys = Yii::$app->db->createCommand($sql2)->queryAll();
        return $querys;
    }

    public static function GetDataCsv($data)
    {
        if (count($data) == 19) {
            return $data;
        } elseif (count($data) == 20) {
            $data[12] = preg_replace('/[^0-9.]/', '', $data[12] . $data[13]);
            unset($data[13]);
        } elseif (count($data) == 21) {
            $data[12] = preg_replace('/[^0-9.]/', '', $data[12] . $data[13] . $data[14]);
            unset($data[13]);
            unset($data[14]);
        }
        $data = explode(',', implode(',', $data));
        return $data;
    }
}
