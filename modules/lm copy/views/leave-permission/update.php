<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeavePermission $model */

$this->title = 'Update Leave Permission: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Leave Permissions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="leave-permission-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
