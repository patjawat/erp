<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\usermanager\models\User $model */

$this->title = 'ตั้งค่าบัญชีเข้าใช้งานระบบ';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'โปรไฟล์', 'url' => ['/me/profile']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-gear"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/me/menu', ['active' => 'profile']) ?>
<?php $this->endBlock(); ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-6">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
        <?php
        $successMsg = Yii::$app->session->getFlash('success');
        if (is_array($successMsg)) {
            $successText = $successMsg['message'] ?? $successMsg['title'] ?? null;
            $successText = is_string($successText) ? $successText : 'บันทึกการตั้งค่าบัญชีเรียบร้อย';
        } else {
            $successText = (string) $successMsg;
        }
        ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= Html::encode($successText) ?>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-warning bg-opacity-10 border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold text-warning"><i class="bi bi-person-badge me-2"></i>ชื่อเข้าใช้งานและรหัสผ่าน</h6>
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'form-account',
                    'options' => ['class' => 'needs-validation'],
                    'fieldConfig' => [
                        'template' => "{label}\n{input}\n{error}",
                        'inputOptions' => ['class' => 'form-control'],
                        'labelOptions' => ['class' => 'form-label fw-medium'],
                    ],
                ]); ?>

                <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'autocomplete' => 'username']) ?>

                <div class="mb-3">
                    <label class="form-label fw-medium text-muted small">รหัสผ่านใหม่</label>
                    <p class="text-muted small mb-2">เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยนรหัสผ่าน</p>
                    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'autocomplete' => 'new-password', 'placeholder' => 'รหัสผ่านใหม่ (ไม่กรอกถ้าไม่เปลี่ยน)'])->label(false) ?>
                </div>

                <?= $form->field($model, 'confirm_password')->passwordInput(['maxlength' => true, 'autocomplete' => 'new-password', 'placeholder' => 'ยืนยันรหัสผ่านใหม่']) ?>

                <hr class="my-4">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check-lg me-1"></i> บันทึก
                    </button>
                    <a href="<?= Url::to(['/me/profile']) ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-x-lg me-1"></i> ยกเลิก
                    </a>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
