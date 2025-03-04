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

    //รวมค่าการแจ้งเตือนต่างๆ
    public static function Info()
    {
        return [
            'total' => (self::Leave()['total']+self::Purchase()['total']+self::StockApprove()['total']),
            'leave' => self::Leave(),
            'booking_car' => self::DriverService(),
            'stock_approve' => self::StockApprove(),
            'purchase' => self::Purchase(),
            // 'helpdesk' => self::Helpdesk(),
            
        ];
    }

    //ระบบการแจ้งเตือนการอนุมัติใช้รถยนต์
    public static function DriverService()
    {
        try {
            $me = UserHelper::GetEmployee();
        $datas = Approve::find()->where(['name' => 'driver_service','status' => 'Pending','emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->limit(10)->all();
        
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
    
//ระบบการแจ้งเตือนการอนุมัติ
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
    
//แจ้งเตือนสะานนะการแจ้งซ่อม
    // public static function Helpdesk()
    // {
    //     $datas = Helpdesk::find()->where(['created_by' => Yii::$app->user->id])->andWhere(['in', 'status', [1, 2, 3]])->all();
    //     return [
    //         'title' => 'แจ้งซ่อม',
    //         'total' => isset($datas) ? count($datas) : 0,
    //         'datas' => $datas
    //     ];
    // }

    //แจ้งเตือนขอซื้อขอจ้าง ole
    // public static function Purchase()
    // {
    //     if (Yii::$app->user->can('director')) {
    //         $datas = Order::find()
    //             ->andwhere(['is not', 'pr_number', null])
    //             ->andwhere(['status' => 1])
    //             ->andFilterwhere(['name' => 'order'])
    //             ->andwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_director_confirm')"), ''])
    //             ->andFilterwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_officer_checker')"), 'Y'])
    //             ->andFilterwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_leader_confirm')"), 'Y'])
    //             ->all();
    //         }else{
    //             $datas = Order::find()->andwhere(['is not', 'pr_number', null])
    //             ->andwhere(['status' => 1])
    //             ->andFilterwhere(['name' => 'order'])
    //             ->andFilterwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_leader_confirm')"), 'Y'])
    //             ->all();
    //         }
            
    //     return [
    //         'title' => 'ขอซื้อขอจ้าง',
    //         'total' => isset($datas) ? count($datas) : 0,
    //         'datas' => $datas
    //     ];
    // }

    //รายการที่ต้องอนุมัติจัดซื้อ
    public static function Purchase()
    {
        try {
            $me = UserHelper::GetEmployee();
            $datas = Approve::find()->where(['name' => 'purchase','status' => 'Pending','emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->all();
            
            return [
                'title' => 'อนุมัติขอซื้อขอจ้าง',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'emp_id' => $me->id
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'อนุมัติขอซื้อขอจ้าง',
                'total' =>  0,
                'datas' => [],
            ];
      
    }
    }
    
    public static function StockApprove()
    {
        try {
            $emp = UserHelper::GetEmployee();
        $datas = isset($emp->id) ? StockEvent::find()->andFilterWhere(['name' => 'order', 'checker' => $emp->id])->andWhere(new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.checker_confirm')) = ''"))->all() : 0;
        return [
            'title' => 'ขออนุมัติเบิกวัสดุ',
            'total' => isset($datas) ? count($datas) : 0,
            'datas' => $datas
        ];
        } catch (\Throwable $th) {
            return [
                'title' => 'ขออนุมัติเบิกวัสดุ',
                'total' =>  0,
                'datas' => []
            ];
        }
       
    }
    
    

}
