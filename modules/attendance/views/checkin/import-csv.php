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
    <p class="text-muted mb-0">ไฟล์ UTF-8 ไม่เกิน 5 MB / 5,000 แถว · มีข้อผิดพลาดจะย้อนกลับทั้งไฟล์ · รายการซ้ำจะข้าม และทุกรายการใหม่รออนุมัติ</p>
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
        <p class="text-body-secondary">ข้ามรายการซ้ำ <?= (int)($skipped ?? 0) ?> รายการ</p>
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
    <div class="card-header bg-body-tertiary py-2 px-3">
        <h6 class="mb-0 small fw-normal text-body-secondary">ตัวอย่าง CSV (เปลี่ยนรหัสพนักงานและวันเวลาให้ตรงกับข้อมูลของคุณ)</h6>
    </div>
    <div class="card-body p-3">
        <p>ใช้หัวคอลัมน์ emp_id สำหรับรหัสพนักงานในระบบ หรือ cid สำหรับเลขบัตรประชาชน ห้ามใช้ทั้งสองคอลัมน์ในไฟล์เดียวกัน ส่วน roster_item_id คือรายการเวรที่ประกาศใช้ เว้นว่างเพื่อให้ผู้ตรวจสอบจับคู่ภายหลังได้</p><p>คอลัมน์เสริม: method, lat, lng, out_of_location_reason ข้อมูลนำเข้าจะระบุว่ายังไม่ได้ตรวจ GPS แม้มีพิกัดจากต้นทาง</p><pre class="mb-0 small bg-body-tertiary p-3 rounded">emp_id,checkin_at,check_type,roster_item_id
1,2026-02-24 08:30:00,in,
1,2026-02-24 16:30:00,out,</pre>
    </div>
</div>
