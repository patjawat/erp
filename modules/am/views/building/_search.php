<?php

use yii\helpers\Html;
use app\models\Categorise;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $model */
/** @var yii\widgets\ActiveForm $form */
$listAssetType = ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(), 'code', 'title');
$listAssetGroup = ArrayHelper::map(Categorise::find()
    ->where(['name' => 'asset_group'])
    ->andwhere(['IN', 'code', [1, 2, 3]])
    ->all(), 'code', 'title');
?>


<?php $form = ActiveForm::begin([

    'method' => 'get',
    'options' => [
        'data-pjax' => 0
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
]); ?>

<div class="row">
    <div class="col-lg-11 col-md-11 col-sm-12">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...', 'width' => '100'])->label(false)->label(false) ?>
    </div>
    <div class="col-1">
        <div class="d-flex flex-row align-items-center gap-2">
            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter">
                <i class="fa-solid fa-filter"></i>
            </button>
        </div>
    </div>
</div>



<?php ActiveForm::end(); ?>

