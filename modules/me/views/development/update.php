<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */

$this->title = 'Update Development: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Developments', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="development-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
