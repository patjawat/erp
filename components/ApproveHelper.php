<?php

namespace app\components;

use Yii;
use yii\db\Expression;
use yii\base\Component;
use app\models\Province;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\modules\purchase\models\Order;
use app\modules\approve\models\Approve;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\inventory\models\StockEvent;

// การแจ้งเตือนต่างๆ
class ApproveHelper extends Component
{
    // รวมค่าการแจ้งเตือนต่างๆ
    public static function Info()
    {
        return [
            'total' => (self::Leave()['total'] + self::Purchase()['total'] + self::StockApprove()['total']+ self::Development()['total']),
            'leave' => self::Leave(),
            'booking_car' => self::DriverService(),
            'stock' => self::StockApprove(),
            'purchase' => self::Purchase(),
            'development' => self::Development(),
            // 'helpdesk' => self::Helpdesk(),
        ];
    }

    // ระบบการแจ้งเตือนการอนุมัติใช้รถยนต์
    public static function DriverService()
    {
        try {
            $me = UserHelper::GetEmployee();
            $datas = Approve::find()->where(['name' => 'driver_service', 'status' => 'Pending', 'emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->limit(10)->all();

            return [
                'title' => 'ขออนุญาตใช้รถ',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'แจ้งซ่อม',
                'total' => 0,
                'datas' => []
            ];
        }
    }

    // ระบบการแจ้งเตือนการอนุมัติ
    public static function Leave()
    {
        try {
            $me = UserHelper::GetEmployee();
            $query = Approve::find()
                ->where(['name' => 'leave', 'status' => 'Pending', 'emp_id' => $me->id])
                ->orderBy(['id' => SORT_DESC]);

            // Debug SQL ที่ถูกสร้าง
            $sql = Yii::debug($query->createCommand()->getRawSql(), 'sql');

            $datas = $query->all();

            return [
                'title' => 'ขออนุมัติลา',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'sql' => $sql
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'ขออนุมัติลา',
                'total' => 0,
                'datas' => [],
                'sql' => 'sql'
            ];
        }
    }

    // รายการที่ต้องอนุมัติจัดซื้อ
    public static function Purchase()
    {
        try {
            $me = UserHelper::GetEmployee();
            $datas = Approve::find()->where(['name' => 'purchase', 'status' => 'Pending', 'emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->all();

            return [
                'title' => 'อนุมัติขอซื้อขอจ้าง',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'emp_id' => $me->id
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'อนุมัติขอซื้อขอจ้าง',
                'total' => 0,
                'datas' => [],
            ];
        }
    }

    // รายการที่ต้องอนุมัติจัดซื้อ
    public static function StockApprove()
    {
        try {
            $me = UserHelper::GetEmployee();
            $datas = Approve::find()->where(['name' => 'main_stock', 'status' => 'Pending', 'emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->all();

            return [
                'title' => 'อนุมัติขอเบิกวัสดุ',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'emp_id' => $me->id
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'อนุมัติขอเบิกวัสดุ',
                'total' => 0,
                'datas' => [],
            ];
        }
    }

    //อบรม/ประชุม/ดูงาน
    public static function Development()
    {
        try {
            $me = UserHelper::GetEmployee();
            $datas = Approve::find()->where(['name' => 'development', 'status' => 'Pending', 'emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->all();

            return [
                'title' => 'อนุมัติอบรม/ประชุม/ดูงาน',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'emp_id' => $me->id
            ];
        } catch (\Throwable $th) {
            return [
                'title' =>'อนุมัติอบรม/ประชุม/ดูงาน',
                'total' => 0,
                'datas' => [],
            ];
        }
    }
    
}
