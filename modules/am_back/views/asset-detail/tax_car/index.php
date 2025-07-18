<?php

use app\components\AppHelper;
use app\modules\am\models\AssetDetail;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\web\View;

$this->title = Yii::$app->request->get('title');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="d-flex justify-content-between">
    <?=app\components\AppHelper::Btn([
    'title' => '<i class="fa-solid fa-circle-plus me-2"></i> สร้างใหม่',
    'url' => ['/am/asset-detail/create', 'id' => $id, 'name' => 'tax_car', 'title' => '<i class="fa-solid fa-car-on"></i> ข้อมูล พรบ./การต่อภาษี', 'code' => $code],
    'modal' => true,
    'size' => 'lg',
])?>
</div>

<?php Pjax::begin(['id' => 'tax-container']);?>

<table class="table table-striped mt-3">
    <thead class="table-dark">
        <tr>
            <th><i class="fa-solid fa-tag"></i> ข้อมูลการต่อภาษี</th>
            <th><i class="fa-solid fa-user-injured"></i> ข้อมูล พรบ.</th>
            <th><i class="fa-solid fa-car-burst"></i> ประกันภัย</th>
            <th style="width:100px">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody>

        <?php foreach ($dataProvider->getModels() as $model): ?>
        <?php
            $startDate = AppHelper::DateFormDb($model->date_start);
            $endDate = AppHelper::DateFormDb($model->date_end);
            // $startDate1 = isset($model->data_json['date_start1']) ? $model->data_json['date_start1'] : '-';
            // $endDate1 = isset($model->data_json['date_end1']) ? $model->data_json['date_end1'] : '-';
            // $startDate2 = isset($model->data_json['date_start2']) ? AppHelper::DateFormDb($model->data_json['date_start2']) : '-';
            // $endDate2 = isset($model->data_json['date_end2']) ? AppHelper::DateFormDb($model->data_json['date_end2']) : '-';
            ?>

        <tr class="">
            <td>
                <div>ค่าภาษี : <?=$model->data_json['price']?></div>
                <div><?=$startDate?> ถึง <?=$endDate?></div>
            </td>
            <td>
                <div class="d-flex flex-column">
                    <?=isset($model->data_json['company1']) ? $model->data_json['company1'] : '-'?>
                    <br>
                    <?php
                    try {
                        $dateStart1 =  isset($model->data_json["date_start1"]) ? Yii::$app->thaiFormatter->asDate($model->data_json["date_start1"], 'medium') : '';
                        $dateEnd1=  isset($model->data_json["date_end1"]) ? Yii::$app->thaiFormatter->asDate($model->data_json["date_end1"], 'medium') : '';
                        echo $dateStart1.' ถึง '.$dateEnd1;
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?>
                </div>
            </td>
            <td>
            <?php if(isset($model->data_json['company2']) && $model->data_json['company2'] != ""):?>
                <div class="d-flex flex-column">
                <?=isset($model->data_json['company2']) ? $model->data_json['company2'] : '-'?>
                    <div>
                        <div>
                        <?php
                    try {
                        $dateStart2 =  isset($model->data_json["date_start2"]) ? Yii::$app->thaiFormatter->asDate($model->data_json["date_start2"], 'medium') : '';
                        $dateEnd2=  isset($model->data_json["date_end2"]) ? Yii::$app->thaiFormatter->asDate($model->data_json["date_end2"], 'medium') : '';
                        echo $dateStart2.' ถึง '.$dateEnd2;
                    } catch (\Throwable $th) {
                       echo '-';
                    }
                    ?>
                        </div>
                    </div>
                </div>
                <?php endif;?>
            </td>
            <td class="align-middle">
                <?=Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/am/asset-detail/update', 'id' => $model->id, 'title' => 'แก้ไขพ.ร.บ./ต่อภาษี', "name" => "tax_car"], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-lg']])?>
                <?=Html::a('<i class="fa-solid fa-trash"></i>', ['/am/asset-detail/delete', 'id' => $model->id,'container' => 'am-container'], [
                                        'class' => 'btn btn-sm btn-danger delete-item',
                                        ])?>
            </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>

<?php Pjax::end();?>
<?php
$js = <<< JS

JS;
$this->registerJS($js,View::POS_END);
?>