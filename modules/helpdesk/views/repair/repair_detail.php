<?php
use app\modules\hr\models\Employees;
use yii\helpers\Html;
?>
<tr>
            <td colspan="6" class="text-center bg-warning-subtle">
                <span class="fw-semibold"> <i class="fa-solid fa-file-pen"></i> บันทึกการแจ้งซ่อม : </span>
                <?= Yii::$app->thaiFormatter->asDateTime($repair->created_at, 'medium') ?>
            </td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">ผู้แจ้งซ่อม : </span></td>
            <td colspan="3">
                <span class="text-danger"><?= isset($repair->data_json['create_name']) ? $repair->data_json['create_name'] : '' ?></span>
            </td>
            <td class="text-end"><span class="fw-semibold">ความเร่งด่วน : </span></td>
            <td colspan="3"><?= $repair->viewUrgency() ?></td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">อาการแจ้งซ่อม : </span></td>
            <td colspan="3">
                <span class="text-danger"><?= $repair->data_json['title'] ?></span>
            </td>
            <td class="text-end"><span class="fw-semibold">สภานะงานซ่อม : </span></td>
            <td colspan="3"><?= $repair->viewStatus() ?></td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">ข้อมูลเพิ่มเติม : </span></td>
            <td colspan="5">
                <span class="text-danger">
                    <?= $repair->data_json['note'] ?>
                </span>
            </td>
        </tr>
        <?php if (isset($repair->data_json['accept_name'])): ?>
        <tr class="align-middle">
            <td class="text-end"><span class="fw-semibold">ผู้รับเรื่อง : </span></td>
            <td colspan="3"><?= $repair->data_json['accept_name']; ?></td>
            <td class="text-end"><span class="fw-semibold">ช่างผู้ร่วมงาน : </span></td>
            <td colspan="3"><?= $repair->avatarStack() ?></td>
        </tr>
        <?php endif; ?>
        <tr class="align-middle">
            <td class="text-end"><span class="fw-semibold">หน่วยงานรับซ่อม : </span></td>
            <td colspan="5"><?= $repair->viewRepairGroup(); ?>
            <?= html::a('แก้ไข', ['/helpdesk/repair/switch-group', 'id' => $repair->id, 'title' => '<i class="fa-solid fa-wrench"></i> หน่วยงานรับซ่อม'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-sm']]) ?>
        </td>
        </tr>