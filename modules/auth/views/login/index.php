<?php

use yii\helpers\Url;
use yii\web\View;
use yii\helpers\Html;
use app\models\Categorise;
use app\components\SiteHelper;
use kartik\widgets\ActiveForm;

$site = Categorise::findOne(['name' => 'site']);
$color = isset($site->data_json['theme_color']) ? $site->data_json['theme_color'] : '';
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : '';
$dataJson = ($site && is_array($site->data_json)) ? $site->data_json : [];
/** ข้อความบรรทัด 1–2 และไอคอน: ดึงจากตั้งค่าองค์กร (Header) เมื่อมีการกำหนด — ถ้าเว้นว่างใช้ HOSPITAL / ERP SYSTEM ตาม SiteHelper */
$loginHeaderBrand = SiteHelper::resolveHeaderBrand($dataJson);
if (!empty($loginHeaderBrand['google_font_href'])) {
    $this->registerLinkTag([
        'rel' => 'stylesheet',
        'href' => $loginHeaderBrand['google_font_href'],
    ]);
}
$loginBrandFontFamily = trim($loginHeaderBrand['brand_font_family_css'] ?? '');
$this->title = 'กรุณายืนยันตัวตน';
$this->params['breadcrumbs'][] = $this->title;

$thaidClientId = env('THAID_CLIENT_ID');
$authUrl = "https://moph.id.th/oauth/redirect?" . http_build_query([
    'client_id'     => '0194e132-099e-7e9b-b25c-a927c7e35d83',
    'redirect_uri'  => 'https://provider.tphcp.go.th/callback',
    'response_type' => 'code',
    'state'         => $thaidClientId,
]);

$providerLoginUrl = \yii\helpers\Url::to('https://moph.id.th/oauth/redirect?' . http_build_query([
    'client_id' => '0194e132-099e-7e9b-b25c-a927c7e35d83',
    'redirect_uri' => 'https://providerid.erpcph.com/callback',
    'response_type' => 'code',
    'state' => env('PROVIDER_REDIRECT_URI')
]));

// ยกเลิกพื้นหลังเทา (#f3f3f3) จาก web/css/custom.css สำหรับ .form-check-input — เฉพาะหน้า login นี้
$rememberMeInputId = Html::getInputId($model, 'rememberMe');
$this->registerCss(<<<CSS
#{$rememberMeInputId}.form-check-input:not(:checked) {
  --bs-form-check-bg: transparent;
  background-color: transparent;
}
.erp-login-brand-icon svg {
  width: 42px;
  height: 42px;
  stroke-width: 1.25;
}
CSS
);
?>

