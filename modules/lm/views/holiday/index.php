<?php

use app\components\AppHelper;
use app\modules\lm\models\Holiday;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\web\View;
use yii\web\ViewAction;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\lm\models\HolidaySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'กำหนดวันหยุด';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
$this->title = 'Calendar';
?>
<?php Pjax::begin(['id' => 'leave']); ?>

<div class="row d-flex justify-content-center">

    <div class="col-xl-6 col-lg-6 col-md-8 col-sm-12">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> วันหยุด <span class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?> </span> รายการ</h6>

            <div class="btn-group">
       <span class="btn btn-light">
          <?= Html::a('<i class="bi bi-calendar2-plus"></i> เพิ่มวันหยุด',['/lm/holiday/create','title' => '<i class="bi bi-calendar2-plus"></i> เพิ่มวันหยุด'],['class' => 'open-modal sync-date','data' => ['size' => 'modal-md'] ])?>
        </span>
        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"
            aria-expanded="false" data-bs-reference="parent">
            <i class="bi bi-caret-down-fill"></i>
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a('<i class="bi bi-database-fill-check me-1 fs-6"></i> การซิงค์ข้อมูลวันหยุด', ['/lm/holiday/sync-date'], ['class' => 'dropdown-item open-modal sync-date','data' => ['size' => 'modal-md']] ) ?>
            
            </li>
        </ul>
    </div>
    
        </div>
 
        <table
            class="table table-striped"
        >
            <thead>
                <tr>
                    <th scope="col"  style="width:150px">วันที่</th>
                    <th scope="col"  style="width:80px">ปีงบ</th>
                    <th scope="col">รายการ</th>
                    <th scope="col" class="text-center" style="width:80px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($dataProvider->getModels() as $model):?>
                <tr class="">
                    <td scope="row"><?=Yii::$app->thaiFormatter->asDate($model->data_json['date'], 'long')?></td>
                    <td><?=$model->data_json['thai_year']?></td>
                    <td><?=$model->title?></td>
                    <td class="text-center">
                        <?=Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/lm/holiday/update','id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i>แก้ไข'],['class' => 'btn btn-sm btn-warning open-modal','data' => ['size' => 'modal-md'] ])?>
                    </td>
                </tr>
                <?php endforeach?>
               
            </tbody>
        </table>
        </div>
</div>
</div>
</div>


  
<?php
$js = <<< JS
$('.sync-date').click(function (e) { 
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (res) {
            if(res.status == 'success') {
                //  $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
                 location.reload();
            }
        }
    });
    
});
JS;
$this->registerJS($js,View::POS_END);
    ?>
  <?php Pjax::end(); ?>
