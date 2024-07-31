<?php
use yii\helpers\Html;
?>
<?php if($model->gr_number != ''):?>
<table class="table table-striped-columns">
    <tbody>
        <tr class="">
            <td class="text-end" style="width:150px;">ตรวจรับเลขที่</td>
            <td class="fw-semibold"><?= $model->gr_number ?></td>
            <td class="text-end">ลงวันที่</td>
            <td colspan="3">123</td>

        </tr>
        <tr class="">
            <td class="text-end">วันที่ลงนาม</td>
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
            <td><?= isset($model->data_json['pq_method_get_name']) ? $model->data_json['pq_method_get_name'] : '' ?>
            </td>
            <td class="text-end">ลงวันที่</td>
            <td> <?= isset($model->data_json['due_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') : '' ?>
            </td>
            <td class="text-end">เลขที่คำสั่ง</td>
            <td> กกน-123</td>
        </tr>
        <tr class="">
            <td class="text-end">ประเภทเงิน</td>
            <td><?= isset($model->data_json['pq_budget_type_name']) ? $model->data_json['pq_budget_type_name'] : '' ?>
            </td>
            <td class="text-end">ชื่อโครงการ</td>
            <td colspan="3">
                <?= isset($model->data_json['pq_project_name']) ? $model->data_json['pq_project_name'] : '' ?></td>
        </tr>

    </tbody>
</table>
<div class="d-flex justify-content-center mt-3">
    <?=Html::a('<i class="bi bi-pencil-square"></i> แก้ไขใบตรวจรับ',['/purchase/gr-order/update','id' => $model->id,'title' => '<i class="bi bi-pencil-square"></i> แก้ไขใบตรวจรับ'],['class' => 'btn btn-warning rounded-pill shadow text-center open-modal','data' => ['size' => 'modal-xl']])?>
</div>

<?php else:?>
    <div class="d-flex justify-content-center my-5">
    <?=Html::a('<i class="fa-solid fa-circle-plus text-white"></i> สร้างใบตรวจรับ',['/purchase/gr-order/update','id' => $model->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> ตรวจรับ'],['class' => 'btn btn-primary rounded-pill shadow text-center open-modal','data' => ['size' => 'modal-xl']])?>
</div>
<?php endif;?>