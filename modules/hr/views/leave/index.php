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
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu') ?>
<?php $this->endBlock(); ?>

<div class="card text-start">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา <span class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(),0)?></span> รายการ</h6>
         
            <div class="d-flex gap-3">
                <?php foreach($searchModel->listStatusSummary() as $status):?>
                <p class="text-muted mb-0 fs-13"><span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><?php echo $status['title']?> <span class="badge text-bg-primary"><?php echo $status['total']?></span></span></p>
                <?php endforeach;?>
            </div>
            <h2>&nbsp;</h2>
                
        </div>
        <div class="d-flex justify-content-between  align-top align-items-center mt-4">
                <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
                <?= Html::a('<i class="fa-solid fa-plus"></i> สร้างใหม่', ['/hr/leave/create','title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-primary shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
        </div>

        <table class="table table-striped mt-3">
            <thead>
                <tr class="table-secondary">
                    <th scope="col">ผู้ขออนุมัติการลา</th>
                    <th class="text-center" scope="col">เป็นเวลา/วัน</th>
                    <th scope="col">จากวันที่</th>
                    <th scope="col">ถึงวันที่</th>
                    <th scope="col">มอบหมาย</th>
                    <th scope="col">ผู้ตรวจสอบและอนุมัติ</th>
                    <th class="text-start">ความคืบหน้า</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-center">#</th>
                </tr>
            </thead>
            <tbody class="align-middle">
                <?php foreach($dataProvider->getModels() as $model):?>
                <tr class="">
                    <td  class="text-truncate" style="max-width: 250px;"><?=$model->getAvatar(false)['avatar']?></td>
                    <td class="text-center fw-semibold "><?php echo $model->sum_days?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($model->date_start, 'medium')?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($model->date_end, 'medium')?></td>
                    <td><?php echo $model->leaveWorkSend()?></td>
                    <td><?php echo $model->stackChecker()?></td>
                    <td class="fw-light align-middle text-start">
                        <?php
                       // echo $model->statusProcess();
                       echo $model->showStatus();
                        ?>

                </td>
                    <td class="fw-center align-middle text-center">
                        <?php
                        try {
                            echo $model->leaveStatus->title;
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        ?>
                    </td>

                    <td class="text-center">
                        <?=Html::a(' <i class="fa-solid fa-eye me-1"></i>',['/hr/leave/view','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],['class' => 'btn btn-sm btn-primary open-modalx','data' => ['size' => 'modal-lg']]) ?>
                        <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>',['/hr/leave/update','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],['class' => 'btn btn-sm btn-warning open-modal','data' => ['size' => 'modal-lg']]) ?>
                        
                    </td>
                </tr>
                <?php endforeach;?>
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