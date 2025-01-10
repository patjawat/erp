<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentsDetail $model */

$this->title = 'Create Documents Detail';
$this->params['breadcrumbs'][] = ['label' => 'Documents Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documents-detail-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
