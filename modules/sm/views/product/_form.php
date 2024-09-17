<?php

use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use unclead\multipleinput\MultipleInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
// use unclead\widgets\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetType $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
.modal-body {
    background-color: #f1f5f9;
}
</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-product',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/sm/product/createvalidator'],
    ]); ?>

<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

<div class="row">
    <div class="col-4">
        <input type="file" id="my_file" style="display: none;" />
        <div class="d-flex justify-content-center">
            <a href="#" class="select-photo">
                <?= Html::img($model->ShowImg(), ['class' => 'avatar-profile object-fit-cover rounded', 'style' => 'max-width:100%;height: 219px;']) ?>
            </a>
        </div>
    </div>
    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-circle-exclamation"></i> ข้อมูลรายการ</h5>
                        <?= $form->field($model, 'auto')->checkbox(['custom' => true, 'switch' => true,'checked' => true])->label('รหัสอัตโนมัติ');?>
                </div>

                <div class="row">
                    <div class="col-8">
                        <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'ระบุชื่อสินค้า/บริการ'])->label('ชื่อรายการ') ?>
                    </div>
                    <div class="col-4">

                        <?php if($model->isNewRecord):?>

                        <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'ระบุรหัสสินค้า/barcode'])->label('รหัสสินค้า') ?>
                        <?php else:?>
                        <div class="mb-3 highlight-addon field-product-code">
                            <label class="form-label has-star" for="product-code">รหัสสินค้า</label>

                            <input type="text" class="form-control" name="Product[code]" maxlength="255"
                                placeholder="<?=$model->code?>" disabled="">

                            <div class="invalid-feedback"></div>

                        </div>
                        <?php endif?>
                    </div>
                    <div class="col-6">
                        <?php
                                echo $form->field($model, 'category_id')->widget(Select2::classname(), [
                                    'data' => $model->listProductType(),
                                    'options' => ['placeholder' => 'ระบุ...'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'dropdownParent' => '#main-modal',
                                    ],
                                ])->label('หมวดหมู่')
                            ?>
                    </div>
                    <div class="col-6">

                        <?php
                                echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
                                    'data' => $model->listUnit(),
                                    'options' => ['placeholder' => 'ระบุหน่วยนับหลัก...'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'dropdownParent' => '#main-modal',
                                    ],
                                    'pluginEvents' => [
                                        'select2:select' => "function(result) { 
                                            var data = \$(this).select2('data')[0].text;
                                            console.log(data)
                                        }",
                                    ]
                                ])->label('หน่วยนับ')
                            ?>
                    </div>

                </div>
                <div class="col-12">
                    <?= $form->field($model, 'data_json[asset_type_name]')->radioList([ 'วัสดุคงทนถาวร' => 'วัสดุคงทนถาวร','วัสดุสิ้นเปลือง' => 'วัสดุสิ้นเปลือง','บริการ' => 'บริการ'], ['custom' => true, 'inline' => true])->label('ประเภท') ?>

                </div>
                <div class="col-5">

                    <?php
                            // echo $form->field($model, 'data_json[unit2]')->widget(Select2::classname(), [
                            //     'data' => $model->listUnit(),
                            //     'options' => ['placeholder' => 'ระบุหน่วยนับหลัก...'],
                            //     'pluginOptions' => [
                            //         'allowClear' => true,
                            //         'dropdownParent' => '#main-modal',
                            //     ],
                            //     'pluginEvents' => [
                            //         'select2:select' => "function(result) {
                            //                 var data = \$(this).select2('data')[0].text;
                            //                 console.log(data)
                            //             }",
                            //     ]
                            // ])->label('หน่วยนับหลัก')
                        ?>
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



        console.log($("#product-auto").val())
            if($("#product-auto").val()){
            $( "#product-auto" ).prop( "checked", localStorage.getItem('auto') == 1 ? true : false );
            $('#product-code').prop('disabled',localStorage.getItem('auto') == 1 ? true : false );

            if(localStorage.getItem('fsn_auto') == true)
            {
                $('#product-code').val('')
            }

            }

            $("#product-auto").change(function() {
                //ตั้งค่า Run Lot Auto
                if(this.checked) {
                    console.log('auto');
                    localStorage.setItem('auto',1);
                    $('#product-code').prop('disabled',this.checked);
                    $('#product-code').val('อัตโนมัติ')

                    $('#product-code').prop('disabled',this.checked);
                    $('#product-code').val('อัตโนมัติ')

                }else{
                    localStorage.setItem('auto',0);
                    $('#product-code').prop('disabled',this.checked);
                    $('#product-code').val('')

                    $('#product-code').prop('disabled',this.checked);
                    $('#product-code').val('')

                    console.log('lot_manual');
                }
            });


        \$(".select-photo").click(function() {
            \$("input[id='my_file']").click();
        });

                    \$("input[id='my_file']").on("change", function() {
                        var fileInput = \$(this)[0];
                        if (fileInput.files && fileInput.files[0]) {
                            var reader = new FileReader();
                            console.log(reader);
                            reader.onload = function(e) {
                            \$(".avatar-profile").attr("src", e.target.result);
                            };
                            reader.readAsDataURL(fileInput.files[0]);
                        }

                    });
                    $('#my_file').change(function (e) { 
                    e.preventDefault();
                    formdata = new FormData();
                    if($(this).prop('files').length > 0)
                    {
                        file =$(this).prop('files')[0];
                        formdata.append("product_item", file);
                        formdata.append("id", 1);
                        formdata.append("ref", '$ref');
                        formdata.append("name", 'product_item');

                        console.log(file);
                        $.ajax({
                            url: '$urlUpload',
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            success: function (res) {
                                console.log(res);
                                $('.avatar-profile').attr('src', res.img)
                                // success('แก้ไขภาพ')
                            }
                        });
                    }
                });
                

                    // \$("button[id='submit']").on('click', function() {
                    //     formdata = new FormData();
                    //     if(\$("input[id='my_file']").prop('files').length > 0)
                    //     {
                    // var file = \$("input[id='my_file']").prop('files')[0];
                    //         formdata.append("product_item", file);
                    //         formdata.append("id", 1);
                    //         formdata.append("ref", '$ref');
                    //         formdata.append("name", 'product_item');

                    //         console.log(file);
                    //         \$.ajax({
                    //         url: '/filemanager/uploads/single',
                    //         type: "POST",
                    //         data: formdata,
                    //         processData: false,
                    //         contentType: false,
                    //         success: function (res) {
                    //                         console.log(res)
                    //                     }
                    //         });
                    //             }
                    //         })

                    \$('#form-product').on('beforeSubmit', function (e) {
                        var form = \$(this);
                        \$.ajax({
                            url: form.attr('action'),
                            type: 'post',
                            data: form.serialize(),
                            dataType: 'json',
                            success: async function (response) {
                                form.yiiActiveForm('updateMessages', response, true);
                                if(response.status == 'success') {
                                    closeModal()
                                    success()
                                    await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                }
                            }
                        });
                        return false;
                    });
    JS;
$this->registerJS($js)
?>