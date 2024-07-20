<?php
use yii\helpers\Html;
use app\components\SiteHelper;
?>
<table class="table table-striped-columns">
    <tbody>
        <tr class="">
            <td class="text-end">วันที่ขอซื้อ</td>
            <td> <?php echo Yii::$app->thaiFormatter->asDateTime($model->created_at, 'medium') ?></td>
            <td class="text-end" style="width:150px;">เลขที่ขอซื้อ</td>
            <td class="fw-semibold"><?= $model->pr_number ?></td>
        </tr>
        <tr class="">
            <td class="text-end">เพื่อจัดซื้อ/ซ่อมแซม</td>
            <td>
                <?php
                            // echo $model->productType->id;
                            try {
                                echo $model->data_json['type_name'];
                            } catch (\Throwable $th) {
                            }
                        ?></td>
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