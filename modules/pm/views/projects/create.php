<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\Projects $model */

$this->title = 'Create Projects';
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="projects-create">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
