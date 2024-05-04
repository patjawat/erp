<?php
use yii\helpers\Html;
use app\modules\am\models\AssetDetail;


$model_detail = AssetDetail::find()->where(['name' => "tax_car",'code'=>$model->code])->orderBy(['date_start' => SORT_DESC])->one()
?>

<div class="alert alert-success" role="alert">
    <div class="d-flex justify-content-between">
        <span class="fw-semibold">
            <i class="fa-solid fa-car-on fs-4"></i> ข้อมูลการต่อภาษี
        </span>

    </div>
    <?php if(isset($model_detail->data_json['company1']) && $model_detail->data_json['company1'] != ""):?>
        <hr>
    <strong><i class="fa-solid fa-user-injured"></i> พรบ.</strong>
    <div class="border-bottom">
        <div class="row">
            <div class="col-6">
                <ul class="list-inline">
                    <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">บริษัท</span> :
                    <?=isset($model_detail->data_json['company1']) ? $model_detail->data_json['company1'] : '-'?></li>
                    <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                    class="fw-semibold">กรมธรรม์เลขที่</span>:
                    <?=isset($model_detail->data_json['number1']) ? $model_detail->data_json['number1'] : '-'?></li>
                </ul>
            </div>
            
            <div class="col-6">
                <ul class="list-inline">
                    <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">เริ่มต้น</span>
                    <?php
                    print_r($model->data_json)
                    ?>
                    <?php // isset($model_detail->data_json["date_start1"]) ? Yii::$app->thaiFormatter->asDate($model_detail->data_json["date_start1"],'medium') : '-' ?>
                </li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">สิ้นสุด</span>
                <?php //  isset($model_detail->data_json["date_end1"]) ? Yii::$app->thaiFormatter->asDate($model_detail->data_json["date_end1"],'medium') : '-' ?>
            </li>
            
        </ul>
    </div>
</div>
</div>
<?php endif;?>
<?php if(isset($model_detail->data_json['company2']) && $model_detail->data_json['company2'] != ""):?>

        <strong><i class="fa-solid fa-car-burst"></i> ประกันภัย</strong>
    <div class="row">
        <div class="col-6">
            <ul class="list-inline">
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">บริษัท</span> :
                    <?= isset($model_detail->data_json['company2']) ? $model_detail->data_json['company2'] : '-'?></li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                        class="fw-semibold">กรมธรรม์เลขที่</span>:
                    <?=isset($model_detail->data_json['number2']) ? $model_detail->data_json['number2'] : '-'?></li>
            </ul>
        </div>

        <div class="col-6">
            <ul class="list-inline">
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">เริ่มต้น</span>
                    <?= isset($model_detail->data_json["date_start2"]) ? Yii::$app->thaiFormatter->asDate(date("m/d/Y", strtotime(str_replace("/", "-", $model_detail->data_json["date_start2"]))), 'medium') . " : " . date("H:i", strtotime(str_replace("/", "-", $model_detail->data_json["date_start2"]))): '-' ?>
                </li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">สิ้นสุด</span>
                    <?= isset($model_detail->data_json["date_end"]) ? Yii::$app->thaiFormatter->asDate(date("m/d/Y", strtotime(str_replace("/", "-",$model_detail->data_json["date_end2"]))), 'medium') . " : " . date("H:i", strtotime(str_replace("/", "-",$model_detail->data_json["date_end2"]))): '-' ?>
                </li>
            </ul>
        </div>
    </div>
    <?php endif;?>
    <hr class="mt-1">

    <div class="d-flex justify-content-between align-items-center">
        <ul class="list-inline mb-0">
            <li><i class="fa-regular fa-calendar-check fs-5"></i> <span class="">วันที่ต่อภาษี</span> :
                <span class="text-danger fw-semibold">
                    <?= isset($model_detail->date_end) ? Yii::$app->thaiFormatter->asDate(date("m/d/Y", strtotime($model_detail->date_end)), 'medium') : '-' ?>
                </span>
            </li>
            <li><i class="fa-regular fa-calendar-xmark fs-5"></i> <span class="">วันที่ครบกำหนดชำระ</span> :
                <span class="text-danger fw-semibold">
                    <?= isset($model_detail->date_end) ? Yii::$app->thaiFormatter->asDate(date("m/d/Y", strtotime($model_detail->date_end)), 'medium') : '-' ?>
                </span>
            </li>

        </ul>
        <?=Html::a('<i class="fa-solid fa-gear"></i> ดำเนินการ',['/am/asset-detail/','id'=> $model->id,'name' => 'tax_car','title' => '<i class="fa-solid fa-car-on"></i> ข้อมูลการต่อภาษี',"code"=>$model->code],['class' => 'btn btn-primary rounded-pill border border-white open-modal','data' => ['size' => 'modal-xl']])?>

    </div>


</div>