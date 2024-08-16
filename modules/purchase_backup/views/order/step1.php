<?php
use yii\helpers\Html;
?>

<td colspan="6" class="p-0">
    <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded mb-3 d-flex justify-content-between">
        <h5><i class="fa-solid fa-circle-info text-primary"></i> ขอซื้อขอจ้าง</h5>
        <div class="dropdown float-end">
            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/purchase/pr-order/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขใบขอซื้อ : ' . $model->pr_number], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                <?= Html::a('<i class="bx bx-trash me-1 text-danger"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                    'class' => 'dropdown-item  delete-item',
                ]) ?>
            </div>
        </div>
    </div>
</td>
<tr class="">
    <td class="text-end" style="width:150px;">เลขที่ขอซื้อ</td>
    <td class="fw-semibold"><?= $model->pr_number ?></td>
    <td class="text-end">ผู้ขอ</td>
    <td> <?= $model->getUserReq()['avatar'] ?></td>
</tr>
<tr class="">
    <td>เพื่อจัดซื้อ/ซ่อมแซม</td>
    <td>
        <?php
            try {
                echo $model->data_json['product_type_name'];
            } catch (\Throwable $th) {
            }
        ?></td>
    <td class="text-end">วันที่ขอซื้อ</td>
    <td> <?php echo Yii::$app->thaiFormatter->asDateTime($model->created_at, 'medium') ?>
    </td>

</tr>
<tr class="">
    <td class="text-end">เหตุผล</td>
    <td><?= isset($model->data_json['comment']) ? $model->data_json['comment'] : '' ?>
    </td>
    <td class="text-end">วันที่ต้องการ</td>
    <td> <?= isset($model->data_json['due_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') : '' ?>
    </td>
</tr>
<tr>
    <td  colspan="6" class="p-0">
    <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded my-2 d-flex justify-content-between">
    <h5><i class="fa-solid fa-circle-info text-primary"></i> การตรวจและเห็นชอบ</h5>
</div>
    </td>
 
</tr>
<tr>
    <td class="text-end">หัวหน้า</td>
    <td colspan="2"><span class="btn btn-sm btn-warning"><i class="fa-regular fa-clock"></i> ลงความเห็น</span></td>
    <td class="text-end">พัสดุ</td>
    <td  colspan="2"><span class="btn btn-sm btn-warning"><i class="fa-regular fa-clock"></i> ลงความเห็น</span></td>
</tr>
<tr>
    <td class="text-end">ผู้อำนวยการ</td>
    <td  colspan="5"><span class="btn btn-sm btn-warning"><i class="fa-regular fa-clock"></i> ลงความเห็น</span></td>
 
</tr>
