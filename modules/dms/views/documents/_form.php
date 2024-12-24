<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>
<div class="card">
    <div class="card-body">

        <div class="row">
 
            <div class="col-6">
                <?php $form = ActiveForm::begin(); ?>
                <div class="row">
                    <div class="col-6">
                        <?php echo $form->field($model, 'document_type')->widget(Select2::classname(), [
                                'data' => $model->ListDocumentType(),
                                'options' => ['placeholder' => 'ประเภทหนังสือ'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    // 'width' => '370px',
                                ],
                                'pluginEvents' => [
                                    'select2:select' => 'function(result) { 
                                            }',
                                    'select2:unselecting' => 'function() {

                                            }',
                                ]
                            ])->label('ประเภทหนังสือ');
                            ?>

                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'thai_year')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <?= $form->field($model, 'doc_regis_number')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-9">
                        <?= $form->field($model, 'topic')->textInput(['maxlength' => true]) ?>

                    </div>

                </div>


                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'doc_number')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'secret')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'doc_speed')->textInput(['maxlength' => true]) ?>
                        
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'doc_date')->textInput(['maxlength' => true]) ?>
                        <div class="d-flex gap-2">
                            <?= $form->field($model, 'doc_receive')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'doc_time')->textInput(['maxlength' => true]) ?>
                        </div>
                        <?= $form->field($model, 'doc_expire')->textInput(['maxlength' => true]) ?>
                    </div>

                </div>
                <?php echo $model->Upload('document')?>










                <div class="form-group">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                </div>

                <?php ActiveForm::end(); ?>



            </div>

        </div>

    </div>
</div>