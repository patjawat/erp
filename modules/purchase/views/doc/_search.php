<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\modules\purchase\models\Doc;
use app\modules\purchase\models\DocTemplate;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\DocSearch $model */

$form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => ['data-pjax' => 1, 'id' => 'doc-search-form'],
]);
?>
<div class="row g-2 align-items-end">
    <div class="col-lg-4 col-md-6">
        <?= $form->field($model, 'q')->textInput([
            'placeholder' => 'ค้นหาชื่อเอกสาร / เลขที่หนังสือ / หมายเหตุ',
        ])->label('ค้นหา') ?>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
        <?= $form->field($model, 'thai_year')->dropDownList(Doc::listThaiYear(), [
            'prompt' => 'ทุกปี',
        ]) ?>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
        <?= $form->field($model, 'status')->dropDownList(Doc::statusList(), [
            'prompt' => 'ทุกสถานะ',
        ]) ?>
    </div>
    <div class="col-lg-2 col-md-6">
        <?= $form->field($model, 'template_id')->dropDownList(
            ArrayHelper::map(
                DocTemplate::find()->orderBy(['sort_order' => SORT_ASC])->all(),
                'id',
                'name'
            ),
            ['prompt' => 'ทุกแม่แบบ']
        )->label('แม่แบบ') ?>
    </div>
    <div class="col-lg-2 col-md-6">
        <div class="d-flex gap-2 mb-3">
            <?= Html::submitButton('<i class="bi bi-search me-1"></i>ค้นหา', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('ล้าง', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
