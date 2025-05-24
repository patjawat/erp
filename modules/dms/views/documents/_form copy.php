<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use softark\duallistbox\DualListbox;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

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
<?php $form = ActiveForm::begin(); ?>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-6">
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
                        <?= $form->field($model, 'doc_regis_number')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <?= $form->field($model, 'thai_year')->textInput(['maxlength' => true]) ?>
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
                        <?php echo $form->field($model, 'doc_date')->widget(Datetimepicker::className(), [
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y',
                            ],
                        ])->label('ตั้งแต่วันที่') ?>
                        <div class="d-flex gap-2">
                            <?= $form->field($model, 'doc_transactions_date')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'doc_time')->textInput(['maxlength' => true]) ?>
                        </div>
                        <?= $form->field($model, 'doc_expire')->textInput(['maxlength' => true]) ?>
                    </div>

                </div>
                <?php echo $model->Upload('document')?>



            </div>
            <div class="col-6">
                <?=$form->field($model, 'data_json[department_tag]')->widget(\kartik\tree\TreeViewInput::className(), [
                    'query' => Organization::find()->addOrderBy('root, lft'),
                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                    'fontAwesome' => true,
                    'asDropdown' => true,
                    'multiple' => true,
                    'options' => ['disabled' => false],
                ])->label('ส่งหน่วยงาน');?>


<div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6><i class="bi bi-person-circle"></i> กรรมการตรวจรับ</h6>
                            </div>
                            <?php // $model->StackComittee()?>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <?=  Html::a('รายการ', [
                            '/purchase/order-item/committee','category_id' => $model->id,'title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับ'
                        ], ['class' => 'open-modal','data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่มกรรมการ', ['/purchase/order-item/create', 'id' => $model->id, 'name' => 'committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        </div>
                    </div>

                    
                <div class="border border-secondary border-opacity-25 p-3 rounded py-5">

              
                    <?php 
                        echo DualListbox::widget([
                            'model' => $model,
                            'attribute' => 'data_json[employee_tag]',
                            'items' => $model->listEmployeeSelectTag(),
                            'options' => [
                                'id' => 'myDualListbox', // กำหนด ID ให้ custom
                                'multiple' => true,
                                'size' => 8,
                                'encode' => false, // รองรับ HTML
                            ],
                            'clientOptions' => [
                                'moveOnSelect' => false,
                                'nonSelectedListLabel' => 'รายชื่อบุคลากร',
                                'selectedListLabel' => 'ส่งต่อบุคลากร',
                            ],
                        ]);
                        ?>

                </div>

                <div class="d-flex justify-content-between align-top align-items-center mt-5">
                    <?= $form->field($model, 'req_approve')->checkbox(['custom' => true, 'switch' => true, 'checked' => $model->req_approve == 1 ? true : false])->label('เสนอผู้อำนวยการ'); ?>


                    <div class="form-group mt-3 d-flex justify-content-center gap-3">
                        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
                    </div>
                </div>


            </div>

        </div>


    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$url = Url::to(['/dms/documents/get-items']);
$js = <<< JS

$(document).ready(function () {
    $.ajax({
        url: '$url',
        type: 'GET',
        success: function (data) {
            var dualListbox = $('#myDualListbox');
            data.forEach(function (item) {
                dualListbox.append('<option value=\"' + item.id + '\">' + item.name + '</option>');
            });
            dualListbox.bootstrapDualListbox('refresh');
           
        },
        error: function (xhr, status, error) {
            console.error('Error loading items:', error);
        }
    });
});

var thaiYear = function (ct) {
            var leap=3;
            var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];
            if(ct){
                var yearL=new Date(ct).getFullYear()-543;
                leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;
                if(leap==2){
                    dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];
                }
            }
            this.setOptions({
                i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,
            })
        };

        $("#documents-doc_date").datetimepicker({
            timepicker:false,
            format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
            lang:'th',  // แสดงภาษาไทย
            onChangeMonth:thaiYear,
            onShow:thaiYear,
            yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
            closeOnDateSelect:true,
        });
        
JS;
$this->registerJS($js);
?>