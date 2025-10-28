<?php
use yii\helpers\Html;
use app\components\ApproveHelper;
$notify = ApproveHelper::Info();
$total = $notify['total'];
$totalLeave = $notify['leave']['total'];
$totalPurchase = $notify['purchase']['total'];


$layout = app\components\SiteHelper::getInfo()['layout'];
?>
<?php if($layout == 'horizontal'):?>
    <?php if(!Yii::$app->user->can('branch')):?>
<li class="nav-item mt-1">
    <?=Html::a('<i class="fa-solid fa-gauge me-1"></i> MyDashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"></span>',['/me'],['class' => 'nav-link ' . (isset($active) && $active == 'dashboard' ? 'active' : '')])?>
</li>
<li class="nav-item mt-1">
    <?=Html::a('<i class="fa-regular fa-circle-check me-1"></i> รายการที่ต้องอนุมัติ <span class="badge rounded-pill badge-soft-primary text-primary fw-semibold ms-1"> '.$total.' </span>',['/approve'],['class' => 'nav-link ' . (isset($active) && $active == 'approve' ? 'active' : '')])?>
</li>
<?php endif;?>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?=(isset($active) && $active == 'store' ? 'active' : '')?>" href="#"
        id="topnav-dashboard" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="bi bi-shop me-1"></i> คลังหน่วยงาน
        <i class="bx bx-chevron-down"></i>
    </a>
    <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
        <?=Html::a('<i class="fa-solid fa-gauge me-2"></i> Dashboard ',['/me/store-v2/dashboard'],['class' => 'dropdown-item'])?>
        <?= Html::a('<i class="fa-solid fa-cube me-2"></i> เบิกวัสดุคลังหลัก ',['/me/main-stock/store'],['class' => 'dropdown-item'])?>
        <?php // Html::a('<i class="fa-solid fa-cube me-2"></i> เบิกวัสดุคลังหลัก ',['/me/store-v2/set-warehouse'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="bi bi-shop me-2"></i> สต๊อก/ตัดจ่าย ',['/me/store-v2/index'],['class' => 'dropdown-item'])?>
    </div>
</li>
<?php if(!Yii::$app->user->can('branch')):?>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?=(isset($active) && $active == 'service' ? 'active' : '')?>" href="#"
        id="topnav-dashboard" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="bi bi-app-indicator me-2"></i> บริการ
        <i class="bx bx-chevron-down"></i>
    </a>
    <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
        <?=Html::a('<i class="fa-solid fa-screwdriver-wrench me-2"></i> แจ้งซ่อม ',['/me/repair-v2'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-bag-shopping me-2"></i> ขอซื้อขอจ้าง ',['/me/purchase'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-car me-2"></i> จองรถ',['/me/booking-vehicle/calendar'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-handshake me-2"></i> ห้องประชุม',['/me/booking-meeting/calendar'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-briefcase me-2"></i> อบรม/ประชุม/ดูงาน',['/me/development'],['class' => 'dropdown-item'])?>
    </div>
</li>
<?php endif;?>

<?php else:?>
<div class="d-flex gap-2">
    <?=Html::a('<i class="fa-solid fa-gauge"></i> Dashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"></span>',['/me','name' => 'leave'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-regular fa-circle-check"></i> รายการที่ต้องอนุมัติ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">'.$total.'</span>',['/approve'],['class' => 'btn btn-light'])?>

</div>
<?php endif;?>