<div class="min-vh-100 d-flex align-items-center justify-content-center bg-primary p-3 p-md-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-6 col-xl-5">
                <div class="card border-0 shadow-lg rounded-3 bg-white overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <!-- Header: ไอคอน + บรรทัด 1–2 จากตั้งค่าองค์กร (หรือค่าเริ่มต้น HOSPITAL / ERP SYSTEM) -->
                        <div class="text-center mb-4">
                            <div class="rounded-3 bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center p-3 mb-3 shadow-sm text-primary">
                                <span class="erp-login-brand-icon d-inline-flex align-items-center justify-content-center" aria-hidden="true"><i data-lucide="<?= Html::encode($loginHeaderBrand['lucide_icon']) ?>"></i></span>
                            </div>
                            <h1 class="h4 fw-bold text-primary mb-1"<?= $loginBrandFontFamily !== '' ? ' style="' . Html::encode($loginBrandFontFamily) . '"' : '' ?>><?= Html::encode($loginHeaderBrand['line1']) ?></h1>
                            <p class="small text-muted"<?= $loginBrandFontFamily !== '' ? ' style="' . Html::encode($loginBrandFontFamily) . '"' : '' ?>><?= Html::encode($loginHeaderBrand['line2']) ?></p>
                            <p class="text-muted small mb-0">กรอกข้อมูลเพื่อเข้าสู่บัญชีของคุณ</p>
                        </div>
                        <div class="text-center mb-4">
                        </div>

                        <?php $form = ActiveForm::begin(['id' => 'blank-form', 'enableAjaxValidation' => false]); ?>

                        <?= $form->field($model, 'username', [
                            'template' => '{label}<div class="position-relative">{input}<i class="bi bi-person-fill text-muted position-absolute top-50 translate-middle-y ms-3 z-2 pe-none start-0"></i></div>{error}',
                        ])->textInput([
                            'placeholder' => 'ชื่อเข้าใช้งาน',
                            'value' => '',
                            'class' => 'form-control form-control-lg rounded-pill ps-5'
                        ])->label('ชื่อเข้าใช้งาน', ['class' => 'form-label text-dark']) ?>

                        <?= $form->field($model, 'password', [
                            'template' => '{label}<div class="position-relative">{input}<i class="bi bi-lock text-muted position-absolute top-50 translate-middle-y ms-3 z-2 pe-none start-0"></i></div>{error}',
                        ])->passwordInput([
                            'placeholder' => 'รหัสผ่านของคุณ',
                            'value' => '',
                            'class' => 'form-control form-control-lg rounded-pill ps-5'
                        ])->label('รหัสผ่าน', ['class' => 'form-label text-dark']) ?>

                        <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                            <div class="flex-grow-1 min-w-0">
                               
                                    <div class="form-check form-switch mb-0">
                                        <?= $form->field($model, 'rememberMe')->checkbox([
                                            'class' => 'form-check-input mt-0',
                                            'role' => 'switch',
                                            'template' => "{input}\n{label}",
                                        ])->label('จำการเข้าสู่ระบบไว้ในอุปกรณ์นี้ (สูงสุด 1 วัน)', ['class' => 'form-check-label text-dark fw-medium']) ?>
                                    </div>
                                
                            </div>
                            <div class="flex-shrink-0 pt-1">
                                <?= Html::a('ลืมรหัสผ่าน?', ['/site/forgot-password'], ['class' => 'text-primary small text-decoration-none']) ?>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button class="btn btn-primary rounded-2 py-2" id="btn-login" type="submit">
                                <span class="fw-bold">เข้าสู่ระบบ</span> <i class="bi bi-arrow-right"></i>
                            </button>
                            <button class="btn btn-primary btn-lg rounded-2 py-2" id="btnAwait" type="button" disabled>
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                รอสักครู่...
                            </button>
                        </div>

                        <?php ActiveForm::end(); ?>

                        <div class="d-flex align-items-center my-4">
                            <hr class="flex-grow-1 border-secondary">
                            <span class="mx-2 text-muted small">หรือเข้าด้วย</span>
                            <hr class="flex-grow-1 border-secondary">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <a href="<?= $providerLoginUrl ?>" class="btn btn-light w-100 d-flex align-items-center justify-content-center rounded-2 py-2 border">
                                    <?= Html::img('@web/images/provider_logo.png', ['class' => 'rounded me-2 object-fit-contain', 'alt' => 'Provider ID', 'width' => '28', 'height' => '28']) ?>
                                    <span class="small text-dark">PROVIDER ID</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?= Url::to(['/auth/thaid/']) ?>" class="btn btn-light w-100 d-flex align-items-center justify-content-center rounded-2 py-2 border">
                                    <?= Html::img('@web/images/thaid_logo.jpg', ['class' => 'rounded me-2 object-fit-contain', 'alt' => 'ThaiID', 'width' => '28', 'height' => '28']) ?>
                                    <span class="small text-dark">ThaiID</span>
                                </a>
                            </div>
                        </div>

                        <div class="text-center mb-4">
                            <p class="small text-muted mb-0">
                                ยังไม่มีบัญชี? <?= Html::a('สมัครสมาชิก', ['/site/sign-up'], ['class' => 'text-primary fw-medium text-decoration-none']) ?>
                            </p>
                        </div>

                        <hr class="border-secondary my-4">

                        <div class="text-center">
                            <?php $companyName = SiteHelper::getInfo()['company_name'] ?? 'โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย'; ?>
                            <p class="fw-bold text-dark mb-1"><?= Html::encode($companyName) ?></p>
                            <p class="text-success mb-3 small">"ผู้ให้การสนับสนุนและรวมพลังพัฒนาโดย มูลนิธิรามาธิบดี โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย และ โรงพยาบาลอุบลรัตน์"</p>
                            <div class="d-flex justify-content-center align-items-center gap-3 gap-md-4 flex-wrap mb-2">
                                <?= Html::img('@web/banner/banner2.png', ['class' => 'rounded object-fit-contain', 'alt' => 'PCSHS', 'width' => '52', 'height' => '52']) ?>
                                <?= Html::img('@web/banner/banner1.png', ['class' => 'rounded object-fit-contain', 'alt' => 'Rama Foundation', 'width' => '70', 'height' => '70']) ?>
                                <?= Html::img('@web/banner/banner3.png', ['class' => 'rounded object-fit-contain', 'alt' => 'MOPH', 'width' => '52', 'height' => '52']) ?>
                            </div>
                            <p class="small text-muted mb-0">© 2026 HOSPITAL ERP SYSTEM | DAN SAI</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<< JS
if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
$('#btnAwait').hide();
$('#btn-login').show();
$('#blank-form').on('beforeSubmit', function (e) {
    e.preventDefault();
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
                window.location.href = data.redirect;
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