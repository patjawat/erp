<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$this->title = 'Create Documents';
$this->params['breadcrumbs'][] = ['label' => 'Documents', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documents-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
