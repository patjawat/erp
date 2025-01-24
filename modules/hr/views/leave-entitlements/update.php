<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveEntitlements $model */

$this->title = 'Update Leave Entitlements: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Leave Entitlements', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="leave-entitlements-update">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
