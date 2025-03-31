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
    <table class="table border-0 table-striped-columns mt-3">
            <tbody>
                <tr>
                    <td class="text-dark fw-semibold">เรื่อง </td>
                    <td colspan="3"><?php echo $model->reason?></td>
                </tr>
                <tr>
                    <td class="text-dark fw-semibold">วันออกเดินทาง : </td>
                    <td><?php echo Yii::$app->thaiFormatter->asDate($model->date_start, 'medium');?> เวลา
                        <?php echo $model->time_start?></td>

                    <td class="text-dark fw-semibold">วันกลับ : </td>
                    <td><?php echo Yii::$app->thaiFormatter->asDate($model->date_end, 'medium');?> เวลา
                        <?php echo $model->time_end?></td>
                </tr>
                <tr>
                    <td class="text-dark fw-semibold">สถานที่ไป : </td>
                    <td><?php echo $model->location?></span></td>
                    <td class="text-dark fw-semibold">ผู้ร่วมเดินทาง : </td>
                    <td><?php echo $model->data_json['total_person_count'] ?? '-'?></td>
                </tr>
                <!-- <tr>
                    <td class="text-dark fw-semibold">รถที่ร้องขอ : </td>
                    <td colspan="3"><?php echo $model->data_json['req_license_plate'] ?? '-'?></td>
                </tr>
                <tr>
                    <td class="text-dark fw-semibold">พนักงานขับที่ร้องขอ : </td>
                    <td colspan="3">
                        <?php 
                                try {
                                    echo $model->reqDriver()->getAvatar(false);
                                } catch (\Throwable $th) {
                                    //throw $th;
                                }
                                ?>
                </tr> -->
                <tr>
                    <td class="text-dark fw-semibold">หนังสืออ้างอืง : </td>
                    <td colspan="4"></td>
                </tr>

            </tbody>
        </table>
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