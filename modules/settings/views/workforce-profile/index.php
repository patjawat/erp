<?php

use app\modules\hr\models\WorkforceProfile;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var WorkforceProfile $model */
/** @var int $year */
/** @var array $years */
/** @var array $levels */

$this->title = 'โปรไฟล์โรงพยาบาล';
$missing = $model->missingDrivers();
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/settings/views/_workforce_menu', ['active' => 'workforce-profile']) ?>
<?php $this->endBlock(); ?>

<?php foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls): ?>
    <?php if (Yii::$app->session->hasFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
            <?= Yii::$app->session->getFlash($key) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <div class="fw-semibold">ตัวขับเคลื่อนที่สูตรกรอบอัตรากำลังใช้</div>
                <p class="text-body-secondary small mb-0">
                    กรอกครั้งเดียวต่อปี ระบบจะคำนวณกรอบตามเกณฑ์ สป.สธ. ให้เอง
                    ปีถัดไปจะตั้งต้นด้วยค่าของปีนี้
                </p>
            </div>
            <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-end gap-2']) ?>
                <div>
                    <label class="form-label small mb-1">ปีงบประมาณ</label>
                    <select name="thai_year" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($years as $value => $label): ?>
                            <option value="<?= (int) $value ?>" <?= $year === (int) $value ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<?php if ($missing !== []): ?>
    <div class="alert alert-warning d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div>
            <div class="fw-semibold">ยังกรอกไม่ครบ <?= count($missing) ?> รายการ</div>
            <div class="small">
                กรอบของสายงานที่ใช้ค่าเหล่านี้จะยังคำนวณไม่ได้ —
                <?= Html::encode(implode(' · ', $missing)) ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php $form = ActiveForm::begin(['id' => 'workforce-profile-form']); ?>
<div class="card mb-3">
    <div class="card-header bg-body-tertiary fw-semibold">ขนาดโรงพยาบาล</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-4">
                <?= $form->field($model, 'level_code')->dropDownList($levels, ['prompt' => '— เลือกระดับ —']) ?>
                <div class="form-text">ระดับกำหนดว่าสายงานไหนมีกรอบได้บ้างตามเกณฑ์</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-body-tertiary fw-semibold">ตัวเลขที่เข้าสูตร</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:26%">ตัวขับเคลื่อน</th>
                    <th style="width:22%">ค่า</th>
                    <th>ใช้กับสายงาน</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (WorkforceProfile::DRIVERS as $attribute => [$label, $unit, $usedBy]): ?>
                    <tr>
                        <th scope="row" class="fw-normal">
                            <?= Html::encode($label) ?>
                            <?php if (isset($missing[$attribute])): ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis ms-1">ยังไม่กรอก</span>
                            <?php endif; ?>
                        </th>
                        <td>
                            <div class="input-group input-group-sm">
                                <?= Html::activeTextInput($model, $attribute, [
                                    'class' => 'form-control text-end',
                                    'inputmode' => 'decimal',
                                    'aria-label' => $label,
                                ]) ?>
                                <span class="input-group-text"><?= Html::encode($unit) ?></span>
                            </div>
                            <?= Html::error($model, $attribute, ['class' => 'invalid-feedback d-block small']) ?>
                        </td>
                        <td class="text-body-secondary small"><?= Html::encode($usedBy) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <?= $form->field($model, 'note')->textarea(['rows' => 2]) ?>
    </div>
</div>

<div class="d-flex gap-2">
    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ทะเบียนเกณฑ์กรอบอัตรากำลัง', ['/settings/workforce-standard'], ['class' => 'btn btn-outline-secondary']) ?>
</div>
<?php ActiveForm::end(); ?>
