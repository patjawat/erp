<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\lm\models\Leave;
/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ระบบการลา';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา <span class="badge rounded-pill text-bg-primary">
                    1</span> รายการ</h6>
            <div>
                <a class="btn btn-primary rounded-pill shadow open-modal"
                    href="/me/leave/create?title=%3Ci+class%3D%22fa-solid+fa-calendar-plus%22%3E%3C%2Fi%3E+%E0%B8%9A%E0%B8%B1%E0%B8%99%E0%B8%97%E0%B8%B6%E0%B8%81%E0%B8%81%E0%B8%B2%E0%B8%A3%E0%B8%A5%E0%B8%B2"
                    data-size="modal-lg"><i class="fa-solid fa-calendar-plus"></i> สร้างใหม่</a>
            </div>
        </div>

    </div>
    <div class="card-body">

        <p>
            <?= Html::a('สร้างใหม่', ['type-select'], ['class' => 'btn btn-primary']) ?>
        </p>

        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <table class="table table-primary">
            <thead>
                <tr>
                    <th scope="col">ชื่อ-นามสกุล</th>
                    <th scope="col">ประเภท</th>
                    <th scope="col">จากวันที่</th>
                    <th scope="col">ถึงวันที่</th>
                    <th scope="col">จำนวนวัน</th>
                    <th scope="col">เหตุผล</th>
                    <th>หัวหน้างาน</th>
                    <th>หัวหน้ากลุ่มงาน</th>
                    <th>ผู้อำนวยการ</th>
                    <th class="text-center">#</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $model):?>
                <tr class="">
                    <td scope="row"><?=$model->CreateBy()->getAvatar(false)?></td>
                    <td><?=$model->leaveType->title?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($model->date_start, 'medium')?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($model->date_end, 'medium')?></td>
                    <td>xx</td>
                    <td>1</td>
                    <td>
                        <?=Html::a('<i class="bi bi-check2-circle me-1"></i> <span clas="fw-bold">อนุมัติ</span>',['/lm/leave/view','id' => $model->id],['class' => 'btn btn-sm btn-primary rounded-pill'])?>
                        <!-- <div class="btn btn-sm btn-primary rounded-pill ">
                        <i class="bi bi-check2-circle me-1"></i> <span clas="fw-bold">อนุมัติ</span>
                </div>  -->
                    </td>
                    <td>
                        <div class="btn btn-sm btn-warning rounded-pill ">
                            <i class="bi bi-check2-circle me-1"></i> <span clas="fw-bold">รอดำเนินการ</span>
                        </div>
                    </td>
                    <td>
                        <div class="btn btn-sm btn-danger rounded-pill ">
                            <i class="bi bi-check2-circle me-1"></i> <span clas="fw-bold">ไม่อนุมัติ</span>
                        </div>
                        <!-- <div class="badge rounded-pill text-bg-primary">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check2-circle fs-6 me-1"></i> <span clas="fw-bold">อนุมัติ</span>
                    </div>
                </div>  -->
                    </td>
                    <td class="text-center">
                        <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>',['/lm/leave/update','id' => $model->id],['class' => 'btn btn-sm btn-warning'])?>
                        <!-- <div class="dropdown">
                            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข',['/lm/leave/update','id' => $model->id],['class' => 'dropdown-item'])?>
                                <?= Html::a('<i class="fa-solid fa-trash me-1"></i> ลบ', ['/lm/leave/delete','id' => $model->id], [
                                    'class' => 'dropdown-item',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to delete this item?',
                                        'method' => 'post',
                                    ],
                                ]) ?>                                                     </div>
                        </div> -->
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>


    </div>
</div>