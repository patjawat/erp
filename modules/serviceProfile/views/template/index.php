<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\serviceProfile\models\ServiceProfileTemplate;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Template Service Profile';
$statusLabels = ServiceProfileTemplate::statusLabels();
$badgeClasses = ['draft' => 'bg-secondary-subtle text-secondary-emphasis', 'active' => 'bg-success-subtle text-success-emphasis', 'retired' => 'bg-body-secondary text-body-secondary'];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= $this->render('@app/modules/serviceProfile/menu', ['active' => 'template']) ?><?php $this->endBlock(); ?>

<form method="get" class="card bg-body border shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold" for="sp-owner-filter">หน่วยงาน / ทีมประสาน</label>
                <?= Html::dropDownList('owner_id', $ownerId, $ownerOptions, ['id' => 'sp-owner-filter', 'class' => 'form-select', 'prompt' => 'ทั้งหมด']) ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label fw-semibold" for="sp-status-filter">สถานะ</label>
                <?= Html::dropDownList('status', $status, $statusLabels, ['id' => 'sp-status-filter', 'class' => 'form-select', 'prompt' => 'ทุกสถานะ']) ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 d-flex gap-2">
                <?= Html::submitButton('<i class="bi bi-search me-1" aria-hidden="true"></i> ค้นหา', ['class' => 'btn btn-primary flex-grow-1']) ?>
                <?= Html::a('ล้างค่า', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>
</form>

<?php Pjax::begin(['id' => 'sp-template-list', 'enablePushState' => false]); ?>
<section class="card bg-body border shadow-sm" aria-labelledby="sp-template-heading">
    <div class="card-header bg-body-tertiary d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 py-3">
        <div>
            <h2 id="sp-template-heading" class="h5 fw-semibold mb-1">รายการ Template</h2>
            <p class="small text-body-secondary mb-0">โครงสร้างหัวข้อแยกตามหน่วยงานและ Revision</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('<i class="bi bi-stars me-1" aria-hidden="true"></i> สร้างด้วย AI', ['ai-generate'], ['class' => 'btn btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1" aria-hidden="true"></i> สร้าง Template', ['create'], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-lg']) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="d-none d-lg-block">
            <table class="table align-middle mb-0">
                <thead class="table-group-divider"><tr><th>หน่วยงาน / Template</th><th class="text-end">ปีเริ่มใช้</th><th class="text-center">Revision</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
                <tbody>
                <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr>
                        <td><div class="fw-semibold"><?= Html::encode($model->owner_name_snapshot) ?></div><div class="small text-body-secondary"><?= Html::encode($model->name) ?></div></td>
                        <td class="text-end font-monospace"><?= (int) $model->effective_fiscal_year ?></td>
                        <td class="text-center font-monospace"><?= (int) $model->revision_no ?></td>
                        <td><span class="badge <?= $badgeClasses[$model->lifecycle_status] ?? 'bg-body-secondary text-body-secondary' ?>"><?= Html::encode($statusLabels[$model->lifecycle_status] ?? $model->lifecycle_status) ?></span></td>
                        <td class="text-end"><div class="d-inline-flex gap-1">
                            <?= Html::a('<i class="bi bi-list-check" aria-hidden="true"></i>', ['structure', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'จัดการหัวข้อ', 'aria-label' => 'จัดการหัวข้อ']) ?>
                            <?php if ($model->lifecycle_status === ServiceProfileTemplate::STATUS_DRAFT): ?><?= Html::a('<i class="bi bi-pencil" aria-hidden="true"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data-size' => 'modal-lg', 'title' => 'แก้ไข', 'aria-label' => 'แก้ไข Template']) ?><?= Html::a('<i class="bi bi-trash" aria-hidden="true"></i>', ['delete', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบ Template', 'aria-label' => 'ลบ Template '.$model->name, 'data-method' => 'post', 'data-confirm' => 'ยืนยันลบ Template ฉบับร่างนี้? การดำเนินการนี้ไม่สามารถย้อนกลับได้']) ?><?php endif; ?>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($dataProvider->getCount() === 0): ?><tr><td colspan="5" class="text-center py-5"><div class="fw-semibold">ยังไม่มี Template</div><div class="small text-body-secondary mt-1">สร้าง Template แรกให้หน่วยงานเพื่อเริ่มกำหนดหัวข้อ</div></td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <ul class="list-group list-group-flush d-lg-none" role="list">
            <?php foreach ($dataProvider->getModels() as $model): ?><li class="list-group-item p-3"><div class="d-flex flex-column gap-3"><div class="min-w-0"><div class="fw-semibold text-break"><?= Html::encode($model->owner_name_snapshot) ?></div><div class="small text-body-secondary"><?= Html::encode($model->name) ?> · ปี <?= (int) $model->effective_fiscal_year ?> · Revision <?= (int) $model->revision_no ?></div><span class="badge mt-2 <?= $badgeClasses[$model->lifecycle_status] ?? 'bg-body-secondary text-body-secondary' ?>"><?= Html::encode($statusLabels[$model->lifecycle_status] ?? $model->lifecycle_status) ?></span></div><div class="d-flex flex-wrap gap-2"><?= Html::a('จัดการหัวข้อ', ['structure', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?><?php if ($model->lifecycle_status === ServiceProfileTemplate::STATUS_DRAFT): ?><?= Html::a('แก้ไข Template', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary open-modal', 'data-size' => 'modal-lg']) ?><?= Html::a('ลบ Template', ['delete', 'id' => $model->id], ['class' => 'btn btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันลบ Template ฉบับร่างนี้? การดำเนินการนี้ไม่สามารถย้อนกลับได้']) ?><?php endif; ?></div></div></li><?php endforeach; ?>
        </ul>
    </div>
    <div class="card-footer bg-body border-top py-3 px-3 px-md-4"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
</section>
<?php Pjax::end(); ?>
