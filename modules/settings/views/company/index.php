<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use yii\widgets\MaskedInput;
use app\components\AppHelper;
use kartik\widgets\Typeahead;
use app\components\SiteHelper;
use kartik\editors\Summernote;
use app\modules\hr\models\Employees;
use iamsaint\datetimepicker\Datetimepicker;
use app\modules\filemanager\components\FileManagerHelper;

$this->title = 'ตั้งค่าองค์กร';

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
<style>
    .select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
        background-color: #eaecee !important;
        color: #fff;
    }

    :not(.form-floating)>.input-lg.select2-container--krajee-bs5 .select2-selection--single,
    :not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
        height: calc(2.875rem + 12px) !important;
    }

    .select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
        background-color: #eaecee !important;
        color: #3F51B5;
    }
    .field-director_type{
        margin-bottom: 3px !important;
    }
</style>

<!-- <h1 class="text-center"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h1> -->

<div class="card">
    <div class="card-body">

        <h4 class="card-title"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h4>
        
        <div class="d-flex justify-content-center">
            
            <input type="file" id="my_file" style="display: none;" />
            <a href="#" class="select-photo">
                <?php if ($model->isNewRecord): ?>
                    <?= Html::img('@web/img/placeholder-img.jpg', ['class' => 'object-fit-cover rounded shadow', 'style' => 'margin-top: 25px;max-width: 135px;max-height: 135px;    width: 100%;height: 100%;']) ?>
                    <?php else: ?>
                        
                        <?php echo Html::img($model->logo(),['class' => 'object-fit-cover rounded','style' =>'margin-top: 25px;max-width: 135px;max-height: 135px;    width: 100%;height: 100%;']) 
                ?>
            <?php endif ?>
        </a>
    </div>

        <?php $form = ActiveForm::begin(['id' => 'form-company']); ?>
        <?= $form->field($model, 'data_json[leader_fullname]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[leader_position]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[director_position]')->hiddenInput()->label(false) ?>

        <div class="row d-flex justify-content-center mt-5">
            <div class="col-4">
                <div class="d-flex justify-content-between gap-3">
                    <div class="w-50">
                        <?= $form->field($model, 'data_json[company_name]')->textInput()->label('ชื่อหน่วยงาน') ?>
                    </div>
                    <div class="w-50">
                        <?= $form->field($model, 'data_json[doc_number]')->textInput()->label('เลขที่หนังสือ') ?>
                    </div>

                </div>
                <?= $form->field($model, 'data_json[phone]')->textInput()->label('โทรศัพท์') ?>
                <div class="d-flex justify-content-between gap-3">
                    <?php //  $form->field($model, 'data_json[director_name]')->textInput()->label('ผู้อำนวยการ') 
                    ?>
                    <?php //  $form->field($model, 'data_json[director_position]')->textInput()->label('ตำแหน่ง') 
                    ?>
                </div>
                <?= $form->field($model, 'data_json[website]')->textInput()->label('เว็บไซต์') ?>
                <div>
                    <?php
                    echo $form->field($model, 'data_json[director_type]')->radioList(
                        ['ผู้อำนวยการ' => 'ผู้อำนวยการ', 'รักษาการแทน' => 'รักษาการแทน'], 
                        ['custom' => true, 'inline' => true, 'id' => 'director_type']
                    )->label(false);
                    ?>
                                <?php
                try {
                    //code...
                    $initEmployee = isset($model->data_json['director_name']) ? Employees::find()->where(['id' => $model->data_json['director_name']])->one()->getAvatar(false) : null;
                } catch (\Throwable $th) {
                    //throw $th;
                    $initEmployee = '';
                }
                // echo $initEmployee->getAvatar(false);
                echo $form->field($model, 'data_json[director_name]')->widget(Select2::classname(), [
                    'initValueText' => $initEmployee,
                    'id' => 'boardId',
                    'options' => ['placeholder' => 'เลือก ...'],
                    'size' => Select2::LARGE,
                    'pluginEvents' => [
                        'select2:unselect' => 'function() {
                            $("#categorise-data_json-director_position").val("")
                            
                            }',
                            'select2:select' => 'function() {
                                var position_name = $(this).select2("data")[0].position_name_text;
                                $("#categorise-data_json-director_position").val(position_name)
                                
                                }',
                            ],
                            'pluginOptions' => [
                                // 'dropdownParent' => '#main-modal',
                                'allowClear' => true,
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
                            ])->label(false)
                            ?>
        </div>
            </div>
            
            <div class="col-4">
                <?= $form->field($model, 'data_json[province]')->textInput(['placeholder' => 'ระบุ เช่น จังหวัดขอนแก่น'])->label('จังหวัด') ?>
                <?= $form->field($model, 'data_json[hoscode]')->textInput()->label('รหัสโรงพยาบาล') ?>
                <div class="d-flex gap-3">
                    <div class="w-100">
                        <?= $form->field($model, 'data_json[email]')->textInput()->label('อีเมล') ?>
                    </div>
                    <div class="w-100">
                        <?= $form->field($model, 'data_json[fax]')->textInput()->label('แฟกซ์') ?>
                    </div>
                </div>

                <?php
                try {
                    //code...
                    $initEmployee = isset($model->data_json['leader']) ? Employees::find()->where(['id' => $model->data_json['leader']])->one()->getAvatar(false) : null;
                } catch (\Throwable $th) {
                    //throw $th;
                    $initEmployee = '';
                }
                // echo $initEmployee->getAvatar(false);
                echo $form->field($model, 'data_json[leader]')->widget(Select2::classname(), [
                    'initValueText' => $initEmployee,
                    'id' => 'boardId',
                    'options' => ['placeholder' => 'เลือก ...'],
                    'size' => Select2::LARGE,
                    'pluginEvents' => [
                        'select2:unselect' => 'function() {
                            $("#categorise-data_json-leader_fullname").val("")
                            $("#categorise-data_json-leader_position").val("")

         }',
                        'select2:select' => 'function() {
                        console.log($(this).select2("data")[0])
                            var fullname = $(this).select2("data")[0].fullname;
                            var position_name = $(this).select2("data")[0].position_name_text;
                            $("#categorise-data_json-leader_fullname").val(fullname)
                            $("#categorise-data_json-leader_position").val(position_name)
               
         }',
                    ],
                    'pluginOptions' => [
                        // 'dropdownParent' => '#main-modal',
                        'allowClear' => true,
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
                ])->label('หัวหน้าเจ้าหน้าที่')
                ?>
            </div>

            <div class="col-8">
                <?= $form->field($model, 'data_json[address]')->textArea(['style' => 'height:100px'])->label('ที่อยู่') ?>
                <?= $form->field($model, 'data_json[pdpa_url]')->textInput()->label('เงื่อนไขการให้บริการ') ?>
                <?= $form->field($model, 'data_json[active_pdpa]')->checkbox([
                    'custom' => true, 
                    'switch' => true,
                    'checked' => (isset($model->data_json['active_pdpa']) && $model->data_json['active_pdpa'] == "1" ? true : false)
                ])->label('เปิดใช้เงื่อนไขการให้บริการ');?>

            </div>
            
        </div>

        <div class="form-group d-flex justify-content-center">
            <?= AppHelper::BtnSave() ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>


<?php
$ref = $model->ref;
$urlUpload = Url::to('/filemanager/uploads/single');
$js = <<< JS

                \$(".select-photo").click(function() {
                    \$("input[id='my_file']").click();
                });


                \$('#my_file').change(function (e) { 
                    e.preventDefault();
                    formdata = new FormData();
                    if(\$(this).prop('files').length > 0)
                    {
                        file =\$(this).prop('files')[0];
                        formdata.append("logo", file);
                        formdata.append("id", 1);
                        formdata.append("ref", '$ref');
                        formdata.append("name",'logo');

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
                                window.location.reload(true);
                                // success('แก้ไขภาพ')
                            }
                        });
                    }
                });
                
    JS;
$this->registerJS($js)
?>