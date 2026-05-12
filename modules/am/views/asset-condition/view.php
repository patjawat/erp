<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetCondition $model */

$this->title = $model->name;
\yii\web\YiiAsset::register($this);
?>

<div class="asset-condition-view">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= Html::encode($this->title) ?></h5>
            <span class="badge text-bg-primary"><?= Html::encode($model->id) ?></span>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'name',
                    'sort_order',
                    [
                        'attribute' => 'is_active',
                        'value' => static function ($model) {
                            return (int) $model->is_active === 1 ? 'Active' : 'Inactive';
                        },
                    ],
                ],
            ]) ?>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <?= Html::a('แก้ไข', ['update', 'id' => $model->id, 'title' => 'แก้ไข Asset Condition'], [
                'class' => 'btn btn-warning open-modal',
                'data' => ['size' => 'modal-md'],
            ]) ?>
            <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger delete-item',
                'data-method' => 'post',
                'data' => ['confirm' => 'ยืนยันลบรายการนี้ใช่หรือไม่?'],
            ]) ?>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
        </div>
    </div>
</div>
