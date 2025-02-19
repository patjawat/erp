<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'ระบบจองห้องประชุม ';
?>
<?php // Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->

<i class="fa-solid fa-person-chalkboard fs-1"></i><?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนการ<?php echo $this->title?> <span
                    class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>


        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">ลำดับ</th>
                    <th class="text-start" scope="col">เรื่อง</th>
                    <th class="text-center" scope="col">ช่วงเวลา</th>
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
                            <?php echo Html::img($item->employee->showAvatar(),['class' => 'avatar avatar-sm']);?>
                            <div class="avatar-detail">
                                <div>
                                    <?php echo $item->reason;?>
                                </div>
                                <div><?=Yii::$app->thaiFormatter->asDate($item->date_start, 'medium')?> เวลา
                                <?php echo $item->time_end.' - '. $item->time_end?> ผู้เข้าร่วม <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"> <?=$item->data_json['employee_total'] ?? 0?></span> คน </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center"><?php echo $item->data_json['period_time'] ?? '-';?></td>
                    <td><?php echo $item->room->title;?></td>
                    <td class="text-center"><?=$item->status?></td>
                    <td class="text-center">
                        <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/booking/meeting/view','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
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