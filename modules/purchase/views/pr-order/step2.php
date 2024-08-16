<?php
use yii\helpers\Html;
?>


<div class="d-flex justify-content-between">
    <div>
        <h5><i class="fa-solid fa-circle-info text-primary"></i> ลงทะเบียนคุม</h5>
    </div>

    
</div>
<div class="card">
    <div class="card-body">
        <div class="dropdown float-end">
            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fa-solid fa-ellipsis"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <?= Html::a('<i class="fa-regular fa-eye me-1 text-primary"></i> แสดง', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                <?= Html::a('<i class="fa-regular fa-file-word me-1"></i> พิมพ์', ['/ms-word/purchase_3', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                <?= Html::a('<i class="bx bx-trash me-1 text-danger"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                    'class' => 'dropdown-item  delete-item',
                ]) ?>
            </div>
        </div>
        <table class="table table-striped-columns">
            <tbody>
                <tr class="">
                    <td class="text-end" style="width:150px;">ทะเบียนคุม</td>
                    <td class="fw-semibold"><?= $model->pq_number ?></td>
                    <td class="text-end">ตามคำสั่ง</td>
                    <td colspan="3">123</td>

                </tr>
                <tr class="">
                    <td class="text-end">วิธีซื้อหรือจ้าง</td>
                    <td></td>
                    <td class="text-end">เลขที่คำสั่ง</td>
                    <td> กกน-123</td>
                    <td class="text-end">ลงวันที่</td>
                    <td> กกน-123</td>

                </tr>
                <tr class="">
                    <td class="text-end">วิธีจัดหา</td>
                    <td><?= isset($model->data_json['comment']) ? $model->data_json['comment'] : '' ?></td>
                    <td class="text-end">ลงวันที่</td>
                    <td> <?= isset($model->data_json['due_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') : '' ?>
                    </td>
                    <td class="text-end">เลขที่คำสั่ง</td>
                    <td> กกน-123</td>
                </tr>
                <tr class="">
                    <td class="text-end">ประเภทเงิน</td>
                    <td></td>
                    <td class="text-end">ชื่อโครงการ</td>
                    <td colspan="3"></td>
                </tr>

                <tr class="">
                    <td class="text-end">การพิจารณา</td>
                    <td></td>
                    <td class="text-end">โครงการเลขที่</td>
                    <td></td>
                    <td class="text-end">การเบิกจ่ายเงิน</td>
                    <td></td>
                </tr>

                <tr class="">
                    <td class="text-end">หมวดเงิน</td>
                    <td></td>
                    <td class="text-end">รหัสอ้างอิง EGP</td>
                    <td></td>
                    <td class="text-end">การเบิกจ่รายการแผน EGPายเงิน</td>
                    <td></td>
                </tr>

                <tr>
                    <td class="text-end">เหตุผลความจำเป็น</td>
                    <td colspan="3"></td>
                    <td class="text-end">เหตุผลการจัดหา</td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>

    </div>
</div>