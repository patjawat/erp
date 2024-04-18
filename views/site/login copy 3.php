<?php
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use app\models\Categorise;
$site = Categorise::findOne(['name' => 'site']);
$color = isset($site->data_json['theme_color']) ? $site->data_json['theme_color'] : '';
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : '';

$this->title = 'กรุณายืนยันตัวตน';
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
input,
input::placeholder {
    font-weight: 200;
}
</style>



<div id="signup-container" class="row justify-content-center mt-2">
    <div class="sign-in-from">
        <h4 class="text-center mb-3 text-primary" data-aos="fade-down" data-aos-delay="100"><?=$this->title?></h4>
        <?php $form = ActiveForm::begin(['id' => 'blank-form','enableAjaxValidation' => false,]); ?>
        <div class="row justify-content-center" data-aos="fade-up" data-aos-duration="4000">
            <div class="col-lg-4 col-md-12 col-sm-12">
                <?= $form->field($model, 'username')->textInput(['placeholder' => 'ระบุอีเมล','class' => 'form-control form-control-lg rounded-pill border-0'])->label('อีเมล') ?>
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'กำหนดรหัสผ่าน','class' => 'form-control form-control-lg rounded-pill border-0'])->label('รหัสผ่าน') ?>
                <div class="d-inline-block w-100">
                    
                <div class="d-flex justify-content-between mt-3">
        <div>
            <?=Html::a('<i class="fa-solid fa-pen-to-square"></i> ลงทะเบียน',['/site/sign-up'],['class' => 'text-primary'])?>
        </div>
        <div>

            <?=Html::a('<i class="fa-solid fa-unlock"></i> ลืมรหัสผ่าน',['/site/forgot-password'],['class' => 'text-primary'])?>
        </div>
    </div>

                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-lg btn-primary account-btn rounded-pill" id="btn-regster" type="submit">
                            <i
                class="fa-solid fa-fingerprint"></i> เข้าสู่ระบบ</button>

                        <button class="btn btn-lg btn-primary account-btn rounded-pill d-none" id="btn-loading"
                            type="button" disabled="">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Loading...
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>




<h3 class="text-center mt-5 text-primary" data-aos="fade-down" data-aos-delay="100">กรุณายืนยันตัวตน</h3>
<div class="login-page d-flex justify-content-center mt-4 mb-4" data-aos="fade-up" data-aos-duration="4000">

    <?php $form = ActiveForm::begin(['id' => 'blank-form']); ?>
    <?= $form->field($model, 'username')->textInput(['autofocus' => true,'placeholder' => 'ระบุอีเมล','class' => 'form-control form-control-lg rounded-pill border-0'])->label('อีเมล') ?>
    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'ระบุรหัสผ่าน','class' => 'form-control form-control-lg rounded-pill border-0'])->label('รหัสผ่าน') ?>

    <?= $form->field($model, 'rememberMe')->checkbox() ?>
    <div class="d-grid gap-2">
        <button class="btn btn-lg btn-primary shadow account-btn rounded-pill " type="submit"><i
                class="fa-solid fa-fingerprint"></i>
            เข้าสู่ระบบ</button>
    </div>

    <hr>
    <div class="d-flex justify-content-between mt-3">
        <div>
            <?=Html::a('<i class="fa-solid fa-pen-to-square"></i> ลงทะเบียน',['/site/sign-up'],['class' => 'text-primary'])?>
        </div>
        <div>

            <?=Html::a('<i class="fa-solid fa-unlock"></i> ลืมรหัสผ่าน',['/site/forgot-password'],['class' => 'text-primary'])?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>


</div>



<style>
#login-form {
    width: 300px;
    font-family: "kanit"
}

.banner>img {
    /* width: 120px; */
}

.account-page .main-wrapper .account-content .account-box {
    background-color: #ffffff;
    box-shadow: 3px 7px 20px 0px rgb(0 0 0 / 13%);
    margin: 0 auto;
    overflow: hidden;
    width: 480px;
    border-radius: 20px;
}

svg#clouds[_ngcontent-ng-c105734841] {
    position: fixed;
    bottom: -170px;
    left: -200px;
    z-index: -10;
    width: 2600px;
}
</style>