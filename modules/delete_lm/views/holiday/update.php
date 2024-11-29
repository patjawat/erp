<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Holiday $model */

$this->title = 'Update Holiday: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Holidays', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="holiday-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
