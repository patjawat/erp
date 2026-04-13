<?php

use yii\web\View;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use app\components\SiteHelper;

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

$this->title = 'เข้าสู่ระบบ';
$siteInfo = SiteHelper::getInfo();
$appName = $siteInfo['company_name'] ?? 'บริการออนไลน์';
$thaidUrl = Url::to(['/auth/thaid/']);
$thaidLogoPath = Yii::getAlias('@webroot/images/thaid_logo.jpg');
$hasThaidLogo = $thaidLogoPath && is_file($thaidLogoPath);

$providerLoginUrl = 'https://moph.id.th/oauth/redirect?' . http_build_query([
    'client_id' => '0194e132-099e-7e9b-b25c-a927c7e35d83',
    'redirect_uri' => 'https://providerid.erpcph.com/callback',
    'response_type' => 'code',
    'state' => function_exists('env') ? (env('PROVIDER_REDIRECT_URI') ?? '') : '',
]);
$providerLogoPath = Yii::getAlias('@webroot/images/provider_logo.png');
$hasProviderLogo = $providerLogoPath && is_file($providerLogoPath);
?>
<style>
.mobile-login-top {
    background: linear-gradient(160deg, #0d6efd 0%, #0a58ca 35%, #084298 100%);
    padding: 2rem 1.5rem calc(2rem + 1.5rem);
    padding-top: calc(env(safe-area-inset-top) + 2rem);
    text-align: center;
    color: #fff;
}
.mobile-login-top .login-logo { max-height: 4rem; width: auto; border-radius: 12px; }
.mobile-login-top .login-app-name { font-size: 1.25rem; font-weight: 600; margin: 0.5rem 0 0; }
.mobile-login-top .login-tagline { font-size: 0.8125rem; opacity: 0.9; margin: 0; }
.mobile-login-card {
    margin: -1.5rem 1rem 0;
    border: 0;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    overflow: hidden;
}
.mobile-login-card .card-body { padding: 1.5rem; }
.mobile-login-card .form-control {
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
}
.mobile-login-card .form-control::placeholder { color: #adb5bd; }
.mobile-login-card .btn-login-submit {
    border-radius: 12px;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    font-weight: 600;
}
.mobile-login-divider {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 1.25rem 0;
    color: #6c757d;
    font-size: 0.875rem;
}
.mobile-login-divider::before,
.mobile-login-divider::after { content: ''; flex: 1; height: 1px; background: #dee2e6; }
.mobile-login-thaid {
    display: block;
    width: 100%;
    border-radius: 16px;
    padding: 1rem 1.25rem;
    font-size: 1rem;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    border: 0;
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(111, 66, 193, 0.35);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.mobile-login-thaid:active { transform: scale(0.98); }
.mobile-login-thaid .thaid-icon { width: 1.5rem; height: 1.5rem; vertical-align: -0.35em; margin-right: 0.35rem; border-radius: 4px; }
.mobile-login-thaid .thaid-desc { display: block; font-size: 0.75rem; font-weight: 500; opacity: 0.95; margin-top: 0.25rem; }
.mobile-login-provider {
    display: block;
    width: 100%;
    border-radius: 16px;
    padding: 1rem 1.25rem;
    font-size: 1rem;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    border: 0;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(13, 110, 253, 0.35);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    margin-bottom: 0.75rem;
}
.mobile-login-provider:active { transform: scale(0.98); }
.mobile-login-provider .provider-icon { width: 1.5rem; height: 1.5rem; vertical-align: -0.35em; margin-right: 0.35rem; border-radius: 4px; }
.mobile-login-provider .provider-desc { display: block; font-size: 0.75rem; font-weight: 500; opacity: 0.95; margin-top: 0.25rem; }
.mobile-login-links { padding: 1rem 1rem 1.5rem; }
.mobile-login-links a { font-size: 0.9375rem; }
</style>

<!-- Top section: gradient + logo + app name -->
<div class="mobile-login-top">
    <?php if (!empty($siteInfo['logo'])): ?>
        <?= Html::img($siteInfo['logo'], ['class' => 'login-logo', 'alt' => '']) ?>
    <?php else: ?>
        <div class="rounded-3 bg-white bg-opacity-15 d-inline-flex align-items-center justify-content-center p-3">
            <i data-lucide="shield-check" style="width: 2.5rem; height: 2.5rem;"></i>
        </div>
    <?php endif; ?>
    <h1 class="login-app-name"><?= Html::encode($appName) ?></h1>
    <p class="login-tagline">บริการออนไลน์ — ยืนยันตัวตนเพื่อเข้าใช้งาน</p>
</div>

<!-- Login card -->
<div class="card mobile-login-card">
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'id' => 'mobile-login-form',
            'enableAjaxValidation' => false,
            'options' => ['class' => ''],
            'fieldConfig' => [
                'template' => "{label}\n{input}\n{error}",
                'labelOptions' => ['class' => 'form-label fw-medium text-body'],
                'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'invalid-feedback'],
            ],
        ]); ?>

    <?= $form->field($model, 'telegram_id')->hiddenInput([
                'id' => 'telegram_id',
                'inputmode' => 'text',
            ])->label(false) ?>

        <?= $form->field($model, 'username')->textInput([
            'placeholder' => 'ชื่อเข้าใช้งาน',
            'autofocus' => true,
            'autocomplete' => 'username',
            'inputmode' => 'text',
            'value' => 'admin'
        ]) ?>

        <?= $form->field($model, 'password')->passwordInput([
            'placeholder' => 'รหัสผ่าน',
            'autocomplete' => 'current-password',
            'value' => 'l;ylfu8iy['
        ]) ?>

        <div class="mb-3">
            <?= $form->field($model, 'rememberMe')->checkbox([
                'template' => "<div class=\"form-check\">{input} {label}</div>\n{error}",
                'class' => 'form-check-input',
                'labelOptions' => ['class' => 'form-check-label text-body-secondary'],
            ])->label('จดจำการเข้าสู่ระบบ') ?>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-login-submit" id="btn-login">
                <i data-lucide="log-in" class="me-1" style="width: 1.15rem; height: 1.15rem; vertical-align: -0.25em;"></i>
                เข้าสู่ระบบ
            </button>
            <button type="button" class="btn btn-primary btn-login-submit d-none" id="btn-wait" disabled>
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                กำลังเข้าสู่ระบบ...
            </button>
        </div>

        <?php ActiveForm::end(); ?>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <?= Html::a('ลืมรหัสผ่าน', ['/site/forgot-password'], ['class' => 'text-primary text-decoration-none']) ?>
            <?= Html::a('สมัครสมาชิก', ['/site/sign-up'], ['class' => 'text-primary text-decoration-none']) ?>
        </div>

        <div class="mobile-login-divider">หรือ</div>

        <a href="<?= Html::encode($providerLoginUrl) ?>" class="mobile-login-provider">
            <?php if ($hasProviderLogo): ?>
                <?= Html::img('@web/images/provider_logo.png', ['class' => 'provider-icon', 'alt' => 'Provider ID']) ?>
            <?php else: ?>
                <i data-lucide="fingerprint" class="me-1" style="width: 1.25rem; height: 1.25rem; vertical-align: -0.3em;"></i>
            <?php endif; ?>
            เข้าสู่ระบบด้วย Provider ID
            <span class="provider-desc">ยืนยันตัวตนผ่าน Provider ID (มธ.)</span>
        </a>

        <a href="<?= Html::encode($thaidUrl) ?>" class="mobile-login-thaid">
            <?php if ($hasThaidLogo): ?>
                <?= Html::img('@web/images/thaid_logo.jpg', ['class' => 'thaid-icon', 'alt' => 'ThaiD']) ?>
            <?php else: ?>
                <i data-lucide="smartphone" class="me-1" style="width: 1.25rem; height: 1.25rem; vertical-align: -0.3em;"></i>
            <?php endif; ?>
            เข้าสู่ระบบด้วย ThaiD
            <span class="thaid-desc">ยืนยันตัวตนผ่านแอป ThaiD</span>
        </a>
    </div>
</div>

<div class="mobile-login-links text-center" style="padding-bottom: env(safe-area-inset-bottom);">
    <p class="small text-body-secondary mb-0">ใช้บัญชีของหน่วยงานเพื่อเข้าสู่ระบบ</p>
</div>

<div id="telegram-debug" class="mt-3 text-danger small"></div>
<?php
$this->registerJs(<<<JS

(function(){

    const tg = window.Telegram?.WebApp;
    const debug = document.getElementById("telegram-debug");

    function log(msg){
        console.log(msg);
        if(debug){
            debug.innerText = typeof msg === "object"
                ? JSON.stringify(msg,null,2)
                : msg;
        }
    }

    if(!tg){
        log("Not running inside Telegram");
        return;
    }

    tg.ready();
    tg.expand();

    const user = tg.initDataUnsafe?.user;

    if(!user){
        log("Telegram user not found");
        window.location.href = "/mobile/auth/login";
        return;
    }
    $('#telegram_id').val(user.id);
    $.ajax({

        url: "/mobile/auth/telegram-auto-login",
        type: "POST",
        dataType: "json",
        data: {
            telegram_id: user.id
        },
        headers: {
            'X-CSRF-Token': yii.getCsrfToken()
        },

        success: function(res){

            log(res);

            if(res.success){

                window.location.href = "/mobile/default/index";

            }

        },

        error:function(err){

            log({
                error:true,
                detail:err
            });

        }

    });

})();

JS,View::POS_END);
?>


<!-- curl -X POST https://api.telegram.org/bot8489332575:AAHIh2X9ipxpDs8x77UQ2IXxG1emctDlzdo/sendMessage \
-d chat_id=7501172744 \
-d text="Hello from system"

curl -X POST https://api.telegram.org/bot8489332575:AAHIh2X9ipxpDs8x77UQ2IXxG1emctDlzdo/sendMessage \
-d chat_id=8177437409 \
-d text="📢 ระบบ ERP แจ้งเตือน

มีคำขออนุมัติใหม่" \
-d reply_markup='{
 "inline_keyboard":[
  [
   {"text":"เปิดระบบ","url":"https://erp.yourdomain.com"}
  ]
 ]
}' -->