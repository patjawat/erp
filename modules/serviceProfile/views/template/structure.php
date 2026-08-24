<?php
use app\modules\serviceProfile\models\ServiceProfileTemplate;
use app\modules\serviceProfile\models\ServiceProfileTemplateSection;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'โครงสร้าง Template';
$editable = $model->lifecycle_status === ServiceProfileTemplate::STATUS_DRAFT;
$typeLabels = ServiceProfileTemplateSection::blockTypeLabels();
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($model->owner_name_snapshot . ' · ' . $model->name . ' · Revision ' . $model->revision_no) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><div class="d-flex flex-wrap gap-2"><?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับไปรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?><?php if ($editable): ?><?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ', ['create-section', 'template_id' => $model->id], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-lg']) ?><?php endif; ?></div><?php $this->endBlock(); ?>

<div class="alert <?= $editable ? 'alert-info' : 'alert-success' ?> d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" role="status">
    <div><div class="fw-semibold"><?= $editable ? 'Template ยังเป็นฉบับร่าง' : 'Template นี้ประกาศใช้แล้ว' ?></div><div class="small"><?= $editable ? 'เพิ่ม ลด หรือแก้ลำดับหัวข้อได้ก่อนประกาศใช้' : 'ข้อมูลถูกล็อกเพื่อรักษาประวัติ หากต้องแก้ให้สร้าง Revision ใหม่' ?></div></div>
    <div class="d-flex gap-2 flex-shrink-0"><?php if ($editable): ?><?= Html::a('ประกาศใช้ Template', ['publish', 'id' => $model->id], ['class' => 'btn btn-success', 'data-method' => 'post', 'data-confirm' => 'ยืนยันประกาศใช้ Template นี้?']) ?><?php else: ?><?= Html::a('สร้าง Revision ใหม่', ['clone', 'id' => $model->id], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?><?php endif; ?></div>
</div>

<?php Pjax::begin(['id' => 'sp-template-sections', 'enablePushState' => false]); ?>
<section class="card bg-body border shadow-sm" aria-labelledby="sp-sections-heading">
    <div class="card-header bg-body-tertiary py-3"><h2 id="sp-sections-heading" class="h5 fw-semibold mb-1">หัวข้อใน Template</h2><p class="small text-body-secondary mb-0"><?= count($model->sections) ?> หัวข้อ เรียงตามลำดับที่ใช้จัดทำเอกสาร</p></div>
    <div class="card-body p-0">
        <ol class="list-group list-group-numbered list-group-flush">
        <?php foreach ($model->sections as $section): ?>
            <li class="list-group-item d-flex flex-column flex-md-row align-items-md-center gap-3 py-3">
                <div class="ms-2 flex-grow-1 min-w-0"><div class="d-flex flex-wrap align-items-center gap-2"><span class="fw-semibold text-break"><?= Html::encode($section->title) ?></span><?php if ($section->is_required): ?><span class="badge bg-warning-subtle text-warning-emphasis">บังคับกรอก</span><?php endif; ?><?php if (!$section->is_enabled): ?><span class="badge bg-body-secondary text-body-secondary">ปิดใช้</span><?php endif; ?></div><div class="small text-body-secondary mt-1"><?= Html::encode($typeLabels[$section->block_type] ?? $section->block_type) ?> · รหัส <?= Html::encode($section->section_code) ?> · ลำดับ <?= (int) $section->sort_order ?></div><?php if ($section->description): ?><div class="small mt-1 text-break"><?= Html::encode($section->description) ?></div><?php endif; ?></div>
                <?php if ($editable): ?><div class="d-flex gap-2 ms-md-auto"><a href="<?= \yii\helpers\Url::to(['update-section', 'id' => $section->id]) ?>" class="btn btn-outline-secondary open-modal" data-size="modal-lg">แก้ไข</a><?= Html::a('<i class="bi bi-trash" aria-hidden="true"></i>', ['delete-section', 'id' => $section->id], ['class' => 'btn btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ลบหัวข้อนี้ออกจาก Template?', 'aria-label' => 'ลบหัวข้อ']) ?></div><?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ol>
    </div>
</section>
<?php Pjax::end(); ?>
