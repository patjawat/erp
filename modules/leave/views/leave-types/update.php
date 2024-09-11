<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\LeaveTypes $model */

$this->title = 'Update Leave Types: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Leave Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="leave-types-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
