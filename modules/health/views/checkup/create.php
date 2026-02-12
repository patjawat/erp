<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\health\models\Checkup $model */

$this->title = 'Create Checkup';
$this->params['breadcrumbs'][] = ['label' => 'Checkups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="checkup-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
