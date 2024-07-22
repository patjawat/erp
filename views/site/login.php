<?php
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\web\View;
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
                <?= $form->field($model, 'username')->textInput(['autofocus' => true,'placeholder' => 'ระบุอีเมล','class' => 'form-control form-control-lg rounded-pill border-0'])->label('อีเมล') ?>
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
                        <button class="btn btn-lg btn-primary account-btn rounded-pill" id="btn-login" type="submit">
                            <i class="fa-solid fa-fingerprint"></i> เข้าสู่ระบบ
                        </button>

                        <button class="btn btn-lg btn-primary account-btn rounded-pill" id="btnAwait" type="submit">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            รอสักครู่...
                        </button>

                        <button class="btn btn-lg btn-primary account-btn rounded-pill d-none" id="btnLoading"
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


<?php
$js = <<< JS
 $('#btnAwait').hide();

$('#blank-form').on('beforeSubmit', function (e) {
e.preventDefault
    var yiiform = $(this);
    $('#btnAwait').show();
    $('#btn-login').hide();

    $.ajax({
        type: yiiform.attr('method'),
            url: yiiform.attr('action'),
            data: yiiform.serializeArray(),
        dataType: "json",
        success: function (data) {
            if(data.success) {
                // data is saved
                $('#success-container').html(data.content);
                $('#signup-container').hide();
                success()
            } else if (data.validation) {
                // server validation failed
                yiiform.yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
                $('#btnAwait').hide();
                $('#btn-login').show();
            
            } else {
                // incorrect server response
            }
        }
    });
    
        return false;

})



JS;
$this->registerJs($js,View::POS_END);
?>