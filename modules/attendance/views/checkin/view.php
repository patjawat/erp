<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายละเอียดการลงเวลา #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'รายการลงเวลา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> รายการ', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal">ข้อมูลการลงเวลา</h6>
    </div>
    <div class="card-body p-4">
        <table class="table table-borderless align-middle mb-0">
            <tbody class="table-group-divider">
                <tr><th class="text-muted" style="width: 180px;">พนักงาน</th><td><?= Html::encode($model->employee ? $model->employee->fname . ' ' . $model->employee->lname : '-') ?></td></tr>
                <tr><th class="text-muted">วันเวลาที่ลงเวลา</th><td><?= Yii::$app->formatter->asDatetime($model->checkin_at, 'php:d/m/Y H:i:s') ?></td></tr>
                <tr><th class="text-muted">วิธีลงเวลา</th><td><?= Html::encode($model->getMethodLabel()) ?></td></tr>
                <tr><th class="text-muted">ประเภทการลง</th><td><?= Html::encode($model->getCheckTypeLabel()) ?></td></tr>
                <tr><th class="text-muted">สถานะ</th><td><span class="badge <?= $model->status === 'approved' ? 'text-bg-success' : ($model->status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning text-dark') ?>"><?= Html::encode($model->getStatusLabel()) ?></span></td></tr>
                <?php if ($model->lat !== null && $model->lng !== null): ?>
                <tr><th class="text-muted">พิกัด</th><td><?= Html::encode($model->lat . ', ' . $model->lng) ?></td></tr>
                <?php endif; ?>
                <?php if ($model->location): ?>
                <tr><th class="text-muted">จุดลงเวลา</th><td><?= Html::encode($model->location->name) ?></td></tr>
                <?php endif; ?>
                <?php if ($model->photo_path): ?>
                <tr><th class="text-muted">รูปถ่าย</th><td><a href="<?= Url::to('@web/' . $model->photo_path) ?>" target="_blank" rel="noopener">ดูรูป</a></td></tr>
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
