<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\health\models\HealthLab $model */

$this->title = 'Update Health Lab: ' . $model->lab_code;
$this->params['breadcrumbs'][] = ['label' => 'Health Labs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->lab_code, 'url' => ['view', 'lab_code' => $model->lab_code]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="health-lab-update">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
