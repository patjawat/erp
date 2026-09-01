<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = 'เพิ่มรายชื่อ: ' . $itemType['name'];
$this->params['breadcrumbs'][] = ['label' => 'เงินเดือน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'รายการรายบุคคล', 'url' => ['employee-items', 'group' => $group, 'item_type_id' => $itemType['id']]];
$this->params['breadcrumbs'][] = $this->title;
$isSalary = $itemType['code'] === 'SALARY';
$isActive = $itemType['status'] === 'active';
$menuActive = $group === 'compensation' ? 'compensation-income' : ($group === 'deduction' ? 'monthly-expense' : 'monthly-income');

$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2"><i class="bi bi-person-plus fs-4"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>เลือกบุคลากร ตรวจสอบจำนวนเงิน และบันทึกเข้ารายการ<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'payroll']); $this->endBlock();
?>
<?= $this->render('_menu', ['active' => $menuActive]) ?>

<div aria-live="polite"><?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $class): if (!Yii::$app->session->hasFlash($flash)) continue; ?>
<div class="alert alert-<?= $class ?> alert-dismissible fade show" role="<?= $flash === 'error' ? 'alert' : 'status' ?>"><?= Html::encode(Yii::$app->session->getFlash($flash)) ?><button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="ปิด"></button></div>
<?php endforeach; ?></div>

<section class="card border shadow-sm payroll-picker" aria-labelledby="picker-title">
<div class="card-header bg-body px-3 px-md-4 py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
    <div><div class="d-flex flex-wrap align-items-center gap-2 mb-1"><h5 id="picker-title" class="mb-0"><?= Html::encode($itemType['name']) ?></h5><span class="badge <?= $isActive ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>"><?= $isActive ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?></span></div><p class="mb-0 text-body-secondary small"><?= $isSalary ? 'จำนวนเงินดึงจากข้อมูลเงินเดือนพื้นฐาน สามารถตรวจสอบก่อนบันทึก' : 'กรอกจำนวนเงินเฉพาะบุคลากรที่เลือก' ?></p></div>
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับหน้ารายชื่อ', ['employee-items', 'group' => $group, 'item_type_id' => $itemType['id']], ['class' => 'btn btn-outline-secondary align-self-start align-self-lg-center']) ?>
</div>

<div class="card-body border-bottom py-3">
<?= Html::beginForm(['add-item-employees'], 'get', ['class' => 'row g-2 align-items-end']) ?>
<input type="hidden" name="group" value="<?= Html::encode($group) ?>"><input type="hidden" name="item_type_id" value="<?= (int) $itemType['id'] ?>">
<div class="col-lg-3"><label class="form-label" for="employee-type">ประเภทบุคลากร</label><?= Html::dropDownList('employee_type_id', $employeeTypeId, ['' => 'ทุกประเภท'] + $typeOptions, ['id' => 'employee-type', 'class' => 'form-select']) ?></div>
<div class="col-lg-7"><label class="form-label" for="employee-search">ค้นหาชื่อ เลขประชาชน หรือหน่วยงาน</label><input class="form-control" id="employee-search" name="q" value="<?= Html::encode($q) ?>" placeholder="พิมพ์คำค้นหา"></div>
<div class="col-lg-2"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search me-1"></i>ค้นหา</button></div>
<?= Html::endForm() ?>
</div>

<?= Html::beginForm(['save-item-employees'], 'post', ['id' => 'add-employees-form']) ?>
<input type="hidden" name="group" value="<?= Html::encode($group) ?>"><input type="hidden" name="item_type_id" value="<?= (int) $itemType['id'] ?>">
<div class="payroll-selection-bar bg-body-tertiary border-bottom px-3 px-md-4 py-2 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
    <div class="d-flex flex-wrap align-items-baseline gap-2"><strong id="selected-count">เลือกแล้ว 0 คน</strong><span class="text-body-secondary">ยอดรวม <span class="fw-semibold text-body" id="selected-total">0.00</span> บาท</span><span class="text-body-secondary small">จาก <?= number_format($pagination->totalCount) ?> คน</span></div>
    <div class="form-check mb-0"><input class="form-check-input" id="select-page" type="checkbox"><label class="form-check-label" for="select-page">เลือกทั้งหมดในหน้านี้</label></div>
</div>

