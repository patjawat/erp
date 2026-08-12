<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\modules\purchase\models\Tor;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\TorSearch $model */

$form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => ['data-pjax' => 1, 'id' => 'tor-search-form'],
]);
?>
<div class="row g-2 align-items-end">
    <div class="col-lg-4 col-md-6">
        <?= $form->field($model, 'q')->textInput([
            'placeholder' => 'ค้นหาชื่อโครงการ / เลขที่ / e-GP',
        ])->label('ค้นหา') ?>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
        <?= $form->field($model, 'thai_year')->dropDownList(Tor::listThaiYear(), [
            'prompt' => 'ทุกปี',
        ]) ?>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
        <?= $form->field($model, 'status')->dropDownList(Tor::statusList(), [
            'prompt' => 'ทุกสถานะ',
        ]) ?>
    </div>
    <div class="col-lg-2 col-md-6">
        <?= $form->field($model, 'asset_type_id')->dropDownList(Tor::listAssetType(), [
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
<?php ActiveForm::end(); ?>
