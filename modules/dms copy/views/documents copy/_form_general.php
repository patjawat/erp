<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
// use softark\duallistbox\DualListbox;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

// use iamsaint\datetimepicker\DateTimePickerAsset::register($this);

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?= $form->field($model, 'ref')->textInput(['maxlength' => 50])->label(false); ?>
<div class="row">

                    <div class="col-6">

                        <?php
                        echo $form->field($model, 'document_type')->widget(Select2::classname(), [
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
                    <div class="col-3">

                        <?= $form->field($model, 'doc_regis_number')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-3">
                        <?= $form->field($model, 'thai_year')->textInput(['maxlength' => true]) ?>
                    </div>

                    <div class="col-6">
                        <div class="d-flex gap-2">
                            <?php echo $form->field($model, 'doc_receive_date')->widget(Datetimepicker::className(), [
                                            'options' => [
                                                'timepicker' => false,
                                                'datepicker' => true,
                                                'mask' => '99/99/9999',
                                                'lang' => 'th',
                                                'yearOffset' => 543,
                                                'format' => 'd/m/Y',
                                            ],
                                        ])->label('ลงรับวันที่') ?>
                            <?= $form->field($model, 'doc_time')->widget(\yii\widgets\MaskedInput::className(), [
                                    'mask' => '99:99',
                                ]) ?>

                        </div>
                    </div>

                    <div class="col-6">
                        <?php
                        echo $form->field($model, 'document_org')->widget(Select2::classname(), [
                            'data' => $model->ListDocumentOrg(),
                            'options' => ['placeholder' => 'เลือกหน่วยงาน'],
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
                        ])->label('จากหน่วยงาน');
                        ?>
                    </div>

                    <div class="col-6">
                        <?php
                        echo $form->field($model, 'secret')->widget(Select2::classname(), [
                            'data' => $model->DocSecret(),
                            'options' => ['placeholder' => 'เลือกชั้นความลับ'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label('ชั้นความลับ');
                        ?>
                    </div>
                    <div class="col-6">
                        <?php
                    echo $form->field($model, 'doc_speed')->widget(Select2::classname(), [
                        'data' => $model->DocSpeed(),
                        'options' => ['placeholder' => 'เลือกชั้นความลับ'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ])->label('ชั้นเร็ว');
                    ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'doc_number')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-3">
                        <?php
                        echo $form->field($model, 'doc_date')->widget(Datetimepicker::className(), [
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y'
                            ],
                        ])->label('วันที่หนังสือ')
                        ?>
                    </div>
                 
                    <div class="col-3">
                    <?php echo $form->field($model, 'doc_expire')->widget(Datetimepicker::className(), [
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y',
                            ],
                        ])->label('วันหมดอายุ') ?>
                        </div>

                    <div class="col-12">
                        <?= $form->field($model, 'topic')->textArea(['rows' => 2]) ?>
                    </div>

                </div>




          

           

                <div class="d-flex justify-content-center align-top align-items-center mt-5">
                    <div class="form-group mt-3 d-flex justify-content-center gap-3">
                        <?php echo Html::button('<i class="fa-solid fa-chevron-left"></i> ย้อนกลับ', [
                            'class' => 'btn btn-secondary rounded-pill shadow me-2',
                            'onclick' => 'window.history.back()',
                        ]); ?>
                        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
                    </div>
                </div>