<?php
use app\components\UserHelper;
use app\components\ApproveHelper;
use yii\helpers\Url;
use app\assets\ApproveV2Asset;

ApproveV2Asset::register($this);

$me = UserHelper::GetEmployee();
    $notify = ApproveHelper::Info();
    $totalLeave = $notify['leave']['total'];
    $totalBookingCar = $notify['booking_car']['total'];
    $totalPurchase = $notify['purchase']['total'];
    $totalDevelopment = $notify['development']['total'];
    $totalAssetMove = $notify['assetMove']['total'];
    $totalCheckin = $notify['checkin']['total'];
    $totalRequisitionV2 = $notify['requisitionV2']['total'] ?? 0;

    // งานมอบหมายที่ยังไม่ปิด — ป้องกันแถบเมนูพังในเครื่องที่ยังไม่ได้รัน migration ของโมดูล task
    $totalTask = 0;
    try {
        if ($me && Yii::$app->db->getTableSchema('{{%task}}') !== null) {
            $totalTask = (int) \app\modules\task\models\Task::find()
                ->where(['assignee_emp_id' => $me->id])
                ->andWhere(['status' => \app\modules\task\models\Task::OPEN_STATUSES])
                ->count();
        }
    } catch (\Throwable $e) {
        $totalTask = 0;
    }
    ?>

<?php //  $this->render('@app/modules/approveV2/views/default/card_summary') ?>

 <div class="approve-v2-tabs d-flex flex-row flex-wrap gap-2 gap-md-3 p-2 mb-3">
        <?php // ทางเข้าปฏิทินงาน — วางไว้ที่นี่เพราะเป็นจุดเดียวที่เข้าถึงงานได้เมื่อปิด popup ไปแล้ว ?>
        <a href="<?= Url::to(['/task']) ?>" class="btn d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= ($menu ?? '') === 'task' ? 'bg-white shadow-sm' : '' ?>">
            <i data-lucide="list-checks"></i>
            To do list
            <?php if ($totalTask > 0): ?>
                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                    <?= $totalTask ?>
                </span>
            <?php endif; ?>
        </a>

        <a href="<?= Url::to(['/approve-v2/leave']) ?>" class="btn d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'leave'  ? 'bg-white shadow-sm' : '' ?>">
            <i data-lucide="calendar"></i>
            วันลา
            <?php if($totalLeave > 0):?>
                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                    <?=$totalLeave?>
                  </span>
            <?php endif;?>

     </a>

     <a href="<?= Url::to(['/approve-v2/vehicle']) ?>" class="position-relative btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'vehicle'   ? 'bg-white shadow-sm' : '' ?>">
         <i data-lucide="car-front"></i>
         ใช้รถ

     </a>


     <a href="<?= Url::to(['/approve-v2/purchase']) ?>" class="btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'purchase'   ? 'bg-white shadow-sm' : '' ?>">
         <i data-lucide="shopping-cart"></i>
         จัดซื้อจัดจ้าง
              <?php if($totalPurchase > 0):?>
                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                    <?=$totalPurchase?>
                  </span>
            <?php endif;?>
     </a>

     <?php // เมนูเบิกวัสดุรุ่นเก่าถูกถอดออก 2026-08-31 — ใช้ "เบิกวัสดุv2" (ระบบคลัง v2) แทนแล้ว ?>

     <a href="<?= Url::to(['/approve-v2/main-stock/requisition-v2']) ?>" class="btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'main-stock-requisition-v2' ? 'bg-white shadow-sm' : '' ?>">
         <i data-lucide="clipboard-list"></i>
         เบิกวัสดุv2
           <?php if($totalRequisitionV2 > 0):?>
                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                    <?= $totalRequisitionV2 ?>
                  </span>
            <?php endif;?>
     </a>

     <a href="<?= Url::to(['/approve-v2/development']) ?>" class="btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'development'   ? 'bg-white shadow-sm' : '' ?>">
         <i data-lucide="user-star"></i>
         อบรม/ประชุม/ดูงาน
           <?php if($totalDevelopment > 0):?>
                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                    <?=$totalDevelopment?>
                  </span>
            <?php endif;?>
     </a>

     <a href="<?= Url::to(['/approve-v2/asset-move']) ?>" class="btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'asset-move'   ? 'bg-white shadow-sm' : '' ?>">
         <i data-lucide="arrow-left-right"></i>
         เคลื่อนย้ายครุภัณฑ์
             <?php if($totalAssetMove > 0):?>
                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                    <?=$totalAssetMove?>
                  </span>
            <?php endif;?>
     </a>

     <!-- <a href="<?= Url::to(['/approve-v2/checkin']) ?>" class="btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'checkin' ? 'bg-white shadow-sm' : '' ?>">
         <i data-lucide="clock"></i>
         ลงเวลา
             <?php if(!empty($totalCheckin) && $totalCheckin > 0):?>
                <span class="badge bg-danger-subtle text-primary border-0 ms-1 fw-normal">
                    <?= $totalCheckin ?>
                  </span>
            <?php endif;?>
     </a> -->

     <?php if (\Yii::$app->user->can('admin')): ?>
     <a href="<?= Url::to(['/approve-v2/setting/index']) ?>" class="btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= (isset($menu) && $menu === 'setting') ? 'bg-white shadow-sm' : '' ?>">
         <i data-lucide="settings"></i>
         ตั้งค่าระดับการอนุมัติ
     </a>
     <?php endif; ?>
 </div>