<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\health\models\Checkup $model */

$this->title = 'Update Checkup: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Checkups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="checkup-update">

    <h1><?= Html::encode($this->title) ?></h1>
<div class="row">
    
    <div class="col-6">
        <?= $this->render('_form', [
            'model' => $model,
            ]) ?>
        </div>
        <div class="col-6">
            xx
            </div>
        </div>
            
</div>
