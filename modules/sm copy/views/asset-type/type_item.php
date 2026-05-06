<?php
use yii\helpers\Html;
$category_id = Yii::$app->request->get('category_id');
?>

<div class="d-flex align-items-center">
    <div class="flex-shrink-0">

        <?=  Html::a('<i class="bi bi-folder-check fs-2"></i>',['/sm/asset-type/view-type','id' => $model->id,'name' => $model->name,'category_id' => $category_id,'title' => $model->title]) ?><br>

    </div>
    <div class="flex-grow-1 ms-3">
        <?= html::a($model->title,['view','id' => $model->id],['class' => 'open-modal', 'data' => ['size' => 'modal-md']]);?>
        <?php /* Html::a($model->title,['/sm/asset-type/view-type','id' => $model->id,'name' => $model->name,'category_id' => $category_id,'title' => $model->title]) */?><br>

        <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
            <?=$model->code?>
        </label>

    </div>
</div>