<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetType $model */
/** @var yii\widgets\ActiveForm $form */
$id = isset($model->id) ? intval($model->id) : 0;
$ref = isset($ref) ? Html::encode($ref) : '';
?>
<style>
/* กรอบแดงสำหรับ Select2 ที่ยังไม่ได้กรอก (validation) */
#form-product .select2-selection.is-invalid { border-color: #dc3545 !important; }
</style>



<?php $form = ActiveForm::begin([
    'id' => 'form-product',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/sm/product/createvalidator'],
]); ?>

<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>

<div class="row">
    <div class="col-6">
        <?= $form->field($model, 'item_name')->textInput(['maxlength' => true, 'placeholder' => 'ระบุชื่อสินค้า/บริการ'])->label('ชื่อวัสดุ') ?>
    </div>
    <div class="col-6">
        <?php
        echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
            'data' => $model->listUnit(),
            'options' => ['placeholder' => 'เช่น ชิ้น, กล่อง, แพ็ค'],
            'pluginOptions' => [
                'allowClear' => true,
                'tags' => true,
                'dropdownParent' => '#main-modal',
            ],
            'pluginEvents' => [
                'select2:select' => "function(result) { 
                                            var data = \$(this).select2('data')[0].text;
                                            console.log(data)
                                        }",
            ]
        ])->label('หน่วย')
        ?>
    </div>
    <div class="col-6">
        <?php if ($model->isNewRecord): ?>
            <?= $form->field($model, 'item_code')->textInput(['maxlength' => true, 'placeholder' => 'ระบุรหัสวัสดุ / Barcode'])->label('รหัสวัสดุ') ?>
        <?php else: ?>
            <div class="mb-3 highlight-addon field-stock-tem-item-item_code">
                <label class="form-label has-star" for="stock-tem-item_code">รหัสวัสดุ</label>
                <input type="text" class="form-control" name="StockItem[item_code]" value="<?= Html::encode($model->item_code) ?>"
                    disabled>
            </div>
        <?php endif ?>

    </div>

    <div class="col-6">
        <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
            'data' => $model->listAssetType(),
            'options' => ['placeholder' => 'ประเภทของวัสดุ'],
            'pluginOptions' => [
                'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) {}',
                'select2:unselecting' => 'function() {}',
            ],

        ])->label('หมวดหมู่') ?>

    </div>

    <div class="col-6">
        <?php
        echo $form->field($model, 'data_json[metter_type]')->widget(Select2::classname(), [
            'data' => $model->listMatterType(),
            'options' => ['placeholder' => 'ระบุประเภทวัสดุ...'],
            'pluginOptions' => [
                'allowClear' => true,
                'tags' => true,
                'dropdownParent' => '#main-modal',
            ],
        ])->label('ประเภทวัสดุ')
        ?>
    </div>
    <div class="col-6">
        <div class="alert alert-info py-2 px-3 small mb-3" role="alert">
            <i class="bi bi-info-circle me-1"></i>
            จำนวนสูงสุด/ต่ำสุดสำหรับสั่งซื้อ ให้ตั้งค่าแยกตามคลังที่หน้า
            <strong>ตั้งค่าคลัง → กำหนดจุดสั่งซื้อต่อคลัง</strong>
        </div>
    </div>

    <div class="col-6">
        <?php
        echo $form->field($model, 'data_json[purchase_type]')->widget(Select2::classname(), [
            'data' => $model->listPurchaseType(),
            'options' => ['placeholder' => 'ระบุการจัดซื้อ...'],
            'pluginOptions' => [
                'allowClear' => true,
                'tags' => true,
                'dropdownParent' => '#main-modal',
            ],
        ])->label('การจัดซื้อ')
        ?>
    </div>

    <div class="col-3">
        <div class="mt-4">
            <?= $form->field($model, 'auto')->checkbox(['custom' => true, 'switch' => true, 'checked' => false])->label('รหัสวัสดุอัตโนมัติ'); ?>
        </div>
    </div>
    <div class="col-3">
        <div class="mt-4">
            <?= $form->field($model, 'is_innovation')->checkbox([
                'custom' => true,
                'switch' => true,
                // ไม่ต้องใส่ 'checked' เอง ถ้าค่าใน Database เป็น 1 หรือ true มันจะจัดการให้ครับ
            ])->label('บัญชีนวัตกรรม'); ?>
        </div>
    </div>


    <div class="col-12">
        <label for="productImage" class="form-label">รูปภาพวัสดุ</label>
        <input type="file" class="form-control" id="product_file" />
    </div>
</div>

