<?php

use app\modules\accounting\models\AccountingChartMapping;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->title = 'ตรวจการจับคู่ผังบัญชี';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบัญชี', 'url' => ['/accounting/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ผังบัญชี', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-link-45deg" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ปีงบประมาณ <?= Html::encode($model->fiscal_year) ?> · รหัสตรงกันยืนยันอัตโนมัติ ส่วนคำแนะนำต้องให้ฝ่ายบัญชีตรวจรับ<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= Html::a('กลับผังโรงพยาบาล', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?><?php $this->endBlock(); ?>

<?php
$confirmed = $readiness['confirmed'];
$suggested = $readiness['suggested'];
$rejected = $readiness['rejected'];
?>
<div class="alert <?= $readiness['ready'] ? 'alert-success' : 'alert-warning' ?>" role="status">
    <strong><?= $readiness['ready'] ? 'พร้อมเปิดใช้ผังโรงพยาบาล' : 'ยังไม่พร้อมเปิดใช้ผังโรงพยาบาล' ?></strong>
    — ตรวจแล้ว <?= number_format($confirmed) ?> จาก <?= number_format($readiness['total']) ?> รหัสรายได้และค่าใช้จ่าย
    <?php if ($standardVersion): ?>· ผังมาตรฐาน <?= Html::encode($standardVersion->version_code) ?><?php endif; ?>
</div>
<div class="d-flex flex-wrap gap-2 mb-3" aria-label="สรุปผลการจับคู่">
    <span class="badge rounded-pill bg-success-subtle text-success-emphasis">ยืนยันแล้ว <?= number_format($confirmed) ?></span>
    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">รอตรวจ <?= number_format($suggested) ?></span>
    <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis">ปฏิเสธ <?= number_format($rejected) ?></span>
    <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">ยังไม่จับคู่ <?= number_format($readiness['unmapped']) ?></span>
</div>

<?php if (!$standardVersion): ?>
<div class="alert alert-warning" role="alert">ยังไม่มีผังบัญชีมาตรฐานของปีนี้ กรุณานำเข้าผังมาตรฐานก่อนดำเนินการจับคู่</div>
<?php elseif (Yii::$app->user->can('accountingChartManage')): ?>
<section class="card border mb-3" aria-labelledby="manual-mapping-heading">
    <div class="card-body">
        <h5 id="manual-mapping-heading">ตัดสินรายการด้วยตนเอง</h5>
        <p class="text-body-secondary small">เลือกบัญชีโรงพยาบาล แล้วจับคู่กับมาตรฐาน หรือระบุว่าเป็นบัญชีเฉพาะโรงพยาบาล หากเลือกบัญชีที่เคยตัดสินแล้ว ระบบจะเปลี่ยนผลเดิม</p>
        <?= Html::beginForm(['map-manually', 'id' => $model->id], 'post', ['id' => 'manual-mapping-form']) ?>
        <div class="row g-3 align-items-end">
            <div class="col-lg-5"><label class="form-label" for="hospital-account-select">บัญชีโรงพยาบาล</label>
                <?= Select2::widget(['name' => 'hospital_account_id', 'data' => ArrayHelper::map(array_merge(array_map(static fn($m) => $m->hospitalAccount, $mappings), $unmapped), 'id', static fn($a) => $a->code . ' — ' . $a->name), 'options' => ['id' => 'hospital-account-select', 'placeholder' => 'ค้นหารหัสหรือชื่อบัญชี'], 'pluginOptions' => ['allowClear' => true, 'width' => '100%']]) ?>
            </div>
            <div class="col-lg-5"><label class="form-label" for="standard-account-select">บัญชีมาตรฐาน</label>
                <?= Select2::widget(['name' => 'standard_account_id', 'data' => ArrayHelper::map($standardAccounts, 'id', static fn($a) => $a->code . ' — ' . $a->name), 'options' => ['id' => 'standard-account-select', 'placeholder' => 'ค้นหารหัสหรือชื่อบัญชี'], 'pluginOptions' => ['allowClear' => true, 'width' => '100%']]) ?>
            </div>
            <div class="col-lg-2"><?= Html::submitButton('ยืนยันการจับคู่', ['class' => 'btn btn-primary w-100']) ?></div>
            <div class="col-12"><label class="form-label" for="mapping-note">เหตุผล/หมายเหตุ</label><?= Html::textInput('note', null, ['id' => 'mapping-note', 'class' => 'form-control', 'maxlength' => 500, 'placeholder' => 'ระบุเหตุผลเพื่อใช้ตรวจสอบย้อนหลัง']) ?></div>
        </div>
        <?= Html::endForm() ?>
        <p class="text-body-secondary small mt-3 mb-0">หากไม่มีคู่มาตรฐาน ให้ใช้ปุ่ม “บัญชีเฉพาะ รพ.” ในรายการด้านล่าง โดยไม่สร้างรหัสมาตรฐานเทียม</p>
    </div>
</section>
<?php endif; ?>

