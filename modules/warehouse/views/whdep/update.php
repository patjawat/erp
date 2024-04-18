<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Whdep $model */

$this->title = 'Update Whdep: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Whdeps', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="whdep-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
