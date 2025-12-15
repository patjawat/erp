<?php

/* @var $this yii\web\View */
/* @var $model yii\base\Model */

$this->title = 'ทรัพย์สิน';
$this->params['breadcrumbs'][] = ['label' => 'รายการ', 'url' => ['index']];

?>

<div class="fade-in">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>