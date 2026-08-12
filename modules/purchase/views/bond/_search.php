<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\purchase\models\Bond;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\BondSearch $model */

$form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => ['data-pjax' => 1, 'id' => 'bond-search-form'],
]);
?>
<div class="row g-2 align-items-end">
    <div class="col-lg-4 col-md-6">
        <?= $form->field($model, 'q')->textInput([
            'placeholder' => 'ค้นหารายการ / เลขที่หนังสือ / ธนาคาร / ผู้วางหลักประกัน',
        ])->label('ค้นหา') ?>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
        <?= $form->field($model, 'thai_year')->dropDownList(Bond::listThaiYear(), [
            'prompt' => 'ทุกปี',
        ]) ?>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
        <?= $form->field($model, 'status')->dropDownList(Bond::statusList(), [
            'prompt' => 'ทุกสถานะ',
        ]) ?>
    </div>
    <div class="col-lg-2 col-md-6">
        <?= $form->field($model, 'bond_type')->dropDownList(Bond::typeList(), [
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
