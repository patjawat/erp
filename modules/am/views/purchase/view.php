<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListPurchase $model */
?>

<div class="list-purchase-view">
    <div class="card border-0">
        <div class="card-body p-0">
            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-borderless mb-0'],
                'attributes' => [
                    [
                        'attribute' => 'code',
                        'format' => 'raw',
                        'value' => '<span class="badge bg-amber-soft text-amber font-monospace fw-semibold">' . Html::encode($model->code) . '</span>',
                    ],
                    'title',
                    [
                        'attribute' => 'description',
                        'value' => $model->description && $model->description !== '0' ? $model->description : '—',
                    ],
                    [
                        'attribute' => 'sort',
                        'value' => $model->sort ?: '—',
                    ],
                    [
                        'attribute' => 'active',
                        'format' => 'raw',
                        'value' => (int) $model->active === 1
                            ? '<span class="badge bg-amber-soft text-amber"><span class="status-dot status-dot--amber me-1"></span>ใช้งาน</span>'
                            : '<span class="badge bg-secondary-subtle text-secondary"><span class="status-dot status-dot--muted me-1"></span>ปิดใช้</span>',
                    ],
                ],
            ]) ?>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
            <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $model->id, 'title' => 'แก้ไขวิธีการได้มา'], [
                'class' => 'btn btn-warning text-white open-modal',
                'data' => ['size' => 'modal-md'],
            ]) ?>
        </div>
    </div>
</div>
