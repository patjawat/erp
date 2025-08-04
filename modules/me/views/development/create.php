<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */

$this->title = 'อบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = ['label' => 'Developments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-briefcase fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/me/menu',['active' => 'development']) ?>
<?php $this->endBlock(); ?>

<div class="development-create">
    <?= $this->render('@app/modules/hr/views/development/_form', [
        'model' => $model,
    ]) ?>

</div>
