<?php
use yii\helpers\Html;
use app\components\SiteHelper;
?>
<table class="table table-striped-columns">
    <tbody>
        <tr class="">
            <td class="text-end" style="width:150px;">เลขที่ขอซื้อ</td>
            <td class="fw-semibold"><?= $model->pr_number ?></td>
            <td class="text-end">วันที่ขอซื้อ</td>
            <td> <?php echo Yii::$app->thaiFormatter->asDateTime($model->pr_create_date, 'medium') ?></td>
        </tr>
        <tr class="">
           
            <td class="text-end">วันที่ต้องการ</td>
            <td> <?= isset($model->data_json['due_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') : '' ?>
            </td>
        </tr>
        <tr>
            <td class="text-end">ผู้ขอ</td>
            <td colspan="3"> <?= $model->getUserReq()['avatar'] ?></td>

        </tr>

    </tbody>
</table>

<div class="d-flex justify-content-center mt-3">
    <?=Html::a('<i class="bi bi-pencil-square"></i> แก้ไข',['/purchase/pr-order/update','id' => $model->id,'title' => '<i class="bi bi-pencil-square"></i> แก้ไขคำขอซื้อ'],['class' => 'btn btn-warning rounded-pill shadow text-center open-modal','data' => ['size' => 'modal-md']])?>
</div>