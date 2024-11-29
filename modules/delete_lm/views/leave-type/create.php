<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveTypes $model */

$this->title = 'Create Leave Types';
$this->params['breadcrumbs'][] = ['label' => 'Leave Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="leave-types-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
