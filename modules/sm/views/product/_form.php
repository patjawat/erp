<?php

use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetType $model */
/** @var yii\widgets\ActiveForm $form */
?>



<?php $form = ActiveForm::begin([
    'id' => 'form-product',
    // 'type' => ActiveForm::TYPE_HORIZONTAL,
    // 'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL]
]); ?>
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
<div class="row">
    <div class="col-4">
        <input type="file" id="my_file" style="display: none;" />
        <div class="d-flex justify-content-center">
            <a href="#" class="select-photo">
                <?= Html::img($model->ShowImg(), ['class' => 'avatar-profile object-fit-cover rounded', 'style' => 'max-width:100%;']) ?>
            </a>
        </div>
    </div>
    <div class="col-8">
        <div class="row">
            <div class="col-8">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'ระบุชื่อครุภัณฑ์'])->label('ชื่อรายการ') ?>
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
            <div class="col-4">
                <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'ระบุรหัส'])->label('รหัส') ?>
                <?php
                    echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
                        'data' => $model->listUnit(),
                        'options' => ['placeholder' => 'ระบุหน่วยนับ...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                        ],
                    ])->label('หน่วยนับ')
                ?>
            </div>
        </div>
        <div class="col-12">
            
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