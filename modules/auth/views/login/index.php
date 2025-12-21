<?php

use yii\web\View;
use yii\helpers\Html;
use app\models\Categorise;
use app\components\SiteHelper;
use kartik\widgets\ActiveForm;

$site = Categorise::findOne(['name' => 'site']);
$color = isset($site->data_json['theme_color']) ? $site->data_json['theme_color'] : '';
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : '';
$this->title = 'กรุณายืนยันตัวตน';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid min-vh-100 d-flex p-0">
    <!-- Left Column - Image and Text -->
    <div class="d-none d-md-flex col-md-6 bg-primary text-white align-items-center justify-content-center">
        <div class="px-4 py-5 text-center">
            <?= Html::img('@web/images/logo_new.png', ['class' => 'img-fluid', 'style' => 'max-width:400px; height:auto;']) ?>

            <div class="mb-4 mt-3">
            </div>
            <div class="bg-primary-dark p-3 rounded">
                <p class="lead mb-4"> <?= SiteHelper::getInfo()['company_name'] != '' ?  (SiteHelper::getInfo()['company_name']) : '' ?></p>
                <p class="fst-italic small">
                    "ผู้ให้การสนับสนุนและรวมพลังพัฒนาโดย มูลนิธิรามาธิบดี โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย และ โรงพยาบาลอุบลรัตน์"
                </p>
                <div class="text-endx">
                    <div class="d-flex justify-content-center align-items-center gap-3 gap-md-5 mt-4 flex-wrap">
                        <?= Html::img('@web/banner/banner2.png', ['style' => 'width:70px;height:70px']) ?>
                        <?= Html::img('@web/banner/banner1.png', ['style' => 'width:170px;height:100px']) ?>
                        <?= Html::img('@web/banner/banner3.png', ['style' => 'width:100px;height:100px']) ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Right Column - Login Form -->
    <div class="col-12 col-md-6 d-flex flex-column align-items-center justify-content-center">
        <div class="text-center mb-4">
            <?php echo Html::img($site->logo(), ['class' => 'object-fit-cover rounded mt-0', 'style' => 'margin-top: 25px;max-width: 110px;max-height: 110px;    width: 100%;height: 100%;']) ?>
        </div>
        <div class="w-100 px-4 py-5" style="max-width: 420px;">
            <div class="text-center mb-4">
                <h2 class="mb-2 text-primary">เข้าสู่ระบบ</h2>
                <p class="text-muted">กรอกข้อมูลเพื่อเข้าสู่บัญชีของคุณ</p>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'blank-form', 'enableAjaxValidation' => false,]); ?>

            <!-- Email Input -->
            <?= $form->field($model, 'username', [
                'addon' => [
                    'prepend' => [
                        'content' => '<i class="bi bi-person-fill"></i>',
                        'options' => ['class' => 'input-group-text']
                    ],
                ],
            ])->textInput([
                'placeholder' => 'ชื่อเข้าใช้งาน',
                'value' => '',
            ])->label('ชื่อเข้าใช้งาน', ['class' => 'form-label']) ?>

            <!-- Password Input -->
            <?= $form->field($model, 'password', [
                'addon' => [
                    'prepend' => [
                        'content' => '<i class="bi bi-lock"></i>',
                        'options' => ['class' => 'input-group-text']
                    ],
                ],
            ])->passwordInput([
                'placeholder' => 'รหัสผ่านของคุณ',
                'value' => '',
            ])->label('รหัสผ่าน', ['class' => 'form-label']) ?>

            <!-- Remember Me -->
            <?php
            echo $form->field($model, 'rememberMe')->checkbox([
                'class' => 'form-check-input',
                'template' => "<div class='form-check mb-3'>{input} {label}\n{error}</div>"
            ])->label('จดจำฉันไว้ในระบบ', ['class' => 'form-check-label'])
            ?>
            <!-- Login Button -->
            <div class="d-grid mb-3">
                <?php //  Html::submitButton('เข้าสู่ระบบ', ['class' => 'btn btn-primary btn-lg']) 
                ?>
                <button class="btn btn-primary account-btn shadowr" id="btn-login" type="submit">
                    เข้าสู่ระบบ
                </button>
                <button class="btn btn-primary account-btn" id="btnAwait" type="submit">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    รอสักครู่...
                </button>
            </div>

            <?php ActiveForm::end(); ?>

            <!-- Divider -->
            <div class="d-flex align-items-center my-3">
                <hr class="flex-grow-1">
                <span class="mx-2 text-muted small">หรือเข้าสู่ระบบด้วย</span>
                <hr class="flex-grow-1">
            </div>

            <!-- Social Login Buttons -->
            <div class="d-grid gap-2 d-md-flex">
                <a href="<?= \yii\helpers\Url::to(['https://moph.id.th/oauth/redirect?client_id=0194e132-099e-7e9b-b25c-a927c7e35d83&redirect_uri=https://provider.tphcp.go.th/callback&response_type=code&state=https://erp.tphcp.go.th/auth/provider']) ?>"
                    class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center mb-2 mb-md-0">
                    <?= Html::img('@web/images/provider_logo.png', ['class' => 'rounded me-2', 'style' => 'max-width: 55px']) ?>
                </a>

                <a href="<?= \yii\helpers\Url::to(['/auth/thaid/']) ?>"
                    class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center">
                    <?= Html::img('@web/images/thaid_logo.jpg', ['class' => 'rounded me-2', 'style' => 'max-width: 25px']) ?> ThaiD
                </a>
            </div>


            <!-- Sign Up Link -->
            <div class="text-center mt-4">
                <p class="small text-muted mb-0">
                    ยังไม่มีบัญชี?
                    <?= Html::a('สมัครสมาชิก', ['/site/sign-up'], ['class' => 'text-primary fw-medium']) ?>
                </p>
            </div>
        </div>
    </div>
</div>



<?php
$js = <<< JS
$('#btnAwait').hide();
$('#btn-login').show();
 $('#blank-form').on('beforeSubmit', function (e) {
    e.preventDefault(); // ✅ ต้องมีวงเล็บ
    var yiiform = $(this);
    $('#btnAwait').show();
    $('#btn-login').hide();

    $.ajax({
        type: yiiform.attr('method'),
        url: yiiform.attr('action'),
        data: yiiform.serialize(),
        dataType: "json",
        success: function (data) {
            if (data.success) {
                window.location.href = data.redirect; // ✅ redirect ทันที
            } else if (data.validation) {
                yiiform.yiiActiveForm('updateMessages', data.validation, true);
                $('#btnAwait').hide();
                $('#btn-login').show();
            } else {
                alert('Login error occurred');
                $('#btnAwait').hide();
                $('#btn-login').show();
            }
        },
        error: function () {
            alert('Network or server error');
            $('#btnAwait').hide();
            $('#btn-login').show();
        }
    });

    return false;
});


JS;
$this->registerJs($js, View::POS_END);
?>