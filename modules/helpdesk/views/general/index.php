<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\hr\models\Employees;
use app\modules\helpdesk\models\Helpdesk;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\RepairSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'งานซ่อมบำรุง';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<?php // Pjax::begin(['id' => 'helpdesk-container','timeout' => 5000 ]); ?>

<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
    <h6><i class="bi bi-ui-checks"></i> ทะเบียนงานซ่อม <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
    <?=$this->render('_search', ['model' => $searchModel])?>
    </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">รายการ</th>
                    <th scope="col">ผู้แจ้งซ่อม</th>
                    <th style="width:300px">ผู้ร่วมงานซ่อม </th>
                    <th class="text-center" style="width:150px">สถานะ</th>
                    <th class="text-center" style="width:150px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $key => $model): ?>
                <tr class="align-middle">
                    <td><?php echo ($key+1)?></td>
                    <td>
                            <div class="d-flex">
                            <?php echo $model->RepairType()['image']?>
                            <div class="avatar-detail">
                                
                                <p class="text-primary fw-semibold fs-13 mb-0">
                                    <span class="badge text-bg-primary fs-13"><i class="fa-solid fa-circle-exclamation"></i>
                                    <?php echo $model->RepairType()['title']?>
                                </span>
                                <?= $model->viewUrgency() ?>
                            </p>
                            <p style="width:600px" class="text-truncate fw-semibold fs-6 mb-0"><?php echo $model->data_json['title']?></p>
                        </div>
                    </div>
                    </td>
                    <td> <?= $model->showAvatarCreate(); ?></td>
                    <td><?= $model->StackTeam() ?></td>
                    <td class="text-center"> <?= $model->viewStatus() ?></td>
                    <td class="text-center">
                        <?php if($model->status == 1):?>
                            <?= Html::a('<i class="fa-solid fa-user-pen"></i> รับเรื่อง', ['/helpdesk/repair/accept-job', 'id' => $model->id, 'title' => '<i class="fa-solid fa-hammer"></i> แก้ไขรายการส่งซ่อม'], ['class' => 'btn btn-warning accept-job', 'data' => ['size' => 'modal-lg']]) ?>
                            <?php // echo Html::a('<i class="fa-regular fa-hourglass-half"></i> รับเรื่อง',['/helpdesk/general/update','id' => $model->id],['class' => 'open-modal-x','data' => ['size' => 'modal-lg']])?>
                        <?php else:?>
                        <?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>',['update','id' => $model->id],['class' => 'open-modal-x','data' => ['size' => 'modal-lg']])?>
                    <?php endif;?>
                    </td>
                </tr>
    
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            <div class="text-muted">
                <?= LinkPager::widget([
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
</div>
<?php // Pjax::end(); ?>


<?php
$js = <<< JS
    $("body").on("click", ".accept-job", async function (e) {
      e.preventDefault();
      var url = \$(this).attr("href");
      await Swal.fire({
        title: "ยืนยันรับเรื่อง?",
        text: "รับเรื่องเพื่อบันทึกงานซ่อมต่อไป!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "ใช่, ลบเลย!",
        cancelButtonText: "ยกเลิก",
      }).then(async (result) => {
        console.log("result", result.value);
        if (result.value == true) {
           await \$.ajax({
            type: "post",
            url: url,
            dataType: "json",
            success:  function (response) {
                console.log(response);
                
              if (response.status == "success") {
                   location.reload()
              }

              
            },
          });
        }
      });
    });

JS;
$this->registerJS($js);
?>