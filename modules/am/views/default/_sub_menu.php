<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="bi bi-ui-checks"></i> กลุ่มสินทรัพย์',['/am/asset-group'],['class' => 'btn '.(isset($active) && $active == 'group' ? 'btn-primary' : 'btn-light')])?>
        <?php echo  Html::a('<i class="bi bi-ui-checks"></i>  ประเภทครุภัณฑ์',['/am/asset-type'],['class' => 'btn '.(isset($active) && $active == 'type' ? 'btn-primary' : 'btn-light')])?>
        <?php echo  Html::a('<i class="bi bi-ui-checks"></i>  หมวดหมู่ครุภัณฑ์',['/am/asset-category'],['class' => 'btn '.(isset($active) && $active == 'category' ? 'btn-primary' : 'btn-light')])?>
        <?php echo  Html::a('<i class="bi bi-ui-checks"></i> กำหนดชื่อครุภัณฑ์',['/am/asset-item'],['class' => 'btn '.(isset($active) && $active == 'item' ? 'btn-primary' : 'btn-light')])?>
        <?php echo  Html::a('<i class="bi bi-ui-checks"></i> กำหนดเลข FSN',['/am/fsn'],['class' => 'btn '.(isset($active) && $active == 'fsn' ? 'btn-primary' : 'btn-light')])?>

</div>