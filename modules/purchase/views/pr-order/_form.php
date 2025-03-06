<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use app\modules\hr\models\Employees;
use app\widgets\datepicker\DatepickerThai;
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
:not(.form-floating) > .input-lg.select2-container--krajee-bs5 .select2-selection--single, :not(.form-floating) > .input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 12px) !important;
}
.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #eaecee !important;
    color: #3F51B5;
}
</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-order',
     'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/purchase/pr-order/createvalidator'],
]); ?>

<div class="row">
    <div class="col-6">

    <?=$form->field($model, 'data_json[pr_create_date]')->textInput(['placeholder' => 'เลือกวันที่ขอซื้อ'])->label('วันที่ขอซื้อ');
                ?>

</div>
        <div class="col-6">
        <?=$form->field($model, 'data_json[due_date]')->textInput(['placeholder' => 'เลือกวันที่ต้องการ'])->label('วันที่ต้องการ');?>


    </div>
</div>



<?php
        echo $form->field($model, 'vendor_id')->widget(Select2::classname(), [
            'data' => $model->ListVendor(),
            'options' => ['placeholder' => 'เลือกบริษัทแนะนำ)'],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
            'pluginEvents' => [
                "select2:unselecting" => "function() { 
                    $('#order-data_json-vendor_address').val('')
                    $('#order-data_json-vendor_phone').val('')
                    $('#order-data_json-vendor_tax').val('')
                    $('#order-data_json-account_name').val('')
                    $('#order-data_json-account_number').val('')
                    $('#order-data_json-contact_name').val('')
                    $('#order-data_json-contact_position').val('')
                }",
                'select2:select' => "function(result) { 
                                    var data =  $(this).select2('data')[0].text;
                                      $('#order-data_json-vendor_name').val(data)
                                    $.ajax({
                                        type: 'get',
                                        url: '/depdrop/get-vendor',
                                        data:{id:$(this).val()},
                                        dataType:'json',
                                        success: function (res) {
                                            $('#order-data_json-vendor_address').val(res.data_json.address)
                                            $('#order-data_json-vendor_phone').val(res.data_json.phone)
                                            $('#order-data_json-vendor_tax').val(res.code)
                                            $('#order-data_json-account_name').val(res.data_json.account_name)
                                            $('#order-data_json-account_number').val(res.data_json.account_number)
                                            $('#order-data_json-contact_name').val(res.data_json.contact_name)
                                            $('#order-data_json-contact_position').val(res.data_json.contact_position)
                                        }
                                    });

                                }",
            ]
        ])->label('บริษัทแนะนำ');
    ?>

<?php
try {
    //code...
    $initEmployee =  Employees::find()->where(['id' => $model->data_json['leader1']])->one()->getAvatar(false);
} catch (\Throwable $th) {
    $initEmployee = '';
}
        echo $form->field($model, 'data_json[leader1]')->widget(Select2::classname(), [
            'initValueText' => $initEmployee,
            'options' => ['placeholder' => 'เลือก ...'],
            'size' => Select2::LARGE,
            'pluginEvents' => [
                'select2:unselect' => 'function() {
                $("#order-data_json-board_fullname").val("")

         }',
                'select2:select' => 'function() {
                var fullname = $(this).select2("data")[0].fullname;
                var position_name = $(this).select2("data")[0].position_name;
                $("#order-data_json-board_fullname").val(fullname)
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
        ])->label('ผู้เห็นชอบ')
    ?>

<?= $form->field($model, 'data_json[comment]')->textArea()->label('หมายเหตุ') ?>

<?= $form->field($model, 'data_json[vendor_address]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[vendor_phone]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[vendor_tax]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[account_name]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[account_number]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[contact_name]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[contact_position]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[item_type]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[leader1_fullname]')->hiddenInput(['value' => $employee->leaderUser()['leader1_fullname']])->label(false) ?>
<?= $form->field($model, 'data_json[department]')->hiddenInput(['value' => $model->getUserReq()['department']])->label(false) ?>
<?= $form->field($model, 'data_json[product_type_name]')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'status')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_leader_confirm]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_confirm_comment]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_director_confirm]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_director_comment]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_confirm_2]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_officer_checker]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>


<?php ActiveForm::end(); ?>



<?php
    $ref = $model->ref;
    $urlUpload = Url::to('/filemanager/uploads/single');
    $getAvatar = Url::to(['/filemanager/uploads/show','id' => 1]);
    $js = <<<JS



    thaiDatepicker('#order-data_json-pr_create_date,#order-data_json-due_date')
    $('#form-order').on('beforeSubmit', function (e) {
        var form = \$(this);
        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกขออนุมัติจัดซื้อจัดจ้าง!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            beforLoadModal()
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    form.yiiActiveForm('updateMessages', response, true);
                    if(response.status == 'success') {
                        // closeModal()
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
$this->registerJS($js, View::POS_END)
    ?>