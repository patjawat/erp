<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetType $model */
/** @var yii\widgets\ActiveForm $form */
$id = isset($model->id) ? intval($model->id) : 0;
$ref = isset($ref) ? Html::encode($ref) : '';
?>

<style>
.modal-body {
    background-color: #f1f5f9;
}
</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-product',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/sm/product/createvalidator'],
]); ?>

<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

<div class="row">
    <div class="col-4">
        <input type="file" id="product_file" style="display: none;" />
        <div class="d-flex justify-content-center">
            <a href="#" class="select-photo">
                <?= Html::img($model->ShowImg(), ['class' => 'product-img object-fit-cover rounded', 'style' => 'max-width:100%;height: 219px;']) ?>
            </a>
        </div>
    </div>
    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-circle-exclamation"></i> ข้อมูลรายการ</h6>
                    <?= $form->field($model, 'auto')->checkbox(['custom' => true, 'switch' => true, 'checked' => true])->label('รหัสอัตโนมัติ'); ?>
                </div>
                <div class="row">
                   

                    
                    <div class="col-8">
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
                        
                            ])->label('ประเภท') ?>
                        <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'ระบุชื่อสินค้า/บริการ'])->label('ชื่อรายการ') ?>
                    </div>
                    <div class="col-4">
                    <?php if ($model->isNewRecord): ?>
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'ระบุรหัสสินค้า/barcode'])->label('รหัสสินค้า') ?>
                    <?php else: ?>
                        <div class="mb-3 highlight-addon field-product-code">
                            <label class="form-label has-star" for="product-code">รหัสสินค้า</label>
                            <input type="text" class="form-control" name="Product[code]" value="<?= Html::encode($model->code) ?>" disabled>
                        </div>
                    <?php endif ?>
                    
                        <?php
                                echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
                                    'data' => $model->listUnit(),
                                    'options' => ['placeholder' => 'ระบุหน่วยนับหลัก...'],
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
                    
                    
                </div>
            </div>
        </div>
    </div>
</div>

<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'submit']) ?>
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
                    form.yiiActiveForm('updateMessages', response, true);
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