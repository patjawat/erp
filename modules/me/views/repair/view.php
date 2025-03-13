<?php
use yii\helpers\Html;
use app\modules\hr\models\Employees;
?>
<tr>
    <td colspan="6" class="text-center bg-warning-subtle">
        <span class="fw-semibold"> <i class="fa-solid fa-file-pen"></i> บันทึกการแจ้งซ่อม : </span>
        <?= Yii::$app->thaiFormatter->asDateTime($model->created_at, 'medium') ?>
    </td>
</tr>
<tr>

    <td class="text-end" style="width:180px">
        <span class="fw-semibold">ผู้แจ้งซ่อม : </span>
    </td>
    <td colspan="3" style="width:200px">
        <?= $model->getUserReq()['avatar'] ?>
    </td>
    <td class="text-end" style="width:200px">
        <span class="fw-semibold">อาการแจ้งซ่อม : </span>
    </td>
    <td colspan="3">
        <span class="text-danger"><?= $model->data_json['title'] ?></span>
    </td>


</tr>
<tr>
    <td class="text-end"><span class="fw-semibold">หน่วยงาน : </span></td>
    <td colspan="3"><?= $model->data_json['location'] ?></td>
    <td class="text-end"><span class="fw-semibold">ความเร่งด่วน : </span></td>
    <td colspan="3"><?= $model->viewUrgency() ?></td>

</tr>
<tr>
    <td class="text-end"><span class="fw-semibold">ระบุสถานที่อื่นๆ : </span></td>
    <td colspan="3"><?= isset($model->data_json['location_other']) ? $model->data_json['location_other'] : '-' ?></td>
    <td class="text-end"><span class="fw-semibold">โทรศัพท์ : </span></td>
    <td colspan="3"><?= isset($model->data_json['phone']) ? $model->data_json['phone'] : '-' ?></td>
</tr>
<tr>
    <td class="text-end"><span class="fw-semibold">ข้อมูลเพิ่มเติม : </span></td>
    <td colspan="5">
        <span class="text-danger">
            <?= $model->data_json['note'] ?>
        </span>
    </td>
</tr>
<?php if (isset($model->data_json['accept_name'])): ?>
<tr class="align-middle">
    <td class="text-end"><span class="fw-semibold">ผู้รับเรื่อง : </span></td>
    <td colspan="3"><?= $model->data_json['accept_name']; ?></td>
    <td class="text-end"><span class="fw-semibold">ช่างผู้ร่วมงาน : </span></td>
    <td colspan="3"><?= $model->avatarStack() ?></td>
</tr>
<?php endif; ?>
<tr class="align-middle">
    <td class="text-end"><span class="fw-semibold">หน่วยงานรับซ่อม : </span></td>
    <td colspan="3"><?= $model->viewRepairGroup(); ?>
        <?= html::a('แก้ไข', ['/helpdesk/repair/switch-group', 'id' => $model->id, 'title' => '<i class="fa-solid fa-wrench"></i> หน่วยงานรับซ่อม'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-sm']]) ?>
    </td>
    <td class="text-end"><span class="fw-semibold">ประเภทการซ่อม : </span></td>
    <td colspan="3">
        <?php
            try {
                echo $model->data_json['repair_type'];
            } catch (\Throwable $th) {
                // throw $th;
            }
        ?>
    </td>
</tr>
<tr class="align-middle">
    <td class="text-end">
        <span class="fw-semibold">สถานะงานซ่อม : </span>
    </td>
    <?php if (isset($model->data_json['repair_type']) && $model->data_json['repair_type'] == 'ซ่อมภายนอก'): ?>
    <td colspan="3">
        <?= $model->viewStatus() ?>
    </td>
    
    <td class="text-end"><span class="fw-semibold">วันที่ส่งซ่อมถายนอก : </span></td>
    <td colspan="3">
        <?php
        try {
            echo Yii::$app->formatter->asDateTime($model->data_json['repair_type_date'], 'php:d/m/Y');
        } catch (\Throwable $th) {
        }
        ?>
    </td>
    <?php else: ?>
        <td colspan="5">
        <?= $model->viewStatus() ?>
    </td>
        <?php endif ?>

</tr>