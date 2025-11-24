<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\sm\models\Order;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ทะเบียนประวัติแจ้งซ่อม';
$this->params['breadcrumbs'][] = ['label' => 'แจ้งซ่อม', 'url' => ['/me/repair']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'repair']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?=$this->render('@app/modules/helpdesk2/views/service/_search', ['model' => $searchModel])?>
    </div>
</div>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนงานซ่อม
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between gap-3">
                <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/me/repair-v2/create', 'title' => '<i class="fa-solid fa-screwdriver-wrench"></i> แจ้งซ่อม'],['class' => 'btn btn-light shadow open-modal','data' => ['size' => 'modal-lg']])?>
            </div>
        </div>
    </div>

    <div class="card-body">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                          <th scope="col" class="text-start">รหัสงานซ่อม</th>
                        <th scope="col">อุปกรณ์</th>
                        <th scope="col">ปัญหา</th>
                        <th scope="col">สถานที่</th>
                        <th scope="col">ผู้แจ้ง</th>
                        <th scope="col">วันที่แจ้ง</th>
                        <th scope="col">ความเร่งด่วน</th>
                        <th scope="col">สถานะ</th>
                        <th scope="col">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                     <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-start"><?php echo $item->repair_number?>
            </td>
                        <td><?=$item->deviceType->title ?? '-'?></td>
                        <td><?=$item->title?></td>
                        <td><?=$item->data_json['location']?></td>
                        <td><?=$item->emp->fullname?></td>
                        <td><?=$item->viewCreateDateTime()?></td>
                        <td><?=$item->viewUrgent()['view']?></td>
                        <td><?=$item->repairStatus?->title ?? '-'?></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" style="">
                                    <li><?=Html::a('<i class="bi bi-eye me-2"></i>ดูรายละเอียด',['/me/repair-v2/view','id' => $item->id,'title' => 'รายละเอียดงานซ่อม #'.$item->repair_number],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-xl']])?></li>
                                    <li><?=Html::a('<i class="bi bi-pencil me-2"></i>แก้ไข',['/me/repair-v2/update','id' => $item->id,'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?></li>
                                    <li><?=Html::a('<i class="fa-solid fa-ban me-2"></i>ยกเลิก',['/me/repair-v2/cancel','id' => $item->id,'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'],['class' => 'dropdown-item cancel-order'])?></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>

        <div class="d-flex justify-content-center">
            <div class="text-muted">
                <?= LinkPager::widget([
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