<?php

use app\components\AppHelper;
use app\components\widgets\DataSummaryWidget;
use app\modules\ha12\models\Ha12MrecAudit;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12MrecAudit[] $audits */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var array<int,array{id:int,name:string}> $units */
/** @var array{unit_id:?int,deleted:int} $filters */

$this->title = 'HA12-PCT · ความสมบูรณ์เวชระเบียน';
$unitOptions = [];
foreach ($units as $u) {
    $unitOptions[$u['id']] = $u['name'];
}
$showDeleted = (int) ($filters['deleted'] ?? 0) === 1;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>กิจกรรม 9 · ความสมบูรณ์ของเวชระเบียน · ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'mrec']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $tone): ?>
        <?php if ($msg = Yii::$app->session->getFlash($flash)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="card border shadow-sm mb-3"><div class="card-body">
        <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold mb-1">ปีงบ</label>
                <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select', 'onchange' => 'this.form.submit()']) ?>
            </div>
            <div class="col-12 col-lg-4">
                <label class="form-label small fw-semibold mb-1">หน่วยงาน</label>
                <?= Html::dropDownList('unit_id', $filters['unit_id'], $unitOptions, ['class' => 'form-select', 'prompt' => 'ทุกหน่วยงาน', 'onchange' => 'this.form.submit()']) ?>
            </div>
            <div class="col-12 col-lg-6 d-flex align-items-center">
                <div class="form-check mb-0">
                    <?= Html::checkbox('deleted', $showDeleted, ['value' => 1, 'class' => 'form-check-input', 'id' => 'flt-del', 'onchange' => 'this.form.submit()']) ?>
                    <label class="form-check-label small" for="flt-del">แสดงรายการที่ลบ</label>
                </div>
            </div>
        <?= Html::endForm() ?>
    </div></div>

    <div class="d-flex justify-content-end mb-2">
        <?php if (!$showDeleted): ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i> สร้างการตรวจ', ['create', 'fy' => $fiscalYear, 'title' => 'สร้างการตรวจเวชระเบียน'], ['class' => 'btn btn-success rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        <?php endif; ?>
    </div>

    <div class="card border shadow-sm">
        <div class="card-body p-0">
            <?php if (!$audits): ?>
                <div class="text-center py-5"><div class="fw-semibold mb-1"><?= $showDeleted ? 'ไม่มีรายการที่ลบ' : 'ยังไม่มีการตรวจ' ?></div><div class="text-body-secondary small">กด “สร้างการตรวจ” เพื่อเริ่ม</div></div>
            <?php else: ?>
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-tertiary"><tr>
                            <th style="width:44px;" class="text-center">#</th>
                            <th>ช่วง/หน่วยงาน</th>
                            <th style="width:110px;" class="text-end">จำนวนตรวจ</th>
                            <th style="width:120px;" class="text-end">ความสมบูรณ์รวม</th>
                            <th style="width:150px;" class="text-center">จัดการ</th>
                        </tr></thead>
                        <tbody>
                        <?php $i = $dataProvider->pagination->offset; foreach ($audits as $a): $pct = $a->overallPercent(); ?>
                            <tr>
                                <td class="text-center text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= ++$i ?></td>
                                <td>
                                    <?= Html::a(($a->period_start ? AppHelper::convertToThai($a->period_start) . '–' . AppHelper::convertToThai($a->period_end) : 'รอบตรวจ'), ['view', 'id' => $a->id], ['class' => 'fw-semibold text-decoration-none']) ?>
                                    <div class="text-body-secondary small"><?= Html::encode($a->ownerUnit->name ?? '—') ?></div>
                                </td>
                                <td class="text-end" style="font-variant-numeric:tabular-nums;"><?= $a->total_charts !== null ? number_format((int) $a->total_charts) : '—' ?></td>
                                <td class="text-end fw-semibold" style="font-variant-numeric:tabular-nums;"><?= $pct !== null ? number_format($pct, 1) . '%' : '—' ?></td>
                                <td class="text-center"><div class="d-inline-flex gap-1">
                                    <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $a->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'ดู']) ?>
                                    <?php if (!$showDeleted): ?>
                                        <?= Html::a('<i class="bi bi-pencil-square"></i>', ['edit', 'id' => $a->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'กรอก/แก้ไข']) ?>
                                        <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $a->id], ['class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบ', 'data' => ['method' => 'post', 'confirm' => 'ลบรายการนี้?']]) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="bi bi-arrow-counterclockwise"></i>', ['restore', 'id' => $a->id], ['class' => 'btn btn-sm btn-outline-success', 'title' => 'กู้คืน', 'data' => ['method' => 'post']]) ?>
                                    <?php endif; ?>
                                </div></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <ul class="list-group list-group-flush d-lg-none">
                    <?php foreach ($audits as $a): $pct = $a->overallPercent(); ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <?= Html::a(($a->period_start ? AppHelper::convertToThai($a->period_start) . '–' . AppHelper::convertToThai($a->period_end) : 'รอบตรวจ'), ['view', 'id' => $a->id], ['class' => 'fw-semibold text-decoration-none']) ?>
                                <span class="badge bg-primary-subtle text-primary-emphasis"><?= $pct !== null ? number_format($pct, 1) . '%' : '—' ?></span>
                            </div>
                            <div class="text-body-secondary small mt-1"><?= Html::encode($a->ownerUnit->name ?? '—') ?></div>
                            <div class="d-flex gap-2 mt-2">
                                <?= Html::a('ดู', ['view', 'id' => $a->id], ['class' => 'btn btn-sm btn-primary']) ?>
                                <?php if (!$showDeleted): ?>
                                    <?= Html::a('กรอก/แก้ไข', ['edit', 'id' => $a->id], ['class' => 'btn btn-sm btn-light']) ?>
                                <?php else: ?>
                                    <?= Html::a('กู้คืน', ['restore', 'id' => $a->id], ['class' => 'btn btn-sm btn-outline-success', 'data' => ['method' => 'post']]) ?>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if ($audits): ?><div class="card-footer bg-body-tertiary"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div><?php endif; ?>
    </div>
</div>
