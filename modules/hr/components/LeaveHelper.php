<?php

namespace app\modules\hr\components;

use Yii;
use yii\base\Component;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\usermanager\models\User;


class LeaveHelper extends Component
{
 

    // นับวันหยุด
    public static function CalDay($dateStart, $dateEnd, $emp_id = null)
    {
        $me = UserHelper::GetEmployee();
        // นับสาร์-อาทิตย์
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

        // นับวัน Off
        $sqlDayOff = "SELECT count(id) FROM `calendar` WHERE name = 'off' AND emp_id =  :emp_id AND MONTH(date_end) = MONTH(:date_end);";
        
        //ถ้าเป็นการแก้ไขด้วย admin
        $empId = ($emp_id > 0 ? $emp_id :  $me->id);

        $countDayOff = Yii::$app
            ->db
            ->createCommand($sqlDayOff)
            ->bindValue(':emp_id', $empId)
            ->bindValue(':date_end', $dateEnd)
            ->queryScalar();

        // นับจำนวนวันทั้งหมด
        $sqlAllDays = 'WITH RECURSIVE date_range AS (SELECT :date_start AS date UNION ALL SELECT DATE_ADD(date, INTERVAL 1 DAY) FROM date_range WHERE date < :date_end ) SELECT count(date) as count_days FROM date_range;';
        $countAllDays = Yii::$app->db->createCommand($sqlAllDays)->bindValue(':date_start', $dateStart)->bindValue(':date_end', $dateEnd)->queryScalar();
        $satsunDays = Yii::$app->db->createCommand($sqlsatsunDays)->bindValue(':date_start', $dateStart)->bindValue(':date_end', $dateEnd)->queryScalar();
        $holiday = Yii::$app->db->createCommand($sqlHoliday)->bindValue(':date_start', $dateStart)->bindValue(':date_end', $dateEnd)->queryScalar();

        return [
            'allDays' => $countAllDays,
            'satsunDays' => $satsunDays,
            'dayOff' => $countDayOff,
            // 'sunDay' => $sunDay,
            'holiday' => $holiday,
            //  'holidy_me' =>  $holidayMe
        ];
    }

}