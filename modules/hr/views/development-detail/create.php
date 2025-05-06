<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentDetail $model */

$this->title = 'Create Development Detail';
$this->params['breadcrumbs'][] = ['label' => 'Development Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="development-detail-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
