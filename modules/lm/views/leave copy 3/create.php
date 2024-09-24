<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */

$this->title = 'Create Leave';
$this->params['breadcrumbs'][] = ['label' => 'Leaves', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="leave-create">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
