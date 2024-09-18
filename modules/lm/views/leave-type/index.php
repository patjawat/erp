<?php

use app\modules\lm\models\LeaveTypes;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\widgets\SwitchInput;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveTypesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ตั้งค่าประเภทการลา';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-gear"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'leave']); ?>
<div class="row d-flex justify-content-center">
    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6><i class="bi bi-ui-checks"></i> ประเภทการลา <span class="badge rounded-pill text-bg-primary">
                            <?= $dataProvider->getTotalCount() ?></span> รายการ</h6>
                    <div>
                        <?php //  Html::a('<i class="bi bi-plus-circle-fill"></i> สร้างรายการใหม่', ['/leave/leave-types/create', 'title' => '<i class="bi bi-plus-circle-fill"></i> สร้างรายการใหม่'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']]) 
            ?>
                        <?= Html::a('<i class="bi bi-gear"></i> กำหนดวันลา', ['/leave/setting', 'title' => '<i class="bi bi-plus-circle-fill"></i> สร้างรายการใหม่'], ['class' => 'btn btn-primary rounded-pill shadow', 'data' => ['size' => 'modal-md']]) ?>
                    </div>
                </div>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col" style="width:32px">#</th>
                            <th scope="col">รายการ</th>
                            <th scope="col" style="width:30px">สถานะ</th>
                            <th class="text-center" scope="col" style="width:100px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataProvider->getModels() as $item): ?>
                        <tr>
                            <td><?= $item->code ?></td>
                            <td><?= $item->title ?></td>
                            <td class="text-center">

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="<?= $item->id ?>"
                                        <?= $item->active == 1 ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="dropdown float-center">
                                    <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">

                                        <?= Html::a('<i class="fa-solid fa-eye me-1"></i>แสดง', ['/leave/leave-type/view', 'id' => $item->code, 'title' => '<i class="fa-solid fa-eye"></i> แสดง'], ['class' => 'dropdown-item', 'data' => ['size' => 'modal-md']]) ?>
                                        <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/leave/leave-type/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    </div>

                                </div>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>



            </div>
        </div>


    </div>
</div>
<div class="d-flex justify-content-center">
    <?= Html::a('<i class="bi bi-arrow-left-circle"></i> ย้อนกลับ', ['/lm/setting'], ['class' => 'btn btn-primary shadow rounded-pill text-center']) ?>
</div>

<?php Pjax::end(); ?>




<?php
$chageActiveUrl = Url::to(['/leave/leave-type/set-active']);
$js = <<< JS
        $("body").on("change", ".form-check-input", function (e) {

          var id = $(this).attr('id');
          $.ajax({
            type: "post",
            url: "$chageActiveUrl",
            data:{
              id:id
            },
            dataType: "json",
            success: function (res) {
              if(res.status == 'success'){
                 $.pjax.reload({container:res.container, history:false});
              }
            }
          });
          
                        if ($(this).is(':checked')) {
                            // alert('Checkbox is checked!');
                        } else {
                            // alert('Checkbox is unchecked!');
                        }
                    });

              
JS;
$this->registerJS($js)
?>