<?php
use yii\helpers\Html;
?>

<tr>
    <td colspan="6" class="p-0">
        <div
            class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded mb-3 d-flex justify-content-between mt-2">
            <h5><i class="fa-solid fa-circle-info text-primary"></i> ทะเบียนคุม</h5>

            <div class="dropdown float-end">
                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa-solid fa-ellipsis"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right" style="">
                    <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/purchase/pq-order/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขทะเบียนคุม'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                    <?= Html::a('<i class="bx bx-trash me-1 text-danger"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                        'class' => 'dropdown-item  delete-item',
                    ]) ?>
                </div>
            </div>
        </div>
    </td>
</tr>
<tr class="">
    <td class="text-end" style="width:150px;">ทะเบียนคุม</td>
    <td class="fw-semibold"><?= $model->pq_number ?></td>
    <td class="text-end">ตามคำสั่ง</td>
    <td colspan="3">123</td>

</tr>
<tr class="">
    <td class="text-end">วิธีซื้อหรือจ้าง</td>
    <td>
        <?= isset($model->data_json['pq_purchase_type_name']) ? $model->data_json['pq_purchase_type_name'] : '' ?>
    </td>
    <td class="text-end">เลขที่คำสั่ง</td>
    <td> กกน-123</td>
    <td class="text-end">ลงวันที่</td>
    <td> กกน-123</td>

</tr>
<tr class="">
    <td class="text-end">วิธีจัดหา</td>
    <td><?= isset($model->data_json['pq_method_get_name']) ? $model->data_json['pq_method_get_name'] : '' ?></td>
    <td class="text-end">ลงวันที่</td>
    <td> <?= isset($model->data_json['due_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') : '' ?>
    </td>
    <td class="text-end">เลขที่คำสั่ง</td>
    <td> กกน-123</td>
</tr>
<tr class="">
    <td class="text-end">ประเภทเงิน</td>
    <td><?= isset($model->data_json['pq_budget_type_name']) ? $model->data_json['pq_budget_type_name'] : '' ?></td>
    <td class="text-end">ชื่อโครงการ</td>
    <td colspan="3">
        <?= isset($model->data_json['pq_project_name']) ? $model->data_json['pq_project_name'] : '' ?></td>
</tr>

<tr class="">
    <td class="text-end">การพิจารณา</td>
    <td><?= isset($model->data_json['pq_consideration']) ? $model->data_json['pq_consideration'] : '' ?></td>
    <td class="text-end">โครงการเลขที่</td>
    <td><?= isset($model->data_json['pq_project_id']) ? $model->data_json['pq_project_id'] : '' ?></td>
    <td class="text-end">การเบิกจ่ายเงิน</td>
    <td><?= isset($model->data_json['pq_disbursement']) ? $model->data_json['pq_disbursement'] : '' ?></td>
</tr>

<tr class="">
    <td class="text-end">หมวดเงิน</td>
    <td><?= isset($model->data_json['pq_budget_group_name']) ? $model->data_json['pq_budget_group_name'] : '' ?></td>
    <td class="text-end">รหัสอ้างอิง EGP</td>
    <td><?= isset($model->data_json['pq_egp_number']) ? $model->data_json['pq_egp_number'] : '' ?></td>
    <td class="text-end">รายการแผน EGP</td>
    <td><?= isset($model->data_json['pq_egp_report']) ? $model->data_json['pq_egp_report'] : '' ?></td>
</tr>

<tr>
    <td class="text-end">เหตุผลความจำเป็น</td>
    <td colspan="3"></td>
    <td class="text-end">เหตุผลการจัดหา</td>
    <td colspan="3"></td>
</tr>