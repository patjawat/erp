<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\modules\inventory\models\StockEvent;
use yii\widgets\Pjax;
$warehouse = Yii::$app->session->get('warehouse');
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */

$this->title = 'เลขที่รับเข้า : ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Stock Ins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php Pjax::begin(['id' => 'inventory']); ?>
<div class="row">
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
                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข', ['/inventory/stock-out/update', 'id' => $model->id, 'title' => 'แก้ไขใบรับเข้า'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        </div>
                    </div>
                </div>

                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'order_status',
                        'name',
                        'code',
                    ],
                ]) ?>

            </div>
        </div>
        <!-- End Card -->

        

    </div>

    <div class="col-4">
    <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0">หัวหน้าตรวจสอบ</h6>
                <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#Director"
                    aria-expanded="true" aria-controls="collapseCard">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>

            <div class="card-body collapse show">
                <!-- Start Flex Contriler -->
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-truncate">
                        <?=  $model->viewChecker()['avatar'] ?>
                    </div>
                </div>
                <!-- End Flex Contriler -->
            </div>

            <div class="card-footer d-flex justify-content-between">
                <h6>การอนุมัติ</h6>
                <div>

                    <?=Html::a('<i class="bi bi-check2-circle"></i> อนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-success rounded-pill open-modal','data' => ['size' => 'modal-md']])?>

                </div>
            </div>

        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> รับเข้า <span
                            class="badge rounded-pill text-bg-primary"><?=count($model->getItems())?> </span> รายการ
                    </h6>
                    <?php if($model->order_status == 'await'):?>
                    <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เลืกอรายการ',['/inventory/stock-out/create','order_id' => $model->id,'name' => 'order_item','title' => 'สร้างรายการเบิก'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>
                    <?php endif;?>
                </div>
                <table class="table table-striped mt-3">
                    <thead class="table-primary">
                        <tr>
                            <th>
                                รายการ
                            </th>
                            <th class="text-center">หน่วย</th>
                            <th class="text-center">มูลค่า</th>
                            <th class="text-center">จำนวนเบิก</th>
                            <th class="text-center">จำนวนจ่าย</th>
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

                            <td class="align-middle text-center">
                                <?=isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-'?>
                            </td>
                            <td class="align-middle text-end"><?= $item->total_price ?></td>
       
                            <td class="align-middle text-center"><?= $item->data_json['req_qty']?></td>
                            <td class="align-middle text-center"><?= $item->qty ?></td>

                            <td class="align-middle text-center"><?= $item->lot_number ?></td>
                            <td class="align-middle text-center">
                                <?= isset($item->data_json['exp_date']) ? AppHelper::convertToThai($item->stock->data_json['exp_date']) : '-' ?>
                            </td>

                            <td class="align-middle text-center">
                                <!-- ถ้าเป็็นคลังของผู้จ่ายให้แสดงปุ่มจ่าย -->
                                <?php if($model->warehouse_id == $warehouse['warehouse_id']):?>
                                <?= $model->data_json['checker_confirm'] == 'Y' ? Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/inventory/stock-out/update-lot','id' => $item->id],['class' => 'text-center open-modal','data' => ['size' => 'modal-md']]) : '-'?>
                                <?php else:?>
                                <!-- ถ้า้ป็รคลังของผู้ขอเบิกของให้แสดงปุ่มแก้ไขและลบได้ -->
                                <div class="d-flex justify-content-center gap-2">
                                    <?php if($item->order_status == 'pending'):?>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/stock-out/update', 'id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['/inventory/stock-out/delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger shadow rounded-pill delete-item']) ?>
                                    <?php else:?>
                                    <span>ดำเนินการแล้ว</span>
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
            <?php echo Html::a('<i class="bi bi-check2-circle"></i> saveOrder',['/inventory/stock-out/save-order','id' => $model->id],['class' => 'btn btn-primary rounded-pill shadow checkout'])?>
         <?php endif;?>
         <?php if($model->order_status == 'pending' && $model->data_json['checker_confirm'] == 'Y'):?>
            <?php echo Html::a('<i class="bi bi-check2-circle"></i> checkout',['/inventory/stock-out/check-out','id' => $model->id],['class' => 'btn btn-primary rounded-pill shadow checkout'])?>
            <?php else:?>
                <h6>รออนุมัติ</h6>
        <?php endif;?>
        </div>
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
