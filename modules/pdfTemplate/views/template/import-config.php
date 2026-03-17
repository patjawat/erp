<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\pdfTemplate\models\PdfTemplate[] $templates */

$this->title = 'นำเข้า config ตำแหน่งฟิลด์';
$this->params['breadcrumbs'][] = ['label' => 'Template รายงานขอไปราชการ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-info bg-opacity-10 text-info border-0 py-3">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-upload me-2"></i><?= Html::encode($this->title) ?></h5>
    </div>
    <div class="card-body p-4">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <p class="text-muted small mb-4">เลือกเทมเพลตปลายทาง แล้วอัปโหลดไฟล์ JSON ที่ส่งออกจากเทมเพลตอื่น (หรือวางเนื้อหา JSON ด้านล่าง) — ตำแหน่งฟิลด์จะถูกแทนที่ด้วย config ที่นำเข้า</p>

        <?php $form = ActiveForm::begin(['action' => ['import-config'], 'method' => 'post', 'options' => ['enctype' => 'multipart/form-data']]); ?>
        <div class="mb-4">
            <label class="form-label fw-semibold">เทมเพลตที่จะนำเข้า config เข้าไป</label>
            <select name="template_id" class="form-select" required>
                <option value="">— เลือกเทมเพลต —</option>
                <?php foreach ($templates as $t): ?>
                <option value="<?= (int) $t->id ?>"><?= Html::encode($t->name) ?> (id=<?= (int) $t->id ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">ไฟล์ JSON (ส่งออกจากเมนู «ส่งออก config»)</label>
            <input type="file" name="config_file" class="form-control" accept=".json,application/json">
            <div class="form-text">หรือวางเนื้อหา JSON ด้านล่าง (ถ้ามีทั้งไฟล์และช่องวาง ระบบใช้ไฟล์)</div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">หรือวางเนื้อหา JSON ตรงนี้</label>
            <textarea name="config_json" class="form-control" rows="8" placeholder='{"template_name":"...","fields":[...]}' style="font-family: monospace; font-size: 0.9rem;"></textarea>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-upload me-1"></i> นำเข้า config</button>
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
