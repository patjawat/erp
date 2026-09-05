<?php

use app\modules\qms\models\CycleItem;
use app\modules\qms\models\Evidence;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\qms\models\CycleItem $item */
/** @var app\modules\qms\models\Cycle $cycle */
/** @var app\modules\qms\models\Standard $standard */
/** @var app\modules\qms\models\Evidence[] $evidences */

$this->title = $item->title_snapshot;
$sourceIcons = [
    Evidence::SOURCE_DMS => 'bi-folder-symlink',
    Evidence::SOURCE_MEDSOP => 'bi-journal-medical',
    Evidence::SOURCE_FILE => 'bi-file-earmark-arrow-down',
    Evidence::SOURCE_LINK => 'bi-link-45deg',
];
?>
<?php $this->beginBlock('page-title'); ?>หลักฐาน<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($standard->name) ?> · ปี <?= (int) $cycle->fiscal_year ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับ Checklist', ['checklist', 'standard_id' => $standard->id, 'fy' => $cycle->fiscal_year], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>

    <div class="card border shadow-sm mb-3">
        <div class="card-body">
            <?php if ($item->requirement && $item->requirement->code): ?>
                <span class="badge text-bg-light border mb-1"><?= Html::encode($item->requirement->code) ?></span>
            <?php endif; ?>
            <h1 class="h5 fw-semibold mb-1"><?= Html::encode($item->title_snapshot) ?></h1>
            <?php if ($item->requirement && $item->requirement->evidence_hint): ?>
                <div class="small text-body-secondary"><i class="bi bi-paperclip me-1"></i>ควรมี: <?= Html::encode($item->requirement->evidence_hint) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3">
        <!-- ซ้าย: สถานะ -->
        <div class="col-12 col-lg-4">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary fw-semibold">สถานะ</div>
                <div class="card-body">
                    <?= Html::beginForm(['item-save', 'id' => $item->id], 'post') ?>
                        <div class="mb-3">
                            <label class="form-label small text-body-secondary">สถานะความครบถ้วน</label>
                            <?= Html::dropDownList('status', $item->status, CycleItem::statusLabels(), ['class' => 'form-select']) ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-body-secondary">กำหนดส่ง</label>
                            <?= Html::input('date', 'due_date', $item->due_date, ['class' => 'form-control']) ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-body-secondary">หมายเหตุ</label>
                            <?= Html::textarea('note', $item->note, ['class' => 'form-control', 'rows' => 2]) ?>
                        </div>
                        <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>บันทึกสถานะ', ['class' => 'btn btn-primary w-100']) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>

        <!-- ขวา: หลักฐาน -->
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">หลักฐาน (<?= count($evidences) ?>)</span>
                </div>
                <?php if (empty($evidences)): ?>
                    <div class="card-body text-center text-body-secondary py-4">
                        <i class="bi bi-inbox fs-3"></i>
                        <div>ยังไม่มีหลักฐาน — แนบไฟล์หรือใส่ลิงก์ด้านล่าง</div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($evidences as $ev): ?>
                            <div class="list-group-item d-flex align-items-center gap-2">
                                <i class="bi <?= $sourceIcons[$ev->source_type] ?? 'bi-paperclip' ?> fs-5 text-primary"></i>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="text-truncate"><?= Html::encode($ev->title ?: $ev->file_name ?: $ev->url) ?></div>
                                    <div class="small text-body-secondary"><?= Html::encode($ev->sourceLabel()) ?><?= $ev->note ? ' · ' . Html::encode($ev->note) : '' ?></div>
                                </div>
                                <?php if ($ev->source_type === Evidence::SOURCE_FILE): ?>
                                    <?= Html::a('<i class="bi bi-download"></i>', ['evidence-file', 'id' => $ev->id], ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank', 'title' => 'ดาวน์โหลด']) ?>
                                <?php elseif ($ev->source_type === Evidence::SOURCE_LINK): ?>
                                    <?= Html::a('<i class="bi bi-box-arrow-up-right"></i>', $ev->url, ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank', 'rel' => 'noopener', 'title' => 'เปิดลิงก์']) ?>
                                <?php endif; ?>
                                <?= Html::beginForm(['evidence-delete', 'id' => $ev->id], 'post', ['class' => 'd-inline']) ?>
                                    <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger', 'data' => ['confirm' => 'ลบหลักฐานนี้?'], 'title' => 'ลบ']) ?>
                                <?= Html::endForm() ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- เพิ่มหลักฐาน -->
                <div class="card-footer bg-body">
                    <div class="fw-semibold mb-2 small">เพิ่มหลักฐาน</div>
                    <ul class="nav nav-pills nav-sm mb-2" role="tablist">
                        <li class="nav-item"><button class="nav-link active py-1 px-3" data-bs-toggle="tab" data-bs-target="#ev-file" type="button"><i class="bi bi-file-earmark-arrow-up me-1"></i>แนบไฟล์</button></li>
                        <li class="nav-item"><button class="nav-link py-1 px-3" data-bs-toggle="tab" data-bs-target="#ev-link" type="button"><i class="bi bi-link-45deg me-1"></i>ลิงก์</button></li>
                        <li class="nav-item"><button class="nav-link py-1 px-3 disabled" type="button" title="เฟสถัดไป"><i class="bi bi-folder-symlink me-1"></i>DMS/medsop</button></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="ev-file">
                            <?= Html::beginForm(['evidence-add', 'cycle_item_id' => $item->id], 'post', ['enctype' => 'multipart/form-data']) ?>
                                <?= Html::hiddenInput('source_type', Evidence::SOURCE_FILE) ?>
                                <div class="row g-2">
                                    <div class="col-md-5"><?= Html::fileInput('file', null, ['class' => 'form-control', 'required' => true]) ?></div>
                                    <div class="col-md-5"><?= Html::input('text', 'title', null, ['class' => 'form-control', 'placeholder' => 'ป้ายกำกับ (ไม่บังคับ)']) ?></div>
                                    <div class="col-md-2 d-grid"><?= Html::submitButton('<i class="bi bi-plus-lg"></i>', ['class' => 'btn btn-primary']) ?></div>
                                </div>
                            <?= Html::endForm() ?>
                        </div>
                        <div class="tab-pane fade" id="ev-link">
                            <?= Html::beginForm(['evidence-add', 'cycle_item_id' => $item->id], 'post') ?>
                                <?= Html::hiddenInput('source_type', Evidence::SOURCE_LINK) ?>
                                <div class="row g-2">
                                    <div class="col-md-5"><?= Html::input('url', 'url', null, ['class' => 'form-control', 'placeholder' => 'https://...', 'required' => true]) ?></div>
                                    <div class="col-md-5"><?= Html::input('text', 'title', null, ['class' => 'form-control', 'placeholder' => 'ป้ายกำกับ (ไม่บังคับ)']) ?></div>
                                    <div class="col-md-2 d-grid"><?= Html::submitButton('<i class="bi bi-plus-lg"></i>', ['class' => 'btn btn-primary']) ?></div>
                                </div>
                            <?= Html::endForm() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
