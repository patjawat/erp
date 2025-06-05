<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\web\ViewAction;
use yii\grid\ActionColumn;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\HolidaySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'กำหนดวันหยุด';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-mug-hot fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/hr/views/leave/menu',['active' => 'setting'])?>
<?php $this->endBlock(); ?>


<?php
$this->title = 'กำหนดวันหยุด';
?>


<?php Pjax::begin(['id' => 'leave']); ?>

<div class="card">
    <div class="card-body">
 <div class="d-flex justify-content-between">
    <div>
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มวันหยุด', ['/hr/holiday/create', 'title' => '<i class="bi bi-calendar2-plus"></i> เพิ่มวันหยุด'], ['class' => 'btn btn-primary open-modal rounded-pill shadow', 'data' => ['size' => 'modal-md']]) ?>
        <?= Html::a('<i class="fa-solid fa-cloud"></i> โหลดวันหยุดอัตโนมัติ', ['/hr/holiday/sync-date'], ['class' => 'btn btn-secondary rounded-pill shadow sync-date text-white']) ?>
    </div>
      <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    </div>
</div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> วันหยุด <span class="badge rounded-pill text-bg-primary">
                            <?= $dataProvider->getTotalCount() ?> </span> รายการ</h6>

                    <div class="btn-group">
                        <span class="btn btn-light">
                            
                        </span>
                        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                            <i class="bi bi-caret-down-fill"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>

                            </li>
                        </ul>
                    </div>

                </div>
              

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col" style="width:150px">วันที่</th>
                            <!-- <th scope="col" style="width:80px">ปีงบ</th> -->
                            <th scope="col">รายการ</th>
                            <th scope="col" class="text-center" style="width:120px">ดำเนินการ</th>
                        </tr>
                    </thead>
                   <tbody class="align-middle table-group-divider">
                        <?php foreach ($dataProvider->getModels() as $model): ?>
                            <tr class="">
                                <td scope="row"><?= Yii::$app->thaiFormatter->asDate($model->date_start, 'long') ?>
                                </td>
                                <!-- <td><?php // $model->thai_year ?></td> -->
                                <td><?= $model->title ?></td>
                                <td class="text-center">
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/hr/holiday/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i>แก้ไข'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    <?= Html::a('<i class="fa-solid fa-trash"></i>', ['/hr/holiday/delete', 'id' => $model->id, 'title' => '<i class="fa-solid fa-trash"></i>ลบ'], ['class' => 'btn btn-sm btn-danger delete-item', 'data' => ['size' => 'modal-md']]) ?>
                                </td>
                            </tr>
                        <?php endforeach ?>

                    </tbody>
                </table>

                <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
                    <?= yii\bootstrap5\LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'firstPageLabel' => 'หน้าแรก',
                        'lastPageLabel' => 'หน้าสุดท้าย',
                        'options' => [
                            'listOptions' => 'pagination pagination-sm',
                            'class' => 'pagination-sm',
                        ],
                    ]); ?>
                </div>


            </div>
        </div>

<?php Pjax::end(); ?>

<?php
$js = <<< JS
$('.sync-date').click(function (e) { 
e.preventDefault()
    Swal.fire({
            title: 'ยืนยัน',
            html:'<i class="bi bi-database-fill-check me-1 fs-1"></i> การซิงค์ข้อมูลวันหยุด',
            icon: "info",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "<i class='bi bi-x-circle'></i> ยกเลิก",
            confirmButtonText: "<i class='bi bi-check-circle'></i> ยืนยัน"
            }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "get",
                    url: $(this).attr('href'),
                    beforeSend : function(){
                    beforLoadModal()
                },
                dataType: "json",
                success: function (res) {
                    $("#main-modal").modal("toggle");
                    console.log(res.status);
                    if(res.status == 'success') {
                        window.location.reload(true);
                        //  $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
                    }
                }
            });
            }
            });
        
    });


JS;
$this->registerJS($js, View::POS_END);
?>

