<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */

$this->title = 'Create Fsn';
$this->params['breadcrumbs'][] = ['label' => 'Fsns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fsn-create">

    <?= $this->render('_form_2', [
        'model' => $model,
        'ref' => $ref
    ]) ?>

</div>
