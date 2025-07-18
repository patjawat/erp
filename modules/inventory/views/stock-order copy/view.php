<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

$warehouse = Yii::$app->session->get('warehouse');
/* @var yii\web\View $this */
/* @var app\modules\inventory\models\StockEvent $model */

try {
    $this->title = 'ร้องขอจาก'.$model->fromWarehouse->warehouse_name;
} catch (Throwable $th) {
    $this->title = 'ไม่ระบุคลังที่ร้องขอ';
}
$this->params['breadcrumbs'][] = ['label' => 'Stock Ins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('../default/menu'); ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'inventory']); ?>

<div class="row">


    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> จำนวนขอ <span
                            class="badge rounded-pill text-bg-primary"><?php echo count($model->getItems()); ?> </span> รายการ
                    </h6>

                    <?php
                    echo $model->OrderApprove();
?>
                    <?php if (Yii::$app->user->can('warehouse')) { ?>
                    <?php // Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ',['/inventory/stock-order/add-new-item','id' => $model->id,'name' => 'order_item','title' => 'เลือกวัสดุเพิ่มเติม'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
                    <?php // Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ',['/inventory/stock-order/store','id' => $model->id,'name' => 'order_item','title' => 'เลือกวัสดุเพิ่มเติม'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-xl']])?>
                    <?php } else { ?>
                    <?php //  ($model->order_status !== 'success') ? Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ',['/inventory/stock-order/create','order_id' => $model->id,'name' => 'order_item','title' => 'เลือกวัสดุเพิ่มเติม'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']]) : ''?>
                    <?php }?>
                </div>
                <table class="table table-striped mt-3">
                    <thead class="table-primary">
                        <tr>
                            <th>รายการ</th>
                            <th class="text-center">หน่วย</th>
                            <th class="text-start">ล็อตผลิต</th>
                            <th class="text-end">มูลค่า</th>
                            <th class="text-center">คงเหลือ</th>
                            <th class="text-center">ขอเบิก</th>
                            <th class="text-center">จ่าย</th>
                            <th class="text-center" scope="col" style="width:120px;">ดำเนินการ</th>
                        </tr>
                    </thead>

                    <tbody class="table-group-divider align-middle">
                        <?php foreach ($model->getItems() as $item) { ?>

                        <tr class="<?php echo $item->order_status == 'await' ? 'bg-warning-subtle' : ''; ?>">
                            <td class="align-middle">
                                <?php
        try {
            echo $item->product->Avatar();
        } catch (Throwable $th) {
        }
                            ?>
                            </td>
                            <td class="align-middle text-center">
                                <?php echo isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-'; ?>
                            </td>

                            <td class="align-middle text-start"><?php echo $item->lot_number; ?>
                                (<code><?php echo $item->SumLotQty(); ?></code>)</td>
                            <td class="align-middle text-end"><?php echo $item->unit_price; ?></td>
                            <td class="text-center"><?php echo $item->SumStockQty(); ?></td>
                            <td class="align-middle text-center">
                                <?php echo isset($item->data_json['req_qty']) ? $item->data_json['req_qty'] : '-'; ?></td>
                            <td>
                                <?php if ($model->OrderApprove()) { ?>
                                <div class="d-flex d-flex flex-row justify-content-center">
                                    <?php echo Html::a('<i class="fa-solid fa-chevron-left"></i>', ['/inventory/stock-order/update-qty', 'id' => $item->id, 'qty' => ($item->qty - 1)], ['class' => 'btn update-qty', 'data' => ['lot_qty' => $item->SumLotQty()]]); ?>
                                    <?php // echo Html::a('<i class="fa-solid fa-chevron-left"></i>', ['/inventory/stock-order/update-qty', 'id' => $item->id, 'qty' => ($item->qty - 1)], ['class' => 'btn update-qty']);?>
                                    <!-- <input type="text" value="<?php echo ($item->data_json['req_qty'] > $item->SumLotQty()) ? $item->SumLotQty() : $item->qty; ?>" class="form-control update-qty" style="width:50px;font-weight: 600;" id="<?php echo $item->id; ?>" min="1"/> -->
                                    <input type="text" value="<?php echo $item->qty; ?>" class="form-control update-qty"
                                        style="width:50px;font-weight: 600;" id="<?php echo $item->id; ?>" min="1" />
                                    <?php echo Html::a('<i class="fa-solid fa-chevron-right"></i>', ['/inventory/stock-order/update-qty', 'id' => $item->id, 'qty' => ($item->qty + 1)], ['class' => 'btn update-qty', 'data' => ['lot_qty' => $item->SumLotQty(), 'item' => $item->id]]); ?>
                                </div>
                                <?php } else { ?>
                                <?php echo $item->qty; ?>
                                <?php }?>
                            </td>
                            <td class="text-center">
                                <?php if ($model->OrderApprove()) { ?>
                                
                                    <?php if (($item->data_json['req_qty'] > $item->SumLotQty()) && $item->CountItem($model->id) < 2) { ?>
                                <?php echo Html::a('<i class="fa-solid fa-copy"></i>', ['/inventory/stock-order/copy-item', 'id' => $model->id, 'lot_number' => $item->lot_number], ['class' => 'btn btn-sm btn-primary copy-item']); ?>
                                <?php }?>

                                <?php if ($warehouse['warehouse_id'] != $item->warehouse_id) { ?>
                                <?php echo Html::a('<i class="fa-solid fa-trash"></i>', ['/inventory/stock-order/delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger delete-item']); ?>
                                <?php }?>
                                
                                <?php }?>

                            </td>
                        </tr>
                        <?php } ?>

                    </tbody>
                </table>

            </div>
        </div>

    </div>
    <div class="col-4">
        <!-- Star Card -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <!-- <h6><?php echo Html::encode($this->title); ?></h6> -->
                    <?php
                     try {
                         $model->CreateBy('<code>ผู้เบิก</code> '.$model->fromWarehouse->warehouse_name.' | เมื่อ '.$model->viewCreated())['avatar'];
                     } catch (Throwable $th) {
                         // throw $th;
                     }
?>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?php echo Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข', ['/inventory/stock-order/update', 'id' => $model->id, 'title' => 'แก้ไขใบรับเข้า'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]); ?>
                            <?php echo $model->OrderApprove() ? Html::a('<i class="fa-solid fa-eraser me-2"></i> ยกเลิก', ['/inventory/stock-order/cancel-order', 'id' => $model->id, 'title' => '<i class="fa-solid fa-eraser"></i> ยกเลิก'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) : ''; ?>
                        </div>
                    </div>
                </div>

                <?php echo DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label' => 'รหัสขอเบิก',
                            'value' => $model->code,
                        ],
                        [
                            'label' => 'วันที่',
                            'value' => $model->viewCreatedAt(),
                        ],
                        // [
                        //     'label' => 'ผู้ขอเบิก',
                        //     'format' => 'raw',
                        //     'value' =>  $model->CreateBy($model->fromWarehouse->warehouse_name.' | '.$item->viewCreated())['avatar']
                        // ],
                        [
                            'label' => 'ผู้อนุมัติ',
                            'format' => 'raw',
                            'value' => $model->viewChecker('ผู้อนุมัติ')['avatar'],
                        ],
                        [
                            'label' => 'สถานะคำขอ',
                            'format' => 'html',
                            'value' => $model->viewStatus(),
                        ],
                        [
                            'label' => 'มูลค่า',
                            'format' => 'html',
                            'value' => $model->getTotalOrderPrice(),
                        ],
                        [
                            'label' => 'พิมพ์เอกสาร',
                            'format' => 'raw',
                            // 'value' => Html::a('<i class="fa-solid fa-print me-1"></i> เอกสารใบเบิก', ['/inventory/document/stock-order','id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill shadow','target' => '_blank','data' => ['pjax' => false]])
                            'value' => Html::a('<i class="fa-solid fa-print me-1"></i> เอกสารใบเบิก', ['/inventory/document/stock-order', 'id' => $model->id], ['target' => '_blank', 'data-pjax' => '0']),
                            // 'value' => Html::a('<i class="fa-solid fa-print me-1"></i> เอกสารใบเบิก', ['/inventory/document/stock-order','id' => $model->id], ['target' => '_blank','data' => ['pjax' => false]])
                        ],
                    ],
                ]); ?>

            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-6"><?php echo $model->getMe('<code>ผู้สั่งจ่าย</code>')['avatar']; ?></div>
                    <div class="col-6 text-end">
                        <div class="form-group mt-3 d-flex justify-content-end">
                            <?php if ($model->order_status == 'await') { ?>
                            <?php echo Html::a('<i class="bi bi-check2-circle"></i> บันทึก', ['/inventory/stock-order/save-order', 'id' => $model->id], ['class' => 'btn btn-primary rounded-pill shadow checkout']); ?>
                            <?php }?>
                            <?php if ($model->OrderApprove()) { ?>
                            <?php echo $model->countNullQty() == 0 ? Html::a('<i class="bi bi-check2-circle"></i> บันทึกจ่าย', ['/inventory/stock-order/check-out', 'id' => $model->id], ['class' => 'btn btn-primary rounded-pill shadow checkout']) : ''; ?>
                            <?php } else { ?>

                            <?php }?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Card -->
    </div>
