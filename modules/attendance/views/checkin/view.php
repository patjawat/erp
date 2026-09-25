<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายละเอียดการลงเวลา #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'รายการลงเวลา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('action'); ?>
<?php if (\app\modules\attendance\services\AttendanceCorrection::canAmend($model)): ?>
<?= Html::a('แก้ไข / ระบุเวร', ['/attendance/checkin/update', 'id' => $model->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
<?php endif; ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> รายการ', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal">ข้อมูลการลงเวลา</h6>
    </div>
    <div class="card-body p-4">
        <table class="table table-borderless align-middle mb-0">
            <tbody class="table-group-divider">
                <tr><th class="text-body-secondary">บริเวณลงเวลา</th><td><?= $model->is_in_location ? 'ในพื้นที่' : 'นอกพื้นที่' ?></td></tr>
                <?php if ($model->out_of_location_reason): ?><tr><th class="text-body-secondary">เหตุผลนอกพื้นที่</th><td><?= nl2br(Html::encode($model->out_of_location_reason)) ?></td></tr><?php endif; ?>
                <?php $comparison = \app\modules\attendance\services\RosterAttendance::forRecord($model); $shift = $comparison['shift']; ?>
                <tr><th class="text-body-secondary">เวรที่เทียบ</th><td><?= Html::encode($shift ? $shift['name'] . ' · ' . $shift['start'] . ' ถึง ' . $shift['end'] : 'ยังไม่ระบุเวร — รอตรวจสอบ') ?></td></tr>
                <tr><th class="text-body-secondary">สาย / ออกก่อน (นาที)</th><td><?= Html::encode(($comparison['late_minutes'] ?? '-') . ' / ' . ($comparison['early_minutes'] ?? '-')) ?></td></tr>
                <tr><th class="text-muted" style="width: 180px;">พนักงาน</th><td><?= Html::encode($model->employee ? $model->employee->fname . ' ' . $model->employee->lname : '-') ?></td></tr>
                <tr><th class="text-muted">วันเวลาที่ลงเวลา</th><td><?= Yii::$app->formatter->asDatetime($model->checkin_at, 'php:d/m/Y H:i:s') ?></td></tr>
                <tr><th class="text-muted">วิธีลงเวลา</th><td><?= Html::encode($model->getMethodLabel()) ?></td></tr>
                <tr><th class="text-muted">ประเภทการลง</th><td><?= Html::encode($model->getCheckTypeLabel()) ?></td></tr>
                <tr><th class="text-muted">สถานะ</th><td><span class="badge <?= $model->status === 'approved' ? 'text-bg-success' : ($model->status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning text-dark') ?>"><?= Html::encode($model->getStatusLabel()) ?></span></td></tr>
                <?php if ($model->lat !== null && $model->lng !== null): ?>
                <tr><th class="text-muted">พิกัดที่ลงเวลา</th><td><?= Html::a(Html::encode($model->lat . ', ' . $model->lng) . ' <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>', $model->mapUrl(), ['target' => '_blank', 'rel' => 'noopener', 'title' => 'เปิดตำแหน่งจริงบนแผนที่']) ?></td></tr>
                <?php endif; ?>
                <tr><th class="text-muted"><?= $model->is_in_location ? 'จุดลงเวลา' : 'สถานที่' ?></th><td><?= Html::encode($model->locationSummary()) ?></td></tr>
                <?php if ($model->photo_path): ?>
                <tr><th class="text-muted">รูปยืนยันตัวตน</th><td>
                    <a href="<?= Url::to(['/attendance/checkin/photo', 'id' => $model->id]) ?>" target="_blank" rel="noopener">
                        <img src="<?= Url::to(['/attendance/checkin/photo', 'id' => $model->id]) ?>" alt="รูปยืนยันตัวตนตอนลงเวลา" class="img-fluid rounded border" style="max-width: 240px" loading="lazy">
                    </a>
                    <div class="small text-body-secondary mt-1">เก็บ <?= \app\modules\attendance\services\AttendancePhoto::RETENTION_DAYS ?> วัน แล้วลบอัตโนมัติ</div>
                </td></tr>
                <?php endif; ?>
                <?php if ($model->approved_at): ?>
                <tr><th class="text-muted">ผู้อนุมัติ</th><td><?= Html::encode($model->approver ? $model->approver->fname . ' ' . $model->approver->lname : '-') ?></td></tr>
                <tr><th class="text-muted">อนุมัติเมื่อ</th><td><?= Yii::$app->formatter->asDatetime($model->approved_at, 'php:d/m/Y H:i') ?></td></tr>
                <?php if ($model->comment): ?>
                <tr><th class="text-muted">ความเห็น</th><td><?= nl2br(Html::encode($model->comment)) ?></td></tr>
                <?php endif; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->render('_scan_evidence', ['model'=>$model]) ?>
<?php $amendments = (is_array($model->data_json) ? $model->data_json : [])['amendments'] ?? []; ?>
<?php if ($amendments): ?>
<section class="mt-3"><h2 class="h6">ประวัติการแก้ไข</h2><ol class="list-group list-group-numbered">
<?php foreach ($amendments as $change): ?>
<li class="list-group-item"><p class="mb-1"><?= Html::encode(($change['at'] ?? '') . ' · ผู้ใช้ #' . ($change['by'] ?? '') . ' · ' . ($change['reason'] ?? '')) ?></p><p class="small text-body-secondary mb-0"><?= Html::encode(($change['before']['checkin_at'] ?? '') . ' → ' . ($change['after']['checkin_at'] ?? '') . ' · เวร #' . ($change['after']['roster_item_id'] ?? 'ยังไม่ระบุ')) ?></p></li>
<?php endforeach; ?></ol></section>
<?php endif; ?>
