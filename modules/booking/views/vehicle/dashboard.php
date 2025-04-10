<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\ThaiDateHelper;
use app\modules\booking\models\Vehicle;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ERP - ระบบจัดการรถยนต์';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu')?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'vehicles-container', 'timeout' => 500000]); ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center p-2">
        <h5 class="card-title"><i class="fa-solid fa-calendar-day"></i> ปฏิทินการจัดสรรรถ</h5>
        <div>
            <?=Html::a('<i class="bi bi-calendar-check"></i> วันนี้', ['index', 'date' => date('Y-m-d')], ['class' => 'btn btn-sm btn-outline-primary'])?>
            <div class="btn-group">
                <?= Html::a(' <i class="bi bi-chevron-left"></i>', ['index', 'date' => $previousDate], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <button class="btn btn-sm btn-outline-secondary disabled"><?= $thaiMonthYear ?></button>
                <?= Html::a('<i class="bi bi-chevron-right"></i>', ['index', 'date' => $nextDate], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%">รถ / วันที่</th>
                        <!-- <th>1 ม.ค. 68</th> -->
                        <?php foreach ($days as $day): ?>
                        <th class="text-center">
                            <?= ThaiDateHelper::formatThaiDate($day) ?>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProviderDetail->getModels() as $item): ?>
                    <tr>
                        <td class="table-light">
                            <?php if($item->car):?>
                            <div class="d-flex">
                                <?=Html::img($item->car->ShowImg(),['class' => 'avatar rounded border-secondary'])?>
                                <div class="avatar-detail">
                                    <div class="d-flex flex-column">
                                        <p class="mb-0">
                                            <?= Html::a(ThaiDateHelper::formatThaiDate($item->date_start),['index', 'date' => $item->date_start]) ?>
                                        </p>
                                        <p class="mb-0 fw-semibold text-primary"><?=$item->license_plate?></p>
                                    </div>
                                </div>
                            </div>
                            <?php else:?>
                            <?=$item->license_plate;?>
                            <?php endif;?>
                        </td>
                        <?php foreach ($days as $day): ?>
                        <td>
                            <?php if($day == $item->date_start):?>
                        <td>
                            <a href="<?=Url::to(['/booking/vehicle/approve','id' => $item->vehicle_id,'title' => 'แสดงข้อมูลการจัดสรร'])?>"
                                class="open-modal" data-size="modal-lg">
                                <div class="badge-soft-success p-2 rounded">
                                    <?=$item->driver->getAvatar(false,($item->vehicle->locationOrg->title))?>
                                </div>
                            </a>
                        </td>
                        <?php endif;?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center">
    <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'class' => 'pagination pagination-sm',
                ],
            ]); ?>

</div>
<?php Pjax::end();?>