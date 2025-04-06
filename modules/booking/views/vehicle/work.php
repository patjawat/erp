<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\ThaiDateHelper;
use app\modules\booking\models\Vehicle;

$this->title = 'ERP - ภาระกิจ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo $this->render('menu')?>
<?php Pjax::begin(['id' => 'vehicles-container', 'timeout' => 500000]); ?>
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div>
            <h6><i class="bi bi-ui-checks me-1"></i> คำขอรอจัดสรร <span
                    class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
            <?php echo $this->render('_search_detail', ['model' => $searchModel]); ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th>เลขที่</th>
                        <th>ผู้ขอ</th>
                        <th>วันที่ขอใช้</th>
                        <th>จุดหมาย</th>
                        <th>ยานพาหนะ</th>
                        <th class="text-center">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $key => $item):?>
                    <tr class="align-middle">
                        <td class="text-center fw-semibold">
                            <?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                        <td>
                            <p class="mb-0 fw-semibold"><?=$item->vehicle->code?></p>
                            <p class="fs-13 mb-0">
                                <?php echo Yii::$app->thaiDate->toThaiDate($item->vehicle->created_at, true, true)?></p>
                        </td>
                        <td><?php echo $item->vehicle->userRequest()['avatar'];?></td>
                        <td>
                          
                            <p class="mb-0 fw-semibold">
                                <?php echo Yii::$app->thaiDate->toThaiDate($item->date_start, true, true)?></p>
                        </td>
                        <td>
                        <p class="mb-0"><?php echo $item->vehicle->viewGoType()?></p>    
                        <p class="mb-0"><?php echo $item->vehicle->locationOrg?->title ?? '-'?></p>
                    </td>
                        <td>
                            <div class="d-flex">
                                <?=Html::img($item->car->ShowImg(),['class' => 'avatar rounded border-secondary'])?>
                                <div class="avatar-detail">
                                    <div class="d-flex flex-column">
                                        <p class="mb-0"><?=$item->car->data_json['brand'];?></p>
                                        <p class="mb-0 fw-semibold text-primary"><?=$item->license_plate?></p>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>', ['/booking/vehicle/work-update', 'id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> บันทึกภาระกิจ'], ['class' => 'open-modal', 'data' => [ 'size' => 'modal-lg']])?>
                        </td>
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