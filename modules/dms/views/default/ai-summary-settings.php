<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var string $currentLength */
/** @var array $options */

$this->title = 'ตั้งค่า AI สรุปเนื้อหา';
$this->params['breadcrumbs'][] = ['label' => 'งานสารบรรณ', 'url' => ['/dms/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> ความยาวของการสรุปคำ (จาก PDF)
        </h5>
    </div>
    <div class="card-body">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= Yii::$app->session->getFlash('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <p class="text-muted mb-4">
            เลือกระดับความยาวของรายละเอียดที่ AI จะสรุปจากไฟล์ PDF เมื่อกดปุ่ม "สรุปด้วย AI" ในหน้าสร้างหนังสือ
        </p>

        <?php $form = ActiveForm::begin([
            'id' => 'form-ai-summary-settings',
            'options' => ['class' => ''],
        ]); ?>

        <div class="mb-4">
            <label class="form-label fw-semibold">ความยาวการสรุป</label>
            <div class="row g-3">
                <?php foreach ($options as $value => $label): ?>
                    <div class="col-md-4">
                        <div class="form-check card border rounded-3 h-100 <?= $currentLength === $value ? 'border-primary border-2' : '' ?>">
                            <div class="card-body">
                                <input type="radio" name="summary_length" value="<?= Html::encode($value) ?>"
                                       id="length-<?= $value ?>" class="form-check-input"
                                       <?= $currentLength === $value ? 'checked' : '' ?>>
                                <label class="form-check-label w-100" for="length-<?= $value ?>">
                                    <span class="fw-semibold"><?= Html::encode($label) ?></span>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="d-flex gap-2">
            <?= Html::submitButton('<i class="fa-solid fa-check me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> กลับ', ['/dms/dashboard'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
