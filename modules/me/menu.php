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
<li class="nav-item">
        <?php echo  Html::a('<i class="fa-solid fa-gauge me-1 fs-5"></i> MyDashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"></span>',['/me','name' => 'leave'],['class' => 'nav-link ' . (isset($active) && $active == 'dashboard' ? 'active' : '')])?>
</li>
<li class="nav-item">
        <?php echo  Html::a('<i class="fa-regular fa-circle-check me-1 fs-5"></i> รายการที่ต้องอนุมัติ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold ms-1"> '.$total.' </span>',['/approve'],['class' => 'nav-link ' . (isset($active) && $active == 'approve' ? 'active' : '')])?>
</li>
<li class="nav-item">
        <?php echo  Html::a('<i class="bi bi-journal-text me-1 fs-5"></i> ทะเบียนหนังสือ ',['/me/documents'],['class' => 'nav-link ' . (isset($active) && $active == 'document' ? 'active' : '')])?>
</li>
<li class="nav-item">
        <?php echo  Html::a('<i class="fa-solid fa-screwdriver-wrench me-1 fs-5"></i> แจ้งซ่อม ',['/me/repair'],['class' => 'nav-link ' . (isset($active) && $active == 'repair' ? 'active' : '')])?>
</li>
<li class="nav-item">
        <?php echo  Html::a('<i class="fa-solid fa-bag-shopping me-1 fs-5"></i> ขอซื้อขอจ้าง ',['/me/purchase'],['class' => 'nav-link ' . (isset($active) && $active == 'purchase' ? 'active' : '')])?>
</li>
<li class="nav-item">
        <?php echo  Html::a('<i class="fa-solid fa-car me-1 fs-5"></i> จองรถ',['/me/booking-vehicle/calendar'],['class' => 'nav-link ' . (isset($active) && $active == 'vehicle' ? 'active' : '')])?>
</li>
<li class="nav-item">
        <?php echo  Html::a('<i class="fa-solid fa-handshake me-1 fs-5"></i> ห้องประชุม',['/me/booking-meeting/calendar'],['class' => 'nav-link ' . (isset($active) && $active == 'meeting' ? 'active' : '')])?>
</li>
<li class="nav-item">
        <?php echo  Html::a('<i class="fa-solid fa-briefcase me-1 fs-5"></i> อบรม/ประชุม/ดูงาน',['/me/development'],['class' => 'nav-link ' . (isset($active) && $active == 'development' ? 'active' : '')])?>
</li>

        
<?php else:?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-gauge"></i> Dashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"></span>',['/me','name' => 'leave'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-regular fa-circle-check"></i> รายการที่ต้องอนุมัติ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">'.$total.'</span>',['/approve'],['class' => 'btn btn-light'])?>

</div>
<?php endif;?>