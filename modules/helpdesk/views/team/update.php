<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Helpdesk $model */

$this->title = 'Update Helpdesk: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Helpdesks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="helpdesk-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
