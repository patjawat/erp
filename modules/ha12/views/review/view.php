<?php

use app\components\AppHelper;
use app\modules\ha12\models\Ha12Review;
use app\modules\ha12\models\Ha12ReviewFollowup;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12Review $model */
/** @var app\modules\ha12\models\Ha12Activity $activity */
/** @var array $fields */
/** @var Ha12ReviewFollowup $followup */
/** @var bool $canManage */

$this->title = 'HA12-PCT · ' . ($model->title ?: 'การทบทวน');
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($activity->no . '. ' . $activity->name) ?> · ปีงบ <?= $model->fiscal_year ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'review']) ?></div>

    <?php if ($msg = Yii::$app->session->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = Yii::$app->session->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 fw-semibold mb-0"><?= Html::encode($model->title ?: '(ไม่มีหัวข้อ)') ?></h1>
            <div class="text-body-secondary small">
                <?= Html::encode($model->ownerUnit->name ?? '—') ?>
                · วันที่ทบทวน <?= $model->review_date ? AppHelper::convertToThai($model->review_date) : '—' ?>
                · รุ่นที่ <?= (int) $model->revision ?>
                <?php if ($model->deleted): ?><span class="badge bg-danger-subtle text-danger-emphasis ms-1">ลบแล้ว</span><?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับ', ['index', 'activity_id' => $model->activity_id, 'fy' => $model->fiscal_year], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
            <?php if ($canManage && !$model->deleted): ?>
                <?= Html::a('<i class="bi bi-pencil"></i> แก้ไข', ['update', 'id' => $model->id, 'title' => 'แก้ไขการทบทวน'], ['class' => 'btn btn-primary btn-sm open-modal', 'data' => ['size' => 'modal-lg']]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <!-- รายละเอียดการทบทวน -->
            <div class="card border shadow-sm mb-3">
                <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-card-text me-1"></i> รายละเอียดการทบทวน</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <?php foreach ($fields as $f): ?>
                            <?php $val = $model->fields[$f['key']] ?? null; ?>
                            <dt class="col-sm-4 text-body-secondary small fw-semibold"><?= Html::encode($f['label']) ?></dt>
                            <dd class="col-sm-8"><?= $val !== null && $val !== '' ? nl2br(Html::encode((string) $val)) : '<span class="text-body-tertiary">—</span>' ?></dd>
                        <?php endforeach; ?>
                        <dt class="col-sm-4 text-body-secondary small fw-semibold">ผู้ทบทวน</dt>
                        <dd class="col-sm-8"><?= $model->reviewer_name ? Html::encode($model->reviewer_name) : '<span class="text-body-tertiary">—</span>' ?></dd>
                    </dl>
                </div>
            </div>

            <!-- ผลติดตาม -->
            <div class="card border shadow-sm">
                <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-clock-history me-1"></i> ผลติดตาม</div>
                <div class="card-body">
                    <?php if ($model->followups): ?>
                        <ul class="list-group list-group-flush mb-3">
                            <?php foreach ($model->followups as $fu): ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="small text-body-secondary"><?= $fu->followup_date ? AppHelper::convertToThai($fu->followup_date) : '—' ?></div>
                                            <div><?= nl2br(Html::encode((string) $fu->finding)) ?></div>
                                            <?php if ($fu->evidence): ?>
                                                <div class="small text-body-secondary mt-1"><i class="bi bi-paperclip"></i> <?= nl2br(Html::encode((string) $fu->evidence)) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($canManage): ?>
                                            <?= Html::a('<i class="bi bi-x-lg"></i>', ['delete-followup', 'id' => $fu->id], [
                                                'class' => 'btn btn-sm btn-outline-danger flex-shrink-0', 'aria-label' => 'ลบผลติดตาม',
                                                'data' => ['method' => 'post', 'confirm' => 'ลบผลติดตามนี้?'],
                                            ]) ?>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-body-secondary small">ยังไม่มีผลติดตาม</p>
                    <?php endif; ?>

                    <?php if ($canManage && !$model->deleted): ?>
                        <?= Html::beginForm(['add-followup', 'id' => $model->id], 'post', ['class' => 'border-top pt-3']) ?>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold mb-1">วันที่ติดตาม <span class="text-danger">*</span></label>
                                    <?= DatepickerThai::widget([
                                        'name' => 'followup_date_thai',
                                        'value' => AppHelper::convertToThai(date('Y-m-d')),
                                        'options' => ['id' => 'ha12-fu-date', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.', 'class' => 'form-control'],
                                    ]) ?>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold mb-1">ผลที่พบ <span class="text-danger">*</span></label>
                                    <?= Html::textarea('Ha12ReviewFollowup[finding]', '', ['class' => 'form-control', 'rows' => 2, 'required' => true]) ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold mb-1">หลักฐาน</label>
                                    <?= Html::textarea('Ha12ReviewFollowup[evidence]', '', ['class' => 'form-control', 'rows' => 1]) ?>
                                </div>
                            </div>
                            <div class="d-grid d-sm-flex justify-content-sm-end mt-2">
                                <?= Html::submitButton('<i class="bi bi-plus-lg"></i> เพิ่มผลติดตาม', ['class' => 'btn btn-primary btn-sm']) ?>
                            </div>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <!-- ประวัติรุ่น -->
            <div class="card border shadow-sm">
                <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-layers me-1"></i> ประวัติการแก้ไข</div>
                <div class="card-body">
                    <?php if ($model->versions): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($model->versions as $v): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">รุ่น <?= (int) $v->revision ?></span>
                                        <span class="ms-1"><?= Html::encode($v->actionLabel()) ?></span>
                                    </div>
                                    <span class="small text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= $v->created_at ? AppHelper::convertToThai(substr((string) $v->created_at, 0, 10)) : '' ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-body-secondary small mb-0">ยังไม่มีประวัติ</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->registerJs("if (typeof thaiDatepicker === 'function') { thaiDatepicker('#ha12-fu-date'); }"); ?>
