<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */

$this->title = 'ขอใช้'.$model->room->title;
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-route fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>

ระบบขอใช้ห้องประชุม
<?php $this->endBlock(); ?>


<?php $this->beginBlock('sub-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<h6 class="text-primary"><?php echo $model->reason?></h6>
<div class="row">
    <div class="col-6">
        <div class="flex-shrink-0 rounded p-5 mb-3"
            style="background-image:url(<?php echo $model->room ? $model->room->showImg() :  ''?>);background-size:cover;background-repeat:no-repeat;background-position:center;height:258px;">

        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
            
              
                <div class="d-flex gap-3 justify-content-end mb-3">
                        <?= Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขการขอใช้ห้องประชุม'], ['class' => 'btn btn-warning shadow rounded-pill open-modal','data' => ['size' => 'modal-lg']]) ?>

                        <?= Html::a('<i class="fa-solid fa-xmark"></i> ขอยกเลิก', ['/me/booking-meeting/cancel-order', 'id' => $model->id,'title' => '<i class="fa-solid fa-xmark"></i> ยกเลิกการขอใช้ห้องประชุม'], [
        'class' => 'btn btn-sm btn-danger shadow rounded-pill open-modal','data' => ['size' => 'modal-md'],

    ]) ?>
                    </div>

                <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                [
                    'label' => 'วันที่',
                    'format' => 'html',
                    'value' => Yii::$app->thaiFormatter->asDate($model->date_start, 'full')
                ],
                [
                    'label' => 'เวลา',
                    'format' => 'html',
                    'value' =>   $model->time_start.' - '.$model->time_end
                ],
                [
                    'label' => 'ผู้ขอใช้บริการ',
                    'value' => 'ขอ'.$model->reason
                ],
                [
                    'label' => 'สถานะ',
                    'format' => 'html',
                    'value' => $model->viewStatus()
                ],
            ],
            ]) ?>

            </div>
        </div>

    </div>
</div>

<?php if(count($model->listMembers) > 0):?>
    <div class="alert alert-primary p-2" role="alert">

<div class="d-flex justify-content-between align-items-center gap-3">
    
    <h6 class="text-center text-primary"><i class="fa-solid fa-circle-exclamation text-warning fs-/"></i> ผู้เข้าร่วมประชุมจะได้รับการแจ้งเตือนข้อความหลังจากที่ห้องประชุมได้รับการจัดสสร</h6>

</div>
</div>


<?php endif;?>
<div class="row">

    <div class="col-12">


        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6>รายชื่อเชิญเข้าร่วมประชุม <?php echo count($model->listMembers)?> คน</h6>
                    <div>

                        <?php echo Html::a('เลือกตามแผนก/ฝ่ายกลุ่มงาน',['/me/booking-meeting/select-form-department','id' => $model->id],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>
                        <?php echo Html::a('เลือกแบบรายบุคคล',['/me/booking-meeting/form-member','id' => $model->id],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>
                    </div>
                </div>


                <div class="table-responsive mt-3">
                    <table class="table table-primary">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">รายชื่อ</th>
                                <th scope="col">แผนก/หน่วยงาน</th>
                                <th scope="col" style="width:100px">ดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $num=1; foreach($model->listMembers as $member):?>
                            <tr class="">
                                <td scope="row"><?php echo $num++;?></td>
                                <td><?php echo $member->employee->getAvatar(false)?></td>
                                <td><?php echo $member->employee->departmentName()?></td>
                                <td class="text-center">
                                    <?php echo Html::a('<i class="fa-solid fa-trash-can text-danger"></i>',['/me/booking-meeting/delete-menber','id' => $member->id],['class' => 'delete-item']);?>
                                </td>
                            </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>