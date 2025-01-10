<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentsDetail $model */

$this->title = 'Update Documents Detail: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Documents Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="documents-detail-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
