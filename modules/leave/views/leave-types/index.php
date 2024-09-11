<?php

use app\modules\leave\models\LeaveTypes;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\leave\models\LeaveTypesSearch $searchModel */
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
                    <?=$dataProvider->getTotalCount()?></span> รายการ</h6>
            <?=Html::a('<i class="bi bi-plus-circle-fill"></i> สร้างรายการใหม่',['/leave/leave-types/create','title' => '<i class="bi bi-plus-circle-fill"></i> สร้างรายการใหม่'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
        </div>

      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th scope="col" style="width:32px">#</th>
            <th scope="col">รายการ</th>
            <th class="text-center" scope="col" style="width:100px">ดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
            <?php foreach($dataProvider->getModels() as $item):?>
          <tr>
            <td><?=$item->id?></td>
            <td><?=$item->name?></td>
            <td  class="text-center">
            <div class="dropdown float-center">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">                       

                        <?=Html::a('<i class="fa-solid fa-eye me-1"></i>แสดง',['/leave/leave-types/view','id' => $item->id,'title' => '<i class="fa-solid fa-eye"></i> แสดง'],['class' => 'dropdown-item','data' => ['size' => 'modal-md']])?>
                        <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข',['/leave/leave-types/update','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-md']])?>
                        </div>
                            
                    </div>
                    
            </td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>



    </div>
</div>


</div>
</div>
<?php Pjax::end(); ?>