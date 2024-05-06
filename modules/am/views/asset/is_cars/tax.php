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
    <strong><i class="fa-solid fa-user-injured"></i> พรบ.</strong>
    <table class="table border-0 table-striped-columns">
        <tbody>
            <tr class="">
                <td class="text-end"><span class="fw-semibold">บริษัท :</span></td>
                <td colspan="3"> <?=isset($modelDetail->data_json['company1']) ? $modelDetail->data_json['company1'] : '-'?></td>
               
            </tr>
            <tr>
            <td class="text-end"><span class="fw-semibold">กรมธรรม์เลขที่ :</span></td>
            <td colspan="3"><?=isset($modelDetail->data_json['number1']) ? $modelDetail->data_json['number1'] : '-'?></td>
            </tr>
            <tr>
                <td class="text-end"><span class="fw-semibold">เริ่มต้น :</span></td>
                <td>
                    <?php
                    try {
                        echo isset($modelDetail->data_json["date_start1"]) ? Yii::$app->thaiFormatter->asDate($modelDetail->data_json["date_start1"], 'medium') : '';
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?>
                </td>
                <td class="text-end"><span class="fw-semibold">ถึง :</span></td>
                <td> <?php
                    try {
                        echo isset($modelDetail->data_json["date_end1"]) ? Yii::$app->thaiFormatter->asDate($modelDetail->data_json["date_end1"], 'medium') : '';
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif;?>
    <?php if(isset($modelDetail->data_json['company2']) && $modelDetail->data_json['company2'] != ""):?>
    <strong><i class="fa-solid fa-car-burst"></i> ประกันภัย</strong>
    <table class="table border-0 table-striped-columns">
        <tbody>
          
            <tr class="">
                <td class="text-end"><span class="fw-semibold">บริษัท :</span></td>
                <td colspan="3"><?=isset($modelDetail->data_json['company2']) ? $modelDetail->data_json['company2'] : '-'?></td>
            </tr>
            <tr>
                <td class="text-end"><span class="fw-semibold">กรมธรรม์เลขที่ :</span></td>
                <td colspan="3"><?=isset($modelDetail->data_json['number2']) ? $modelDetail->data_json['number2'] : '-'?></td>
            </tr>
            <tr>
                <td class="text-end"><span class="fw-semibold">เริ่มต้น :</span></td>
                <td>
                    <?php
                    try {
                        echo isset($modelDetail->data_json["date_start2"]) ? Yii::$app->thaiFormatter->asDate($modelDetail->data_json["date_start2"], 'medium') : '';
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?>
                </td>
                <td class="text-end"><span class="fw-semibold">ถึง :</span></td>
                <td> <?php
                    try {
                        echo isset($modelDetail->data_json["date_end2"]) ? Yii::$app->thaiFormatter->asDate($modelDetail->data_json["date_end2"], 'medium') : '';
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif;?>
    
    <div class="d-flex justify-content-between align-items-center bg-primary bg-opacity-10  p-3 rounded">
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