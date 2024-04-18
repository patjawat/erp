<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model sant\admin\models\AuthItem */
/* @var $context sant\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = 'บทบาท';
$this->params['breadcrumbs'][] = ['label' => 'บทบาท', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="auth-item-create">
    <?=
    $this->render('_form', [
        'model' => $model,
    ]);
    ?>

</div>
