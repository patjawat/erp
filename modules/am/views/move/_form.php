<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */


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
    
?>

<div class="asset-detail-form">

 <?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/am/move/validator'],
])
?>

    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'asset_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
           
        </div>
    </div>




    <div class="row g-4">
        <!-- เลือกครุภัณฑ์ -->
        <div class="col-12">
            <label class="form-label fw-bold">1. เลือกครุภัณฑ์ที่ต้องการย้าย</label>
            <div class="asset-preview-box d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded p-3 me-3 text-primary">
                    <i class="bi bi-upc-scan fs-3"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold"><?= $model->asset?->asset_name ?? '-' ?></div>
                    <div class="small text-muted">หมายเลขครุภัณฑ์: <?= $model->asset?->code ?? '-' ?> | สถานที่: <?= $model->asset?->data_json['location'] ?? '-' ?> </div>
                </div>

                 <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('หมายเลขทรัพย์สิน/ครุภัณฑ์') ?>

                <!-- <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-search me-1"></i> เลือกใหม่
                </button> -->
            </div>
        </div>

        <!-- ปลายทาง & วันที่ -->
        <div class="col-md-6">
            <?= $form->field($model, 'data_json[location]')->textInput(['placeholder' => 'ระบุสถานที่ปลายทาง'])->label('2. สถานที่ปลายทาง') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'ระบุบวันที่ต้องการย้าย'])->label('3. วันที่ต้องการย้าย'); ?>
        </div>

        <!-- เหตุผลการย้าย -->
        <div class="col-12">
            <?= $form->field($model, 'data_json[reason]')->radioList(
                [
                    'ตรวจสอบครุภัณฑ์ประจำปี' => 'ตรวจสอบครุภัณฑ์ประจำปี',
                    'ส่งซ่อม/บำรุงรักษา' => 'ส่งซ่อม/บำรุงรักษา',
                    'ย้ายที่ปฏิบัติงาน' => 'ย้ายที่ปฏิบัติงาน',
                    'อื่นๆ' => 'อื่นๆ',
                ],
                [
                    'item' => function ($index, $label, $name, $checked, $value) {
                        $id = 'reason-' . $index;
                        $isChecked = $checked ? 'checked' : '';
                        return "
                <input type='radio' class='btn-check' name='{$name}' id='{$id}' value='{$value}' autocomplete='off' {$isChecked}>
                <label class='btn btn-outline-secondary btn-sm rounded-pill px-3 mb-2' for='{$id}'>{$label}</label>
            ";
                    },
                    'class' => 'd-flex flex-wrap gap-2 mb-3' // wrapper class
                ]
            )->label('4. เหตุผลการเคลื่อนย้าย', ['class' => 'form-label fw-bold']) ?>
            <?= $form->field($model, 'data_json[remask]')->textArea(["rows" => 3, "placeholder" => "ระบุรายละเอียดเพิ่มเติม (ถ้ามี) เช่น ชื่อโครงการ หรือเลขที่คำสั่ง..."])->label('--เพิ่มเติม--') ?>


        </div>

        <!-- ผู้อนุมัติ -->
        <div class="col-md-12">

                     <?php
                            $url = Url::to(['/hr/leave/get-leader-approve']);
                            $leader = empty($model->data_json['leader_id']) ? '' : Employees::findOne(['id' => $model->data_json['leader_id']])->fullname;
                            echo $form->field($model, 'data_json[leader_id]')->widget(Select2::classname(), [
                                // 'data' => $model->ListEmployees(),
                                'initValueText' => $leader,
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                     'dropdownParent' => '#main-modal',
                                    'minimumInputLength' => 1,
                                    'language' => [
                                        'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                                    ],
                                    'ajax' => [
                                        'url' => $url,
                                        'dataType' => 'json',
                                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                    ],
                                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                    'templateResult' => new JsExpression('function(city) { return city.text; }'),
                                    'templateSelection' => new JsExpression('function (city) { return city.text; }'),
                                ],
                                'pluginEvents' => [

                                ]
                            ])->label('5. ผู้อนุมัติ');
                            ?>
                        
        </div>
    </div>


    <?= $model->Upload() ?>


    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS
 thaiDatepicker('#assetdetail-date_start')
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>