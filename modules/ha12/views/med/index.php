<?php

use app\components\AppHelper;
use app\components\widgets\DataSummaryWidget;
use app\modules\ha12\models\Ha12MedReport;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Ha12MedReport[] $reports */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var array<int,array{id:int,name:string}> $units */
/** @var array{unit_id:?int,deleted:int} $filters */

$this->title = 'HA12-PCT · ความคลาดเคลื่อนทางยา';
$unitOptions = [];
foreach ($units as $u) {
    $unitOptions[$u['id']] = $u['name'];
}
$showDeleted = (int) ($filters['deleted'] ?? 0) === 1;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>กิจกรรม 7 · ความคลาดเคลื่อนทางยา · ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'med']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $tone): ?>
        <?php if ($msg = Yii::$app->session->getFlash($flash)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="card border shadow-sm mb-3">
        <div class="card-body">
            <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
                <div class="col-6 col-lg-2">
                    <label class="form-label small fw-semibold mb-1">ปีงบ</label>
                    <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select', 'onchange' => 'this.form.submit()']) ?>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label small fw-semibold mb-1">หน่วยงาน</label>
                    <?= Html::dropDownList('unit_id', $filters['unit_id'], $unitOptions, ['class' => 'form-select', 'prompt' => 'ทุกหน่วยงาน', 'onchange' => 'this.form.submit()']) ?>
                </div>
                <div class="col-12 col-lg-6 d-flex align-items-center gap-3">
                    <div class="form-check mb-0">
                        <?= Html::checkbox('deleted', $showDeleted, ['value' => 1, 'class' => 'form-check-input', 'id' => 'flt-del', 'onchange' => 'this.form.submit()']) ?>
                        <label class="form-check-label small" for="flt-del">แสดงรายการที่ลบ</label>
                    </div>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-2">
        <?php if (!$showDeleted): ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i> สร้างรายงาน', ['create', 'fy' => $fiscalYear, 'title' => 'สร้างรายงานยา'], ['class' => 'btn btn-success rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        <?php endif; ?>
    </div>

    <div class="card border shadow-sm">
        <div class="card-body p-0">
            <?php if (!$reports): ?>
                <div class="text-center py-5">
                    <div class="fw-semibold mb-1"><?= $showDeleted ? 'ไม่มีรายการที่ลบ' : 'ยังไม่มีรายงานยา' ?></div>
                    <div class="text-body-secondary small">กด “สร้างรายงาน” เพื่อเริ่มบันทึกความคลาดเคลื่อนทางยา</div>
                </div>
            <?php else: ?>
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th style="width:44px;" class="text-center">#</th>
                                <th style="width:15rem;">ช่วงข้อมูล</th>
                                <th>หน่วยงาน</th>
                                <th style="width:110px;" class="text-end">รวมทุกหัวข้อ</th>
                                <th style="width:110px;" class="text-end">วันที่ทบทวน</th>
                                <th style="width:150px;" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = $dataProvider->pagination->offset; ?>
                            <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td class="text-center text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= ++$i ?></td>
                                    <td style="font-variant-numeric:tabular-nums;">
                                        <?= Html::a(AppHelper::convertToThai($r->period_start) . ' – ' . AppHelper::convertToThai($r->period_end), ['view', 'id' => $r->id], ['class' => 'fw-semibold text-decoration-none']) ?>
                                    </td>
                                    <td class="text-body-secondary"><?= Html::encode($r->ownerUnit->name ?? '—') ?></td>
                                    <td class="text-end fw-semibold" style="font-variant-numeric:tabular-nums;"><?= number_format($r->grandTotal()) ?></td>
                                    <td class="text-end text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= $r->review_date ? AppHelper::convertToThai($r->review_date) : '—' ?></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'ดู']) ?>
                                            <?php if (!$showDeleted): ?>
                                                <?= Html::a('<i class="bi bi-pencil-square"></i>', ['edit', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'กรอก/แก้ไข']) ?>
                                                <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบ', 'data' => ['method' => 'post', 'confirm' => 'ลบรายงานนี้? (กู้คืนได้)']]) ?>
                                            <?php else: ?>
                                                <?= Html::a('<i class="bi bi-arrow-counterclockwise"></i> กู้คืน', ['restore', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-success', 'data' => ['method' => 'post']]) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <ul class="list-group list-group-flush d-lg-none">
                    <?php foreach ($reports as $r): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <?= Html::a(AppHelper::convertToThai($r->period_start) . ' – ' . AppHelper::convertToThai($r->period_end), ['view', 'id' => $r->id], ['class' => 'fw-semibold text-decoration-none']) ?>
                                <span class="badge bg-primary-subtle text-primary-emphasis">รวม <?= number_format($r->grandTotal()) ?></span>
                            </div>
                            <div class="text-body-secondary small mt-1"><?= Html::encode($r->ownerUnit->name ?? '—') ?></div>
                            <div class="d-flex gap-2 mt-2">
                                <?= Html::a('ดู', ['view', 'id' => $r->id], ['class' => 'btn btn-sm btn-primary']) ?>
                                <?php if (!$showDeleted): ?>
                                    <?= Html::a('กรอก/แก้ไข', ['edit', 'id' => $r->id], ['class' => 'btn btn-sm btn-light']) ?>
                                    <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-danger ms-auto', 'aria-label' => 'ลบ', 'data' => ['method' => 'post', 'confirm' => 'ลบรายงานนี้?']]) ?>
                                <?php else: ?>
                                    <?= Html::a('กู้คืน', ['restore', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-success', 'data' => ['method' => 'post']]) ?>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if ($reports): ?>
            <div class="card-footer bg-body-tertiary"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
        <?php endif; ?>
    </div>
</div>
