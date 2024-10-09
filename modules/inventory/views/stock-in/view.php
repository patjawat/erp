<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\modules\inventory\models\StockEvent;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */

$this->title = 'เลขที่รับเข้า : ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Stock Ins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'inventory', 'enablePushState' => false, 'timeout' => 88888888]); ?>
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> รับเข้า <span class="badge rounded-pill text-bg-primary"><?=count($model->getItems())?> </span> รายการ</h6>
                    <?php if($model->order_status == 'success'):?>

                        <?php else:?>
                   <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ',['/inventory/stock-in/product-list','id' => $model->id,'name' => 'order_item','title' => 'รายการวัสดุ'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>
                   <?php endif?>
                   <!-- <?php if($model->order_status == 'success'):?>
                   
                    <?=Html::a('<i class="fa-solid fa-xmark"></i> ยกเลิก',['/inventory/stock-event/cancel-order','id' => $model->id],['class' => 'btn btn-sm btn-danger rounded-pill shadow confirm-order','data' => ['title' => 'ยืนยัน','text' => 'ยืนยันยกเลิกรายการนี้']])?>
                    <?php else:?>
                    <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เลือกรายการ',['/inventory/stock-in/create','order_id' => $model->id,'name' => 'order_item','title' => 'เพิ่มรายการ'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
                <?php endif?>  -->
                </div>
                <table class="table table-striped mt-3">
                <thead class="table-primary">
                    <tr>
                        <th>
                            รายการ
                        </th>
                        <th class="text-center">หน่วย</th>
                        <th class="text-center">ประเภท</th>
                        <th class="text-end">ราคาต่อหน่วย</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">ล็อตผลิต</th>
                        <th class="text-center">วันผลิต</th>
                        <th class="text-center">วันหมดอายุ</th>
                        <th class="text-center" scope="col" style="width: 120px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($model->getItems() as $item): ?>
                    <tr class="<?=$item->order_status == 'pending' ? 'bg-warning-subtle' : ''?>">
                        <td class="align-middle">
                            <?php
                            try {
                                echo $item->product->Avatar();
                            } catch (\Throwable $th) {}
                            ?>
                        </td>

                        <td class="align-middle text-center">
                            <?=isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-'?></td>
                        <td class="align-middle text-center">
                            <?=isset($item->data_json['item_type']) ? $item->data_json['item_type'] : '-'?></td>
                        <td class="align-middle text-end">
                            <?=isset($item->unit_price) ? number_format($item->unit_price, 2) : '-' ?></td>

                        <td class="align-middle text-center"><?= $item->qty ?></td>

                        <td class="align-middle text-center"><?= $item->lot_number ?></td>
                        <td class="align-middle text-center">
                            <?= $item->mfgDate; ?></td>
                        <td class="align-middle text-center">
                            <?=$item->expDate?></td>

                        <td class="align-middle">
                            <div class="d-flex justify-content-center gap-2">
                                <?php if($item->order_status == 'pending'):?>
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/stock-in/update', 'id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['/inventory/stock-in/delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger shadow rounded-pill delete-item']) ?>
                                <?php else:?>
                                    <span>ดำเนินการแล้ว</span>
                                    <?php endif?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
            
            </div>
        </div>
        <div class="form-group mt-3 d-flex justify-content-center">
            <?=($model->isPending() >= 1) ? Html::a('<i class="bi bi-check2-circle"></i> บันทึกรับเข้า',['/inventory/stock-in/confirm-order','id' => $model->id],['class' => 'btn btn-primary rounded-pill shadow confirm-order','data' => ['title' => 'รับวัสดุเข้าคลัง','text' => 'ยืนยันการรับวัสดุเข้าคลัง']]) : ''?>
    </div>
    </div>
    <div class="col-4">
        <!-- Star Card -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><?= Html::encode($this->title) ?></h6>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข', ['/inventory/stock-in/update', 'id' => $model->id, 'title' => 'แก้ไขใบรับเข้า'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            <?= Html::a('<i class="fa-solid fa-rotate-left me-2"></i> ยกเลิก', ['/inventory/stock-in/cancel', 'id' => $model->id, 'title' => 'ยกเลิกใบรับเข้า'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        </div>
                    </div>
                </div>

                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label' => 'สถานะ',
                            'format' => 'raw',
                            'value' => $model->viewStatus()
                        ],
                        [
                            'label' => 'มูลค่า',
                            'value' => number_format($model->getTotalOrderPrice(),2)
                        ]
                    ],
                ]) ?>

            </div>
        </div>
        <!-- End Card -->
        <!-- committee -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง</h6>
                </div>
                <?=$model->StackComittee()?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <?= Html::a('รายการ', [
                            '/inventory/committee/list','id' => $model->id,'title' => '<i class="bi bi-person-circle"></i> กรรมการกำหนดรายละเอียด'
                        ], ['class' => 'open-modal','data' => ['size' => 'modal-lg']]) ?>
                <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่มกรรมการ', ['/inventory/committee/create', 'id' => $model->id, 'action' => 'create','name' => 'receive_committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับเข้าคลัง'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>
        <!-- End Committee -->
    </div>
</div>

<?php

$js = <<< JS

               
JS;
$this->registerJS($js)
?>

<?php Pjax::end()?>