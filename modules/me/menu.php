<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\ApproveHelper;
use app\components\CategoriseHelper; 
use app\modules\am\models\AssetItem;
$notify = ApproveHelper::Info();
$total = $notify['total'];
$totalLeave = $notify['leave']['total'];
$totalPurchase = $notify['purchase']['total'];


$layout = app\components\SiteHelper::getInfo()['layout'];
?>
<?php if($layout == 'horizontal'):?>
<li class="nav-item mt-1">
    <?php echo  Html::a('<i class="fa-solid fa-gauge me-1 fs-5"></i> MyDashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"></span>',['/me'],['class' => 'nav-link ' . (isset($active) && $active == 'dashboard' ? 'active' : '')])?>
</li>
<li class="nav-item mt-1">
    <?php echo  Html::a('<i class="fa-regular fa-circle-check me-1 fs-5"></i> รายการที่ต้องอนุมัติ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold ms-1"> '.$total.' </span>',['/approve'],['class' => 'nav-link ' . (isset($active) && $active == 'approve' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?php echo  Html::a('<i class="bi bi-shop me-1 fs-5"></i> คลังหน่วยงาน ',['/me/store-v2/dashboard'],['class' => 'nav-link ' . (isset($active) && $active == 'store' ? 'active' : '')])?>
</li>


<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?=(isset($active) && $active == 'setting' ? 'active' : '')?>" href="#"
        id="topnav-dashboard" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="bi bi-app-indicator fs-5 me-2"></i> บริการ
        <i class="bx bx-chevron-down"></i>
    </a>
    <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
        <?=Html::a('<i class="fa-solid fa-screwdriver-wrench me-2"></i> แจ้งซ่อม ',['/me/repair'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-bag-shopping me-2"></i> ขอซื้อขอจ้าง ',['/me/purchase'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-car me-2"></i> จองรถ',['/me/booking-vehicle/calendar'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-handshake me-2"></i> ห้องประชุม',['/me/booking-meeting/calendar'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-briefcase me-2"></i> อบรม/ประชุม/ดูงาน',['/me/development'],['class' => 'dropdown-item'])?>
    </div>
</li>


<?php else:?>
<div class="d-flex gap-2">
    <?php echo  Html::a('<i class="fa-solid fa-gauge"></i> Dashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"></span>',['/me','name' => 'leave'],['class' => 'btn btn-light'])?>
    <?php echo  Html::a('<i class="fa-regular fa-circle-check"></i> รายการที่ต้องอนุมัติ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">'.$total.'</span>',['/approve'],['class' => 'btn btn-light'])?>

</div>
<?php endif;?>