<section class="card border mb-3" aria-labelledby="mapping-list-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="mapping-list-heading">ผลการจับคู่และคำตัดสิน</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>รหัสโรงพยาบาล</th><th>ชื่อบัญชีโรงพยาบาล</th><th>รหัสมาตรฐาน</th><th>วิธีจับคู่</th><th>สถานะ</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($mappings as $mapping): ?>
                <tr>
                    <td class="font-monospace text-nowrap"><?= Html::encode($mapping->hospitalAccount->code) ?></td>
                    <td><?= Html::encode($mapping->hospitalAccount->name) ?></td>
                    <td><?php if ($mapping->standardAccount): ?><div class="font-monospace text-nowrap"><?= Html::encode($mapping->standardAccount->code) ?></div><div class="small text-body-secondary"><?= Html::encode($mapping->standardAccount->name) ?></div><?php else: ?>—<?php endif; ?></td>
                    <td><?= Html::encode([AccountingChartMapping::TYPE_EXACT => 'รหัสตรงกัน', AccountingChartMapping::TYPE_PARENT => 'คำแนะนำรหัสแม่', AccountingChartMapping::TYPE_MANUAL => 'จับคู่ด้วยตนเอง', AccountingChartMapping::TYPE_HOSPITAL_ONLY => 'บัญชีเฉพาะ รพ.'][$mapping->match_type] ?? $mapping->match_type) ?></td>
                    <td>
                        <?php if ($mapping->status === AccountingChartMapping::STATUS_CONFIRMED): ?><span class="badge bg-success-subtle text-success-emphasis">ยืนยันแล้ว</span>
                        <?php elseif ($mapping->status === AccountingChartMapping::STATUS_REJECTED): ?><span class="badge bg-danger-subtle text-danger-emphasis">ปฏิเสธ</span>
                        <?php else: ?><span class="badge bg-warning-subtle text-warning-emphasis">รอตรวจ</span><?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <?php if (Yii::$app->user->can('accountingChartManage') && $mapping->status === AccountingChartMapping::STATUS_SUGGESTED): ?>
                            <?= Html::beginForm(['confirm-mapping', 'id' => $mapping->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('ยืนยัน', ['class' => 'btn btn-sm btn-success']) ?><?= Html::endForm() ?>
                            <?= Html::beginForm(['reject-mapping', 'id' => $mapping->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('ปฏิเสธ', ['class' => 'btn btn-sm btn-outline-danger']) ?><?= Html::endForm() ?>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('accountingChartManage')): ?>
                            <?= Html::beginForm(['mark-hospital-only', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
                            <?= Html::hiddenInput('hospital_account_id', $mapping->hospital_account_id) ?>
                            <?= Html::submitButton('บัญชีเฉพาะ รพ.', ['class' => 'btn btn-sm btn-outline-secondary', 'data' => ['confirm' => 'ยืนยันว่าบัญชีนี้ไม่มีคู่มาตรฐาน?']]) ?>
                            <?= Html::endForm() ?>
                            <?= Html::beginForm(['reset-mapping', 'id' => $mapping->id], 'post', ['class' => 'd-inline']) ?>
                            <?= Html::submitButton('นำผลออก', ['class' => 'btn btn-sm btn-outline-secondary', 'data' => ['confirm' => 'นำผลตัดสินนี้ออกเพื่อพิจารณาใหม่?']]) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$mappings): ?><tr><td colspan="6" class="text-center text-body-secondary py-4">ยังไม่มีรหัสที่จับคู่กับผังมาตรฐานได้</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card border" aria-labelledby="unmapped-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="unmapped-heading">รหัสรายได้และค่าใช้จ่ายที่ยังไม่จับคู่</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>รหัสโรงพยาบาล</th><th>ชื่อบัญชี</th><th>สิ่งที่ต้องทำ</th></tr></thead>
            <tbody>
            <?php foreach ($unmapped as $account): ?><tr><td class="font-monospace text-nowrap"><?= Html::encode($account->code) ?></td><td><?= Html::encode($account->name) ?></td><td><?php if (Yii::$app->user->can('accountingChartManage') && $standardVersion): ?>
                <?= Html::beginForm(['mark-hospital-only', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?><?= Html::hiddenInput('hospital_account_id', $account->id) ?><?= Html::submitButton('บัญชีเฉพาะ รพ.', ['class' => 'btn btn-sm btn-outline-secondary', 'data' => ['confirm' => 'ยืนยันว่าบัญชีนี้ไม่มีคู่มาตรฐาน?']]) ?><?= Html::endForm() ?>
                <span class="text-body-secondary small">หรือเลือกในแบบฟอร์มด้านบนเพื่อจับคู่</span>
            <?php else: ?><span class="text-body-secondary">รอตรวจสอบ</span><?php endif; ?></td></tr><?php endforeach; ?>
            <?php if (!$unmapped): ?><tr><td colspan="3" class="text-center text-body-secondary py-4">บัญชีหมวดรายได้และค่าใช้จ่ายถูกจับคู่ครบแล้ว</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
