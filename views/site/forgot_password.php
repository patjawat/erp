<?php
use yii\bootstrap5\ActiveForm;
$this->title ="Reset Password | เซ็ตรหัสผ่าน";
?>
<style>
#form-reset input {
    background-color: #8495e2;
    ;
    color: #fff;
}
</style>
<div class="container">

    <div class="row justify-content-center mt-5">
        <div class="col-lg-4 col-md-4 col-sm-12">
            
            <h1 class="mb-0">รีเซ็ตรหัสผ่าน</h1>
            <p>ป้อนที่อยู่อีเมลของคุณ แล้วเราจะส่งอีเมลพร้อมคำแนะนำในการรีเซ็ตรหัสผ่านของคุณ</p>
            <?php $form = ActiveForm::begin([
                'id' => 'blank-form'
            ]); ?>

<div class="form-group">
    <?= $form->field($model, 'email')->textInput(['placeholder' => 'ระบุที่อยู่อีเมล...','class' => 'form-control form-control-lg rounded-pill border-0'])->label(false) ?>
</div>

<div class="d-inline-block w-100">
    
    <button type="submit" class="btn btn-primary float-right"><i class="fa-solid fa-envelope-circle-check"></i>
    รีเซ็ตรหัสผ่าน</button>
</div>
<?php ActiveForm::end(); ?>
</div>
</div>
</div>