<div class="row">

    <div class="col-6">
        <a href="#" class="select-photo">
            <?= Html::img($model->ShowImg(), ['class' => 'product-img object-fit-cover rounded mt-3', 'style' => 'max-width:100%;height: 219px;']) ?>
        </a>
    </div>
    <div class="form-group mt-3 d-flex justify-content-center gap-3">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary shadow me-2', 'id' => 'summit']) ?>
        <?= Html::button('ปิด', ['class' => 'btn btn-secondary', 'data-bs-dismiss' => 'modal']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php
    $urlUpload = Url::to('/filemanager/uploads/single');
    $ref = $model->ref;
    $isNew = $model->isNewRecord ? 'true' : 'false';
    $js = <<< JS
    // ---- สวิตช์ "รหัสวัสดุอัตโนมัติ": ปิดช่องรหัสเมื่อเปิด (ระบบออกรหัสให้ตอนบันทึก) ----
    var isNewRecord = $isNew;
    var autoCb = $('#form-product [name="StockItem[auto]"]');
    var codeInput = $('#form-product [name="StockItem[item_code]"]');
    function applyAutoState() {
        if (!isNewRecord) return; // หน้าแก้ไข: รหัสวัสดุถูกล็อกไว้ ไม่ยุ่งกับสวิตช์ auto
        var isAuto = autoCb.is(':checked');
        if (isAuto) {
            codeInput.prop('disabled', true).val('').attr('placeholder', 'ระบบจะออกรหัสให้อัตโนมัติ').removeClass('is-invalid');
        } else {
            codeInput.prop('disabled', false).attr('placeholder', 'ระบุรหัสวัสดุ / Barcode');
        }
    }
    autoCb.on('change', applyAutoState);
    applyAutoState();

    // ล้างสถานะ error เมื่อผู้ใช้เริ่มแก้ไข
    $('#form-product').on('input change', '[name="StockItem[item_name]"], [name="StockItem[item_code]"]', function () {
        $(this).removeClass('is-invalid');
    });
    $('#form-product').on('change', '[name="StockItem[data_json][unit]"], [name="StockItem[category_id]"]', function () {
        $(this).removeClass('is-invalid').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
    });

    
function getIMG() {
    if ($id > 0) {
        $.ajax({
            type: "GET",
            url: "/filemanager/uploads/show?id=$id",
            dataType: "json",
            success: function (res) {
                console.log('get Avatar success', res);
            }
        });
    }
}
getIMG();

$(".select-photo").click(function() {
    $("#product_file").click();
});

$("#product_file").on("change", function() {
    var fileInput = $(this)[0];
    if (fileInput.files && fileInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $(".product-img").attr("src", e.target.result);
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
});

$('#form-product').on('beforeSubmit', function (e) {
    e.preventDefault();
    let form = $(this);

    // ---------- ตรวจสอบข้อมูลที่จำเป็นก่อนบันทึก ----------
    var missing = [];
    function checkField(sel, label) {
        var el = form.find(sel);
        var ok = $.trim((el.val() || '') + '') !== '';
        el.toggleClass('is-invalid', !ok);
        el.next('.select2-container').find('.select2-selection').toggleClass('is-invalid', !ok);
        if (!ok) missing.push(label);
        return ok;
    }
    checkField('[name="StockItem[item_name]"]', 'ชื่อวัสดุ');
    checkField('[name="StockItem[data_json][unit]"]', 'หน่วย');
    checkField('[name="StockItem[category_id]"]', 'หมวดหมู่');
    // รหัสวัสดุ: บังคับเฉพาะกรณีไม่ได้เลือกรหัสอัตโนมัติ
    var codeEl = form.find('[name="StockItem[item_code]"]');
    if (codeEl.length && !codeEl.prop('disabled') && !autoCb.is(':checked')) {
        checkField('[name="StockItem[item_code]"]', 'รหัสวัสดุ');
    }
    if (missing.length) {
        Swal.fire({
            icon: 'warning',
            title: 'กรอกข้อมูลไม่ครบ',
            html: 'กรุณากรอก: <strong>' + missing.join(', ') + '</strong>',
            confirmButtonText: 'ตกลง'
        });
        return false;
    }

    Swal.fire({
        title: 'ยืนยันการบันทึก?',
        text: "คุณต้องการบันทึกข้อมูลหรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, บันทึกเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                beforeSend: async function () {
                    await uploadFile();
                },
                success: async function (response) {
                    console.log();
                    
                    if (response.status === 'success') {
                        closeModal();
                        await Swal.fire({
                            title: 'บันทึกสำเร็จ!',
                            text: 'ข้อมูลของคุณถูกบันทึกเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 1000, // ปิดอัตโนมัติใน 3 วินาที
                            confirmButtonText: 'ตกลง'
                        });
                        await $.pjax.reload({ container: response.container, history: false, replace: false, timeout: false });
                    }

                    if (response.status === 'error') 
                    {
                        Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: response.msg,
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง',
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                }
            });
        }
    });

    return false;
});


    function uploadFile(){
        formdata = new FormData();
        var checkFile = $("input[id='product_file']").prop('files').length;
        console.log(checkFile);
        
        
        if($("input[id='product_file']").prop('files').length > 0)
        {
            file = $("input[id='product_file']").prop('files')[0];
            formdata.append("product_item", file);
            formdata.append("id", 1);
            formdata.append("ref", '$ref');
            formdata.append("name", 'product_item');
            

            console.log(file);
            $.ajax({
                url: '/filemanager/uploads/single',
                type: "POST",
                data: formdata,
                processData: false,
                contentType: false,
                success: function (res) {
                    return true;
                }
            });
        }
    }


JS;
    $this->registerJS($js);
    ?>