<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\ha12\models\Ha12Review;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\ha12\models\Ha12Activity[] $activities */
/** @var int $activityId */
/** @var Ha12Review[] $reviews */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var array<int,array{id:int,name:string}> $units */
/** @var array<int,int> $followupCounts */
/** @var array{unit_id:?int,q:string,deleted:int} $filters */

$this->title = 'HA12-PCT · การทบทวน';

$current = null;
foreach ($activities as $a) {
    if ((int) $a->id === $activityId) {
        $current = $a;
        break;
    }
}
$unitOptions = [];
foreach ($units as $u) {
    $unitOptions[$u['id']] = $u['name'];
}
$activityOptions = [];
foreach ($activities as $a) {
    $activityOptions[$a->id] = $a->no . '. ' . $a->name;
}
$showDeleted = (int) ($filters['deleted'] ?? 0) === 1;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>การทบทวน 12 กิจกรรม · ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'review']) ?></div>

    <?php if ($msg = Yii::$app->session->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = Yii::$app->session->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- แถบเลือกกิจกรรม + ตัวกรอง -->
    <div class="card border shadow-sm mb-3">
        <div class="card-body">
            <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
                <div class="col-12 col-lg-4">
                    <label class="form-label small fw-semibold mb-1">กิจกรรม</label>
                    <?= Html::dropDownList('activity_id', $activityId, $activityOptions, ['class' => 'form-select', 'onchange' => 'this.form.submit()']) ?>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label small fw-semibold mb-1">ปีงบ</label>
                    <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select', 'onchange' => 'this.form.submit()']) ?>
                </div>
                <div class="col-6 col-lg-3">
                    <label class="form-label small fw-semibold mb-1">หน่วยงาน</label>
                    <?= Html::dropDownList('unit_id', $filters['unit_id'], $unitOptions, ['class' => 'form-select', 'prompt' => 'ทุกหน่วยงาน', 'onchange' => 'this.form.submit()']) ?>
                </div>
                <div class="col-12 col-lg-3">
                    <label class="form-label small fw-semibold mb-1">ค้นหา</label>
                    <div class="input-group">
                        <?= Html::textInput('q', $filters['q'], ['class' => 'form-control', 'placeholder' => 'หัวข้อ/ผู้ทบทวน']) ?>
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="col-12 d-flex flex-wrap align-items-center gap-3 mt-2">
                    <div class="form-check mb-0">
                        <?= Html::checkbox('deleted', $showDeleted, ['value' => 1, 'class' => 'form-check-input', 'id' => 'flt-deleted', 'onchange' => 'this.form.submit()']) ?>
                        <label class="form-check-label small" for="flt-deleted">แสดงรายการที่ลบ</label>
                    </div>
                    <?php if (($filters['q'] ?? '') !== '' || !empty($filters['unit_id']) || $showDeleted): ?>
                        <a href="<?= Url::to(['index', 'activity_id' => $activityId, 'fy' => $fiscalYear]) ?>" class="small text-decoration-none">ล้างตัวกรอง</a>
                    <?php endif; ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <div class="text-body-secondary small">
            <?php if ($current): ?>
                <i class="bi bi-journal-check me-1"></i><b><?= Html::encode($current->name) ?></b>
                <span class="ms-1"><?= Html::encode((string) $current->data_hint) ?></span>
            <?php endif; ?>
        </div>
        <?php if (!$showDeleted && $current): ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มการทบทวน', ['create', 'activity_id' => $activityId, 'fy' => $fiscalYear, 'title' => 'เพิ่มการทบทวน'], [
                'class' => 'btn btn-success rounded-pill open-modal',
                'data' => ['size' => 'modal-lg'],
            ]) ?>
        <?php endif; ?>
    </div>

    <div class="card border shadow-sm">
        <?php Pjax::begin(['id' => 'ha12-review-list', 'enablePushState' => false, 'timeout' => 8000]); ?>
        <div class="card-body p-0">
            <?php if (!$reviews): ?>
                <div class="text-center py-5">
                    <div class="fw-semibold mb-1"><?= $showDeleted ? 'ไม่มีรายการที่ลบ' : 'ยังไม่มีการทบทวนในกิจกรรมนี้' ?></div>
                    <div class="text-body-secondary small mb-3">เลือกกิจกรรม/ปีงบ แล้วกด “เพิ่มการทบทวน” เพื่อเริ่มบันทึก</div>
                </div>
            <?php else: ?>
                <!-- Desktop -->
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th style="width:44px;" class="text-center">#</th>
                                <th>หัวข้อ</th>
                                <th style="width:16rem;">หน่วยงาน</th>
                                <th style="width:110px;" class="text-end">วันที่ทบทวน</th>
                                <th style="width:90px;" class="text-center">ผลติดตาม</th>
                                <th style="width:120px;" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = $dataProvider->pagination->offset; ?>
                            <?php foreach ($reviews as $r): ?>
                                <tr>
                                    <td class="text-center text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= ++$i ?></td>
                                    <td>
                                        <?= Html::a(Html::encode($r->title ?: '(ไม่มีหัวข้อ)'), ['view', 'id' => $r->id], ['class' => 'fw-semibold text-decoration-none']) ?>
                                        <?php if ($r->reviewer_name): ?>
                                            <div class="text-body-secondary small">ผู้ทบทวน: <?= Html::encode($r->reviewer_name) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-body-secondary" title="<?= Html::encode($r->ownerUnit->name ?? '') ?>">
                                        <?= Html::encode($r->ownerUnit->name ?? '—') ?>
                                    </td>
                                    <td class="text-end text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= $r->review_date ? \app\components\AppHelper::convertToThai($r->review_date) : '—' ?></td>
                                    <td class="text-center">
                                        <?php $fc = $followupCounts[(int) $r->id] ?? 0; ?>
                                        <span class="badge <?= $fc ? 'bg-info-subtle text-info-emphasis' : 'bg-body-secondary text-body-secondary' ?>"><?= $fc ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'ดูรายละเอียด', 'data-pjax' => '0']) ?>
                                            <?php if (!$showDeleted): ?>
                                                <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $r->id, 'title' => 'แก้ไขการทบทวน'], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'แก้ไข']) ?>
                                                <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $r->id], [
                                                    'class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบ', 'data-pjax' => '0',
                                                    'data' => ['method' => 'post', 'confirm' => 'ลบรายการนี้? (กู้คืนได้ภายหลัง)'],
                                                ]) ?>
                                            <?php else: ?>
                                                <?= Html::a('<i class="bi bi-arrow-counterclockwise"></i> กู้คืน', ['restore', 'id' => $r->id], [
                                                    'class' => 'btn btn-sm btn-outline-success', 'data-pjax' => '0',
                                                    'data' => ['method' => 'post'],
                                                ]) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile -->
                <ul class="list-group list-group-flush d-lg-none">
                    <?php foreach ($reviews as $r): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <?= Html::a(Html::encode($r->title ?: '(ไม่มีหัวข้อ)'), ['view', 'id' => $r->id], ['class' => 'fw-semibold text-decoration-none']) ?>
                                <?php $fc = $followupCounts[(int) $r->id] ?? 0; ?>
                                <span class="badge <?= $fc ? 'bg-info-subtle text-info-emphasis' : 'bg-body-secondary text-body-secondary' ?> flex-shrink-0">ติดตาม <?= $fc ?></span>
                            </div>
                            <div class="text-body-secondary small mt-1">
                                <?= Html::encode($r->ownerUnit->name ?? '—') ?> · <?= $r->review_date ? \app\components\AppHelper::convertToThai($r->review_date) : '—' ?>
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <?= Html::a('ดู', ['view', 'id' => $r->id], ['class' => 'btn btn-sm btn-primary', 'data-pjax' => '0']) ?>
                                <?php if (!$showDeleted): ?>
                                    <?= Html::a('แก้ไข', ['update', 'id' => $r->id, 'title' => 'แก้ไขการทบทวน'], ['class' => 'btn btn-sm btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                    <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-danger ms-auto', 'data-pjax' => '0', 'aria-label' => 'ลบ', 'data' => ['method' => 'post', 'confirm' => 'ลบรายการนี้?']]) ?>
                                <?php else: ?>
                                    <?= Html::a('กู้คืน', ['restore', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-success', 'data-pjax' => '0', 'data' => ['method' => 'post']]) ?>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if ($reviews): ?>
            <div class="card-footer bg-body-tertiary">
                <?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
            </div>
        <?php endif; ?>
        <?php Pjax::end(); ?>
    </div>
</div>
