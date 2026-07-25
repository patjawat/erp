<?php
use yii\helpers\Html;
$this->title = $title;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<div class="container-fluid py-3"><div class="card border-0 shadow-sm"><div class="card-body"><?= $this->render('_form', ['model' => $model]) ?></div></div></div>
