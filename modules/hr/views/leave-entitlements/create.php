<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveEntitlements $model */

$this->title = 'Create Leave Entitlements';
$this->params['breadcrumbs'][] = ['label' => 'Leave Entitlements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="leave-entitlements-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
