<?php

use app\components\AppHelper;
use app\components\SiteHelper;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use kartik\widgets\Typeahead;
use yii\helpers\Html;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;

/** @var yii\web\View $this */
/** @var app\models\Employees $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
.card {
    border: 1px solid #ededed;
    margin-bottom: 30px;
    -webkit-box-shadow: 0 1px 10px rgba(0, 0, 0, 0.2);
    -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
    box-shadow: 0 1px 4px 0px rgb(126 114 114 / 7%);
}

.select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 6px) !important;
}
</style>



<?php $form = ActiveForm::begin([
    'id' => 'form-employee',
]); ?>
<h1>Position</h1>
<div class="row justify-content-center align-items-center g-2">
    <div class="col">
    <?=$form->field($model, 'data_json[birthday]')->widget(Datetimepicker::className(),[
                                        'options' => [
                                            'timepicker' => false,
                                            'datepicker' => true,
                                            'mask' => '99/99/9999',
                                            'lang' => 'th',
                                            'yearOffset' => 543,
                                            'format' => 'd/m/Y', 
                                        ],
                                        ]);
                                    ?>
    </div>
    <div class="col">Column</div>
</div>

<div class="d-flex justify-content-center">
    <?= SiteHelper::btnSave() ?>
</div>

<?php ActiveForm::end(); ?>


