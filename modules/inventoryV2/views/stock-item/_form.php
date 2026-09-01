<?php

use app\components\ThaiDateHelper;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockItem $model */
/** @var array $purchaseHistory */

$purchaseHistory = $purchaseHistory ?? [];
$dataJson = is_array($model->data_json) ? $model->data_json : (json_decode((string) $model->data_json, true) ?: []);
if (empty($dataJson['unit_name']) && !empty($dataJson['unit'])) {
    $dataJson['unit_name'] = $dataJson['unit'];
}
$model->data_json = $dataJson;
$isNewRecord = $model->isNewRecord;
$autoSelected = $isNewRecord && (string) $model->auto === '1';
$uploadUrl = Url::to(['/filemanager/uploads/single']);
$listUrl = Url::to(['/inventory-v2/stock-item/index']);
?>

<style>
#form-product .select2-selection.is-invalid { border-color: var(--bs-danger) !important; }
.stock-item-section + .stock-item-section { border-top: 1px solid var(--bs-border-color); }
.stock-item-image-panel { max-width: 160px; }
.stock-item-image { display: block; width: 100%; height: 88px; object-fit: contain; }
.stock-item-code-option:has(input:checked) { border-color: var(--bs-primary) !important; background: var(--bs-tertiary-bg); }
.stock-item-history td, .stock-item-history th { white-space: nowrap; }
</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-product',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/sm/product/createvalidator'],
    'options' => ['data-list-url' => $listUrl],
]); ?>

<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>

<section class="stock-item-section px-3 px-lg-4 pt-2 pb-3" aria-labelledby="stock-item-main-heading">
    <h2 id="stock-item-main-heading" class="h6 fw-semibold text-primary mb-3">1. ข้อมูลวัสดุ</h2>
    <div class="row g-2">
        <div class="col-12 col-lg-9">
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <?= $form->field($model, 'item_name')->textInput(['maxlength' => true, 'placeholder' => 'ระบุชื่อวัสดุ'])->label('ชื่อวัสดุ') ?>
                </div>
                <div class="col-12 col-md-6">
                    <?= $form->field($model, 'category_id')->widget(Select2::class, [
                        'data' => $model->listAssetType(),
                        'options' => ['placeholder' => 'เลือกหมวดหมู่'],
                        'pluginOptions' => ['tags' => true, 'allowClear' => true, 'dropdownParent' => '#main-modal'],
                    ])->label('หมวดหมู่') ?>
                </div>
                <div class="col-12 col-md-6">
                    <?= $form->field($model, 'data_json[metter_type]')->widget(Select2::class, [
                        'data' => $model->listMatterType(),
                        'options' => ['placeholder' => 'เลือกประเภทวัสดุ'],
                        'pluginOptions' => ['allowClear' => true, 'tags' => true, 'dropdownParent' => '#main-modal'],
                    ])->label('ประเภทวัสดุ') ?>
                </div>
                <div class="col-12 col-md-6">
                    <?= $form->field($model, 'data_json[purchase_type]')->widget(Select2::class, [
                        'data' => $model->listPurchaseType(),
                        'options' => ['placeholder' => 'เลือกวิธีการจัดซื้อ'],
                        'pluginOptions' => ['allowClear' => true, 'tags' => true, 'dropdownParent' => '#main-modal'],
                    ])->label('การจัดซื้อ') ?>
                </div>
                <div class="col-12 d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                    <?= $form->field($model, 'is_innovation', ['options' => ['class' => 'mb-0']])
                        ->checkbox(['class' => 'form-check-input', 'switch' => true])->label('บัญชีนวัตกรรม') ?>
                    <small class="text-body-secondary">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>จุดสั่งซื้อกำหนดแยกตามคลัง
                    </small>
                </div>
            </div>
        </div>
        <div class="stock-item-image-panel col-12 col-sm-6 col-lg-3 mx-auto">
            <label for="product_file" class="form-label">รูปภาพวัสดุ</label>
            <?= Html::img($model->ShowImg(), [
                'class' => 'stock-item-image img-thumbnail bg-body-tertiary rounded-3 mb-1',
                'alt' => 'รูปภาพวัสดุ ' . $model->item_name,
                'id' => 'product-preview',
            ]) ?>
            <?= Html::button('<i class="bi bi-image me-1" aria-hidden="true"></i>เปลี่ยนรูป', [
                'type' => 'button', 'class' => 'btn btn-sm btn-outline-secondary w-100', 'id' => 'select-product-photo',
            ]) ?>
            <input type="file" class="visually-hidden" id="product_file" accept="image/*">
        </div>
    </div>
