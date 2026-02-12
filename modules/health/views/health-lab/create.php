<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\health\models\HealthLab $model */

$this->title = 'Create Health Lab';
$this->params['breadcrumbs'][] = ['label' => 'Health Labs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="health-lab-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
