<?php

use app\modules\finance\services\FinanceArImportService;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\base\DynamicModel $model */
/** @var array|null $result */
/** @var int[] $fiscalYears */

$this->title = 'นำเข้าลูกหนี้จาก HIS/เคลม';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ลูกหนี้ค่ารักษา', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'นำเข้า';

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-upload fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'ar']);
$this->endBlock();
?>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-body"><h6 class="mb-0">อัปโหลดไฟล์แม่แบบ</h6></div>
            <div class="card-body">
                <?= Html::beginForm(['import'], 'post', ['enctype' => 'multipart/form-data']) ?>
                <div class="mb-3">
                    <label class="form-label small mb-1">ปีงบประมาณ</label>
                    <select name="fiscal_year" class="form-select form-select-sm" style="max-width:12rem">
                        <?php foreach ($fiscalYears as $y): ?><option value="<?= $y ?>" <?= (int) $model->fiscal_year === $y ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small mb-1">แหล่งข้อมูล (เช่น HIS, e-Claim, NHSO)</label>
                    <?= Html::textInput('source_label', $model->source_label, ['class' => 'form-control form-control-sm', 'style' => 'max-width:20rem', 'placeholder' => 'ไม่บังคับ']) ?>
                </div>
                <div class="mb-3">
                    <label class="form-label small mb-1">ไฟล์ Excel (.xlsx)</label>
                    <?= Html::fileInput('file', null, ['class' => 'form-control form-control-sm', 'accept' => '.xlsx,.xls']) ?>
                    <?php if ($model->hasErrors('file')): ?>
                        <div class="text-danger small mt-1"><?= Html::encode(implode(' ', $model->getErrors('file'))) ?></div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i>นำเข้า</button>
                <a href="<?= Url::to(['template']) ?>" class="btn btn-outline-primary"><i class="bi bi-file-earmark-excel me-1"></i>ดาวน์โหลดแม่แบบ</a>
                <?= Html::endForm() ?>
            </div>
        </div>

        <?php if ($result !== null): ?>
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-body"><h6 class="mb-0">ผลการนำเข้า</h6></div>
                <div class="card-body">
                    <p class="mb-2">นำเข้าสำเร็จ <strong class="text-success-emphasis"><?= (int) $result['imported'] ?></strong> รายการ<?= $result['batch'] ? ' · ยอดรวม ' . number_format((float) $result['batch']->total_amount, 2) . ' บาท' : '' ?></p>
                    <?php if (!empty($result['errors'])): ?>
                        <div class="alert alert-warning mb-0">
                            <div class="fw-semibold mb-1">ข้าม/ผิดพลาด <?= count($result['errors']) ?> รายการ:</div>
                            <ul class="mb-0 small"><?php foreach (array_slice($result['errors'], 0, 30) as $e): ?><li><?= Html::encode($e) ?></li><?php endforeach; ?></ul>
                        </div>
                    <?php endif; ?>
                    <a href="<?= Url::to(['invoices', 'fiscal_year' => $model->fiscal_year]) ?>" class="btn btn-sm btn-outline-primary mt-2">ดูทะเบียนลูกหนี้</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-body"><h6 class="mb-0">รูปแบบแม่แบบ</h6></div>
            <div class="card-body">
                <p class="small text-body-secondary">แถวแรกเป็นหัวตาราง ข้อมูลเริ่มแถวที่ 2 คอลัมน์ตามลำดับ:</p>
                <ol class="small mb-3">
                    <?php foreach (FinanceArImportService::HEADERS as $i => $h): ?>
                        <li><strong><?= chr(65 + $i) ?></strong> — <?= Html::encode($h) ?></li>
                    <?php endforeach; ?>
                </ol>
                <p class="small text-body-secondary mb-0">
                    <i class="bi bi-info-circle me-1"></i>รหัสสิทธิต้องตรงกับที่ระบบมี (ดูแท็บ "รหัสสิทธิ" ในไฟล์แม่แบบ) — วันที่รับได้ทั้งรูปแบบ Excel และ วว/ดด/พ.ศ.
                </p>
            </div>
        </div>
    </div>
</div>
