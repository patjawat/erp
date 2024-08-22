<?php

use kartik\widgets\ActiveForm;
use softark\duallistbox\DualListbox;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\db\Expression;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\web\View;
use yii\web\JsExpression;
/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

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
.col-form-label {
    text-align: end;
}

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
</style>
<div class="warehouse-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

    <div class="row">
        <div class="col-xl-7 col-lg-7 col-md-12 col-sm-12">
            <div class="d-flex justify-content-between gap-1">
                <?=$form->field($model, 'warehouse_type')->radioList(['MAIN' => 'คลังหลัก', 'SUB' => 'คลังย่อย'],['custom' => true,'inline' => true]);?>

                <?php
                echo $form->field($model, 'category_id')->widget(Select2::classname(), [
                    'data' => $model->ListGroup(),
                    'options' => ['placeholder' => 'Select a state ...'],
                    'pluginOptions' => [
                        'allowClear' => true                    ],
                ])->label('คลังหลัก');
            ?>

            </div>
            <div class="row">
                <div class="col-7">
                    <?= $form->field($model, 'warehouse_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-5">

                    <?= $form->field($model, 'warehouse_code')->textInput(['maxlength' => true]) ?>
                </div>
            </div>



            <?php
try {
    //code...
    $initEmployee =  Employees::find()->where(['id' => $model->data_json['checker']])->one()->getAvatar(false);
} catch (\Throwable $th) {
    $initEmployee = '';
}
        echo $form->field($model, 'data_json[checker]')->widget(Select2::classname(), [
            'initValueText' => $initEmployee,
            'options' => ['placeholder' => 'เลือก ...'],
            'size' => Select2::LARGE,
            'pluginEvents' => [
                'select2:unselect' => 'function() {
                $("#warehouse-data_json-checker_name").val("")

         }',
                'select2:select' => 'function() {
                var fullname = $(this).select2("data")[0].fullname;
                var position_name = $(this).select2("data")[0].position_name;
                $("#warehouse-data_json-checker_name").val(fullname)
                $("#order-data_json-position_name").val(position_name)
               
         }',
            ],
            'pluginOptions' => [
                'dropdownParent' => '#main-modal',
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
        ])->label('หัวหน้าตรวจสอบ')
    ?>
            <?= $form->field($model, 'data_json[checker_name]')->textInput()->label(false) ?>

            <?php
                // echo $form->field($model, 'warehouse_type')->widget(Select2::classname(), [
                //     'data' => ['MAIN' => 'คลังหลัก', 'SUB' => 'คลังย่อย', 'BRANCH' => 'คลังนอก'],
                //     'options' => ['placeholder' => 'Select a state ...'],
                //     'pluginOptions' => [
                //         'allowClear' => true
                //     ],
                // ]);
                  
            ?>





        </div>
        <div class="col-xl-5 col-lg-5 col-md-12 col-sm-12">
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


        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">กำหนดผู้รับผิดชอบคลัง</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">กำหนดประเภทที่รับเข้า</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">กำหนดหน่วยงานเบิก</button>
  </li>
</ul>
<div class="tab-content" id="pills-tabContent">
  <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">


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
  <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">

  <div
                class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded mb-3 d-flex justify-content-between mt-3">
                <h5><i class="fa-solid fa-circle-info text-primary"></i> กำหนดประเภทที่รับเข้า</h5>

            </div>
            <?php
                echo DualListbox::widget([
                    'model' => $model,
                    'attribute' => 'data_json[item_type]',
                    'items' => $model->ListOrderType(),
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



  </div>
  <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab" tabindex="0">



  <?=$form->field($model, 'department')->widget(\kartik\tree\TreeViewInput::className(), [
    'query' => Organization::find()->addOrderBy('root, lft'),
    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
    'fontAwesome' => true,
    'asDropdown' => true,
    'multiple' => true,
    'options' => ['disabled' => false],
])->label('หน่วยงานภายในตามโครงสร้าง');?>



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