<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model sant\admin\models\AuthItem */
/* @var $context sant\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', $labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


$opts = Json::htmlEncode([
    'items' => $model->getItems(),
]);
$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
?>

<div class="card">
    <div class="card-body">


<div class="d-flex justify-content-between align-items-center mb-3">
    <p style="margin-bottom: 0px;">
        <?= Html::a('<i class="far fa-edit"></i> แก้ไข', ['update', 'id' => $model->name], ['class' => 'btn btn-primary']); ?>
        <?= Html::a('<i class="fas fa-trash"></i> ลบทิ้ง', ['delete', 'id' => $model->name], [
                'class' => 'btn btn-danger',
                'data-confirm' => Yii::t('rbac-admin', 'Are you sure to delete this item?'),
                'data-method' => 'post',
            ]); ?>
        <?= Html::a('<i class="fas fa-plus"></i> สร้างใหม่', ['create'], ['class' => 'btn btn-success']); ?>
    </p>
    <?=$this->render('../default/navlink')?>
</div>


<?=
            DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'name',
                    'description:ntext',
                    'ruleName',
                    'data:ntext',
                ],
                'template' => '<tr><th style="width:25%">{label}</th><td>{value}</td></tr>',
            ]);
        ?>

</div>
</div>
<div class="auth-item-view">
    <div class="row">
        <div class="col-md-5">

            <div class="card shadow">
                <div class="card-header">
                    ตัวเลือก
                </div>
                <div class="card-body">
                    <input class="form-control search" data-target="available"
                        placeholder="<?= Yii::t('rbac-admin', 'Search for available'); ?>">
                    <select multiple size="20" class="form-control list" data-target="available"></select>
                </div>
            </div>
        </div>
        <div class="col-md-2" style="text-align: center;">
            <br><br>
            <?= Html::a('&gt;&gt;' . $animateIcon, ['assign', 'id' => $model->name], [
                'class' => 'btn btn-success btn-assign',
                'data-target' => 'available',
                'title' => Yii::t('rbac-admin', 'Assign'),
            ]); ?><br><br>
            <?= Html::a('&lt;&lt;' . $animateIcon, ['remove', 'id' => $model->name], [
                'class' => 'btn btn-danger btn-assign',
                'data-target' => 'assigned',
                'title' => Yii::t('rbac-admin', 'Remove'),
            ]); ?>
        </div>
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header">
                    เลือก
                </div>
                <div class="card-body">
                    <input class="form-control search" data-target="assigned"
                        placeholder="<?= Yii::t('rbac-admin', 'Search for assigned'); ?>">
                    <select multiple size="20" class="form-control list" data-target="assigned"></select>
                </div>
            </div>

        </div>
    </div>
</div>