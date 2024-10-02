<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

?>
<?php if($model->pq_number != ''):?>
    
<table class="table table-striped-columns">
    <tbody>
        <tr class="">
            <td class="text-end" style="width:150px;">ทะเบียนคุม</td>
            <td class="fw-semibold"><?= $model->pq_number ?></td>
            <td class="text-end">ตามคำสั่ง</td>
            <td><?=$model->data_json['order']?></td>

        </tr>
        <tr class="">
            <td class="text-end">วิธีซื้อหรือจ้าง</td>
            <td>
                <?= isset($model->data_json['pq_purchase_type_name']) ? $model->data_json['pq_purchase_type_name'] : '' ?>
            </td>
            <td class="text-end">เลขที่คำสั่ง</td>
            <td> <?=$model->data_json['order_number']?></td>
          

        </tr>
        <tr class="">
            <td class="text-end">วิธีจัดหา</td>
            <td><?= isset($model->data_json['pq_method_get_name']) ? $model->data_json['pq_method_get_name'] : '' ?>
            </td>
            <td class="text-end">ลงวันที่</td>
            <td> <?= isset($model->data_json['due_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'long') : '' ?>
            </td>
         
        </tr>
        <tr>
        <td class="text-end">เลขที่คำสั่ง</td>
        <td> กกน-123</td>
        <td class="text-end">การเบิกจ่ายเงิน</td>
        <td><?= isset($model->data_json['pq_disbursement']) ? $model->data_json['pq_disbursement'] : '' ?></td>
        </tr>
        <tr class="">
            <td class="text-end">ประเภทเงิน</td>
            <td><?= isset($model->data_json['pq_budget_type_name']) ? $model->data_json['pq_budget_type_name'] : '' ?>
            </td>
            <td class="text-end">ชื่อโครงการ</td>
            <td>
                <?= isset($model->data_json['pq_project_name']) ? $model->data_json['pq_project_name'] : '' ?></td>
        </tr>

        <tr class="">
            <td class="text-end">การพิจารณา</td>
            <td><?= isset($model->data_json['pq_consideration']) ? $model->data_json['pq_consideration'] : '' ?></td>
            <td class="text-end">โครงการเลขที่</td>
            <td><?= isset($model->data_json['pq_project_id']) ? $model->data_json['pq_project_id'] : '' ?></td>
          
        </tr>

        <tr class="">
            <td class="text-end">หมวดเงิน</td>
            <td><?= isset($model->data_json['pq_budget_group_name']) ? $model->data_json['pq_budget_group_name'] : '' ?>
            </td>
            <td class="text-end">รหัสอ้างอิง EGP</td>
            <td><?= isset($model->data_json['pq_egp_number']) ? $model->data_json['pq_egp_number'] : '' ?></td>
          
        </tr>
        <tr>
            <td class="text-end">เหตุผลความจำเป็น</td>
            <td>
                <?=$model->data_json['pq_reason']?>
            </td>
            <td class="text-end">รายการแผน EGP</td>
            <td><?= isset($model->data_json['pq_egp_report']) ? $model->data_json['pq_egp_report'] : '' ?></td>
        </tr>
        <tr>
        <td class="text-end">เหตุผลการจัดหา</td>
            <td colspan="3">
                <?=$model->data_json['pq_income_reason']?>
            </td>
        </tr>
    </tbody>
</table>

<div class="d-flex justify-content-center mt-3">
    <?=Html::a('<i class="bi bi-pencil-square"></i> แก้ไขทะเบียนคุม',['/purchase/pq-order/update','id' => $model->id,'title' => '<i class="bi bi-pencil-square"></i> แก้ไขทะเบียนคุม'],['class' => 'btn btn-warning rounded-pill shadow text-center open-modal-x','data' => ['size' => 'modal-lg']])?>
</div>

<?php else:?>
    <div class="d-flex justify-content-center my-5">
    <?=Html::a('<i class="fa-solid fa-circle-plus text-white"></i> สร้างทะเบียนคุม',['/purchase/pq-order/update','id' => $model->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> สร้างทะเบียนคุม'],['class' => 'btn btn-primary rounded-pill shadow text-center open-modal','data' => ['size' => 'modal-xl']])?>
</div>
<?php endif;?>