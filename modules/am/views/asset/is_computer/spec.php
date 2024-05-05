<?php
use yii\helpers\Html;
use app\modules\am\models\AssetDetail;

$modelDetail = AssetDetail::find()->where(['name' => "tax_car",'code'=>$model->code])->orderBy(['date_start' => SORT_DESC])->one();

?>

<div class="alert alert-success" role="alert">
    <div class="d-flex justify-content-between">
        <span class="fw-semibold">
            <i class="fa-solid fa-computer fs-4"></i> Spec Computer
        </span>
    </div>
    <hr class="p-1">

    <table class="table border-0 table-striped-columns">
        <tbody>
            <tr class="">
                <td><span class="fw-semibold">ยี่ห้อ :</span></td>
                <td><?=isset($model->data_json['brand']) ? $model->data_json['brand'] : '-'?></td>
                <td><span class="fw-semibold">รุ่น :</span></td>
                <td><?=isset($model->data_json['model']) ? $model->data_json['model'] : '-'?></td>
                <td><span class="fw-semibold">CPU :</span></td>
                <td><?=isset($model->data_json['cpu']) ? $model->data_json['cpu'] : '-'?></td>
            </tr>
            <tr class="">
                <td><span class="fw-semibold">RAM : </span></td>
                <td><?=isset($model->data_json['ram']) ? $model->data_json['ram'] : '-'?></td>
                <td><span class="fw-semibold">Storage : </span></td>
                <td><?=isset($model->data_json['storage_type']) ? $model->data_json['storage_type'] : '-'?></td>
                <td><span class="fw-semibold">ขนาด : </span></td>
                <td><?=isset($model->data_json['storage_size']) ? $model->data_json['storage_size'] : '-'?></td>
            </tr>
            <tr class="">
                <td><span class="fw-semibold">OS : </span></td>
                <td colspan="5"><?=isset($model->data_json['os']) ? $model->data_json['os'] : '-'?></td>

            </tr>
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center">
        <?=Html::a('<i class="fa-solid fa-gear"></i> ดำเนินการ',['/am/asset/update-computer','id'=> $model->id,'title' => ' <i class="fa-solid fa-computer fs-4"></i> Spec Computer'],['class' => 'btn btn-primary rounded-pill border border-white open-modal','data' => ['size' => 'modal-md']])?>

    </div>


</div>