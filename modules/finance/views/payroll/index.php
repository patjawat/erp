<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
use kartik\select2\Select2;

$this->title = 'ทะเบียนบัญชีเงินเดือน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$canManage = Yii::$app->user->can('payrollBankManage');
$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2"><i class="bi bi-bank fs-4" aria-hidden="true"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>กำหนดบัญชีรับเงินของบุคลากรที่ยังปฏิบัติงาน<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'payroll']); $this->endBlock();
?>
<?= $this->render('_menu', ['active' => 'overview']) ?>

<div aria-live="polite"><?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $class): if (!Yii::$app->session->hasFlash($flash)) continue; ?>
<div class="alert alert-<?= $class ?> alert-dismissible fade show" role="<?= $flash === 'error' ? 'alert' : 'status' ?>"><?= Html::encode(Yii::$app->session->getFlash($flash)) ?><button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="ปิด"></button></div>
<?php endforeach; ?></div>

<section class="card border shadow-sm" aria-labelledby="registry-heading">
<div class="card-header bg-body px-3 px-md-4 py-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
    <div><h5 id="registry-heading" class="mb-1">รายชื่อบุคลากร</h5><p class="small text-body-secondary mb-0">พบ <?= number_format($pagination->totalCount) ?> คน · กำหนดบัญชีแล้ว <?= number_format($registeredCount) ?> คน</p></div>
    <?php if ($canManage): ?><div class="d-flex flex-wrap gap-2">
        <?= Html::a('<i class="bi bi-download me-1" aria-hidden="true"></i>ดาวน์โหลด Template CSV', ['download-bank-template'], ['class' => 'btn btn-outline-success']) ?>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#bank-csv-import" aria-expanded="false" aria-controls="bank-csv-import"><i class="bi bi-file-earmark-arrow-up me-1" aria-hidden="true"></i>นำเข้า CSV</button>
    </div><?php endif; ?>
</div>
<?php if ($canManage): ?><div class="collapse border-top" id="bank-csv-import"><div class="card-body bg-body-tertiary py-3">
    <?= Html::beginForm(['import-bank-csv'], 'post', ['enctype' => 'multipart/form-data', 'class' => 'row g-2 align-items-end']) ?>
    <div class="col-lg-8"><label class="form-label" for="bank-csv-file">ไฟล์บัญชีธนาคาร CSV</label><input class="form-control" id="bank-csv-file" type="file" name="csv_file" accept=".csv,text/csv" required><div class="form-text">กรอกเฉพาะ bank_code และ account_number ใน Template · เลขบัญชีที่ขึ้นต้นด้วย 0 ให้ใส่เครื่องหมาย ' นำหน้า · รหัสธนาคาร: <?= Html::encode(implode(', ', array_keys($bankOptions))) ?></div></div>
    <div class="col-lg-4 d-flex justify-content-lg-end"><button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>ตรวจสอบและนำเข้า</button></div>
    <?= Html::endForm() ?>
</div></div><?php endif; ?>
<div class="card-body border-bottom py-3">
<?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
<div class="col-md-6 col-xl-3"><label class="form-label" for="employee-type">ประเภทบุคลากร</label><?= Select2::widget(['name' => 'employee_type_id', 'value' => $employeeTypeId ?: null, 'data' => $typeOptions, 'options' => ['id' => 'employee-type', 'placeholder' => 'ทุกประเภท'], 'pluginOptions' => ['allowClear' => true, 'width' => '100%']]) ?></div>
<div class="col-md-6 col-xl-3"><label class="form-label" for="employee-department">หน่วยงาน</label><?= Select2::widget(['name' => 'department', 'value' => $departmentId ?: null, 'data' => $departmentOptions, 'options' => ['id' => 'employee-department', 'placeholder' => 'ทุกหน่วยงาน'], 'pluginOptions' => ['allowClear' => true, 'width' => '100%']]) ?></div>
<div class="col-md-6 col-xl-3"><label class="form-label" for="bank-status">สถานะบัญชี</label><?= Html::dropDownList('bank_status', $bankStatus, ['' => 'ทุกสถานะ', 'registered' => 'กำหนดบัญชีแล้ว', 'missing' => 'ยังไม่กำหนดบัญชี'], ['id' => 'bank-status', 'class' => 'form-select']) ?></div>
<div class="col-md-6 col-xl-3"><label class="form-label" for="bank-code-filter">ธนาคาร</label><?= Select2::widget(['name' => 'bank_code', 'value' => $bankCode ?: null, 'data' => $bankOptions, 'options' => ['id' => 'bank-code-filter', 'placeholder' => 'ทุกธนาคาร'], 'pluginOptions' => ['allowClear' => true, 'width' => '100%']]) ?></div>
<div class="col-lg-9"><label class="form-label" for="employee-search">ค้นหาชื่อหรือเลขประชาชน</label><input id="employee-search" class="form-control" name="q" value="<?= Html::encode($q) ?>" placeholder="พิมพ์ชื่อหรือเลขประชาชน"></div>
<div class="col-lg-3 d-flex gap-2"><button class="btn btn-outline-primary flex-grow-1" type="submit"><i class="bi bi-funnel me-1" aria-hidden="true"></i>กรองข้อมูล</button><?= Html::a('ล้างตัวกรอง', ['index'], ['class' => 'btn btn-outline-secondary']) ?></div>
<?= Html::endForm() ?>
</div>

