<?php

use app\modules\accounting\services\AccountingChartImportService;
use app\modules\accounting\models\AccountingChartVersion;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'นำเข้าผังบัญชีจาก Excel';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบัญชี', 'url' => ['/accounting/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ผังบัญชี', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ตรวจรหัส ชื่อบัญชี และความแตกต่างก่อนสร้างเวอร์ชันใหม่<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('@app/modules/accounting/menu', ['active' => 'chart']) ?><?php $this->endBlock(); ?>

<div class="alert alert-info d-flex gap-2 align-items-start" role="status">
    <i class="bi bi-info-circle mt-1" aria-hidden="true"></i>
    <div><strong>รูปแบบไฟล์</strong><div class="small">คอลัมน์ A = รหัสบัญชี และคอลัมน์ B = ชื่อบัญชี · ผังโรงพยาบาลรองรับรหัสย่อย เช่น .137.01 และเลือกแท็บ “รหัสบัญชี” ได้ · รองรับสูงสุด <?= number_format(AccountingChartImportService::MAX_ROWS) ?> แถว</div></div>
</div>

<section class="card border mb-3" aria-labelledby="chart-upload-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="chart-upload-heading">เลือกไฟล์และกำหนดเวอร์ชัน</h5></div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'class' => 'row g-3 align-items-end']]); ?>
        <div class="col-12">
            <?= $form->field($model, 'scope')->radioList(AccountingChartVersion::scopeOptions(), [
                'class' => 'd-flex flex-wrap gap-3',
                'itemOptions' => ['class' => 'btn-check'],
                'item' => static function ($index, $label, $name, $checked, $value) {
                    $id = 'chart-scope-' . $value;
                    return Html::radio($name, $checked, ['value' => $value, 'id' => $id, 'class' => 'btn-check'])
                        . Html::label(Html::encode($label), $id, ['class' => 'btn btn-outline-primary']);
                },
            ]) ?>
        </div>
        <div class="col-12 col-lg-4"><?= $form->field($model, 'file')->fileInput(['accept' => '.xlsx,.xls']) ?><div class="form-text">ไฟล์ไม่เกิน 10 MB</div></div>
        <div class="col-6 col-lg-2"><?= $form->field($model, 'fiscal_year')->textInput(['inputmode' => 'numeric']) ?></div>
        <div class="col-6 col-lg-2"><?= $form->field($model, 'version_code')->textInput(['maxlength' => true]) ?></div>
        <div class="col-12 col-lg-4"><?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?></div>
        <div class="col-12 col-lg-4"><?= $form->field($model, 'sheet')->textInput(['maxlength' => true, 'placeholder' => 'เว้นว่าง = ใช้แท็บแรก']) ?></div>
        <div class="col-12 col-lg-3 pb-3 d-grid"><?= Html::submitButton('<i class="bi bi-shield-check me-1" aria-hidden="true"></i>ตรวจสอบไฟล์', ['class' => 'btn btn-primary']) ?></div>
        <?php ActiveForm::end(); ?>
    </div>
</section>

<?php if ($preview): ?>
<?php $labels = ['new' => ['รหัสใหม่', 'bg-primary-subtle text-primary-emphasis'], 'unchanged' => ['ตรงกับฐาน', 'bg-success-subtle text-success-emphasis'], 'changed' => ['ชื่อต่างจากฐาน', 'bg-warning-subtle text-warning-emphasis'], 'invalid' => ['ไม่ผ่าน', 'bg-danger-subtle text-danger-emphasis']]; ?>
<section class="card border" aria-labelledby="chart-preview-heading">
    <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div><h5 class="mb-1" id="chart-preview-heading">ผลตรวจสอบก่อนนำเข้า</h5><div class="small text-body-secondary"><?= Html::encode(AccountingChartVersion::scopeOptions()[$preview['scope']] ?? $preview['scope']) ?> · <?= Html::encode($preview['file_name']) ?> · แท็บ “<?= Html::encode($preview['sheet']) ?>” · <?= number_format(count($preview['rows'])) ?> รายการ</div></div>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($labels as $key => [$label, $class]): ?><span class="badge rounded-pill <?= $class ?>"><?= $label ?> <?= number_format($preview['counts'][$key]) ?></span><?php endforeach; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>แถว</th><th>รหัส</th><th>ชื่อบัญชีในไฟล์</th><th>ชื่อในฐานเดิม</th><th>ผลตรวจ</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($preview['rows'], 0, 150) as $row): ?>
                <?php [$label, $class] = $labels[$row['result']]; ?>
                <tr>
                    <td><?= number_format($row['row']) ?></td>
                    <td class="font-monospace text-nowrap fw-semibold"><?= Html::encode($row['code']) ?></td>
                    <td><?= Html::encode($row['name'] ?: '—') ?></td>
                    <td class="text-body-secondary"><?= Html::encode($row['old_name'] ?: '—') ?></td>
                    <td><span class="badge <?= $class ?>"><?= $label ?></span><?php if ($row['errors']): ?><div class="small text-danger-emphasis mt-1"><?= Html::encode(implode(' · ', $row['errors'])) ?></div><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="small text-body-secondary">การยืนยันจะสร้างเวอร์ชันสถานะ “รอตรวจสอบ” เท่านั้น<?php if (count($preview['rows']) > 150): ?> · แสดง 150 รายการแรก<?php endif; ?></div>
        <div class="d-flex gap-2">
            <?= Html::beginForm(['delete-import-preview'], 'post') . Html::submitButton('ล้างตัวอย่าง', ['class' => 'btn btn-outline-secondary']) . Html::endForm() ?>
            <?= Html::beginForm(['confirm-import'], 'post') . Html::hiddenInput('preview_token', $preview['token']) . Html::submitButton('<i class="bi bi-database-check me-1" aria-hidden="true"></i>สร้างฉบับรอตรวจสอบ ' . number_format($preview['valid']) . ' รหัส', ['class' => 'btn btn-success', 'disabled' => (bool) $preview['invalid']]) . Html::endForm() ?>
        </div>
    </div>
</section>
<?php endif; ?>
