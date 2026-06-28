<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/** @var app\models\UploadCsvForm $model */
/** @var array $listWarehouses */

$this->title = 'นำเข้ายอดยกมาจากการนับจริง → Inventory V2';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="file-spreadsheet"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2 mb-0">
            <i class="fa-solid fa-file-csv me-1"></i> ขั้นที่ 1: ดาวน์โหลด Template + กรอกผลนับ
        </h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-2">
            ดาวน์โหลด template CSV → กรอกผลการนับจริงในคลัง → upload กลับมาเพื่อสร้างใบรับเข้า V2 (สถานะ CONFIRMED) ที่ตัดยอด stock_balance ทันที
        </p>
        <p class="text-muted small mb-2">
            Columns: <code>item_code</code> · <code>lot_number</code> (เว้นว่างได้) · <code>qty</code> · <code>unit_price</code> · <code>note</code>
        </p>
        <?= Html::a('<i class="fa-solid fa-download me-1"></i> ดาวน์โหลด Template CSV',
            ['import-count-template'],
            ['class' => 'btn btn-outline-primary btn-sm']) ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2 mb-0">
            <i class="fa-solid fa-upload me-1"></i> ขั้นที่ 2: เลือกคลัง + อัปโหลด CSV
        </h6>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'id' => 'upload-form',
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small mb-1">คลังหลัก V2 ปลายทาง <span class="text-danger">*</span></label>
                <?= Html::dropDownList('main_warehouse_id', null,
                    ['' => '— เลือกคลัง —'] + $listWarehouses,
                    ['class' => 'form-select form-select-sm', 'id' => 'main_warehouse_id', 'required' => true]) ?>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">วันที่ของยอดยกมา</label>
                <input type="text" name="order_date" id="order_date"
                       class="form-control form-control-sm"
                       placeholder="<?= date('d/m/') . (date('Y') + 543) ?>"
                       value="<?= date('d/m/') . (date('Y') + 543) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small mb-1">หมายเหตุ (ref)</label>
                <input type="text" name="note" id="note" class="form-control form-control-sm"
                       value="ยอดยกมาจากการนับจริง">
            </div>

            <div class="col-12">
                <label class="form-label small mb-1">ไฟล์ CSV ผลนับ <span class="text-danger">*</span></label>
                <input type="file" name="csvFile" id="csvFile" class="form-control form-control-sm"
                       accept=".csv,text/csv" required>
            </div>

            <div class="col-12 d-flex gap-2 pt-2">
                <button type="button" id="btn-preview" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-eye me-1"></i> Preview ก่อน import
                </button>
                <button type="button" id="btn-confirm" class="btn btn-success btn-sm" disabled>
                    <i class="fa-solid fa-check me-1"></i> Confirm Import + ตัดยอด
                </button>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="card" id="preview-card" style="display:none;">
    <div class="card-header bg-info-gradient text-white d-flex align-items-center flex-wrap gap-2">
        <h6 class="text-white mt-2 mb-0">
            <i class="fa-solid fa-table me-1"></i> Preview ข้อมูลก่อนนำเข้า
        </h6>
        <span id="preview-summary" class="badge bg-light text-dark ms-2"></span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 48px;">#</th>
                    <th>item_code</th>
                    <th>lot_number</th>
                    <th class="text-end">qty</th>
                    <th class="text-end">unit_price</th>
                    <th>note</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody id="preview-tbody"></tbody>
        </table>
    </div>
</div>

<!-- ฟอร์มซ่อนสำหรับ confirm import -->
<?= Html::beginForm(['import-count-save'], 'post', ['id' => 'confirm-form', 'style' => 'display:none']) ?>
    <input type="hidden" name="filePath" id="filePath">
    <input type="hidden" name="main_warehouse_id" id="confirm_warehouse_id">
    <input type="hidden" name="order_date" id="confirm_order_date">
    <input type="hidden" name="note" id="confirm_note">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
