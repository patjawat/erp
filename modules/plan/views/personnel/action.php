<?php

use yii\helpers\Html;

$editable = in_array($model->status, ['draft', 'reject'], true)
    || ($model->status === 'renew' && \app\modules\plan\components\PlanHelper::canAdjust($model->thai_year));
$returnUrl = Yii::$app->request->url;
?>
<div class="dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-bars"></i> จัดการ
    </button>
    <ul class="dropdown-menu">
        <li><?= Html::a('<i class="fa-solid fa-eye me-2"></i>แสดง', ['view', 'id' => $model->id], ['class' => 'dropdown-item']) ?></li>
        <?php if ($editable): ?>
            <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-2"></i>แก้ไข', ['update', 'id' => $model->id, 'returnUrl' => $returnUrl], ['class' => 'dropdown-item']) ?></li>
        <?php endif; ?>
        <?php if (Yii::$app->user->can('planApprove') && $model->status === 'submit'): ?>
            <li><?= Html::a('<i class="fa-solid fa-circle-check me-2"></i>อนุมัติแผน', ['/plan/plan-order/approve', 'id' => $model->id], [
                'class' => 'btn btn-success open-modal dropdown-item',
                'data' => ['size' => 'modal-m'],
            ]) ?></li>
        <?php endif; ?>
        <?php if ($model->status === 'approve'): ?>
            <li><?= Html::a('<i class="fa-solid fa-arrow-rotate-left me-2"></i>ปรับแผน', ['/plan/plan-order/renew'], [
                'class' => 'btn btn-warning dropdown-item renew',
                'data' => ['id' => $model->id],
            ]) ?></li>
        <?php endif; ?>
        <?php if (in_array($model->status, ['draft', 'renew'], true)): ?>
            <li><?= Html::a('<i class="fa-solid fa-paper-plane me-2"></i>ส่งคำขอ', ['/plan/plan-order/update-status'], [
                'class' => 'dropdown-item update-status',
                'data' => ['id' => $model->id, 'status' => 'submit'],
            ]) ?></li>
        <?php endif; ?>
        <?php if ($model->status === 'draft'): ?>
            <li><?= Html::a('<i class="fa-solid fa-trash me-2"></i>ลบ', ['delete', 'id' => $model->id], [
                'class' => 'dropdown-item delete-item',
            ]) ?></li>
        <?php endif; ?>
    </ul>
</div>
