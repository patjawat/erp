<?php
use yii\bootstrap5\Html;

?>
<div class="row justify-content-center mt-5">
<div class="col-5">
    <h4 class="text-center text-white mb-3">ลงทะเบียนสำเร็จ !</h4>
    <?=$this->render('@app/modules/hr/views/employees/avatar',['model' => $model]);?> 
    <div class="d-grid gap-2 mt-5">
<?=Html::a('<i class="fa-solid fa-fingerprint"></i>
            เข้าสู่ระบบ',['/site/login'],['class' => 'btn btn-lg btn-primary account-btn rounded-pill'])?>
</div>
</div>
</div>