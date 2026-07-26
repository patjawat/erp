<?php
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\housing\models\MaintenanceRequest;
use yii\helpers\Html;

$this->title = $model->ticket_no;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'maintenance']) ?><?php $this->endBlock();
$renderPhotos = static function (array $photos, string $label) use ($model): string {
    if ($photos === []) return '<div class="text-muted small">ยังไม่มีรูปภาพ</div>';
    $html = '<div class="row g-2">';
    foreach ($photos as $photo) {
        $html .= '<div class="col-6 col-md-3"><img class="w-100 rounded border" style="aspect-ratio:4/3;object-fit:cover" src="' . Html::encode(FileManagerHelper::getImg($photo->id)) . '" alt="' . Html::encode($label) . '" loading="lazy" decoding="async">';
        $html .= Html::a('ลบภาพ', ['delete-photo', 'id' => $photo->id, 'maintenance_id' => $model->id], ['class' => 'btn btn-sm btn-link text-danger px-0', 'data-method' => 'post', 'data-confirm' => 'ลบรูปภาพนี้หรือไม่?']) . '</div>';
    }
    return $html . '</div>';
};
?>
<div class="container-fluid py-3">
    <?php foreach (['success', 'warning', 'error'] as $type): if (Yii::$app->session->hasFlash($type)): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>" role="alert"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div>
    <?php endif; endforeach; ?>
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-3"><div><h1 class="h4 mb-1"><?= Html::encode($model->title) ?></h1><div class="text-muted"><?= Html::encode($model->building->name ?? '') ?> · <?= Html::encode($model->location_note ?: 'ไม่ระบุจุด') ?></div></div><div><?= Html::a('<i data-lucide="pencil"></i> ปรับปรุงรายการ', ['update', 'id' => $model->id], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-xl']) ?> <?= Html::a('กลับรายการ', ['index', 'building_id' => $model->building_id], ['class' => 'btn btn-outline-secondary']) ?></div></div>
    <div class="row g-3">
        <div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-body">
            <h2 class="h6">รายละเอียดปัญหา</h2><p><?= nl2br(Html::encode($model->description)) ?></p><hr>
            <h2 class="h6">ผลการดำเนินการ</h2><p><?= nl2br(Html::encode($model->resolution ?: 'ยังไม่มีผลการดำเนินการ')) ?></p>
            <div class="row g-3 mt-1"><div class="col-md-6"><h2 class="h6">รูปภาพก่อนซ่อม</h2><?= $renderPhotos($beforePhotos, 'รูปภาพก่อนซ่อม') ?></div><div class="col-md-6"><h2 class="h6">รูปภาพหลังซ่อม</h2><?= $renderPhotos($afterPhotos, 'รูปภาพหลังซ่อม') ?></div></div>
        </div></div></div>
        <aside class="col-lg-4"><div class="card border-0 shadow-sm"><div class="card-body"><dl class="row mb-0 small">
            <dt class="col-5">เลขที่</dt><dd class="col-7"><?= Html::encode($model->ticket_no) ?></dd>
            <dt class="col-5">ผู้แจ้ง</dt><dd class="col-7"><?= Html::encode($model->reporter_name) ?></dd>
            <dt class="col-5">ประเภทผู้แจ้ง</dt><dd class="col-7"><?= Html::encode(MaintenanceRequest::reporterTypeOptions()[$model->reporter_type] ?? $model->reporter_type) ?></dd>
            <dt class="col-5">ขอบเขตปัญหา</dt><dd class="col-7"><?= Html::encode(MaintenanceRequest::scopeOptions()[$model->problem_scope] ?? $model->problem_scope) ?></dd>
            <?php if ($model->reporter_type === MaintenanceRequest::REPORTER_RESIDENT): ?><dt class="col-5">การรับทราบ</dt><dd class="col-7"><?= Html::encode(MaintenanceRequest::acknowledgementOptions()[$model->acknowledgement_status] ?? $model->acknowledgement_status) ?></dd><?php endif; ?>
            <dt class="col-5">วันที่แจ้ง</dt><dd class="col-7"><?= Yii::$app->formatter->asDatetime($model->reported_at, 'php:d/m/Y H:i') ?></dd>
            <dt class="col-5">สถานะ</dt><dd class="col-7"><?= Html::encode(MaintenanceRequest::statusOptions()[$model->status] ?? $model->status) ?></dd>
            <dt class="col-5">ความเร่งด่วน</dt><dd class="col-7"><?= Html::encode(MaintenanceRequest::priorityOptions()[$model->priority] ?? $model->priority) ?></dd>
            <dt class="col-5">ผู้รับผิดชอบ</dt><dd class="col-7"><?= Html::encode($model->assignedEmployee?->fullname() ?: 'ยังไม่มอบหมาย') ?></dd>
            <dt class="col-5">วันที่ดำเนินการ</dt><dd class="col-7"><?= $model->repaired_at ? Yii::$app->formatter->asDatetime($model->repaired_at, 'php:d/m/Y H:i') : '—' ?></dd>
            <dt class="col-5">ค่าใช้จ่ายรวม</dt><dd class="col-7 fw-semibold"><?= Yii::$app->formatter->asDecimal($model->expense_amount, 2) ?> บาท</dd>
        </dl></div></div></aside>
    </div>
</div>
