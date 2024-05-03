<?php

use app\components\AppHelper;
use app\modules\am\models\AssetDetail;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::$app->request->get('title');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="d-flex justify-content-between">
    <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus me-2"></i> สร้างใหม่',
                    'url' =>['/am/asset-detail/create','id'=> $id,'name' => 'tax_car','title' => '<i class="fa-solid fa-car-on"></i> ข้อมูลการต่อภาษี','code'=>$code],
                    'modal' =>true,
                    'size' => 'xl',
            ])?>
       

</div>

<?php Pjax::begin(); ?>
<?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <table class="table table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th style="width:250px">ชื่อบริษัท</th>
                <th scope="col">ระยะเวลาคุ้มครอง</th>
                <th scope="col">เบี้ยประกัน</th>
                <th scope="col">กรมธรรม์</th>
                <th style="width:100px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
                
            <?php foreach($dataProvider->getModels() as $model):?>
                <?php
                    $startDate = AppHelper::DateFormDb($model->data_json["date_start"]);
                    $endDate = AppHelper::DateFormDb($model->data_json["date_end"]);
                    ?>
            <tr class="">
                <td>
                    <div class="d-flex flex-column">
                        <span>
                            <?=$model->data_json['company']?>
                        </span>
                      
                        <span>
                        ตัวแทน : <?=$model->data_json['sale']?>
                        </span>
                    </div>
                </td>
                <td>
                <div class="d-flex flex-column">
                <span>
                    <i class="bi bi-check2-circle"></i> <?= isset($model->date_start) ? Yii::$app->thaiFormatter->asDate(date("m/d/Y", strtotime(str_replace("/", "-", $model->date_start))), 'medium') : '-' ?>
            </span>
                    <span>
                    <i class="bi bi-stop-circle"></i> <?= isset($model->date_end) ? Yii::$app->thaiFormatter->asDate(date("m/d/Y", strtotime(str_replace("/", "-", $model->date_end))), 'medium') : '-' ?> 
            </span>
                </td>
                <td class="align-middle"><?=$model->data_json['price']?></td>

                <td class="align-middle"><?=$model->data_json['number']?></td>
                <td class="align-middle">
                    <?=Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/am/asset-detail/update','id'=> $model->id,'title' => 'แก้ไขพ.ร.บ./ต่อภาษี',"name"=>"tax_car"],['class' => 'btn btn-sm btn-warning open-modal','data' => ['size' => 'modal-xl']])?>
                    <?=Html::a('<i class="fa-solid fa-trash"></i>',['/am/asset-detail/update','id'=> $model->id,'title' => 'แก้ไขพ.ร.บ./ต่อภาษี'],['class' => 'btn btn-sm btn-danger open-modal','data' => ['size' => 'modal-lg']])?>
                </td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>

<?php Pjax::end(); ?>