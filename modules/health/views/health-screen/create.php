<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\health\models\HealthScreen $model */

$this->title = 'Create Health Screen';
$this->params['breadcrumbs'][] = ['label' => 'Health Screens', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="health-screen-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
