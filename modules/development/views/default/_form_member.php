<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;

/** @var app\modules\development\models\DevelopmentDetail $model */

$formatJs = <<<'JS'
var formatRepo = function (repo) {
    if (repo.loading) return repo.avatar;
    var markup = '<div class="row"><div class="col-12"><span>' + repo.avatar + '</span></div></div>';
    if (repo.description) markup += '<p>' + repo.avatar + '</p>';
    return '<div style="overflow:hidden;">' + markup + '</div>';
};
var formatRepoSelection = function (repo) { return repo.avatar || repo.text || ''; }
JS;
$this->registerJs($formatJs, View::POS_HEAD);

$resultsJs = <<<'JS'
function (data, params) {
    params.page = params.page || 1;
    return { results: data.results, pagination: { more: (params.page * 30) < data.total_count } };
}
JS;
?>
<style>
.avatar-form .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 2px); line-height: 1.5; padding: 6px;
}
.avatar-form .avatar { height: 1.9rem !important; width: 1.9rem !important; }
.avatar-form .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 2px); line-height: 1.5; padding: 0.1rem 0.1rem 0.5rem 0.1rem;
}
</style>

<div class="order-form">
<?php $form = ActiveForm::begin(['id' => 'form-member']); ?>
<div class="avatar-form">
    <?php
    $initEmployee = null;
    if (!empty($model->emp_id)) {
        $emp = Employees::findOne($model->emp_id);
        $initEmployee = $emp ? $emp->getAvatar(false) : null;
    }
    echo $form->field($model, 'emp_id')->widget(Select2::classname(), [
        'initValueText' => $initEmployee,
        'id' => 'boardId',
        'options' => ['placeholder' => 'เลือก ...'],
        'size' => Select2::LARGE,
        'pluginEvents' => [
            'select2:unselect' => 'function() { $("#developmentdetail-data_json-emp_fullname").val(""); $("#developmentdetail-data_json-emp_position").val(""); }',
            'select2:select' => 'function() {
                var d = $(this).select2("data")[0];
                var fullname = d && d.fullname ? d.fullname : "";
                var position_name = d && d.position_name_text ? d.position_name_text : "";
                $("#developmentdetail-data_json-emp_fullname").val(fullname);
                $("#developmentdetail-data_json-emp_position").val(position_name);
            }',
        ],
        'pluginOptions' => [
            'dropdownParent' => isset($modal) && $modal ? '#main-modal' : null,
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
    ])->label('ชื่อ');
    ?>
</div>
<?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'development_id')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'data_json[emp_fullname]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[emp_position]')->hiddenInput()->label(false) ?>

<div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill', 'id' => 'summit']) ?>
    <?php if (!$model->isNewRecord): ?>
    <?= Html::a('<i class="bi bi-trash"></i> ลบ', ['/development/default/delete-member', 'id' => $model->id], ['class' => 'btn btn-outline-danger rounded-pill delete-item']) ?>
    <?php endif; ?>
</div>
<?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<'JS'
handleFormSubmit('#form-member', null, function(response) {
    if (response && response.redirect) {
        window.location.href = response.redirect;
    } else {
        location.reload();
    }
});
JS;
$this->registerJs($js, View::POS_END);
?>
