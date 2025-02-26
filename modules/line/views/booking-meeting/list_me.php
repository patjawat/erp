<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
?>

<div class="card">
    <div class="card-body">

<table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">ลำดับ</th>
                    <th class="text-start" scope="col">เรื่อง</th>
                    <th scope="col">จำนวนผู้ประชุม</th>
                    <th scope="col">ช่วงเวลา</th>
                    <th scope="col">ห้องประชุม</th>
                    <th scope="col" class="text-center">สถานะ</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>

            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td><?php echo $key+1?></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <?php  echo Html::img($item->employee->showAvatar(),['class' => 'avatar avatar-sm']);?>
                            <div class="avatar-detail">
                                <div>
                                    <?php  echo $item->reason;?>
                                </div>
                                <div>
                               
                                    <?php echo $item->room->title;?>

                                </div>
                            </div>
                        </div>
                    </td>
                    <td> <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"> <?=$item->data_json['employee_total'] ?? 0?></span></td>
                    <td> <?php echo $item->data_json['period_time'] ?? '-';?></td>
                    <td>
                    <?=Yii::$app->thaiFormatter->asDate($item->date_start, 'medium')?> เวลา
                    <?php echo $item->time_end.' - '. $item->time_end?> 
                    </td>
                    <td class="text-center"><?=$item->status?></td>
                    <td class="text-center">
                        <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/me/booking-meeting/view','id' => $item->id,'title' => 'รายละเอียดขอใช้ห้องประชุม'],['class' => 'open-modal-x','data' => ['size' => 'modal-xl']])?>
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
