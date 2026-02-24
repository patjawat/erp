<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'นำเข้าข้อมูลลงเวลาจาก CSV';
$this->params['breadcrumbs'][] = ['label' => 'รายการลงเวลา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-file-earmark-spreadsheet fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">รูปแบบ: emp_id หรือเลขบัตรประชาชน, วันเวลา (Y-m-d H:i:s), method (qrcode/photo/manual), lat, lng, out_of_location_reason</p>
</div>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <?php $form = ActiveForm::begin([
            'action' => ['import-csv'],
            'method' => 'post',
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">ไฟล์ CSV <span class="text-danger">*</span></label>
                <input type="file" name="csv_file" class="form-control" accept=".csv" required>
            </div>
            <div class="col-12 col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i> นำเข้า</button>
                <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary ms-2">ย้อนกลับ</a>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php if ($saved !== null): ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <p class="text-success mb-2"><strong>นำเข้าสำเร็จ <?= (int)$saved ?> รายการ</strong><?= isset($lineNo) ? ' (จาก ' . $lineNo . ' แถว)' : '' ?></p>
        <?php if (!empty($errors)): ?>
        <p class="text-danger small mb-1">ข้อผิดพลาด:</p>
        <ul class="small text-danger mb-0">
            <?php foreach (array_slice($errors, 0, 20) as $e): ?>
            <li><?= Html::encode($e) ?></li>
            <?php endforeach; ?>
            <?php if (count($errors) > 20): ?>
            <li>... และอีก <?= count($errors) - 20 ?> รายการ</li>
            <?php endif; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3 mt-3">
    <div class="card-header bg-light py-2 px-3">
        <h6 class="mb-0 small fw-normal text-muted">ตัวอย่าง CSV</h6>
    </div>
    <div class="card-body p-3">
        <pre class="mb-0 small bg-light p-3 rounded">emp_id,checkin_at,method,lat,lng,out_of_location_reason
1,2026-02-24 08:30:00,manual,13.7563,100.5018,
2,2026-02-24 08:45:00,qrcode,,,</pre>
    </div>
</div>
