<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeavePolicies $model */

$this->title = 'Update Leave Policies: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Leave Policies', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="leave-policies-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
