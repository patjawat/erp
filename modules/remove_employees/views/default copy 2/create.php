<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Employees $model */

$this->title = 'Create Employees';
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="employees-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
