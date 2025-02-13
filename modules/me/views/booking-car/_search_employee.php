<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use yii\bootstrap5\ActiveForm;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<style>
.field-employeessearch-q {
    margin-bottom: 0px !important;
}

.right-setting {
    width: 500px !important;
}
</style>

<?php $form = ActiveForm::begin([
    'action' => ['/me/booking-car/list-employee'],
    'method' => 'get',
    'id' => 'employees-filter',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="row">
    <div class="col-12">
        <div class="position-relative">
            <div class="position-absolute top-50 start-100 translate-middle" style="width:70px">
                <i class="fa-solid fa-magnifying-glass fs-5"></i>
            </div>
            <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...','class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10'])->label(false) ?>
        </div>
    </div>
    <div class="col-3">
        <?php // AppHelper::BtnSave('ค้นหา')?>
    </div>
</div>



<?php ActiveForm::end(); ?>