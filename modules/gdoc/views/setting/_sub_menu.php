<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-plug"></i> การเชื่อมต่อ',['/gdoc/setting'],['class' => 'btn '.(isset($active) && $active == 'index' ? 'btn-primary' : 'btn-light')])?>
        <?php echo  Html::a('<i class="fa-regular fa-file-code"></i>  แบบฟอร์มใบลา',['/gdoc/setting/template-leave'],['class' => 'btn '.(isset($active) && $active == 'template' ? 'btn-primary' : 'btn-light')])?>
</div>