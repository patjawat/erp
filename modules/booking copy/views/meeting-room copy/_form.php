<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;


$me = UserHelper::GetEmployee();
$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
        // console.log(repo);
        var markup =
    '<div class="row">' +
        '<div class="col-12">' +
            '<span>' + repo.avatar + '</span>' +
        '</div>' +
    '</div>';
        if (repo.description) {
          markup += '<p>' + repo.avatar + '</p>';
        }
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) {
        return repo.avatar || repo.avatar;
    }
    JS;

// Register the formatting script
$this->registerJs($formatJs, View::POS_HEAD);

// script to parse the results into the format expected by Select2
$resultsJs = <<< JS
    function (data, params) {
        params.page = params.page || 1;
        return {
            results: data.results,
            pagination: {
                more: (params.page * 30) < data.total_count
            }
        };
    }
    JS;
    
/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
.select2-container--krajee-bs5 .select2-selection--single .select2-selection__placeholder {
    font-weight: 300;
    font-size: medium;
}

.input-lg.select2-container--krajee-bs5 .select2-selection--single,
:not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    padding: .2rem 0.6rem !important;
}


.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #d4e1f2;
    color: #111111;
}
</style>

<div class="room-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

    <div class="row">
        <div class="col-3">

            <?= $form->field($model, 'code')->textInput(['maxlength' => true,])->label('รหัสห้องประชุม') ?>

        </div>
        <div class="col-9">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true,])->label('ชื่อห้องประชุม') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'data_json[location]')->textInput([])->label('สถานที่ตั้ง') ?>
            <?= $form->field($model, 'data_json[advance_booking]')->textInput(['type' => 'number',])->label('จองล่วงหน้า (วัน)') ?>
        </div>
        <div class="col-6">

            <?php
                        try {
                            //code...
                            if($model->isNewRecord){
                                $initEmployee =  Employees::find()->where(['id' => $model->data_json['owner']])->one()->getAvatar(false);    
                            }else{
                                $initEmployee =  Employees::find()->where(['id' => $model->data_json['owner']])->one()->getAvatar(false);    
                            }
                            // $initEmployee =  Employees::find()->where(['id' => $model->Approve()['leader']['id']])->one()->getAvatar(false);
                        } catch (\Throwable $th) {
                            $initEmployee = '';
                        }

                        echo $form->field($model, 'data_json[owner]')->widget(Select2::classname(), [
                            'initValueText' => $initEmployee,
                            'options' => ['placeholder' => 'เลือกรายการ...',],
                            'size' => Select2::LARGE,
                            'pluginEvents' => [
                                'select2:unselect' => 'function() {
                                    $("#order-data_json-board_fullname").val("")
                                }',
                                'select2:select' => 'function() {
                                    var fullname = $(this).select2("data")[0].fullname;
                                }',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'dropdownParent' => '#main-modal',
                                'minimumInputLength' => 1,
                                'ajax' => [
                                    'url' => Url::to(['/depdrop/employee-by-id']),
                                    'dataType' => 'json',
                                    'delay' => 250,
                                    'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                                    'processResults' => new JsExpression($resultsJs),
                                    'cache' => true,
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                                'templateResult' => new JsExpression('formatRepo'),
                            ],
                        ])->label('ผู้รับผิดชอบ');
                        ?>

            <?= $form->field($model, 'data_json[seat_capacity]')->textInput(['type' => 'number',])->label('ที่นั่ง') ?>
        </div>
    </div>

    <?= $form->field($model, 'data_json[room_accessory]')->widget(Select2::classname(), [
                        'data' => $model->ListAccessory(),
                        'options' => ['placeholder' => 'เลือกหน่วยงาน', 'multiple' => true],
                        'pluginOptions' => [
                            'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                        ],
                        'pluginEvents' => [
                            'select2:select' => 'function(result) { 
                                            }',
                            'select2:unselecting' => 'function() {

                                            }',
                        ],
                
                    ])->label('รายการอุปกรณ์') ?>

    <?= $form->field($model, 'description')->textArea(['maxlength' => true])->label('หมายเหตุ'); ?>

    <?= $form->field($model, 'active')->checkbox(['custom' => true,'switch' => true, 'checked' => $model->active == 1 ? true : false])->label('เปิดใช้งาน') ?>
    <?=$model->upload()?>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <div class="form-group mt-3 d-flex justify-content-center gap-3">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"> <i
                class="fa-regular fa-circle-xmark"></i> ปิด</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS

      $('#form').on('beforeSubmit', function (e) {
        var form = $(this);
        
        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกข้อมูล!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    console.log(response);
                    
                    if(response.status == 'success') {
                        closeModal()
                        success()
                        await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });

        }
        });
        return false;
    });

JS;
$this->registerJS($js, View::POS_END);
?>