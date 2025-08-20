<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Plan Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-dolly me-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'parcel']) ?>
<?php $this->endBlock(); ?>


<div class="plan-order-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'thai_year',
                'label' => 'ปีงบประมาณ',
                'value' => function($model) {
                    return $model->thai_year;
                }
            ],
            [
                'attribute' => 'plan_budget_type',
                'label' => 'ประเภทงบประมาณ',
            ],
            [
                'attribute' => 'asset_group_id',
                'label' => 'หมวด',
                 'value' => function($model) {
                    return $model->assetGroup?->title ?? '-';
                }
            ],
             [
                'attribute' => 'asset_type_id',
                'label' => 'ประเภท',
                 'value' => function($model) {
                    return $model->assetType?->title ?? '-';
                }
            ],
             [
                'attribute' => 'asset_category_id',
                'label' => 'หมวดพัสดุ',
                 'value' => function($model) {
                    return $model->assetCategory?->title ?? '-';
                }
            ],
            [
                'attribute' => 'department_id',
                'label' => 'ของกลุ่มงาน',
                'value' => function($model)
                {
                    return $model->departmentName();
                }
            ],
            [
                'attribute' => 'budget_id',
                'label' => 'แหล่องของเงิน',
            ],
        ],
    ]) ?>

</div>
