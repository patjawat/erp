<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */

$this->title = 'ระบบลา';
$this->params['breadcrumbs'][] = ['label' => 'Leaves', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

    <?= $this->render('@app/modules/hr/views/leave/_form', [
        'model' => $model,
    ]) ?>
