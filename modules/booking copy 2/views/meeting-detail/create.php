<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\MeetingDetail $model */

$this->title = 'Create Meeting Detail';
$this->params['breadcrumbs'][] = ['label' => 'Meeting Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="meeting-detail-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
