<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ApproveHelper;
use app\modules\leave\models\Leave;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$offset = (int) ($dataProvider->pagination->offset ?? 0);
$models = $dataProvider->getModels();
?>
<div class="table-responsive" style="min-height: 500px;">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-center py-3 px-3 small">ลำดับ</th>
                <th class="text-center py-3 px-3 small">ประเภทการลา</th>
                <th class="text-center">จำนวนวันลา</th>
                <th class="py-3 px-3 small">ผู้ขออนุมัติการลา</th>
                <th class="py-3 px-3 small">ประเภทเวร</th>
                <th class="py-3 px-3 small">เหตุผล</th>
                <th class="py-3 px-3 small">ระหว่างวันที่</th>
                <th class="py-3 px-3 small">หน่วยงาน</th>
                <th class="py-3 px-3 small">ผู้อนุมัติ</th>
                <th class="py-3 px-3 small">สถานะ/ความคืบหน้า</th>
                <th class="text-end py-3 px-3 small">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="align-middle table-group-divider">
            <?php foreach ($models as $key => $item): ?>
                <?php
                $no = $offset + $key + 1;
                $stackApproves = $item->approves ? array_filter($item->approves, function ($a) {
                    return !in_array($a->status, ['None', 'Pending'], true);
                }) : [];
                usort($stackApproves, function ($x, $y) {
                    return (int) $y->level - (int) $x->level;
                });
                ?>
                <tr>
                    <td class="text-center py-3 px-3 text-muted small"><?= $no ?></td>
                    <td class="text-center py-3 px-3 small">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                            <?= $item->leaveType ? Html::encode($item->leaveType->title) : '-' ?>
                        </span>
                    </td>
                    <td class="text-center fw-bold">
<?= (float) $item->total_days ?>
                    </td>
                    <td class="py-3 px-3">
                        <a href="<?= Url::to(['/leave/leave/view', 'id' => $item->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา']) ?>"
                            class="open-modal text-decoration-none d-inline-flex align-items-center gap-2" data-size="modal-xl">
                            <?= $item->employee ? $item->employee->getAvatar(false) : '-' ?>
                        </a>
                    </td>
                    <td class="py-3 px-3 small"><?= Html::encode($item->work_shift_name ?? '-') ?></td>
                    <td class="py-3 px-3">
                        <div class="small"><?= Html::encode($item->data_json['reason'] ?? '') ?></div>
                    </td>
                    <td class="py-3 px-3 small"><?= $item->showLeaveDate() ?></td>
                    <td class="py-3 px-3 small text-muted text-truncate" style="max-width: 150px;"><?= $item->employee ? Html::encode($item->employee->departmentName()) : '-' ?></td>
                    <td class="py-3 px-3"><?= Leave::renderStackChecker($stackApproves) ?></td>
                    <td class="py-3 px-3 small">
                        <?= $item->viewStatus() ?>
                        <?= ApproveHelper::viewStepFromSteps($item->approves ?? []) ?>
                    </td>
                    <td class="text-end py-3 px-3">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i> ดำเนินการ
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <?= Html::a(
                                        '<i class="bi bi-eye me-2"></i> แสดง',
                                        ['/leave/leave/view', 'id' => $item->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],
                                        ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]
                                    ) ?>
                                </li>
                                <?php if (!$item->hasApprovalDecision()): ?>
                                <li>
                                    <?= Html::a(
                                        '<i class="bi bi-pencil me-2"></i> แก้ไข',
                                        ['/leave/leave/update', 'id' => $item->id],
                                        ['class' => 'dropdown-item']
                                    ) ?>
                                </li>
                                <?php endif; ?>
                                <li>
                                    <?= Html::a(
                                        '<i class="bi bi-printer me-2"></i> พิมพ์ใบลา (PDF)',
                                        ['/leave/leave/pdf', 'id' => $item->id],
                                        [
                                            'class' => 'dropdown-item',
                                            'target' => '_blank',
                                            'rel' => 'noopener noreferrer',
                                            'data-pjax' => '0',
                                            'title' => 'ใช้เทมเพลตจาก /pdf-template ก่อน; ถ้ายังไม่ตั้งจะใช้แบบฟอร์มใบลาเดิม — พิมพ์ได้ทุกสถานะ',
                                        ]
                                    ) ?>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (count($models) === 0): ?>
                <tr>
                    <td colspan="10" class="text-center text-muted py-5 px-3">
                        <i class="bi bi-inbox display-5 d-block mb-2 opacity-50"></i>
                        <span class="small">ไม่มีรายการวันลา</span>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
