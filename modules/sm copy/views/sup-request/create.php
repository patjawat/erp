<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\SupRequest $model */

$this->title = 'เพิ่มรายการ';
$this->params['breadcrumbs'][] = ['label' => 'ขอซื้อขอจ้าง', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sup-request-create">

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<div class="asset-index">
<div class="asset-sell-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
