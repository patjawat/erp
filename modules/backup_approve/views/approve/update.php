<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\approve\models\Approve $model */

$this->title = 'Update Approve: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Approves', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="approve-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('../'.$model->name.'/view', [
        'model' => $model,
    ]) ?>

</div>
