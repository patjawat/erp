<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */

$this->title = 'Update Repair: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Repairs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="repair-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
