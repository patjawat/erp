<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'จุดลงเวลา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> รายการ', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal"><?= Html::encode($model->name) ?></h6>
    </div>
    <div class="card-body p-4">
        <table class="table table-borderless align-middle mb-0">
            <tbody class="table-group-divider">
                <tr><th class="text-muted" style="width: 160px;">ชื่อจุด</th><td><?= Html::encode($model->name) ?></td></tr>
                <tr><th class="text-muted">Latitude</th><td><?= Html::encode($model->lat ?? '-') ?></td></tr>
                <tr><th class="text-muted">Longitude</th><td><?= Html::encode($model->lng ?? '-') ?></td></tr>
                <tr><th class="text-muted">รัศมี (เมตร)</th><td><?= (int)$model->radius_m ?></td></tr>
                <tr><th class="text-muted">ค่า QR</th><td><code><?= Html::encode($model->qr_token ?? '-') ?></code></td></tr>
                <tr><th class="text-muted">สถานะ</th><td><?= $model->active ? 'เปิดใช้งาน' : 'ปิด' ?></td></tr>
            </tbody>
        </table>
        <?php if ($model->qr_token): ?>
        <p class="text-muted small mt-3 mb-0">สร้าง QR Code โดยใช้ข้อความ: <code><?= Html::encode($model->qr_token) ?></code> แล้วแปะที่จุดลงเวลา</p>
        <?php endif; ?>
    </div>
</div>
