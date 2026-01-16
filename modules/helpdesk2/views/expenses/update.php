<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\HelpdeskDetail $model */

$this->title = 'Update Helpdesk Detail: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Helpdesk Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="helpdesk-detail-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
