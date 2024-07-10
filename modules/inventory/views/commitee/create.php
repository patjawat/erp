<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Commitee $model */

$this->title = 'Create Commitee';
$this->params['breadcrumbs'][] = ['label' => 'Commitees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="commitee-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
