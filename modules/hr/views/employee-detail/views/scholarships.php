<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeeDetail $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Employee Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="employee-detail-view">
    <p>
    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-user-tag"></i> ประวัติการการรับทุน'], ['class' => 'btn btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']])?>
    <?=Html::a('<i class="bx bx-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $model->id], [
'class' => 'btn btn-danger delete-item',
])?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'emp_id',
            'name',
            'updated_at',
            'created_at',
            'created_by',
            'updated_by',
        ],
    ]) ?>

</div>
