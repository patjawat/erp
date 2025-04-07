<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use app\components\ThaiDate;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\MeetingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ระบบขอใช้ห้องประชุม';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-person-chalkboard fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
ระบบห้องประชุม
<?php $this->endBlock(); ?>


<?php $this->beginBlock('sub-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>

<div class="container">
    <?=$this->render('navbar')?>

    <div class="card">
        <div class="card-body">

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="fw-semibold text-center" scope="col">ลำดับ</th>
                        <th class="fw-semibold text-start" scope="col">เรื่อง</th>
                        <th class="fw-semibold" cope="col">ห้องประชุม</th>
                        <th class="fw-semibold" scope="col" class="text-center">สถานะ</th>
                        <th class="fw-semibold text-center">ดำเนินการ</th>
                    </tr>
                </thead>

                <tbody class="align-middle table-group-divider">
                    <?php foreach($dataProvider->getModels() as $key => $item):?>
                        <tr class="align-middle">
                        <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                        <td>

                        <a href="<?php echo Url::to(['/dms/documents/view','id' => $item->id])?>"class="text-dark open-modal-fullscree-xn">
                                        <div>
                                            <p style="width:600px" class="text-truncate fw-semibold fs-6 mb-0"><?=$item->urgent;?> <?php echo $item->title?> <?php // echo $item->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?></p>
                                            <span>  <?=Yii::$app->thaiFormatter->asDate($item->date_start, 'medium')?> เวลา
                                            <?php echo $item->time_end.' - '. $item->time_end?></span>
                                        </div>
                                    </a>
                        </td>
                          <td><?=$item->room->title?></td>
                        <td class="text-center"><?=$item->viewStatus() ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-3">
                                <?php echo Html::a('<i class="fa-solid fa-eye fa-2x text-primary"></i>',['/me/booking-meeting/view','id' => $item->id,'title' => 'รายละเอียดขอใช้ห้องประชุม'],['class' => 'open-modal-x','data' => ['size' => 'modal-xl']])?>
                                <?php echo Html::a('<i class="fa-solid fa-pen-to-square fa-2x text-warning"></i>',['/me/booking-meeting/update','id' => $item->id,'title' => 'รายละเอียดขอใช้ห้องประชุม'],['class' => 'open-modal-x','data' => ['size' => 'modal-xl']])?>
                                <?php echo Html::a('<i class="fa-solid fa-trash fa-2x text-danger"></i>',['/me/booking-meeting/delete','id' => $item->id,'title' => 'รายละเอียดขอใช้ห้องประชุม'],['class' => 'open-modal-x','data' => ['size' => 'modal-xl']])?>
                            </div>
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
</div>