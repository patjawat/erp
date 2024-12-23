<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeavePolicies $model */

$this->title = 'Create Leave Policies';
$this->params['breadcrumbs'][] = ['label' => 'Leave Policies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="leave-policies-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
