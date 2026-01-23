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
$this->params['breadcrumbs'][] = ['label' => 'บริการ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'แจ้งซ่อม', 'url' => ['/me/repair']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
        <?= $this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?= $this->render('@app/components/ui/btnReturn')?>
</div>
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
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
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