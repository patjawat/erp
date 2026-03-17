<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var int $month */
/** @var string|null $message */
/** @var bool $tableExists */

$this->title = 'ประมวลผลค่าเสื่อมรายเดือน';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อม', 'url' => ['/am/depreciation/monthly-processing']];
$this->params['breadcrumbs'][] = $this->title;

$thaiMonths = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
];
$years = range(date('Y') + 1, date('Y') - 5);
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <i data-lucide="trending-down"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0">คำนวณค่าเสื่อมรายเดือนสำหรับครุภัณฑ์ทั้งหมด (วิธีเส้นตรง มูลค่าซาก 1 บาท)</p>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid px-2 px-md-3 pb-3">
    <?php if (!$tableExists): ?>
        <div class="alert alert-warning">
            <strong>ตารางยังไม่มี</strong> กรุณารัน migration สำหรับ am_asset_depreciation_monthly
        </div>
    <?php else: ?>
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success d-flex align-items-start gap-2">
                <div class="flex-grow-1">
                    <?= Yii::$app->session->getFlash('success') ?>
                    <div class="mt-2">
                        <?= Html::a('<i class="fa-solid fa-file-lines me-1"></i> ดูหรือพิมพ์รายงานสำหรับเดือน ' . $thaiMonths[$month] . ' ' . ($fiscalYear + 543), ['/am/report/monthly-depreciation', 'fiscal_year' => $fiscalYear, 'month' => $month], ['class' => 'btn btn-sm btn-success']) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom">
                <h6 class="text-uppercase text-secondary mb-0">เลือกเดือนที่ต้องการประมวลผล</h6>
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'monthly-depreciation-form',
                    'action' => ['/am/depreciation/monthly-processing'],
                    'method' => 'post',
                    'options' => ['class' => 'row g-3'],
                ]); ?>
                <input type="hidden" name="action" value="run">
                <div class="col-12 col-md-4">
                    <label class="form-label">ปี (พ.ศ.)</label>
                    <select name="fiscal_year" class="form-select" required>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y ?>" <?= (int) $fiscalYear === $y ? 'selected' : '' ?>><?= $y + 543 ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">เดือน</label>
                    <select name="month" class="form-select" required>
                        <?php foreach ($thaiMonths as $m => $label): ?>
                            <option value="<?= $m ?>" <?= (int) $month === $m ? 'selected' : '' ?>><?= $label ?> (<?= $m ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                    <?= Html::submitButton('<i class="fa-solid fa-play me-1"></i> Run Monthly Depreciation', ['class' => 'btn btn-primary', 'name' => 'run', 'value' => '1']) ?>
                    <?= Html::a('<i class="fa-solid fa-file-pdf me-1"></i> พิมพ์รายงานรายเดือน', ['/am/report/monthly-depreciation', 'fiscal_year' => $fiscalYear, 'month' => $month], ['class' => 'btn btn-outline-primary']) ?>
                </div>
                <?php ActiveForm::end(); ?>

                <hr class="my-4">
                <p class="text-muted small mb-2">
                    <strong>Regenerate (ผู้ดูแลระบบ):</strong> หากเดือนนั้นประมวลผลไปแล้ว ระบบจะไม่คำนวณซ้ำ หากต้องการคำนวณใหม่ให้ลบข้อมูลเดิมแล้วรันใหม่ ให้กดปุ่มด้านล่างและยืนยัน
                </p>
                <?php $formRegen = ActiveForm::begin([
                    'action' => ['/am/depreciation/monthly-processing'],
                    'method' => 'post',
                    'options' => ['class' => 'd-inline', 'onsubmit' => "return confirm('ยืนยันว่าต้องการลบข้อมูลค่าเสื่อมรายเดือนของเดือนที่เลือกและประมวลผลใหม่ทั้งหมด?');"],
                ]); ?>
                <input type="hidden" name="action" value="regenerate">
                <input type="hidden" name="force" value="1">
                <input type="hidden" name="fiscal_year" value="<?= (int) $fiscalYear ?>">
                <input type="hidden" name="month" value="<?= (int) $month ?>">
                <?= Html::submitButton('<i class="fa-solid fa-rotate me-1"></i> Regenerate เดือนนี้', ['class' => 'btn btn-outline-warning']) ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
