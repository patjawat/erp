<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Calendar $model */

$this->title = 'แจ้งซ่อม';
$this->params['breadcrumbs'][] = ['label' => 'แจ้งซ่อม', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<div class="calendar-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
