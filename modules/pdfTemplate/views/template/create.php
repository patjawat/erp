<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pdfTemplate\models\PdfTemplate $model */

$this->title = 'เพิ่มเทมเพลต';
$this->params['breadcrumbs'][] = ['label' => 'Template รายงานขอไปราชการ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-plus-circle me-2"></i><?= Html::encode($this->title) ?></h5>
    </div>
    <div class="card-body p-4">
        <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
            <div class="mb-3">
                <label class="form-label fw-semibold">ชื่อเทมเพลต</label>
                <input type="text" name="name" class="form-control" value="<?= Html::encode($model->name) ?>" placeholder="เช่น รายงานขอไปราชการ" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">ไฟล์ PDF ต้นแบบ</label>
                <input type="file" name="pdf_file" class="form-control" accept=".pdf,application/pdf" required>
                <div class="form-text">อัปโหลดไฟล์ PDF ที่จะใช้เป็นพื้นหลังสำหรับกำหนดตำแหน่งฟิลด์</div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-check-lg me-1"></i> บันทึก</button>
                <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            </div>
        </form>
    </div>
</div>
