<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Vehicle $model */

$this->title = 'Create Vehicle';
$this->params['breadcrumbs'][] = ['label' => 'Vehicles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vehicle-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
