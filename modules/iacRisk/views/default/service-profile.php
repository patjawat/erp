<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\serviceProfile\models\ServiceProfile;
use yii\helpers\Html;

$this->title = 'Service Profile';
$labels = ServiceProfile::statusLabels();
$badges = [
    'draft' => 'bg-secondary-subtle text-secondary-emphasis',
    'returned' => 'bg-danger-subtle text-danger-emphasis',
    'review_pending' => 'bg-info-subtle text-info-emphasis',
    'approval_pending' => 'bg-warning-subtle text-warning-emphasis',
    'acknowledgement_pending' => 'bg-warning-subtle text-warning-emphasis',
    'active' => 'bg-success-subtle text-success-emphasis',
    'retired' => 'bg-body-secondary text-body-secondary',
    'cancelled' => 'bg-danger-subtle text-danger-emphasis',
];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ข้อมูลที่หน่วยงานจัดทำไว้ สำหรับใช้เชื่อมกระบวนงานและ CSA<?php $this->endBlock(); ?>

<?= $this->render('_context', ['context' => $context]) ?>
<div class="mb-3"><?= $this->render('@app/modules/iacRisk/menu', ['active' => 'service-profile', 'context' => $context]) ?></div>

<section class="card bg-body border shadow-sm" aria-labelledby="iac-sp-list-heading">
    <div class="card-header bg-body-tertiary border-bottom d-flex flex-column flex-sm-row justify-content-between gap-2 py-3">
        <div>
            <h2 id="iac-sp-list-heading" class="h5 fw-semibold mb-1">Service Profile ปีงบประมาณ <?= (int) $fiscalYear ?></h2>
            <p class="small text-body-secondary mb-0">แสดงฉบับร่าง ฉบับที่อยู่ระหว่างรับรอง และฉบับปัจจุบันของหน่วยงานที่เลือก</p>
        </div>
        <span class="badge bg-primary-subtle text-primary-emphasis align-self-start"><?= (int) $dataProvider->getTotalCount() ?> รายการ</span>
    </div>
    <div class="card-body p-0">
        <div class="d-none d-lg-block">
            <table class="table align-middle mb-0">
                <thead><tr><th>หน่วยงาน/ทีมประสาน</th><th class="text-end">ปี</th><th class="text-center">Revision</th><th>สถานะ</th><th>แก้ไขล่าสุด</th><th class="text-end">จัดการ</th></tr></thead>
                <tbody>
                <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr>
                        <td class="fw-semibold"><?= Html::encode($model->owner_name_snapshot) ?></td>
                        <td class="text-end font-monospace"><?= (int) $model->fiscal_year ?></td>
                        <td class="text-center font-monospace"><?= (int) $model->revision_no ?></td>
                        <td><span class="badge <?= $badges[$model->status] ?? 'bg-body-secondary text-body-secondary' ?>"><?= Html::encode($labels[$model->status] ?? $model->status) ?></span></td>
                        <td><span class="small text-body-secondary"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></span></td>
                        <td class="text-end"><?= Html::a('เปิดข้อมูล', ['/service-profile/default/view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($dataProvider->getCount() === 0): ?>
                    <tr><td colspan="6" class="text-center py-5"><div class="fw-semibold">ไม่พบ Service Profile ในขอบเขตที่เลือก</div><div class="small text-body-secondary mt-1">ตรวจปีงบประมาณและหน่วยงาน หรือจัดทำข้อมูลในระบบ Service Profile ก่อน</div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <ul class="list-group list-group-flush d-lg-none" role="list">
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <li class="list-group-item bg-body p-3">
                <div class="d-flex justify-content-between gap-3">
                    <div><div class="fw-semibold"><?= Html::encode($model->owner_name_snapshot) ?></div><div class="small text-body-secondary">ปี <?= (int) $model->fiscal_year ?> · Revision <?= (int) $model->revision_no ?></div><span class="badge mt-2 <?= $badges[$model->status] ?? 'bg-body-secondary text-body-secondary' ?>"><?= Html::encode($labels[$model->status] ?? $model->status) ?></span></div>
                    <?= Html::a('เปิด', ['/service-profile/default/view', 'id' => $model->id], ['class' => 'btn btn-outline-primary align-self-start', 'target' => '_blank', 'rel' => 'noopener']) ?>
                </div>
            </li>
        <?php endforeach; ?>
        <?php if ($dataProvider->getCount() === 0): ?><li class="list-group-item bg-body text-center py-5 text-body-secondary">ไม่พบ Service Profile ในขอบเขตที่เลือก</li><?php endif; ?>
        </ul>
    </div>
    <div class="card-footer bg-body border-top py-3 px-3 px-md-4"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
</section>