</div>

<?php Pjax::end(); ?>

<?php

$js = <<< JS


$("body").on("keypress", ".update-qty", function (e) {
    var keycode = e.keyCode ? e.keyCode : e.which;
    if (keycode == 13) {
        let qty = $(this).val()
        let id = $(this).attr('id')
        console.log(qty);
        
        $.ajax({
            type: "get",
            url: "/inventory/stock-order/update-qty",
            data: {
                'id':id,
                'qty':qty 
            },
            dataType: "json",
            success: function (res) {
                if(res.status == 'error'){
                    Swal.fire({
                    icon: "warning",
                    title: "เกินจำนวน",
                    showConfirmButton: false,
                    timer: 1500,
                });
                }
                  $.pjax.reload({container:'#inventory', history:false});
            }
        });
    }
});


function increase(id,qty) {
    var value + 1;
    $.ajax({
        type: "get",
        url: "/inventory/stock-order/update-qty",
        data:{
            id:id,
            qty:1
        },
        dataType: "dataType",
        success: function (response) {
            
        }
    });
}

function decrease(id) {
    return value - 1;
}

$("body").on("click", ".update-qty", function (e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            data: {},
            dataType: "json",
            success: function (res) {
                if(res.status == 'error'){
                    Swal.fire({
                    icon: "warning",
                    title: "เกินจำนวน",
                    showConfirmButton: false,
                    timer: 1500,
                });
                }
                if(res.status == 'success')
                {
                    $('#'+res.data.id).val(res.data.qty)
                    console.log(res.data.qty);
                    
                }


                //   $.pjax.reload({container:'#inventory', history:false});
            }
        });
        
    });


    $("body").on("click", ".checkout", async function (e) {
    e.preventDefault();

  await Swal.fire({
    title: "ยืนยัน?",
    text: "บันทึกสั่งจ่ายรายการนี้!",
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
          if (response.status == "error") {
            Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: response.message,
                    footer: 'เกิดข้อผิดพลาด'
                    });
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


  $("body").on("click", ".copy-item", async function (e) {
    e.preventDefault();

        await Swal.fire({
            title: "ยืนยัน?",
            text: "บันทึกสั่งจ่ายรายการนี้!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "ใช่, ยืนยัน!",
            cancelButtonText: "ยกเลิก",
        }).then(async (result) => {
            if (result.value == true) {
            await $.ajax({
                type: "get",
                url: $(this).attr('href'),
                dataType: "json",
                success: async function (response) {
                    console.log(response);
                    
                  if (response.status == "success") {
                    await  $.pjax.reload({container:response.container, history:false,url:response.url});
                    // success("บันสำเร็จ!.");
                  }
                },
            });
            }
        });

  });


JS;
$this->registerJS($js);
?>