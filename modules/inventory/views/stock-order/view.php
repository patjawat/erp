<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\modules\inventory\models\StockEvent;
use yii\widgets\Pjax;
$warehouse = Yii::$app->session->get('warehouse');
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */

$this->title = 'ร้องขอจาก'.$model->fromWarehouse->warehouse_name;
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



<?php Pjax::begin(['id' => 'inventory']); ?>

<div class="row">
  

    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> จำนวนขอ <span
                            class="badge rounded-pill text-bg-primary"><?=count($model->getItems())?> </span> รายการ
                    </h6>
                    <?php if($model->order_status == 'await'):?>
                    <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เลืกอรายการ',['/inventory/stock-order/create','order_id' => $model->id,'name' => 'order_item','title' => 'สร้างรายการเบิก'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>
                    <?php else:?>
                        <?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบเบิก', ['/inventory/document/stock-out','id' => $model->id], ['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-xl']]) ?>
                    <?php endif;?>
                </div>
                <table class="table table-striped mt-3">
                    <thead class="table-primary">
                        <tr>
                            <th>
                                รายการ
                            </th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-center">หน่วย</th>
                            <th class="text-center">มูลค่า</th>
                            <th class="text-center">จ่าย</th>
                            <th class="text-center">ล็อตผลิต</th>
                            <th class="text-center">วันหมดอายุ</th>
                            <th class="text-center" scope="col" style="width: 120px;">ดำเนินการ</th>
                        </tr>
                    </thead>
    
                    <tbody class="table-group-divider">
                        <?php foreach ($model->getItems() as $item): ?>

                        <tr class="<?=$item->order_status == 'await' ? 'bg-warning-subtle' : ''?>">
                            <td class="align-middle">
                                <?php
                            try {
                                echo $item->product->Avatar();
                            } catch (\Throwable $th) {}
                            ?>
                            </td>
                            <td class="align-middle text-center"><?= isset($item->data_json['req_qty']) ? $item->data_json['req_qty'] : '-'?></td>

                            <td class="align-middle text-center">
                                <?=isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-'?>
                            </td>
                            <td class="align-middle text-end"><?= $item->unit_price ?></td>
       
                            <td class="align-middle text-center"><?= $item->qty ?></td>

                            <td class="align-middle text-center"><?= $item->lot_number ?></td>
                            <td class="align-middle text-center">
                                <?= isset($item->data_json['exp_date']) ? AppHelper::convertToThai($item->stock->data_json['exp_date']) : '-' ?>
                            </td>

                            <td class="align-middle text-center">
                                <!-- ถ้าเป็็นคลังของผู้จ่ายให้แสดงปุ่มจ่าย -->
                                <?php if(isset($warehouse['warehouse_id']) && $model->warehouse_id == $warehouse['warehouse_id']):?>
                                <?= $model->data_json['checker_confirm'] == 'Y' ? Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/inventory/stock-order/update-lot','id' => $item->id],['class' => 'text-center open-modal','data' => ['size' => 'modal-md']]) : '-'?>
                                <?php else:?>
                                <!-- ถ้า้ป็รคลังของผู้ขอเบิกของให้แสดงปุ่มแก้ไขและลบได้ -->
                                <div class="d-flex justify-content-center gap-2">
                                    <?php if($item->order_status == 'pending'):?>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/stock-order/update', 'id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['/inventory/stock-order/delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger shadow rounded-pill delete-item']) ?>
                                    <?php else:?>
                                    <?=$item->viewStatus()?>
                                    <?php endif?>
                                </div>
                                <?php endif;?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>
        </div>
        <div class="form-group mt-3 d-flex justify-content-center">
        <?php if($model->order_status == 'await'):?>
            <?php echo Html::a('<i class="bi bi-check2-circle"></i> บันทึก',['/inventory/stock-order/save-order','id' => $model->id],['class' => 'btn btn-primary rounded-pill shadow checkout'])?>
         <?php endif;?>
         <?php if($model->order_status == 'pending' && $model->data_json['checker_confirm'] == 'Y'):?>
            <?php echo $model->countNullQty() == 0 ? Html::a('<i class="bi bi-check2-circle"></i> บันทึกจ่าย',['/inventory/stock-order/check-out','id' => $model->id],['class' => 'btn btn-primary rounded-pill shadow checkout']) : ''?>
        
        <?php endif;?>
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
                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข', ['/inventory/stock-order/update', 'id' => $model->id, 'title' => 'แก้ไขใบรับเข้า'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        </div>
                    </div>
                </div>

                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                    [
                       'label' => 'รหัสขอเบิก',
                       'value' => $model->code
                    ],
                    [
                        'label' => 'คลัง',
                        'value' => $model->warehouse->warehouse_name
                     ],
                    [
                        'label' => 'วันที่',
                        'value' => $model->viewCreatedAt()
                     ],
                     [
                        'label' => 'ผู้ขอ',
                        'format' => 'html',
                        'value' => $model->CreateBy($model->note)['avatar']
                     ],
                     [
                        'label' => 'สถานะ',
                        'format' => 'html',
                        'value' => $model->viewStatus()
                     ],
                    ],
                ]) ?>

            </div>
            <div class="card-footer d-flex justify-content-between">
            <div class="text-truncate">
                        <?=  $model->viewChecker('ผู้อนุมัติ')['avatar'] ?>
                    </div>
                <div>

<?=$model->viewChecker()['status']?>

                </div>
            </div>
        </div>
        <!-- End Card -->
    </div>
</div>

<?php

$js = <<< JS



$('.checkout').click(async function (e) { 
    e.preventDefault();

  await Swal.fire({
    title: "ยืนยัน?",
    text: "บันทึกรายการนี้!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ยืนยัน!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: $(this).attr('href'),
        dataType: "json",
        success: async function (response) {
            console.log(response);
            
          if (response.status == "success") {
            // await  $.pjax.reload({container:response.container, history:false,url:response.url});
            success("บันสำเร็จ!.");
          }
        },
      });
    }
  });

  });


$('.confirm-order').click(async function (e) { 
    e.preventDefault();

  await Swal.fire({
    title: "ยืนยัน?",
    text: "บันทึกรายการนี้!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ยืนยัน!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: $(this).attr('href'),
        dataType: "json",
        success: async function (response) {
          if (response.status == "success") {
            await  $.pjax.reload({container:response.container, history:false,url:response.url});
            success("บันสำเร็จ!.");
          }
        },
      });
    }
  });

  });

JS;
$this->registerJS($js)
?>

<?php Pjax::end()?>
