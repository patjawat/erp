<?php
use yii\helpers\Html;
use app\modules\am\models\AssetDetail;

$modelDetail = AssetDetail::find()->where(['name' => "tax_car",'code'=>$model->code])->orderBy(['date_start' => SORT_DESC])->one();

?>

<div class="alert alert-success" role="alert">
    <div class="d-flex justify-content-between">
        <span class="fw-semibold">
            <i class="fa-solid fa-car-on fs-4"></i> ข้อมูลการต่อภาษี
        </span>

    </div>
    <?php if(isset($modelDetail->data_json['company1']) && $modelDetail->data_json['company1'] != ""):?>
        <hr>
    <strong><i class="fa-solid fa-user-injured"></i> พรบ.</strong>
    <div class="border-bottom">
        <div class="row">
            <div class="col-6">
                <ul class="list-inline">
                    <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">บริษัท</span> :
                    <?=isset($modelDetail->data_json['company1']) ? $modelDetail->data_json['company1'] : '-'?></li>
                    <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                    class="fw-semibold">กรมธรรม์เลขที่</span>:
                    <?=isset($modelDetail->data_json['number1']) ? $modelDetail->data_json['number1'] : '-'?></li>
                </ul>
            </div>
            
            <div class="col-6">
                <ul class="list-inline">
                    <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">เริ่มต้น</span>
                </li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">สิ้นสุด</span>
            </li>
            
        </ul>
    </div>
</div>
</div>
<?php endif;?>
<?php if(isset($modelDetail->data_json['company2']) && $modelDetail->data_json['company2'] != ""):?>

        <strong><i class="fa-solid fa-car-burst"></i> ประกันภัย</strong>
    <div class="row">
        <div class="col-6">
            <ul class="list-inline">
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">บริษัท</span> :
                    <?= isset($modelDetail->data_json['company2']) ? $modelDetail->data_json['company2'] : '-'?></li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                        class="fw-semibold">กรมธรรม์เลขที่</span>:
                    <?=isset($modelDetail->data_json['number2']) ? $modelDetail->data_json['number2'] : '-'?></li>
            </ul>
        </div>

        <div class="col-6">
            <ul class="list-inline">
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">เริ่มต้น</span>
                    <?php
                    try {
                        echo isset($modelDetail->data_json["date_start2"]) ? Yii::$app->thaiFormatter->asDate($modelDetail->data_json["date_start2"], 'medium') : '';
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?>
                </li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">สิ้นสุด</span>
                <?php
                    try {
                        echo isset($modelDetail->data_json["date_end2"]) ? Yii::$app->thaiFormatter->asDate($modelDetail->data_json["date_end2"], 'medium') : '';
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?>
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
                    <?= isset($modelDetail->date_start) ? Yii::$app->thaiFormatter->asDate($modelDetail->date_start, 'medium') : '-' ?>
                </span>
            </li>
            <li><i class="fa-regular fa-calendar-xmark fs-5"></i> <span class="">วันที่ครบกำหนดชำระ</span> :
                <span class="text-danger fw-semibold">
                    <?= isset($modelDetail->date_end) ? Yii::$app->thaiFormatter->asDate($modelDetail->date_end, 'medium') : '-' ?>
                </span>
            </li>

        </ul>
        <?=Html::a('<i class="fa-solid fa-gear"></i> ดำเนินการ',['/am/asset-detail/','id'=> $model->id,'name' => 'tax_car','title' => '<i class="fa-solid fa-car-on"></i> ข้อมูลการต่อภาษี',"code"=>$model->code],['class' => 'btn btn-primary rounded-pill border border-white open-modal','data' => ['size' => 'modal-xl']])?>

    </div>


</div>