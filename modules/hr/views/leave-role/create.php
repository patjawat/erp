<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveRole $model */

$this->title = 'Create Leave Role';
$this->params['breadcrumbs'][] = ['label' => 'Leave Roles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="leave-role-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
