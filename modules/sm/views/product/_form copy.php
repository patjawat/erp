<?php

use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use unclead\multipleinput\MultipleInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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

<?php $form = ActiveForm::begin(['id' => 'form-product']); ?>

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
                <h6><i class="fa-solid fa-circle-exclamation"></i> ข้อมูลรายการ</h5>

                    <div class="row">
                        <div class="col-12">
                            <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'ระบุชื่อสินค้า/บริการ'])->label('ชื่อรายการ') ?>
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
                                ])->label('ประเภท')
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
                                ])->label('หน่วยนับหลัก')
                            ?>
                        </div>

                    </div>
                    <div class="col-5">
                        <?php // $form->field($model, 'data_json[unit_stock]')->textInput(['maxlength' => true, 'placeholder' => ''])->label('หน่วยรับเข้า/จ่ายออก') ?>

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

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <p class="card-text">คุณลักษณะ/ขนาด</p>
                <div class="row">
                    <div class="col-6">
                        <div class="d-flex justify-content-between gap-2">
                            <?= $form->field($model, 'data_json[size]')->textInput(['maxlength' => true, 'placeholder' => 'ระบุรหัส'])->label('ขนาดบรรจุ') ?>
                            <?= $form->field($model, 'data_json[size_unit]')->textInput(['maxlength' => true, 'placeholder' => 'ระบุรหัส'])->label('หน่วยบรรจุ') ?>
                        </div>
                    </div>
                </div>
                <?php

                    // echo $form->field($model, 'unit_items')->widget(MultipleInput::class, [
                    //     'allowEmptyList' => false,
                    //     'enableGuessTitle' => true,
                    //     'addButtonPosition' => MultipleInput::POS_HEADER,
                    //     'addButtonOptions' => [
                    //         'class' => ' btn-sm btn btn-success',
                    //         'label' => '<i class="fa-solid fa-circle-plus"></i>',
                    //     ],
                    //     'removeButtonOptions' => [
                    //         'class' => 'btn-sm btn btn-danger',
                    //         'label' => '<i class="fa-solid fa-xmark"></i>'  // also you can use html code
                    //     ],
                    //     'columns' => [
                    //         [
                    //             'name' => 'id',
                    //             'title' => 'ID',
                    //             'enableError' => true,
                    //             'type' => MultipleInputColumn::TYPE_HIDDEN_INPUT,
                    //         ],
                    //         [
                    //             'type' => \kartik\widgets\Select2::className(),
                    //             'name' => 'title',
                    //             'title' => 'หน่วยนับ',
                    //             'options' => [
                    //                 'data' => $model->listUnit(),
                    //                 'pluginOptions' => [
                    //                     'placeholder' => 'ระบุหน่วยนับ...',
                    //                     'allowClear' => true,
                    //                     'dropdownParent' => '#main-modal',
                    //                 ],
                    //             ],
                    //             'enableError' => true
                    //         ],
                    //         [
                    //             'name' => 'qty',
                    //             'type' => 'textInput',
                    //             'title' => 'จำนวน',
                    //         ],
                    //         [
                    //             'name' => 'barcode',
                    //             'type' => 'textInput',
                    //             'title' => "บาร์โค้ด\u{200B}",
                    //             // 'headerOptions' => [
                    //             //     'class' => 'table-light',// กำหนดสไตล์ให้กับพื้นหลังของ label
                    //             // ],
                    //         ],
                    //     ]
                    // ])->label(false);
                ?>
            </div>
        </div>
    </div>
</div>

<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>
<?php ActiveForm::end(); ?>


<?php
$ref = $model->ref;
$js = <<< JS

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

                    \$("button[id='summit']").on('click', function() {
                        formdata = new FormData();
                        if(\$("input[id='my_file']").prop('files').length > 0)
                        {
                    var file = \$("input[id='my_file']").prop('files')[0];
                            formdata.append("product_item", file);
                            formdata.append("id", 1);
                            formdata.append("ref", '$ref');
                            formdata.append("name", 'product_item');

                            console.log(file);
                            \$.ajax({
                            url: '/filemanager/uploads/single',
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            success: function (res) {
                                            console.log(res)
                                        }
                            });
                                }
                            })

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
                                    //closeModal()
                                    \$("#main-modal-label").html(response.title);
                                    \$(".modal-body").html(response.content);
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