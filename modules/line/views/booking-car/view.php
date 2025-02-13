<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */

$this->title = 'รายละเอียดขอใช้ยานพาหนะ';
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-route fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-8">

        <div class="card">
            <div class="card-body">
                <p>
                    <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
                </p>


                <!-- <table class="table border-0 table-striped-columns mt-3">
                    <tbody>
                        <tr>
                            <td>เหตุผลขอใช้รถ : </td>
                            <td><span class="text-pink fw-semibold"><?php echo $model->reason?></span></td>
                        </tr>
                        <tr>
                            <td>วันเวลาออกเดินทาง : </td>
                            <td>
                                <i class="fa-solid fa-calendar-check"></i> <?php echo $model->date_start .' '.$model->time_start?>  
                            </td>
                            
                            <td>สถานที่ไป : </td>
                            <td>
                                <?php echo $model->location?>  
                            </td>
                        </tr>
                        <tr>
                            <td>วันเวลากลับ : </td>
                            <td colspan="1">เพื่อเตรียมตัวก่อนคลอด</td>
                            <td>หัวหน้างานรับรอง : </td>
                            <td><span class="text-pink fw-semibold"><?php echo $model->leader_id?></span></td>
                        </tr>
                       
                        <tr>
                            <td>สถานะ : </td>
                            <td colspan="1"><span class="badge rounded-pill badge-soft-success text-primary fs-13 "><i
                                        class="bi bi-check-circle-fill text-success"></i> ผอ.อนุมัติ</span></td>
                                        <td>ความเร่งด่วน : </td>
                                        <td><span class="text-pink fw-semibold"><?php echo $model->urgent?></span></td>
                                    </tr>

                    </tbody>
                </table> -->

                <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'reason',
            'document_id',
            'urgent',
            'location',
            'date_start',
            'time_start',
            'date_end',
            'time_end',
            'driver_id',
            'leader_id',
        ],
    ]) ?>


            </div>
        </div>


    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <h6>ผู้ร่วมเดินทาง</h6>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h6>บุคคลอื่นร่วมเดินทาง</h6>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h6>งานมอบหมาย</h6>
            </div>
        </div>

    </div>
</div>