</section>

<div class="row g-0">
    <section class="stock-item-section col-12 col-lg-6 px-3 px-lg-4 py-3 border-end" aria-labelledby="stock-item-code-heading">
        <h2 id="stock-item-code-heading" class="h6 fw-semibold text-primary mb-3">2. รหัสวัสดุ</h2>
        <fieldset <?= $isNewRecord ? '' : 'disabled' ?>>
            <legend class="form-label fs-6">วิธีสร้างรหัส <span class="text-danger">*</span></legend>
            <div class="row g-2 mb-3" role="radiogroup" aria-label="วิธีสร้างรหัสวัสดุ">
                <div class="col-6">
                    <label class="stock-item-code-option border rounded-3 p-3 d-flex gap-2 h-100" for="stock-item-code-auto">
                        <input class="form-check-input flex-shrink-0" type="radio" name="StockItem[auto]" id="stock-item-code-auto" value="1" <?= $autoSelected ? 'checked' : '' ?>>
                        <span><span class="d-block fw-semibold">อัตโนมัติ</span><span class="small text-body-secondary">ระบบสร้างรหัสตามหมวดหมู่เมื่อบันทึก</span></span>
                    </label>
                </div>
                <div class="col-6">
                    <label class="stock-item-code-option border rounded-3 p-3 d-flex gap-2 h-100" for="stock-item-code-manual">
                        <input class="form-check-input flex-shrink-0" type="radio" name="StockItem[auto]" id="stock-item-code-manual" value="0" <?= !$autoSelected ? 'checked' : '' ?>>
                        <span><span class="d-block fw-semibold">กำหนดเอง</span><span class="small text-body-secondary">กรอกรหัสวัสดุด้วยตนเอง</span></span>
                    </label>
                </div>
            </div>
        </fieldset>
        <?= $form->field($model, 'item_code')->textInput([
            'maxlength' => true,
            'placeholder' => 'ระบุรหัสวัสดุ / Barcode',
            'readonly' => !$isNewRecord,
            'aria-describedby' => 'stock-item-code-help',
        ])->label('รหัสวัสดุ') ?>
        <div id="stock-item-code-help" class="form-text">
            <?= $isNewRecord ? 'เลือกอัตโนมัติเพื่อให้ระบบสร้างรหัสจากหมวดหมู่' : 'รหัสวัสดุเดิมไม่สามารถเปลี่ยนได้' ?>
        </div>
    </section>

    <section class="stock-item-section col-12 col-lg-6 px-3 px-lg-4 py-3" aria-labelledby="stock-item-package-heading">
        <h2 id="stock-item-package-heading" class="h6 fw-semibold text-primary mb-3">3. หน่วยและข้อมูลบรรจุ</h2>
        <?= $form->field($model, 'data_json[unit_name]')->widget(Select2::class, [
            'data' => $model->listUnit(),
            'options' => ['placeholder' => 'เลือกหรือระบุหน่วยนับสต็อก'],
            'pluginOptions' => ['allowClear' => true, 'tags' => true, 'dropdownParent' => '#main-modal'],
        ])->label('หน่วยนับสต็อก')->hint('หน่วยหลักที่ใช้กับยอดคงเหลือและราคา') ?>
        <div class="row g-3">
            <div class="col-12 col-sm-6">
                <?= $form->field($model, 'data_json[package_unit_name]')->widget(Select2::class, [
                    'data' => $model->listUnit(),
                    'options' => ['placeholder' => 'เช่น ลัง, กล่อง, แพ็ค'],
                    'pluginOptions' => ['allowClear' => true, 'tags' => true, 'dropdownParent' => '#main-modal'],
                ])->label('หน่วยบรรจุ (ไม่บังคับ)') ?>
            </div>
            <div class="col-12 col-sm-6">
                <?= $form->field($model, 'data_json[package_size]')->input('number', [
                    'min' => '0.01', 'step' => '0.01', 'placeholder' => 'เช่น 24', 'inputmode' => 'decimal',
                ])->label('จำนวนต่อบรรจุ (ไม่บังคับ)') ?>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 bg-body-tertiary border rounded-3 py-2 px-3 small text-body-secondary">
            <span id="package-preview" aria-live="polite">
            ยังไม่ได้ระบุข้อมูลบรรจุ
            </span>
            <span><i class="bi bi-eye me-1" aria-hidden="true"></i>แสดงผลเท่านั้น ไม่ใช้คำนวณสต็อก</span>
        </div>
    </section>
</div>

