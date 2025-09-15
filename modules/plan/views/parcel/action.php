<?php
use yii\helpers\Html;
?>
<div class="dropdown mb-2">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bars"></i> จัดการ
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li><?= Html::a('<i class="fa-solid fa-eye me-2"></i>แสดง', ['view', 'id' => $model->id], ['class' => 'dropdown-item']) ?></li>
                    <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-2"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'dropdown-item']) ?></li>
                    <?= $model->status == 'submit' ?  '<li>'.Html::a('<i class="fa-solid fa-circle-check me-2"></i> อนุมัติแผน', ['/plan/plan-order/approve', 'id' => $model->id], ['class' => 'open-modal dropdown-item', 'data' => ['size' => 'modal-m']]).'</li>' : '' ?>
                    <?= $model->status == 'submit' ?  '<li>'.Html::a('<i class="fa-solid fa-hand me-2"></i> ไม่อนุมัติ', ['/plan/plan-order/update-status'], ['class' => 'update-status dropdown-item','data' => ['id' => $model->id, 'status' => 'reject']]).'</li>' : '' ?>
                    <?= $model->status == 'approve' ?  '<li>'.Html::a('<i class="fa-solid fa-arrow-rotate-left me-2"></i> ปรับแผน', ['/plan/plan-order/renew'], ['class' => 'dropdown-item renew', 'data' => ['id' => $model->id]]).'</li>' : '' ?>
                     <?= ($model->status == 'draft' || $model->status == 'renew')
                            ? '<li>'.Html::a(
                                '<i class="fa-solid fa-paper-plane me-2"></i> ส่งคำขอ',
                                ['/plan/plan-order/update-status'],
                                [
                                    'class' => 'dropdown-item update-status',
                                    'data' => ['id' => $model->id, 'status' => 'submit']
                                ]
                            ).'</li>'
                            : ''
                        ?>
                    
                   <li> 
                    <?= Html::a('<i class="fa-solid fa-trash me-2"></i> ลบ', ['delete', 'id' => $model->id], [
                        'class' => 'dropdown-item delete-item']) ?>
                    </li>
                </ul>
            </div>