<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */

$this->title = 'ประเภท' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Fsns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock();?>

<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>
<?=DetailView::widget([
    'model' => $model,
    'attributes' => [
        [
            'label' => 'ชื่อรายการ',
            'value' => function ($model) {
                return $model->title;
            },
        ],
        [
            'label' => 'รหัส',
            'value' => function ($model) {
                return $model->code;
            },
        ],
    ],
])?>
<?=Html::img($model->showImg(), ['class' => '', 'style' => 'max-width:100%'])?>
<hr>
<div class="d-flex gap-2  justify-content-center">
    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/am/asset-item/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-warning  open-modal', 'data' => ['size' => 'modal-lg']])?>
    <?=Html::a('<i class="bi bi-trash"></i>ลบ', ['/am/asset-item/delete', 'id' => $model->id], [
    'class' => 'btn btn-danger  delete-item',
])?>
</div>

<?php Pjax::end();?>