<div class="table-responsive"><table class="table table-hover align-middle mb-0 payroll-candidate-table">
<thead><tr><th class="text-center payroll-select-column">เลือก</th><th>ชื่อ–เลขประชาชน</th><th>ประเภทบุคลากร</th><th>หน่วยงาน</th><th class="text-end payroll-amount-column">จำนวนเงิน (บาท)</th></tr></thead><tbody>
<?php foreach ($pageRows as $row): $employee = $row['employee']; $amount = (float) $row['salary']; $rawAmount = $isSalary && $amount > 0 ? number_format($amount, 2, '.', '') : ''; ?>
<tr><td class="text-center"><input class="form-check-input employee-check" type="checkbox" name="employee_ids[]" value="<?= (int) $row['employee_id'] ?>" aria-label="เลือก <?= Html::encode($row['full_name']) ?>"></td>
<td><strong><?= Html::encode($row['full_name']) ?></strong><div class="small text-body-secondary font-monospace"><?= Html::encode($employee->cid ?: 'ไม่พบเลขประชาชน') ?></div></td>
<td><?= Html::encode($row['employee_type']) ?></td><td class="text-body-secondary"><?= Html::encode($row['department'] ?: '—') ?></td>
<td class="text-end"><input class="form-control form-control-sm text-end employee-amount-display" type="text" inputmode="decimal" name="amounts[<?= (int) $row['employee_id'] ?>]" value="<?= $rawAmount !== '' ? number_format((float) $rawAmount, 2) : '' ?>" <?= $isSalary ? 'readonly' : '' ?> aria-label="จำนวนเงินของ <?= Html::encode($row['full_name']) ?>"></td></tr>
<?php endforeach; ?>
<?php if (!$pageRows): ?><tr><td colspan="5" class="text-center py-5"><strong class="d-block mb-1">ไม่พบรายชื่อที่เพิ่มได้</strong><span class="text-body-secondary">ลองเปลี่ยนประเภทบุคลากรหรือคำค้นหา</span></td></tr><?php endif; ?>
</tbody></table></div>
<div class="card-footer bg-body d-flex flex-column flex-md-row align-items-md-center gap-3 py-3">
<?= LinkPager::widget(['pagination' => $pagination, 'options' => ['class' => 'pagination mb-0'], 'linkContainerOptions' => ['class' => 'page-item'], 'linkOptions' => ['class' => 'page-link']]) ?>
<button class="btn btn-primary ms-md-auto" id="save-employees" type="submit" disabled><i class="bi bi-check-lg me-1"></i>บันทึกรายชื่อที่เลือก</button>
</div>
<?= Html::endForm() ?>
</section>

<?php $this->registerCss(<<<'CSS'
.payroll-picker .form-label{font-weight:600;margin-bottom:.35rem}.payroll-candidate-table{min-width:900px}.payroll-candidate-table th{white-space:nowrap}.payroll-select-column{width:4.5rem}.payroll-amount-column{width:11rem}.employee-amount-display{width:10rem;margin-left:auto;font-variant-numeric:tabular-nums}.payroll-selection-bar{min-height:3.25rem;font-variant-numeric:tabular-nums}.payroll-candidate-table td{padding-top:.55rem;padding-bottom:.55rem}
CSS); ?>
<?php $this->registerJs(<<<'JS'
(function(){
var checks=Array.from(document.querySelectorAll('.employee-check')),selectPage=document.getElementById('select-page'),save=document.getElementById('save-employees'),displays=Array.from(document.querySelectorAll('.employee-amount-display'));
function parseMoney(value){return Number(String(value||'').replace(/,/g,''));}
function formatMoney(input){var value=parseMoney(input.value);input.value=Number.isFinite(value)&&value>0?value.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}):'';}
function update(){var selected=checks.filter(function(x){return x.checked;}),total=selected.reduce(function(sum,x){return sum+parseMoney(x.closest('tr').querySelector('.employee-amount-display').value||0);},0);document.getElementById('selected-count').textContent='เลือกแล้ว '+selected.length+' คน';document.getElementById('selected-total').textContent=total.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});save.disabled=selected.length===0||selected.some(function(x){return parseMoney(x.closest('tr').querySelector('.employee-amount-display').value||0)<=0;});selectPage.checked=checks.length>0&&selected.length===checks.length;selectPage.indeterminate=selected.length>0&&selected.length<checks.length;}
displays.forEach(function(input){formatMoney(input);input.addEventListener('input',update);input.addEventListener('blur',function(){formatMoney(input);update();});});checks.forEach(function(x){x.addEventListener('change',update);});selectPage.addEventListener('change',function(){checks.forEach(function(x){x.checked=selectPage.checked;});update();});document.getElementById('add-employees-form').addEventListener('submit',function(){displays.forEach(function(input){input.value=String(input.value).replace(/,/g,'');});save.disabled=true;save.innerHTML='<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>กำลังบันทึก';});update();
})();
JS, \yii\web\View::POS_END); ?>
