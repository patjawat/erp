<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
$this->title = 'สรุปรายงานวัสดุคงคลังหลัก';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu_dashbroad',['active' => 'report'])?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php

use app\components\ThaiDateHelper;

 echo $this->render('list_summary_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทั้งหมด
                <span class="badge text-bg-light">
                    <?php // echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
                </h6>
                <p>
    <?= \yii\helpers\Html::a('แสดงทั้งหมด', ['list-summary', 'all' => 1] + Yii::$app->request->queryParams, [
        'class' => 'btn btn-info',
    ]) ?>
</p>
            </div>
    </div>
    <div class="card-body">


        <table
            class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th scope="col" class="text-center fw-bold">ลำดับ</th>
                    <th scope="col" class="text-start fw-bold">ชื่อคลัง</th>
                    <th scope="col" class="text-start fw-bold">ประเภทคลัง</th>
                    <th scope="col" class="text-start fw-bold">ประเภทวัสดุ</th>
                    <th scope="col" class="text-center fw-bold">วันที่</th>
                    <th scope="col" class="text-start fw-bold">เลขที่</th>
                    <th scope="col" class="text-center fw-bold">ความเคลื่อนไหว</th>
                    <th scope="col" class="text-start fw-bold">ผู้ขาย</th>
                    <th scope="col" class="text-start fw-bold">ชื่อวัสดุ</th>
                    <th scope="col" class="text-center fw-bold">จำนวน</th>
                    <th scope="col" class="text-center fw-bold">หน่วย</th>
                    <th scope="col" class="text-end fw-bold">ราคาต่อหน่วย</th>
                    <th scope="col" class="text-end fw-bold">รวมราคา</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                       <td class="text-center"><?= ($dataProvider->pagination ? $dataProvider->pagination->offset : 0) + $key + 1 ?></td>
                       <td><?=$item->stockOrder->warehouse->warehouse_name ?></td>
                       <td><?=$item->stockOrder->warehouse->warehouse_type == 'MAIN' ? 'คลังหลัก' : 'คลังย่อย' ?></td>
                       <td><?=$item->stockOrder->assetType?->title ?? $item->stockOrder->asset_type_id; ?></td>
                       <td class="text-center"><?= ThaiDateHelper::formatThaiDate($item->stockOrder->movement_date) ?></td>
                       <td><?= $item->stockOrder->code ?></td>
                       <td class="text-center"><?=$item->stockOrder->transaction_type == 'IN' ? 'รับเข้า' : 'จ่ายออก' ?></td>
                       <td><?= $item->stockOrder?->vendor->title  ?? '-'?></td>
                        <td><?=$item->product?->title ?? $item->asset_item ?></td>
                        <td class="text-center fw-bold"><?=$item->qty ?></td>
                        <td class="text-center"><?=$item->product->unit_name ?></td>
                        <td class="text-end fw-bold"><?=$item->unit_price ?></td>
                        <td class="text-end fw-bold"><?=number_format(($item->qty* $item->unit_price) ?? 0, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="12" class="text-end fw-bold bg-warning">รวมราคาทั้งหมด</td>
                    <td class="fw-bold text-end  bg-warning"><?=number_format((clone $dataProvider->query)->sum('((e.qty * e.unit_price * 100) / 100)') ?? 0, 2); ?></td>
                </tr>
            </tbody>
        </table>
<?php if($dataProvider->pagination):?>
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
        <?php endif;?>

    </div>
</div>