<div class="table-responsive"><table class="table table-hover align-middle mb-0 payroll-bank-table">
<thead><tr><th>ชื่อ–เลขประชาชน</th><th>ประเภทบุคลากร</th><th>หน่วยงาน</th><th>บัญชีธนาคาร</th><th class="text-end payroll-bank-action">จัดการ</th></tr></thead><tbody>
<?php foreach ($pageRows as $row): $bank = $row['bank_account']; $employeeId = (int) $row['employee_id']; ?>
<tr><td><strong><?= Html::encode($row['full_name']) ?></strong><div class="small text-body-secondary font-monospace"><?= Html::encode($row['employee']->cid ?: 'ไม่พบเลขประชาชน') ?></div></td>
<td><?= Html::encode($row['employee_type']) ?></td><td class="text-body-secondary"><?= Html::encode($row['department'] ?: '—') ?></td>
<td><?php if ($bank): ?><strong><?= Html::encode($bankOptions[$bank['bank_code']] ?? $bank['bank_code']) ?></strong><div class="small text-body-secondary font-monospace">•••• •••• <?= Html::encode($bank['account_last4']) ?></div><?php else: ?><span class="text-body-secondary">ยังไม่กำหนดบัญชี</span><?php endif; ?></td>
<td class="text-end"><button class="btn btn-sm <?= $bank ? 'btn-outline-secondary' : 'btn-outline-primary' ?> bank-editor-toggle" type="button" aria-expanded="false" aria-controls="bank-editor-<?= $employeeId ?>" data-target="bank-editor-<?= $employeeId ?>"><i class="bi bi-pencil me-1" aria-hidden="true"></i><?= $bank ? 'เปลี่ยนบัญชี' : 'กำหนดบัญชี' ?></button></td></tr>
<tr class="d-none bank-editor-row" id="bank-editor-<?= $employeeId ?>"><td colspan="5" class="bg-body-tertiary px-3 px-md-4 py-3">
<?php if ($canManage): ?><?= Html::beginForm(['save-bank-account'], 'post', ['class' => 'row g-2 align-items-end']) ?>
<input type="hidden" name="employee_id" value="<?= $employeeId ?>"><input type="hidden" name="q" value="<?= Html::encode($q) ?>"><input type="hidden" name="employee_type_id" value="<?= (int) $employeeTypeId ?>">
<input type="hidden" name="department" value="<?= (int) $departmentId ?>"><input type="hidden" name="bank_status" value="<?= Html::encode($bankStatus) ?>"><input type="hidden" name="bank_code_filter" value="<?= Html::encode($bankCode) ?>">
<div class="col-md-4"><label class="form-label" for="bank-code-<?= $employeeId ?>">ธนาคาร</label><?= Html::dropDownList('bank_code', $bank['bank_code'] ?? '', ['' => 'เลือกธนาคาร'] + $bankOptions, ['id' => 'bank-code-' . $employeeId, 'class' => 'form-select', 'required' => true]) ?></div>
<div class="col-md-5"><label class="form-label" for="account-number-<?= $employeeId ?>">เลขบัญชีธนาคาร</label><input id="account-number-<?= $employeeId ?>" class="form-control font-monospace" name="account_number" inputmode="numeric" pattern="[0-9 -]{6,24}" autocomplete="off" required placeholder="กรอกเลขบัญชีใหม่"></div>
<div class="col-md-3 d-flex justify-content-md-end gap-2"><button class="btn btn-outline-secondary bank-editor-cancel" type="button" data-target="bank-editor-<?= $employeeId ?>">ยกเลิก</button><button class="btn btn-primary" type="submit">บันทึกบัญชี</button></div>
<?= Html::endForm() ?><?php else: ?><span class="text-body-secondary">บัญชีผู้ใช้ของคุณไม่มีสิทธิ์แก้ไขบัญชีธนาคาร</span><?php endif; ?>
</td></tr>
<?php endforeach; ?>
<?php if (!$pageRows): ?><tr><td colspan="5" class="text-center py-5"><strong class="d-block mb-1">ไม่พบรายชื่อบุคลากร</strong><span class="text-body-secondary">ลองเปลี่ยนประเภทบุคลากรหรือคำค้นหา</span></td></tr><?php endif; ?>
</tbody></table></div>
<div class="card-footer bg-body py-3 px-3 px-md-4"><?= LinkPager::widget(['pagination' => $pagination, 'options' => ['class' => 'pagination mb-0'], 'linkContainerOptions' => ['class' => 'page-item'], 'linkOptions' => ['class' => 'page-link']]) ?></div>
</section>

<?php $this->registerCss('.payroll-bank-table{min-width:980px}.payroll-bank-table th{white-space:nowrap}.payroll-bank-action{width:9rem}.payroll-bank-table td{padding-top:.6rem;padding-bottom:.6rem}.bank-editor-row .form-label{font-weight:600;margin-bottom:.35rem}'); ?>
<?php $this->registerJs(<<<'JS'
(function(){function setEditor(id,open){var row=document.getElementById(id),button=document.querySelector('.bank-editor-toggle[data-target="'+id+'"]');if(!row)return;row.classList.toggle('d-none',!open);if(button)button.setAttribute('aria-expanded',open?'true':'false');if(open)row.querySelector('select,input:not([type="hidden"])')?.focus();}document.querySelectorAll('.bank-editor-toggle').forEach(function(button){button.addEventListener('click',function(){var row=document.getElementById(button.dataset.target);setEditor(button.dataset.target,row.classList.contains('d-none'));});});document.querySelectorAll('.bank-editor-cancel').forEach(function(button){button.addEventListener('click',function(){setEditor(button.dataset.target,false);});});})();
JS, \yii\web\View::POS_END); ?>
