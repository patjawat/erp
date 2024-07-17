<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Order $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/sm/views/default/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <table class="table table-striped-columns">
            <tbody>
                <?= $this->render('@app/modules/purchase/views/order/step2.php', ['model' => $model]) ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->render('@app/modules/purchase/views/order/order_items', ['model' => $model]) ?>