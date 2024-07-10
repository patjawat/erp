<?php

use kartik\widgets\ActiveForm;
use softark\duallistbox\DualListbox;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Warehouse $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="warehouse-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

    <div class="row">
        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
            <div class="row">
                <div class="col-6">
                    <?= $form->field($model, 'warehouse_name')->textInput(['maxlength' => true]) ?>

                </div>
                <div class="col-6">
                    <?= $form->field($model, 'warehouse_code')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        
            <?php
                echo $form->field($model, 'warehouse_type')->widget(Select2::classname(), [
                    'data' => ['MAIN' => 'คลังหลัก', 'SUB' => 'คลังย่อย', 'BRANCH' => 'คลังนอก'],
                    'options' => ['placeholder' => 'Select a state ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);
            ?>
           

        </div>
        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
            <input type="file" id="my_file" style="display: none;" />

            <a href="#" class="select-photo">
                <?php if ($model->isNewRecord): ?>
                <?php echo Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar-profile h-40 object-cover rounded img-fluid']) ?>
                <?php else: ?>

                <?= Html::img($model->ShowImg(), ['class' => 'avatar-profile avatar-profile h-40 object-cover rounded img-fluid']) ?>
                <?php endif ?>
            </a>
            <small>อัตรส่วนที่เหมาะสม 7360 × 4912</small>
        </div>
        <div class="col-12">
            <div
                class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded mb-3 d-flex justify-content-between mt-3">
                <h5><i class="fa-solid fa-circle-info text-primary"></i> กำหนดผู้รับผิดชอบคลัง</h5>

            </div>
            <?php
                echo DualListbox::widget([
                    'model' => $model,
                    'attribute' => 'data_json[officer]',
                    'items' => $model->listUserstore(),
                    'options' => [
                        'multiple' => true,
                        'size' => 8,
                    ],
                    'clientOptions' => [
                        'moveOnSelect' => false,
                        'selectedListLabel' => 'เจ้าหน้าที่รับผิดชอบคลัง',
                        'nonSelectedListLabel' => '(กำหนดให้สิทธ์ warehouse ก่อนถึงจะปรากฏ)',
                    ],
                ]);
            ?>
        </div>
    </div>

    <?= $form->field($model, 'is_main')->hiddenInput()->label(false); ?>
    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => 50])->label(false); ?>
    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

<?php
$ref = $model->ref;
$urlUpload = Url::to('/filemanager/uploads/single');
$js = <<< JS

                    \$('#form').on('beforeSubmit', function (e) {
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

                \$(".select-photo").click(function() {
                    \$("input[id='my_file']").click();
                });


                \$('#my_file').change(function (e) { 
                    e.preventDefault();
                    formdata = new FormData();
                    if(\$(this).prop('files').length > 0)
                    {
                        file =\$(this).prop('files')[0];
                        formdata.append("warehouse", file);
                        formdata.append("id", 1);
                        formdata.append("ref", '$ref');
                        formdata.append("name", 'warehouse');

                        console.log(file);
                        \$.ajax({
                            url: '$urlUpload',
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            success: function (res) {
                                console.log(res);
                                \$('.avatar-profile').attr('src', res.img)
                                // success('แก้ไขภาพ')
                            }
                        });
                    }
                });
                
    JS;
$this->registerJS($js)
?>
