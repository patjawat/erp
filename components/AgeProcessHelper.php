<?php

namespace app\components;

use yii\base\Component;

// รวม function ตที่ใช้งานบ่อยๆ
class AgeProcessHelper extends Component
{

   public $age_year;
   public $age_month;
   public $age_day;


    public function process_age()
    {
        $dte = explode('-', $this->birthday); //yyyy-mm-dd
        $time = strtotime($this->birthday);

        //จำนวนเดือน
        $mon = ($dte[1] >= date('m') ? 12 - ($dte[1] - date('m')) : date('m') - $dte[1]);

        //หาจำนวนปี และคำนวณเดือนใหม่ กรณีที่ยังไม่เต็มเดือน
        if (date("md") < "$dte[1]$dte[2]") { //ยังไม่ถึงวันเกิด
            $age = ((date("Y") - $dte[0]) - 1); //ต้อง ลดปีลงหนึ่ง
            if (date("d") < "$dte[2]") {
                $mon--;
            }
            //ถ้า วันที่ปัจจุบัน น้อยกว่าวันที่เกิด  แสดงว่ายังไม่เต็มเดือน ให้ลดตัวเลขเดือน ลงหนึ่ง
        } else { // เดือนวัน เลยเดือนวันเกิด
            $age = (date("Y") - $dte[0]); //ไม่ต้องลดปี เพราะถึงวันเกิดแล้ว
            if ($mon >= 12) {
                $mon = 0;
            }
            //ถ้าเกิน 12 เดือน ให้กำหนดเป็น 0

            if (date("d") < "$dte[2]") {
                $mon--;
            }
            //ถ้าวันที่ปัจจุบัน น้อยกว่าวันที่เกิด  แสดงว่ายังไม่เต็มเดือน ให้ลดตัวเลขเดือน ลงหนึ่ง
        }

        //หาจำนวนวัน
        if (date("d") < "$dte[2]") { //ถ้าวันปัจจุบัน น้อยกว่าวันเกิด (คือข้ามเดือนใหม่)
            $last_day = date('t', strtotime("-1 month")); //หาตัวเลขวันที่ล่าสุดในปฏิทินของเดือนที่ผ่านมา
            if ($last_day > $dte[2]) { //ถ้าวันสุดท้ายของเดือน มากกว่าวันเกิด
                $day = ($last_day - $dte[2]) + date('j'); //เอาค่าวันที่ที่เหลือ ของเดือนก่อนหน้า มารวมกับวันที่ในเดือนปัจจุบัน
            } else { //ถ้าไม่มากกว่า ก็แสดงว่าวันเกิดอยู่วันสดท้าย หรือเดือนนั้นวันที่น้อยกว่าวันเกิด
                $day = date('j'); //ก็นับวันปัจจุบันไปเลย ไม่ต้องบวกเพิ่ม
            }
        } else { //วันที่ เลยวันเกิดไปแล้ว แสดงว่าอยู่ช่วงสิ้นเดือน
            $day = date('d') - $dte[2]; //ให้เอาวันที่ - วันเกิดไปเลย จะได้ส่วนต่างเป็นจำนวนวัน
        }
        $this->age_year = $age;
        $this->age_month = $mon;
        $this->age_day = $day;
    }



    /**
     * ตรวจสอบอายุว่ามากกว่าเกณฑ์ที่กำหนดแล้วหรือยัง
     * @return Boolean คืนค่าเป็นจริงเมื่ออายุผ่านเกณฑ์
     */
    public function validate_age($age = 18)
    {
        $time_allow = strtotime("+$age years", strtotime($this->birthday));
        return (time() > $time_allow); //ถ้าวันปัจจุบัน มากกว่าวันที่ครับเกณฑ์ จะคือค่าเป็น จริง
    }

    /**
     * เปลี่ยนรูปแบบวันที่ให้มีเครื่องหมาย / และเป็นปี พ.ศ.
     */
    public function set_thai_date($string)
    {
        $arr = explode("-", $string);
        return $arr[2] . '/' . $arr[1] . '/' . ($arr[0] + 543);
    }

}