<?php if (!$isNewRecord): ?>
<section class="stock-item-section px-3 px-lg-4 py-3" aria-labelledby="stock-item-history-heading">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 id="stock-item-history-heading" class="h6 fw-semibold text-primary mb-0">
            4. ประวัติการซื้อ
            <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1"><?= number_format(count($purchaseHistory)) ?> รายการล่าสุด</span>
        </h2>
        <span class="small text-body-secondary">แสดงสูงสุด 50 รายการ</span>
    </div>
    <?php if ($purchaseHistory): ?>
        <div class="d-none d-lg-block table-responsive border rounded-3">
            <table class="stock-item-history table table-sm table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>วันที่รับ</th><th>ผู้ขาย/บริษัท</th><th>เลขที่ PO / ใบรับ</th>
                        <th class="text-end">จำนวน</th><th>หน่วย</th><th class="text-end">ราคา/หน่วย</th>
                        <th class="text-end">รวม</th><th class="text-center">เอกสาร</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchaseHistory as $row): ?>
                        <tr>
                            <td><?= Html::encode(ThaiDateHelper::formatThaiDate($row['receive_date'])) ?></td>
                            <td><?= Html::encode($row['vendor_title']) ?></td>
                            <td><?= Html::encode($row['po_number'] . ' / ' . $row['receive_no']) ?></td>
                            <td class="text-end"><?= number_format($row['qty'], 2) ?></td>
                            <td><?= Html::encode($model->unitName ?: '—') ?></td>
                            <td class="text-end"><?= number_format($row['unit_price'], 2) ?></td>
                            <td class="text-end fw-semibold"><?= number_format($row['total'], 2) ?></td>
                            <td class="text-center"><?= Html::a('ดูใบรับ', ['/inventory-v2/receive/view', 'id' => $row['receive_id']], [
                                'class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener',
                            ]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <ul class="list-group d-lg-none" role="list">
            <?php foreach ($purchaseHistory as $row): ?>
                <li class="list-group-item px-0 py-3">
                    <div class="d-flex justify-content-between gap-3 mb-1">
                        <span class="fw-semibold"><?= Html::encode($row['vendor_title']) ?></span>
                        <span class="text-body-secondary text-nowrap"><?= Html::encode(ThaiDateHelper::formatThaiDate($row['receive_date'])) ?></span>
                    </div>
                    <div class="small text-body-secondary mb-2"><?= Html::encode($row['po_number'] . ' / ' . $row['receive_no']) ?></div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span><?= number_format($row['qty'], 2) ?> <?= Html::encode($model->unitName ?: '—') ?> · <?= number_format($row['unit_price'], 2) ?> บาท/หน่วย · รวม <?= number_format($row['total'], 2) ?> บาท</span>
                        <?= Html::a('ดูใบรับ', ['/inventory-v2/receive/view', 'id' => $row['receive_id']], [
                            'class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener',
                        ]) ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="text-center text-body-secondary border rounded-3 py-4 px-3">ยังไม่พบประวัติการซื้อจากใบรับเข้าคลังที่ยืนยันแล้ว</div>
    <?php endif; ?>
    <p class="small text-body-secondary mt-2 mb-0"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>แสดงจากใบรับเข้าคลังที่ยืนยันแล้วเท่านั้น</p>
</section>
<?php endif; ?>

<div class="position-sticky bottom-0 bg-body border-top px-3 px-lg-4 py-3 d-flex justify-content-end gap-2">
    <?= Html::button('ปิด', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('<i class="bi bi-check2-circle me-1" aria-hidden="true"></i>บันทึก', ['class' => 'btn btn-primary', 'id' => 'submit-product']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$ref = Html::encode((string) $model->ref);
$isNewJs = $isNewRecord ? 'true' : 'false';
$js = <<<JS
(function () {
    var form = $('#form-product');
    var isNewRecord = {$isNewJs};
    var codeInput = form.find('[name="StockItem[item_code]"]');
    var submitButton = $('#submit-product');

    function applyCodeMode() {
        if (!isNewRecord) return;
        var isAuto = form.find('[name="StockItem[auto]"]:checked').val() === '1';
        codeInput.prop('readonly', isAuto);
        if (isAuto) {
            codeInput.val('').attr('placeholder', 'ระบบจะสร้างรหัสตามหมวดหมู่เมื่อบันทึก').removeClass('is-invalid');
        } else {
            codeInput.attr('placeholder', 'ระบุรหัสวัสดุ / Barcode');
        }
    }

    function updatePackagePreview() {
        var packageUnit = $.trim((form.find('[name="StockItem[data_json][package_unit_name]"]').val() || '') + '');
        var packageSize = $.trim((form.find('[name="StockItem[data_json][package_size]"]').val() || '') + '');
        var stockUnit = $.trim((form.find('[name="StockItem[data_json][unit_name]"]').val() || '') + '');
        $('#package-preview').text(packageUnit && packageSize && stockUnit
            ? 'ตัวอย่าง: 1 ' + packageUnit + ' บรรจุ ' + packageSize + ' ' + stockUnit
            : 'ยังไม่ได้ระบุข้อมูลบรรจุ');
    }

    function validateRequiredFields() {
        var missing = [];
        function requireField(selector, label) {
            var field = form.find(selector);
            var valid = $.trim((field.val() || '') + '') !== '';
            field.toggleClass('is-invalid', !valid);
            field.next('.select2-container').find('.select2-selection').toggleClass('is-invalid', !valid);
            if (!valid) missing.push(label);
        }
        requireField('[name="StockItem[item_name]"]', 'ชื่อวัสดุ');
        requireField('[name="StockItem[category_id]"]', 'หมวดหมู่');
        requireField('[name="StockItem[data_json][unit_name]"]', 'หน่วยนับสต็อก');
        if (isNewRecord && form.find('[name="StockItem[auto]"]:checked').val() !== '1') requireField('[name="StockItem[item_code]"]', 'รหัสวัสดุ');
        var packageUnit = $.trim((form.find('[name="StockItem[data_json][package_unit_name]"]').val() || '') + '');
        var packageSize = $.trim((form.find('[name="StockItem[data_json][package_size]"]').val() || '') + '');
        if ((packageUnit && !packageSize) || (!packageUnit && packageSize)) missing.push(packageUnit ? 'จำนวนต่อบรรจุ' : 'หน่วยบรรจุ');
        if (packageSize && (isNaN(Number(packageSize)) || Number(packageSize) <= 0)) missing.push('จำนวนต่อบรรจุต้องมากกว่า 0');
        return missing;
    }

    function uploadSelectedFile() {
        var input = document.getElementById('product_file');
        if (!input || !input.files || !input.files.length) return $.Deferred().resolve().promise();
        var data = new FormData();
        data.append('product_item', input.files[0]); data.append('id', 1); data.append('ref', '{$ref}'); data.append('name', 'product_item');
        return $.ajax({ url: '{$uploadUrl}', type: 'POST', data: data, processData: false, contentType: false });
    }

    form.on('change', '[name="StockItem[auto]"]', applyCodeMode);
    form.on('input change', 'input, select', function () { $(this).removeClass('is-invalid').next('.select2-container').find('.select2-selection').removeClass('is-invalid'); });
    form.on('input change', '[name="StockItem[data_json][package_unit_name]"], [name="StockItem[data_json][package_size]"], [name="StockItem[data_json][unit_name]"]', updatePackagePreview);
    $('#select-product-photo').on('click', function () { $('#product_file').trigger('click'); });
    $('#product_file').on('change', function () {
        var file = this.files && this.files[0]; if (!file) return;
        var reader = new FileReader(); reader.onload = function (event) { $('#product-preview').attr('src', event.target.result); }; reader.readAsDataURL(file);
    });

    form.on('beforeSubmit', function (event) {
        event.preventDefault();
        var missing = validateRequiredFields();
        if (missing.length) {
            Swal.fire({ icon: 'warning', title: 'กรอกข้อมูลไม่ครบ', html: 'กรุณาตรวจสอบ: <strong>' + missing.join(', ') + '</strong>', confirmButtonText: 'ตกลง' });
            return false;
        }
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>กำลังบันทึก');
        uploadSelectedFile().then(function () {
            return $.ajax({ url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json' });
        }).done(function (response) {
            if (response.status !== 'success') {
                Swal.fire('บันทึกไม่สำเร็จ', response.message || response.msg || 'กรุณาตรวจสอบข้อมูลแล้วลองอีกครั้ง', 'error'); return;
            }
            closeModal();
            Swal.fire({ icon: 'success', title: 'บันทึกข้อมูลแล้ว', timer: 1000, showConfirmButton: false });
            if (response.container && $(response.container).length) {
                $.pjax.reload({ container: response.container, history: false, replace: false, timeout: false });
            } else {
                window.location.href = form.data('list-url');
            }
        }).fail(function () {
            Swal.fire('บันทึกไม่สำเร็จ', 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง', 'error');
        }).always(function () {
            submitButton.prop('disabled', false).html('<i class="bi bi-check2-circle me-1" aria-hidden="true"></i>บันทึก');
        });
        return false;
    });

    applyCodeMode(); updatePackagePreview();
})();
JS;
$this->registerJs($js);
?>
