<?php

use yii\helpers\Html;

/** @var app\modules\sm\models\AssetItem $model */
?>

<div class="d-flex align-items-center gap-3">
    <div class="flex-shrink-0">
        <?= Html::a(
            Html::img($model->showImg(), [
                'class' => 'rounded-3 border',
                'style' => 'width:56px;height:56px;object-fit:cover;',
            ]),
            ['view', 'id' => $model->id],
            ['class' => 'open-modal', 'data' => ['size' => 'modal-md']]
        ) ?>
    </div>
    <div class="flex-grow-1">
        <?= Html::a(
            Html::encode($model->title),
            ['view', 'id' => $model->id],
            ['class' => 'fw-semibold text-decoration-none open-modal', 'data' => ['size' => 'modal-md']]
        ) ?>
        <div class="mt-1">
            <span class="badge rounded-pill bg-success-subtle text-success">
                <?= Html::encode($model->code ?: '-') ?>
            </span>
        </div>
    </div>
</div>
