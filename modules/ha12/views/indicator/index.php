<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\ha12\models\Ha12Indicator;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var Ha12Indicator[] $indicators */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var array<int,array{id:int,name:string}> $units */
/** @var array{unit_id:?int,deleted:int} $filters */

$this->title = 'HA12-PCT · ตัวชี้วัด';
$unitOptions = [];
foreach ($units as $u) {
    $unitOptions[$u['id']] = $u['name'];
}
$showDeleted = (int) ($filters['deleted'] ?? 0) === 1;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>กิจกรรม 12 · ติดตามตัวชี้วัดสำคัญ · ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'indicator']) ?></div>

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
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มตัวชี้วัด', ['create', 'fy' => $fiscalYear, 'title' => 'เพิ่มตัวชี้วัด'], ['class' => 'btn btn-success rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        <?php endif; ?>
    </div>

    <div class="card border shadow-sm">
        <?php Pjax::begin(['id' => 'ha12-ind-list', 'enablePushState' => false, 'timeout' => 8000]); ?>
        <div class="card-body p-0">
            <?php if (!$indicators): ?>
                <div class="text-center py-5"><div class="fw-semibold mb-1"><?= $showDeleted ? 'ไม่มีรายการที่ลบ' : 'ยังไม่มีตัวชี้วัด' ?></div><div class="text-body-secondary small">กด “เพิ่มตัวชี้วัด” เพื่อเริ่ม</div></div>
            <?php else: ?>
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-tertiary"><tr>
                            <th style="width:44px;" class="text-center">#</th>
                            <th>ตัวชี้วัด</th>
                            <th style="width:16rem;">หน่วยงาน</th>
                            <th style="width:120px;">เป้าหมาย</th>
                            <th style="width:70px;" class="text-center">ระดับ</th>
                            <th style="width:170px;" class="text-center">จัดการ</th>
                        </tr></thead>
                        <tbody>
                        <?php $i = $dataProvider->pagination->offset; foreach ($indicators as $ind): ?>
                            <tr>
                                <td class="text-center text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= ++$i ?></td>
                                <td><?= Html::a(Html::encode($ind->name), ['values', 'id' => $ind->id], ['class' => 'fw-semibold text-decoration-none', 'data-pjax' => '0']) ?></td>
                                <td class="text-body-secondary"><?= Html::encode($ind->ownerUnit->name ?? '—') ?></td>
                                <td class="text-body-secondary"><?= Html::encode(($ind->target ?? '—') . ($ind->unit_label ? ' ' . $ind->unit_label : '')) ?></td>
                                <td class="text-center"><?= $ind->level ? '<span class="badge bg-secondary-subtle text-secondary-emphasis">' . Html::encode($ind->level) . '</span>' : '—' ?></td>
                                <td class="text-center"><div class="d-inline-flex gap-1">
                                    <?= Html::a('<i class="bi bi-table"></i>', ['values', 'id' => $ind->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'ค่ารายเดือน', 'data-pjax' => '0']) ?>
                                    <?php if (!$showDeleted): ?>
                                        <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $ind->id, 'title' => 'แก้ไขตัวชี้วัด'], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'แก้ไข']) ?>
                                        <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $ind->id], ['class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบ', 'data-pjax' => '0', 'data' => ['method' => 'post', 'confirm' => 'ลบตัวชี้วัดนี้?']]) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="bi bi-arrow-counterclockwise"></i> กู้คืน', ['restore', 'id' => $ind->id], ['class' => 'btn btn-sm btn-outline-success', 'data-pjax' => '0', 'data' => ['method' => 'post']]) ?>
                                    <?php endif; ?>
                                </div></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <ul class="list-group list-group-flush d-lg-none">
                    <?php foreach ($indicators as $ind): ?>
                        <li class="list-group-item">
                            <?= Html::a(Html::encode($ind->name), ['values', 'id' => $ind->id], ['class' => 'fw-semibold text-decoration-none', 'data-pjax' => '0']) ?>
                            <div class="text-body-secondary small mt-1"><?= Html::encode($ind->ownerUnit->name ?? '—') ?> · เป้า <?= Html::encode($ind->target ?? '—') ?><?= $ind->level ? ' · ระดับ ' . Html::encode($ind->level) : '' ?></div>
                            <div class="d-flex gap-2 mt-2">
                                <?= Html::a('ค่ารายเดือน', ['values', 'id' => $ind->id], ['class' => 'btn btn-sm btn-primary', 'data-pjax' => '0']) ?>
                                <?php if (!$showDeleted): ?>
                                    <?= Html::a('แก้ไข', ['update', 'id' => $ind->id, 'title' => 'แก้ไขตัวชี้วัด'], ['class' => 'btn btn-sm btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                <?php else: ?>
                                    <?= Html::a('กู้คืน', ['restore', 'id' => $ind->id], ['class' => 'btn btn-sm btn-outline-success', 'data-pjax' => '0', 'data' => ['method' => 'post']]) ?>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if ($indicators): ?><div class="card-footer bg-body-tertiary"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div><?php endif; ?>
        <?php Pjax::end(); ?>
    </div>
</div>
