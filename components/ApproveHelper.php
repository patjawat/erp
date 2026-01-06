<?php

namespace app\components;

use Yii;
use yii\db\Expression;
use yii\base\Component;
use app\models\Province;
use app\models\Categorise;
use app\modules\am\models\AssetDetail;
use yii\helpers\ArrayHelper;
use app\modules\purchase\models\Order;
use app\modules\approveV2\models\Approve;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\inventory\models\StockEvent;

// การแจ้งเตือนต่างๆ
class ApproveHelper extends Component
{
    // รวมค่าการแจ้งเตือนต่างๆ
    public static function Info()
    {
        return [
            'total' => (self::Leave()['total'] + self::Purchase()['total'] + self::StockApprove()['total'] + self::Development()['total']),
            'leave' => self::Leave(),
            'booking_car' => self::DriverService(),
            'stock' => self::StockApprove(),
            'purchase' => self::Purchase(),
            'development' => self::Development(),
            'assetMove' => self::AssetMove(),
            // 'helpdesk' => self::Helpdesk(),
        ];
    }


    public static function viewStep($name, $formId)
{
    // ดึงรายการ Approve ทั้งหมดของ form นั้น
    $steps = Approve::find()
        ->where(['name' => $name, 'from_id' => $formId])
        ->orderBy(['id' => SORT_ASC])
        ->all();

    $count = count($steps);

    // นับ step ที่ pass
    $pass = 0;

    // สร้าง progress bar แบบ dynamic
    $bars = '';

    foreach ($steps as $step) {

        if ($step->status == 'Pass') {
            $color = "bg-primary";
            $pass++;
        } else {
            $color = "bg-secondary bg-opacity-25";
        }

        $bars .= '<div class="rounded-pill '.$color.'" style="width: 20px; height: 6px;"></div>';
    }

    return '
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-1">
            <div class="d-flex gap-1">
                '.$bars.'
            </div>
            <small class="text-muted ms-2" style="font-size: 0.75rem;">Step '.$pass.'/'.$count.'</small>
        </div>
    </div>';
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
            $approveQuery = Approve::find()
                ->alias('approve')
                ->leftJoin('`leave`', "approve.from_id = `leave`.id")
                ->where([
                    'approve.name' => 'leave',
                    'approve.emp_id' => $me->id,
                    'approve.status' => 'Pending'
                ])
                ->andWhere(['NOT IN', 'leave.status', ['ReqCancel', 'cancel']])
                ->orderBy(['approve.id' => SORT_DESC]);

            // Debug SQL ที่ถูกสร้าง
            $sql = $approveQuery->createCommand()->getRawSql();

            $datas = $approveQuery->all();

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

            $approveQuery = Approve::find()
                ->alias('approve')
                ->leftJoin('`development`', "approve.from_id = `development`.id")
                ->where([
                    'approve.name' => 'development',
                    'approve.emp_id' => $me->id,
                    'approve.status' => 'Pending'
                ])
                ->andWhere(['NOT IN', 'development.status', ['ReqCancel', 'Cancel']])
                ->orderBy(['approve.id' => SORT_DESC]);

            // Debug SQL ที่ถูกสร้าง
            $sql = $approveQuery->createCommand()->getRawSql();

            $datas = $approveQuery->all();
            return [
                'title' => 'อนุมัติอบรม/ประชุม/ดูงาน',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'emp_id' => $me->id,
                'sql' => $sql
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'อนุมัติอบรม/ประชุม/ดูงาน',
                'total' => 0,
                'datas' => [],
            ];
        }
    }

    // ขอเคลื่อนย้ายทรัพย์สิน
     public static function AssetMove()
    {
        try {
            $me = UserHelper::GetEmployee();
             $datas = Approve::find()->where(['name' => 'asset_move', 'status' => 'Pending', 'emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->all();

            return [
                'title' => 'อนุมัติเคลื่อนย้ายครุภัณฑ์',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'emp_id' => $me->id,
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'อนุมัติเคลื่อนย้ายครุภัณฑ์',
                'total' => 0,
                'datas' => [],
                 'emp_id' => 0
            ];
        }
    }

}
