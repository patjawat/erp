<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Helpdesk $model */

$this->title = 'Create Helpdesk';
$this->params['breadcrumbs'][] = ['label' => 'Helpdesks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="helpdesk-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
