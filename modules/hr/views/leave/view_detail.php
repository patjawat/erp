<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use app\components\AppHelper;
use app\components\UserHelper;
$me = UserHelper::GetEmployee();
/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
$this->title = 'ระบบลา';
$this->params['breadcrumbs'][] = ['label' => 'Leaves', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<table class="table border-0 table-striped-columns mt-3">
            <tbody>
                <tr>
                    <td>เรื่อง : </td>
                    <td><span class="text-pink fw-semibold">ขอ<?php echo ($model->leaveType->title ?? '-') ?></span></td>

                    <td>เขียนเมื่อ : </td>
                    <td><span class="text-pink fw-semibold"><?php echo $model->viewCreated() ?></span></td>
                </tr>
                <tr>
                    <td>ระหว่างวันที่ : </td>
                    <td>
                    <i class="fa-solid fa-calendar-check"></i> <?php echo AppHelper::convertToThai($model->date_start ?? '') ?> ถึงวันที่ <i class="fa-solid fa-calendar-check"></i> <?php echo AppHelper::convertToThai($model->date_end ?? '') ?>
                    </td>

                    <td>เป็นเวลา : </td>
                    <td>
                    <span class="badge rounded-pill badge-soft-danger text-primary fs-13 "><?php echo $model->total_days ?> วัน</span></td>
                </tr>

                <tr>
                    <td>เหตุผล : </td>
                    <td colspan="4"><?php echo $model->data_json['reason'] ?? '-' ?></td>
                    
                   
                </tr>
                <tr>
                    <td>ระหว่างลาติดต่อ : </td>
                    <td><?php echo $model->data_json['address'] ?? '-' ?></td>
                    <td>โทรศัพท์ : </td>
                    <td><?php echo $model->data_json['phone'] ?? '-' ?></td>
                </tr>
                <tr>
                    <?php if ($model->status == 'Cancel'): ?>
                    <td>สถานะ : </td>
                    <td><?php echo $model->viewStatus() ?></td>
                    <td>ผู้ดำเนินการยกเลิก : </td>
                    <td><?php echo ($model->data_json['cancel_fullname'] ?? '-') . (' วันเวลา ' . Yii::$app->thaiFormatter->asDateTime($model->data_json['cancel_date'], 'medium') ?? '') ?></td>
                    <?php else: ?>
                        <td>สถานะ : </td>
                        <td  colspan="4"><?php echo $model->viewStatus() ?></td>
                    <?php endif ?>
                </tr>

                <tr>
                <td>ไฟล์แนบ : </td>
                <td>
                <?php echo $model->listClipFile()?>
                <?php // echo Html::a('<i class="bi bi-clock-history"></i> ดูไฟล์แนบ', ['/hr/leave/view-history','emp_id' => $model->emp_id,'title' =>'ประวัติการลา'], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-xl']]) ?>
            </td>
                <td>วันลาพักผ่อนสม : </td>
                <td><?php echo $model->sumLeavePermission()['sum']?></td>
                </tr>
                    
                
            </tbody>
        </table>

      



        