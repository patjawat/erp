<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\CommitteeGroup $model */

$this->title = 'Create Committee Group';
$this->params['breadcrumbs'][] = ['label' => 'Committee Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="committee-group-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
