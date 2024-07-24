<?php
use yii\helpers\Html;
$category_id = Yii::$app->request->get('category_id');
?>
<?php // Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]); ?>
<div class="d-flex flex-column align-items-center  justify-content-center">
    <?=Html::img($model->ShowImg(),['class' => 'avatar-profile object-fit-cover rounded mb-3','style' =>'max-width:50%;'])?>
        <?= html::a($model->title,['view','id' => $model->id],['class' => 'h2', 'data' => ['size' => 'modal-md']]);?>
        <span class="h3"><?=$model->code?></span>

    <hr>
        <div class="d-flex gap-2  justify-content-center">
    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/am/setting/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-warning  open-modal', 'data' => ['size' => 'modal-lg']])?>
    <?=Html::a('<i class="bi bi-trash"></i>ลบ', ['/am/setting/delete', 'id' => $model->id], [
    'class' => 'btn btn-danger  delete-item',
])?>
</div>

</div>
