<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\inventory\models\StockEvent;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'] . 'เบิกจากคลังหลัก';
$this->params['breadcrumbs'][] = $this->title;
$createIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-plus-2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M3 15h6"/><path d="M6 12v6"/></svg>';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(); ?>
<div class="card p-0">
    <div class="card-body d-flex justify-content-between">
        <h6>ทะเบียนประวัติ</h6>
        <?php // Html::a($createIcon . ' เลือกรายการ', ['/inventory/store'], ['class' => 'btn btn-primary rounded-pill shadow']) ?>
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนประวัติ <span class="badge rounded-pill text-bg-primary">
                    <?= $dataProvider->getTotalCount() ?></span> รายการ</h6>
            <div>
                มูลค่า <span class="fw-semibold badge rounded-pill text-bg-light fs-6"><?= number_format($searchModel->SummaryTotal(false),2) ?></span>บาท
            </div>
        </div>
        <table class="table table-primary mb-5 mt-3">
            <thead>
                <tr>
                <th class="text-center">#</th>
                <th class="text-center">ปีงบ</th>
                <th scope="col">รหัส/วันที่</th>
                    <th class="text-center" style="width:120px">ความเคลื่อนไหว</th>
                    <th>ผู้ดำเนินการ</th>
                    <th>ประเภท</th>
                    <th>คลัง</th>
                    <th style="width:130px" class="text-end">มูลค่า</th>
                    <th style="width:100px" class="text-center">สถานะ</th>
                    <th class="text-end" style="width:100px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle">
                <?php  $row = 1;foreach ($dataProvider->getModels() as $item): ?>
                <tr>
                <td class="text-center"><?= $row++ ?></td>
                <td class="text-center"><?= $item->thai_year ?></td>
                <td class="fw-light align-middle">
                    
                    <?php if ($item->transaction_type == 'IN'): ?>
                        <div class=" d-flex flex-column">
                            <span class="fw-semibold "><?= $item->code ?></span>
                            <?= $item->ViewReceiveDate(); ?>
                        </div>
                        <?php else:?>
                            <div class=" d-flex flex-column">
                            <span class="fw-semibold "><?= $item->code ?></span>
                            <?= Yii::$app->thaiFormatter->asDate($item->created_at, 'medium'); ?>
                        </div>
                            <?php endif?>
                    </td>
                    <td class="text-center">
                        <?php if ($item->transaction_type == 'IN'): ?>
                        <div class="badge rounded-pill badge-soft-primary text-primary fs-13"><i
                                class="fa-solid fa-circle-plus"></i> รับ</div>
                        <?php else: ?>
                        <div class="badge rounded-pill badge-soft-danger text-danger fs-13"><i
                                class="fa-solid fa-circle-minus"></i> จ่าย</div>

                        <?php endif ?>
                    </td>
                    <td>
                        
                    <?php if ($item->transaction_type == 'IN'): ?>
                        <?= $item->CreateBy($item->ViewReceiveDate())['avatar']; ?>
                        <?php else:?>
                            <?= $item->ShowPlayer()['avatar']; ?>
                                    <?php //  $item->UserReq($item->ViewReceiveDate())['avatar']; ?>
                                    <?php endif ?>
                    
                </td>
                <td>  <?= isset($item->data_json['asset_type_name']) ? $item->data_json['asset_type_name'] : '' ?></td>
                <td>
                    <?=$item->fromWarehouseName()?>
                    <?php if ($item->transaction_type == 'IN'): ?>
                        <?php $item->warehouse->warehouse_name ?>
                        <?php else: ?>
                            <?php  $model->fromWarehouse->warehouse_name ?? '-' ?>
                                <?php endif ?>
                    </td>
                  
                  
                  
                    <td class="text-end">
                        <span class="fw-semibold <?=$item->transaction_type == 'OUT' ?  'text-danger' : ''?>"><?= ($item->transaction_type == 'OUT' ?  '-' : '').number_format($item->getTotalOrderPrice(), 2); ?>
                        </span>
                    </td>
                    <td class="text-center"><?= $item->viewStatus(); ?></td>
                    <td class="text-end">
                        <?= Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>', ['/inventory/stock-order/view', 'id' => $item->id], ['class' => 'btn btn-light']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php Pjax::end(); ?>