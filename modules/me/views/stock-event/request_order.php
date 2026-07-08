<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;

$this->title = 'ทะเบียนเบิกวัสดุคลังหลัก';
$this->params['breadcrumbs'][] = ['label' => 'คลังหน่วยงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'เบิกวัสดุคลังหลัก', 'url' => ['/me/main-stock/store']];
$this->params['breadcrumbs'][] = $this->title;


$warehouse = Yii::$app->session->get('sub-warehouse');
$this->title = 'คลัง' . $warehouse->warehouse_name;

$cart = Yii::$app->cartSub;
$products = $cart->getItems();
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check">
            <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
            <path d="m9 14 2 2 4-4" />
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?php echo $this->render('@app/modules/me/stock_menu', ['active' => 'store']) ?>
    <?= $this->render('@app/components/ui/btnReturn') ?>
</div>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'store']); ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search_order', ['model' => $searchModel]); ?>
    </div>
</div>




<div class="stock-event-index">



    <div class="card">
        <div class="card-header bg-primary-gradient text-white">
            <div class="d-flex justify-content-between">
                <h6>
                    <i class="bi bi-ui-checks"></i>ทะเบียนเบิกวัสดุคลังหลัก <span class="badge rounded-pill text-bg-primary"> <?= $dataProvider->getTotalCount() ?></span> รายการ
                </h6>
                <a href="<?= Url::to(['/me/main-stock/store']) ?>" class="btn btn-light" data-pjax="0">
                    <i data-lucide="circle-plus"></i>
                    เลือกเบิกวัสดุ
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th style="width:210px">รหัส/วันที่</th>
                        <th scope="col">ผู้เบิก</th>
                        <th>หัวหน้าตรวจสอบ</th>
                        <th>คลังหลัก</th>
                        <th class="text-start">มูลค่า</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center" style="width:150px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $item): ?>
                        <tr>
                            <td>
                                <div>
                                    <p class="fw-semibold mb-0"><?= $item->code ?></p>
                                    <p class="text-muted mb-0 fs-13"><?= $item->viewCreatedAt() ?></p>
                                </div>
                            </td>
                            <td>
                                <?php
                                try {
                                    echo $item->UserReq()['avatar'];
                                } catch (\Throwable $th) {
                                }
                                ?>
                            </td>
                            <td><?= $item->viewChecker()['avatar'] ?></td>
                            <td>
                                <?php echo $item->warehouse->warehouse_name ?></td>

                            <td class="text-end">

                                <span class="fw-semibold mb-0">
                                    <?= number_format($item->getTotalOrderPrice(), 2) ?>
                            </td>
                            <td class="text-center">
                                <?php echo $item->viewstatus() ?>
                                <?php if ($item->order_status == 'success' && isset($item->data_json['player'])): ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>', ['/me/store-v2/view', 'id' => $item->id], ['class' => 'btn btn-light']) ?>
                                    <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                        <i class="bi bi-caret-down-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบเบิก', ['/inventory/document/stock-order', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data-pjax' => '0', 'data' => ['size' => 'modal-xl']]) ?></li>
                                        <?php if ($item->order_status == 'pending' or $item->order_status == '' or $item->order_status == 'none'): ?>
                                            <!-- <li><?php //  Html::a('<i class="fa-solid fa-pen-to-square me-1"></i> แก้ไขใบขอเบิก', ['/me/stock-event/update','id' => $item->id,'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไขใบขอเบิก'], ['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-md']]) 
                                                        ?></li> -->
                                            <li><?= Html::a('<i class="fa-solid fa-xmark me-1"></i> ยกเลิก', ['/me/stock-event/cancel-order', 'id' => $item->id], ['class' => 'dropdown-item cancel-order']) ?></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-center mt-5">
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
</div>
<?php Pjax::end(); ?>