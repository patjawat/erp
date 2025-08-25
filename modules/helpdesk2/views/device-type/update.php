<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\DeviceType $model */

$this->title = 'Update Device Type: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Device Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="device-type-update">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