<?= Html::endForm() ?>

<?php
$previewUrl = Url::to(['import-count-preview']);
$js = <<<JS
(function () {
    const \$btnPreview = document.getElementById('btn-preview');
    const \$btnConfirm = document.getElementById('btn-confirm');
    const \$csvFile   = document.getElementById('csvFile');
    const \$warehouse = document.getElementById('main_warehouse_id');
    const \$orderDate = document.getElementById('order_date');
    const \$note      = document.getElementById('note');
    const \$card      = document.getElementById('preview-card');
    const \$tbody     = document.getElementById('preview-tbody');
    const \$summary   = document.getElementById('preview-summary');

    \$btnPreview.addEventListener('click', function () {
        if (!\$csvFile.files || !\$csvFile.files[0]) {
            alert('กรุณาเลือกไฟล์ CSV');
            return;
        }
        const fd = new FormData();
        fd.append('csvFile', \$csvFile.files[0]);
        fd.append('_csrf', '___CSRF___');

        \$btnPreview.disabled = true;
        \$btnPreview.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังประมวลผล...';

        fetch('{$previewUrl}', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            \$btnPreview.disabled = false;
            \$btnPreview.innerHTML = '<i class="fa-solid fa-eye me-1"></i> Preview ก่อน import';
            if (res.status !== 'success') {
                alert(res.message || 'อ่านไฟล์ไม่สำเร็จ');
                return;
            }
            document.getElementById('filePath').value = res.filePath;
            \$tbody.innerHTML = '';
            res.preview.forEach((r, i) => {
                const tr = document.createElement('tr');
                if (!r.is_valid) tr.classList.add('table-warning');
                tr.innerHTML = [
                    '<td class="text-muted">'+(i+1)+'</td>',
                    '<td><code>'+escapeHtml(r.item_code)+'</code></td>',
                    '<td>'+escapeHtml(r.lot_number || '-')+'</td>',
                    '<td class="text-end">'+Number(r.qty).toLocaleString('en', {minimumFractionDigits:2})+'</td>',
                    '<td class="text-end">'+(r.unit_price>0?Number(r.unit_price).toLocaleString('en',{minimumFractionDigits:2}):'-')+'</td>',
                    '<td class="small text-muted">'+escapeHtml(r.note || '')+'</td>',
                    '<td>'+ (r.is_valid
                        ? '<span class="badge bg-success">OK</span>'
                        : '<span class="badge bg-warning text-dark" title="'+escapeHtml(r.error)+'">'+escapeHtml(r.error)+'</span>'
                    ) + '</td>'
                ].join('');
                \$tbody.appendChild(tr);
            });
            \$summary.textContent = 'ทั้งหมด '+res.preview.length+' แถว · ใช้ได้ '+res.validCount+' · ข้าม '+res.invalidCount;
            \$card.style.display = '';
            \$btnConfirm.disabled = (res.validCount === 0);
        })
        .catch(e => {
            \$btnPreview.disabled = false;
            \$btnPreview.innerHTML = '<i class="fa-solid fa-eye me-1"></i> Preview ก่อน import';
            alert('เกิดข้อผิดพลาด: ' + e.message);
        });
    });

    \$btnConfirm.addEventListener('click', function () {
        if (!\$warehouse.value) { alert('กรุณาเลือกคลัง V2 ปลายทาง'); return; }
        if (!confirm('ยืนยันสร้างใบรับเข้า V2 จากผลนับ (จะตัดยอด stock_balance ทันที)?')) return;

        document.getElementById('confirm_warehouse_id').value = \$warehouse.value;
        document.getElementById('confirm_order_date').value = \$orderDate.value;
        document.getElementById('confirm_note').value = \$note.value;
        document.getElementById('confirm-form').submit();
    });

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        })[c]);
    }
})();
JS;
$js = str_replace('___CSRF___', Yii::$app->request->csrfToken, $js);
$this->registerJs($js);
