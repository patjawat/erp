<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Whcheck $model */

$this->title = 'Update Whcheck: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Whchecks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="whcheck-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
