<?php
use yii\helpers\Html;
use app\modules\am\models\AssetDetail;

$modelCar = AssetDetail::find()->where(['name' => "tax_car",'code'=>$model->code])->orderBy(['date_start' => SORT_DESC])->one();
?>
 
<table class="table table-striped-columns">
    <tbody>
<tr>
            <td class="text-end"><span class="fw-semibold">วันที่ต่อภาษี</span></td>
            <td colspan="3"><?= isset($modelCar->date_start) ? Yii::$app->thaiFormatter->asDate($modelCar->date_start, 'medium') : '-' ?></td>
            <td class="text-end"><span class="fw-semibold">วันที่ครบกำหนดชำร</span></td>
            <td colspan="3"><?= isset($modelCar->date_end) ? Yii::$app->thaiFormatter->asDate($modelCar->date_end, 'medium') : '-' ?></td>
        </tr>
        <?php if(isset($modelCar->data_json['company1']) && $modelCar->data_json['company1'] != ""):?>
        <tr>
            <td class="text-end"><span class="fw-semibold">พรบ : </span></td>
            <td colspan="3"><?=isset($modelCar->data_json['company1']) ? $modelCar->data_json['company1'] : '-'?></td>
            <td class="text-end"><span class="fw-semibold">กรมธรรม์เลขที่ :</span></td>
            <td colspan="3"><?=isset($modelDetail->data_json['number1']) ? $modelDetail->data_json['number1'] : '-'?></td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">เริ่มต้น</span></td>
            <td colspan="3">
            <?php
                    try {
                        echo isset($modelCar->data_json["date_start1"]) ? Yii::$app->thaiFormatter->asDate($modelCar->data_json["date_start1"], 'medium') : '';
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?>
            </td>
            <td class="text-end"><span class="fw-semibold">ถึงวันที่</span></td>
            <td colspan="3">
            <?php
                    try {
                        echo isset($modelCar->data_json["date_end1"]) ? Yii::$app->thaiFormatter->asDate($modelCar->data_json["date_end1"], 'medium') : '';
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?>

            </td>
        </tr>
        <?php endif;?>
        <?php if(isset($modelCar->data_json['company2']) && $modelCar->data_json['company2'] != ""):?>
        <tr>
            <td class="text-end"><span class="fw-semibold">ประกันภัย : </span></td>
            <td colspan="3"><?=isset($modelCar->data_json['company2']) ? $modelCar->data_json['company2'] : '-'?></td>
            <td class="text-end"><span class="fw-semibold">กรมธรรม์เลขที่ :</span></td>
            <td colspan="3"><?=isset($modelDetail->data_json['number2']) ? $modelDetail->data_json['number2'] : '-'?></td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">เริ่มต้น</span></td>
            <td colspan="3">
            <?php
                    try {
                        echo isset($modelCar->data_json["date_start2"]) ? Yii::$app->thaiFormatter->asDate($modelCar->data_json["date_start2"], 'medium') : '';
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?>
            </td>
            <td class="text-end"><span class="fw-semibold">ถึงวันที่</span></td>
            <td colspan="3">
            <?php
                    try {
                        echo isset($modelCar->data_json["date_end2"]) ? Yii::$app->thaiFormatter->asDate($modelCar->data_json["date_end2"], 'medium') : '';
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?>

            </td>
        </tr>
        <?php endif;?>
        </tbody>
</table>
<div class="d-flex justify-content-center">
    <?=Html::a('<i class="fa-solid fa-gear"></i> ดำเนินการ',['/am/asset-detail/','id'=> $model->id,'name' => 'tax_car','title' => '<i class="fa-solid fa-car-on"></i> ข้อมูลการต่อภาษี',"code"=>$model->code],['class' => 'btn btn-primary rounded-pill border border-white open-modal text-center','data' => ['size' => 'modal-xl']])?>
</div>
