<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Whdep $model */

$this->title = 'Create Whdep';
$this->params['breadcrumbs'][] = ['label' => 'Whdeps', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="whdep-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
