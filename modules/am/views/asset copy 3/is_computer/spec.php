<?php
use app\modules\am\models\AssetDetail;
use yii\helpers\Html;

$modelDetail = AssetDetail::find()->where(['name' => 'tax_car', 'code' => $model->code])->orderBy(['date_start' => SORT_DESC])->one();

?>
<tr>
    <td>
    </td>
    <td colspan="5">
        <?= Html::a('<span class="fw-semibold"><i class="fa-solid fa-computer fs-5"></i> Computer Spec</span>', ['/am/asset/update-computer', 'id' => $model->id, 'title' => ' <i class="fa-solid fa-computer fs-4"></i> Spec Computer'], ['class' => 'btn btn-primary rounded-pill border border-white open-modal', 'data' => ['size' => 'modal-md']]) ?>
    </td>
</tr>
<tr class="">
    <td class="text-end"><span class="fw-semibold">ยี่ห้อ :</span></td>
    <td><?= isset($model->data_json['brand']) ? $model->data_json['brand'] : '-' ?></td>
    <td class="text-end"><span class="fw-semibold">รุ่น :</span></td>
    <td><?= isset($model->data_json['model']) ? $model->data_json['model'] : '-' ?></td>
    <td class="text-end"><span class="fw-semibold">CPU :</span></td>
    <td><?= isset($model->data_json['cpu']) ? $model->data_json['cpu'] : '-' ?></td>
</tr>
<tr class="">
    <td class="text-end"><span class="fw-semibold">RAM : </span></td>
    <td><?= isset($model->data_json['ram']) ? $model->data_json['ram'] : '-' ?></td>
    <td class="text-end"><span class="fw-semibold">Storage : </span></td>
    <td><?= isset($model->data_json['storage_type']) ? $model->data_json['storage_type'] : '-' ?></td>
    <td class="text-end"><span class="fw-semibold">ขนาด : </span></td>
    <td><?= isset($model->data_json['storage_size']) ? $model->data_json['storage_size'] : '-' ?></td>
</tr>
<tr class="">
    <td class="text-end"><span class="fw-semibold">OS : </span></td>
    <td colspan="5"><?= isset($model->data_json['os']) ? $model->data_json['os'] : '-' ?></td>
</tr>
<tr class="">
    <td class="text-end"><span class="fw-semibold">IP-Address: </span></td>
    <td colspan="5"><?= isset($model->data_json['ip_address']) ? $model->data_json['ip_address'] : '-' ?></td>
</tr>


<!-- <div class="d-flex justify-content-between align-items-center">
        <?= Html::a('<i class="fa-solid fa-gear"></i> ดำเนินการ', ['/am/asset/update-computer', 'id' => $model->id, 'title' => ' <i class="fa-solid fa-computer fs-4"></i> Spec Computer'], ['class' => 'btn btn-primary rounded-pill border border-white open-modal', 'data' => ['size' => 'modal-md']]) ?>

    </div> -->