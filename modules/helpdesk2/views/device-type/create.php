<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\DeviceType $model */

$this->title = 'Create Device Type';
$this->params['breadcrumbs'][] = ['label' => 'Device Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-type-create">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
