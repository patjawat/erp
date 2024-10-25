<?php
use yii\helpers\Html;
?>
<?php if($model->po_number != ''):?>
    <?php
        echo $model->data_json['po_date'];
        ?>
<table class="table table-striped-columns">
    <tbody>
        <tr class="">
            <td class="text-end" style="width:150px;">คำสั่งซื้อเลขที่</td>
            <td class="fw-semibold"><?= $model->po_number ?></td>
            <td class="text-end">ลงวันที่</td>
            <td> <?= isset($model->data_json['po_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['po_date'], 'long') : '' ?>

        </tr>
        <tr class="">
            <td class="text-end">สิ้นสุดวันที่</td>
            <td> <?= isset($model->data_json['po_expire_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['po_expire_date'], 'long') : '' ?>
            <td class="text-end">วันที่รับใบสั่ง</td>
            <td> <?= isset($model->data_json['order_receipt_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['order_receipt_date'], 'long') : '' ?>

        </tr>
        <tr class="">
            <td class="text-end">กำหนดวันส่งมอบ</td>
            <td> <?= isset($model->data_json['delivery_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['delivery_date'], 'long') : '' ?>
        </td>
        <td class="text-end">วันที่ลงนาม</td>
        <td> <?= isset($model->data_json['signing_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['signing_date'], 'long') : '' ?>
        
        </tr>

        <tr class="">
        <td class="text-end">การรับประกัน</td>
        <td> <?= isset($model->data_json['warranty_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['warranty_date'], 'long') : '' ?>
        </td>
       
        </tr>

        <tr>
        <td class="text-end">ผู้รับใบสั่งซื้อ</td>
        <td colspan="3"> <?= isset($model->data_json['contact_name']) ? $model->data_json['contact_name'] : '' ?> (<?= isset($model->data_json['contact_position']) ? $model->data_json['contact_position'] : '' ?>)
        </tr>

    </tbody>
</table>
<div class="d-flex justify-content-center mt-3">
    <?=Html::a('<i class="bi bi-pencil-square"></i> แก้ไขคำสั่งซื้อ',['/purchase/po-order/update','id' => $model->id,'title' => '<i class="bi bi-pencil-square"></i> แก้ไขคำสั่งซื้อ'],['class' => 'btn btn-warning rounded-pill shadow text-center open-modal','data' => ['size' => 'modal-lg']])?>
</div>

<?php else:?>
    <div class="d-flex justify-content-center my-5">
    <?=Html::a('<i class="fa-solid fa-circle-plus text-white"></i> สร้างคำสั่งซื้อ',['/purchase/po-order/update','id' => $model->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> สร้างคำสั่งซื้อ'],['class' => 'btn btn-primary rounded-pill shadow text-center open-modal','data' => ['size' => 'modal-lg']])?>
</div>
<?php endif;?>