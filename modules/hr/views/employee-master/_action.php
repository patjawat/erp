<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
?>

<div class="dropdown">
    <button
        type="button"
        class="btn btn-outline-secondary btn-sm rounded-3 dropdown-toggle"
        data-bs-toggle="dropdown"
        aria-expanded="false"
    >
        <i class="fa-solid fa-ellipsis-vertical"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-1">
        <div class="dropdown-header text-uppercase small text-muted px-3 py-2">การจัดการ</div>
        <?= Html::a(
            '<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข',
            ['/hr/employee-master/update', 'type' => $type, 'id' => $model->id, 'title' => 'แก้ไข' . $model->title],
            ['class' => 'dropdown-item rounded-2 py-2 open-modal', 'data' => ['size' => $size]]
        ) ?>
        <?= Html::a(
            '<i class="fa-solid fa-trash-can me-2"></i> ลบ',
            ['/hr/employee-master/delete', 'type' => $type, 'id' => $model->id],
            ['class' => 'dropdown-item rounded-2 py-2 text-danger delete-item']
        ) ?>
    </div>
</div>
