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
        echo $form->field($model, 'data_json[unit_name]')->widget(Select2::classname(), [
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
    <div class="col-3">
        <?= $form->field($model, 'max_qty')->textInput([
            'type' => 'number',
            'maxlength' => true,
            'placeholder' => 'ระบุจำนวนสูงสุด',
            'min' => 0
        ])->label('จำนวนสูงสุด') ?>
    </div>

    <div class="col-3">
        <?= $form->field($model, 'min_qty')->textInput([
            'type' => 'number',
            'maxlength' => true,
            'placeholder' => 'ระบุจำนวนต่ำสุด',
            'min' => 0
        ])->label('จำนวนต่ำสุด') ?>
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
    $js = <<< JS
    if (localStorage.getItem('auto') === '1') {
        $('#product-auto').prop('checked', true);
        $('#product-code').prop('disabled', true).val('อัตโนมัติ');
    }
    
    $('#product-auto').change(function () {
        let isChecked = this.checked;
        localStorage.setItem('auto', isChecked ? '1' : '0');
        $('#product-code').prop('disabled', isChecked).val(isChecked ? 'อัตโนมัติ' : '');
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