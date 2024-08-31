<?php
use yii\helpers\Html;
?>
<?php if($model->gr_number != ''):?>
<table class="table table-striped-columns">
    <tbody>
        <tr class="">
            <td class="text-end" style="width:150px;">ตรวจรับเลขที่</td>
            <td class="fw-semibold"><?= $model->gr_number ?></td>
            <td class="text-end">เลขที่ส่งสินค้า</td>
            <td><?=isset($model->data_json['gr_number']) ? $model->data_json['gr_number'] : '-'?></td>
        </tr>
        <tr>
        
          <td class="text-end">วันที่ตรวจรับ</td>
          <td> <?= isset($model->data_json['gr_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['gr_date'], 'long') : '' ?>
            <td class="text-end">ผลการตรวจสอบ</td>
            <td colspan="3">
                <?php //  $model->data_json['order_item_checke']?>
                <?=isset($model->data_json['order_item_checker']) ? $model->data_json['order_item_checker'] : '-'?></td>
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