<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Holiday $model */

$this->title = 'Create Holiday';
$this->params['breadcrumbs'][] = ['label' => 'Holidays', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="holiday-create">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
