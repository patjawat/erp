<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */

$this->title = 'Create Development';
$this->params['breadcrumbs'][] = ['label' => 'Developments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="development-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
