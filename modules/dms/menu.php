<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem; 

try {
 $countReceive = $model->CountType('receive') ?? 0;
$countSend = $model->CountType('send') ?? 0;
$countAppointment = $model->CountType('appointment') ?? 0;
$countAnnounce = $model->CountType('announce') ?? 0;
} catch (\Throwable $th) {
    $countReceive =  0;
    $countSend =  0;
    $countAppointment =  0;
    $countAnnounce =  0;
}


$layout = app\components\SiteHelper::getInfo()['layout'];
?>
<?php if($layout == 'horizontal'):?>
    <?php // Html::a($menu['icon'].$menu['title'],$menu['url'],['class' => 'nav-link ' . (isset($active) && $active == $menu['active'] ? 'active' : '')])?>

<li class="nav-item">
    <?php echo Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashboard',['/dms/dashboard'],['class' => 'nav-link ' . (isset($active) && $active == 'dashboard' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?php echo  Html::a('<i class="fa-solid fa-inbox  me-1"></i> หนังสือรับ',['/dms/documents/receive'],['class' => 'nav-link ' . (isset($active) && $active =='receive' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?php echo  Html::a('<i class="fa-solid fa-paper-plane  me-1"></i> หนังสือส่ง',['/dms/documents/send'],['class' => 'nav-link ' . (isset($active) && $active == 'send' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?php echo  Html::a('<i class="fa-solid fa-flag  me-1"></i> คำสั่ง',['/dms/documents/appointment'],['class' => 'nav-link ' . (isset($active) && $active == 'appointment' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?php echo  Html::a('<i class="fa-solid fa-bullhorn  me-1"></i> ประกาศ/นโยบาย',['/dms/documents/announce'],['class' => 'nav-link ' . (isset($active) && $active == 'announce' ? 'active' : '')])?>
</li>
        

<?php else:?>
<div class="d-flex gap-2">
    <?php echo Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashboard',['/dms/dashboard'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-inbox"></i> หนังสือรับ',['/dms/documents/receive'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-paper-plane"></i> หนังสือส่ง',['/dms/documents/send'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-flag"></i> คำสั่ง ',['/dms/documents/appointment'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-bullhorn"></i> ประกาศ/นโยบาย ',['/dms/documents/announce'],['class' => 'btn btn-light'])?>

</div>
<?php endif;?>