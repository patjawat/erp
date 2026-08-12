<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\purchase\models\Contract;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\ContractSearch $model */

$form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => ['data-pjax' => 1, 'id' => 'contract-search-form'],
]);
?>
<div class="row g-2 align-items-end">
    <div class="col-lg-4 col-md-6">
        <?= $form->field($model, 'q')->textInput([
            'placeholder' => 'ค้นหาชื่อสัญญา / เลขที่ / คู่สัญญา / e-GP',
        ])->label('ค้นหา') ?>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
        <?= $form->field($model, 'thai_year')->dropDownList(Contract::listThaiYear(), [
            'prompt' => 'ทุกปี',
        ]) ?>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
        <?= $form->field($model, 'status')->dropDownList(Contract::statusList(), [
            'prompt' => 'ทุกสถานะ',
        ]) ?>
    </div>
    <div class="col-lg-2 col-md-6">
        <?= $form->field($model, 'contract_type')->dropDownList(Contract::typeList(), [
            'prompt' => 'ทุกประเภท',
        ]) ?>
    </div>
    <div class="col-lg-2 col-md-6">
        <div class="d-flex gap-2 mb-3">
            <?= Html::submitButton('<i class="bi bi-search me-1"></i>ค้นหา', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('ล้าง', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>
<?= Html::activeHiddenInput($model, 'flag') ?>
<?php ActiveForm::end(); ?>
