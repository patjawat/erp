<?php

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\services\FinanceLoanImportService;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceLoanImportForm $model */
/** @var array|null $preview */

$this->title = 'นำเข้าทะเบียนเงินยืมจากไฟล์ Excel';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเงินยืม', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ตรวจสอบข้อมูลทีละแถวก่อนบันทึกเข้าฐานข้อมูลจริง<?php $this->endBlock();
?>

<div class="alert alert-info d-flex align-items-start gap-2" role="status">
    <i class="bi bi-info-circle mt-1" aria-hidden="true"></i>
    <div>
        <strong>ใช้ไฟล์ทะเบียนรูปแบบเดิมได้เลย</strong>
        <div class="small">
            ระบบอ่านข้อมูลตั้งแต่แถวที่ 6 ของแท็บที่เลือก แล้วกระจายหนึ่งแถวออกเป็นหัวสัญญา
            บรรทัดประมาณการ และรายการส่งใช้ · ยอดสี่ช่อง (เบี้ยเลี้ยง–ที่พัก–พาหนะ–อื่น ๆ) กลายเป็นบรรทัดประมาณการ
            ถ้าไฟล์กรอกแต่ยอดรวมจะสร้างบรรทัดเดียวให้แทน · รองรับสูงสุด <?= number_format(FinanceLoanImportService::MAX_ROWS) ?> แถว
        </div>
    </div>
</div>

<section class="card border mb-3" aria-labelledby="upload-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="upload-heading">เลือกไฟล์ Excel</h5></div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'class' => 'row g-3 align-items-end']]); ?>
        <div class="col-12 col-lg-5">
            <?= $form->field($model, 'file')->fileInput(['accept' => '.xlsx,.xls']) ?>
            <div class="form-text">รองรับ .xlsx และ .xls ขนาดไม่เกิน 10 MB</div>
        </div>
        <div class="col-6 col-lg-2">
            <?= $form->field($model, 'fiscal_year')->textInput(['inputmode' => 'numeric']) ?>
            <div class="form-text">บันทึกให้ทุกแถวในไฟล์</div>
        </div>
        <div class="col-6 col-lg-3">
            <?= $form->field($model, 'sheet')->textInput(['maxlength' => true, 'placeholder' => 'เว้นว่าง = หาอัตโนมัติ']) ?>
            <div class="form-text">ระบุเมื่อชื่อแท็บไม่ตรงกับปีงบประมาณ</div>
        </div>
        <div class="col-12 col-lg-2 pb-3 d-grid">
            <?= Html::submitButton('<i class="bi bi-shield-check me-1"></i> ตรวจสอบไฟล์', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</section>

<?php if ($preview): ?>
<?php
$displayRows = array_slice($preview['rows'], 0, 100);
$date = fn($value) => $value ? Yii::$app->formatter->asDate($value, 'php:d/m/Y') : '—';
?>
<section class="card border" aria-labelledby="preview-heading">
    <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-1" id="preview-heading">ผลตรวจสอบก่อนนำเข้า</h5>
            <div class="text-body-secondary small">
                อ่านจากแท็บ “<?= Html::encode($preview['sheet']) ?>” ของไฟล์ <?= Html::encode($preview['file_name']) ?>
                · บันทึกเป็นปีงบประมาณ <?= Html::encode($preview['fiscal_year']) ?>
                · พบ <?= number_format(count($preview['rows'])) ?> แถว
            </div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success-subtle text-success-emphasis align-self-center">พร้อมนำเข้า <?= number_format($preview['valid']) ?></span>
            <?php if ($preview['invalid']): ?>
                <span class="badge bg-danger-subtle text-danger-emphasis align-self-center">ต้องตรวจสอบ <?= number_format($preview['invalid']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th>แถว</th><th>เลขที่สัญญา</th><th>ผู้ยืม / รายการ</th>
                <th>ยืม / ครบกำหนด</th><th>สถานะ</th>
                <th class="text-end">ยอดยืม</th><th>บรรทัดประมาณการ</th><th>ส่งใช้</th><th>ผลตรวจ</th>
            </tr></thead>
            <tbody>
            <?php foreach ($displayRows as $item): ?>
                <?php $row = $item['data']; $loan = $row['loan']; ?>
                <tr>
                    <td><?= number_format($row['import_row']) ?></td>
                    <td class="fw-semibold text-nowrap"><?= Html::encode($loan['contract_no']) ?></td>
                    <td>
                        <div><?= Html::encode($loan['borrower_name']) ?></div>
                        <div class="small text-body-secondary"><?= Html::encode(mb_substr((string) $loan['purpose'], 0, 60)) ?></div>
                    </td>
                    <td class="text-nowrap small">
                        <div><?= $date($loan['borrowed_at']) ?></div>
                        <div class="text-body-secondary">ครบ <?= $date($loan['due_at']) ?></div>
                    </td>
                    <td class="small"><?= Html::encode(FinanceLoan::statusOptions()[$loan['status']] ?? $loan['status']) ?></td>
                    <td class="text-end font-monospace text-nowrap"><?= number_format($row['legacy']['approved'], 2) ?></td>
                    <td class="small">
                        <?php if (!$row['items']): ?>
                            <span class="text-body-secondary">—</span>
                        <?php else: ?>
                            <?= count($row['items']) ?> บรรทัด
                            <div class="text-body-secondary"><?= Html::encode(implode(' · ', array_column($row['items'], 'label'))) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="small text-nowrap">
                        <?php if ($row['settlement']): ?>
                            <?= number_format($row['settlement']['voucher_amount'] + $row['settlement']['cash_amount'], 2) ?>
                            <div class="text-body-secondary"><?= $date($row['settlement']['settled_at']) ?></div>
                        <?php else: ?>
                            <span class="text-body-secondary">ยังไม่ส่งใช้</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item['errors']): ?>
                            <span class="badge bg-danger-subtle text-danger-emphasis">ไม่นำเข้า</span>
                            <div class="small text-danger-emphasis mt-1"><?= Html::encode(implode(' · ', $item['errors'])) ?></div>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success-emphasis">พร้อม</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="text-body-secondary small">
            ระบบจะบันทึกเฉพาะแถวที่ผ่านการตรวจสอบ ทั้งหมดอยู่ในธุรกรรมเดียว ถ้ามีแถวใดล้มจะไม่บันทึกอะไรเลย
            <?php if (count($preview['rows']) > 100): ?>
                · แสดงตัวอย่าง 100 แถวแรกจาก <?= number_format(count($preview['rows'])) ?> แถว
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <?= Html::beginForm(['delete-import-preview'], 'post')
                . Html::submitButton('ล้างตัวอย่าง', ['class' => 'btn btn-outline-secondary'])
                . Html::endForm() ?>
            <?= Html::beginForm(['confirm-import'], 'post')
                . Html::hiddenInput('preview_token', $preview['token'])
                . Html::submitButton('<i class="bi bi-database-check me-1"></i> ยืนยันนำเข้า ' . number_format($preview['valid']) . ' ใบยืม', [
                    'class' => 'btn btn-success',
                    'disabled' => !$preview['valid'],
                ])
                . Html::endForm() ?>
        </div>
    </div>
</section>
<?php endif; ?>
