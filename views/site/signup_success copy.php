<?php
use yii\bootstrap5\Html;

?>
    <h3 class="text-center mt-4">ยินดีด้วยคุณลงทะเบียนสำเร็จ !</h3>
<div class="d-flex  justify-content-center"> 
    <?=Html::a('<i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ',['/site/login'],['class' => 'btn btn-primary text-center'])?>
</div>
<div class="d-flex  justify-content-center mt-5"> 
<?=Html::img('@web/img/high_fiving2.png',['width' => 800])?>
</div>
