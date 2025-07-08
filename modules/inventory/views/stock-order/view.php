<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\db\Expression;
use yii\widgets\DetailView;
use app\components\UserHelper;
use app\modules\inventory\models\Warehouse;

$warehouse = Yii::$app->session->get('warehouse');
// $this->registerJsFile($this->render('stock-order.js'), ['depends' => [\yii\web\JqueryAsset::className()]]);

/* @var yii\web\View $this */
/* @var app\modules\inventory\models\StockEvent $model */

try {
    $this->title = $warehouse->warehouse_name;
} catch (Throwable $th) {
    $this->title = 'ไม่ระบุคลังที่ร้องขอ';
}
$this->params['breadcrumbs'][] = ['label' => 'คลัง', 'url' => ['/inventory']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = 'เบิกวัสดุ';
yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'request'])?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'inventory-container']); ?>

<?php
//ตรวจสอบว่าเป็นผู้ดูแลคลัง
$userid = \Yii::$app->user->id;
$office = Warehouse::find()->andWhere(['id' => $model->warehouse_id])->andWhere(new Expression("JSON_CONTAINS(data_json->'$.officer','\"$userid\"')"))->one();
$emp = UserHelper::GetEmployee();

?>
<?php $balanced=0;foreach ($model->getItems() as $item):?>
<?php
                        if($item->qty > $item->SumlotQty()){
                            $balanced +=1;
                        }
                                ?>
<?php endforeach; ?>
<div class="row">

    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> จำนวนขอ <span
                            class="badge rounded-pill text-bg-primary"><?php echo count($model->getItems()); ?> </span>
                        รายการ
                    </h6>
                    <?php if (Yii::$app->user->can('warehouse')) { ?>
                    <?php // Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ',['/inventory/stock-order/add-new-item','id' => $model->id,'name' => 'order_item','title' => 'เลือกวัสดุเพิ่มเติม'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
                    <?php // Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ',['/inventory/stock-order/store','id' => $model->id,'name' => 'order_item','title' => 'เลือกวัสดุเพิ่มเติม'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-xl']])?>
                    <?php } else { ?>
                    <?php //  ($model->order_status !== 'success') ? Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ',['/inventory/stock-order/create','order_id' => $model->id,'name' => 'order_item','title' => 'เลือกวัสดุเพิ่มเติม'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']]) : ''?>
                    <?php }?>
                </div>
                <!-- รายละเอียดรายการขอเบิก -->
                <?php // echo $this->render('order_items',['model' => $model,'office' => $office])?>
                <?php // echo $this->render('list_items',['model' => $model])?>
                <div id="showOrderItem"></div>
            </div>
        </div>

    </div>
    <div class="col-4">
        <!-- Star Card -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <?php
                    // echo $model->CreateBy()['avatar'];
                    ?>

                    <?php
                    //  try {
                         echo $model->CreateBy('<code>ผู้ขอเบิก</code> '.$model->fromWarehouse->warehouse_name.' | เมื่อ '.$model->viewCreated())['avatar'];
                    //  } catch (Throwable $th) {

                    //     }
                    ?>
                    <?php if(!in_array($model->order_status, ['success','cancel'])):?>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?php echo Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข', ['/inventory/stock-order/update', 'id' => $model->id, 'title' => 'แก้ไขใบรับเข้า'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]); ?>
                            <?php  echo $model->OrderApprove() ? Html::a('<i class="fa-solid fa-eraser me-2"></i> ยกเลิก', ['/inventory/stock-order/cancel-order', 'id' => $model->id, 'title' => '<i class="fa-solid fa-eraser"></i> ยกเลิก'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) : ''; ?>
                        </div>
                    </div>
                    <?php endif;?>
                </div>

                <?php echo DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label' => 'รหัสขอเบิก',
                            'value' => $model->code,
                        ],
                        [
                            'label' => 'จากคลัง',
                            'value' => $model->fromWarehouse->warehouse_name ?? '-',
                        ],
                        [
                            'label' => 'วันที่',
                            'value' => $model->viewCreatedAt(),
                        ],
                        [
                            'label' => 'สถานะคำขอ',
                            'format' => 'html',
                            'value' => $model->viewStatus(),
                        ],
                        [
                            'label' => 'มูลค่า',
                            'format' => 'html',
                            'value' => function($model){
                                return '<span>'.number_format($model->getTotalOrderPrice(),2).'</span>';
                            },
                            'contentOptions' => ['id' => 'sumPrice','class' => 'fw-semibold']
                        ],
                        [
                            'label' => 'พิมพ์เอกสาร',
                            'format' => 'raw',
                            // 'value' => Html::a('<i class="fa-solid fa-print me-1"></i> เอกสารใบเบิก', ['/inventory/document/stock-order','id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill shadow','target' => '_blank','data' => ['pjax' => false]])
                            // 'value' => Html::a('<i class="fa-solid fa-print me-1"></i> เอกสารใบเบิก', ['/inventory/document/stock-order', 'id' => $model->id], ['target' => '_blank', 'data-pjax' => '0']),
                            // 'value' => Html::a('<i class="fa-solid fa-print me-1"></i> เอกสารใบเบิก', ['/inventory/document/stock-order','id' => $model->id], ['target' => '_blank','data' => ['pjax' => false]])
                             'value' => Html::a('<i class="fa-solid fa-print me-1"></i> เอกสารใบเบิก', ['/inventory/document/stock-order','id' => $model->id], ['class' => 'open-modal','data-pjax' => '0','data' => ['size' => 'modal-xl']]),
                        ],
                    ],
                ]); ?>

            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <div class="">
                        <?php  if(isset($model->data_json['player'])):?>
                        <?=$model->ShowPlayer()['avatar'];?>
                        <?php else :?>

                        <?=isset($office) ? $model->getMe('<code>ผู้สั่งจ่าย</code>')['avatar'] : null;?>
                        <?php endif;?>
                        <!-- player -->
                    </div>
                    <div class="">
                        <div class="form-group mt-3 d-flex justify-content-end">
                            <?php if ($model->order_status == 'await') { ?>
                            <?php // echo Html::a('<i class="bi bi-check2-circle"></i> บันทึก', ['/inventory/stock-order/save-order', 'id' => $model->id], ['class' => 'btn btn-primary rounded-pill shadow checkout']); ?>
                            <?php }?>
                            <?php if ($model->OrderApprove() && isset($office) && ($model->order_status !='success') && ($model->warehouse_id == $warehouse->id)): ?>

                            <?php   //if($balanced == 0):?>
                            <?php echo  Html::a('<i class="bi bi-check2-circle"></i> บันทึกจ่าย', ['/inventory/stock-order/check-out', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill shadow checkout','id' => 'btnSave']); ?>

                            <?php  //endif;?>
                            <?php else:?>

                            <?php endif;?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- End Card -->
        <?php if($model->order_status == 'success' && $model->transaction_type == 'OUT'):?>
        <div class="card">
            <div class="card-body">
                <?php if(isset($model->data_json['recipient'])):?>

                <div class="d-flex justify-content-between align-items-center">
                    <?=$model->Recipient()['avatar']?>
                    <?=Html::a('<i class="fa-regular fa-pen-to-square"></i> ผู้รับวัสดุ',['/inventory/stock-order/recipient','id' => $model->id,'title' => 'ผู้รับวัสดุ'],['class' => 'btn btn-sm btn-primary shadow text-center rounded-pill open-modal','data' => ['size' => 'modal-md']]);?>
                </div>
                <?php else:?>
                <div class="d-flex justify-content-center">
                    <?=Html::a('<i class="bi bi-plus-circle"></i> ผู้รับวัสดุ',['/inventory/stock-order/recipient','id' => $model->id,'title' => 'ผู้รับวัสดุ'],['class' => 'btn btn-sm btn-primary shadow text-center rounded-pill open-modal','data' => ['size' => 'modal-md']]);?>
                </div>
                <?php endif?>
            </div>
        </div>
        <?php endif?>

        <div class="d-grid gap-2">
            <?php  // echo Html::a('อนุมัติเห็นชอบแทนหัวหน้า',['/inventory/stock-order/approve-form-store', 'id' => $model->leaderApproveId()],['class' => 'btn btn-primary shadow open-modal','data' => ['size' => 'modal-md']])?>
        </div>

        <!-- หัวหน้าอนุมัติให้เบิก -->
        <?php // if($model->viewChecker('ผู้เห็นชอบ')['status'] !==''):?>
        <!-- approve -->
        <div class="card">
            <div class="card-header bg-primary-gradient d-flex justify-content-between">
                <h6 class=" text-white">ผู้เห็นชอบ</h6>
                <?php  echo $model->leaderApprove()->status == 'Pending' ? Html::a('<i class="fa-solid fa-circle-check text-primary"></i> อนุมัติเห็นชอบแทนหัวหน้า',['/inventory/stock-order/approve-form-store', 'id' => $model->leaderApprove()->id],['class' => 'btn btn-light rounded-pill shadow open-modal','data' => ['size' => 'modal-md']]) : ''?>
            </div>
            <div class="card-body">
                <?php
                // echo $model->getStatus()['title'];
                
                ?>
                <div class="d-flex justify-content-between align-items-center">
                    <?php echo $model->viewChecker('ผู้เห็นชอบ')['avatar']; ?>
                    <?php // echo $model->viewChecker('ผู้เห็นชอบ')['position']; ?>
                    <?php if($model->checker == $emp->id && $model->order_status != 'success'):?>
                    <?php // echo Html::a('<i class="fa-regular fa-pen-to-square"></i> ดำเนินการ', ['/me/approve/view-stock-out', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-md']]); ?>
                    <?php endif;?>
                </div>
            </div>
        </div>
        <?php // endif;?>
        <!-- หัวหน้าอนุมัติให้เบิก จบ -->
    </div>
</div>


<?php
$id = isset($model->id) ? $model->id : null;
$js = <<< JS


showOrderItem();

//โหลดรายการวัสดุที่ขอเบิก
async function showOrderItem() {
    try {
        if ($id !== null) {
            var id = $id;
        let res = await $.ajax({
            type: "get",
            url: "/inventory/stock-order/show-order-item",
            data: { id: id }
        });
        $("#showOrderItem").html(res.content);
        $("#sumPrice").html(res.sumPrice);
        
        if(res.balance == 0){
            $('#btnSave').show();
            console.log('show');
            
        }else{
            $('#btnSave').hide();
            console.log('hide');
        }
        $("#checkout").html(res.sumPrice);
        console.log('balance',res.sumPrice)
    }
    } catch (error) {
        console.error("Error fetching order item:", error);
    }
}



// ฟังก์ชันอัปเดตค่าจำนวนสินค้า
async function updateQuantity(id, qty, quantityField) {
    let res = await $.getJSON("/inventory/stock-order/update-qty", { id, qty });
    // if (res.status === "error") {
    //     Swal.fire({ icon: "warning", title: "เกินจำนวน", showConfirmButton: false, timer: 1500 });
    // } else {
    //     quantityField.val(qty);
    // }
}

// ฟังก์ชันแจ้งเตือนเมื่อเกินจำนวน
function showLimitWarning() {
    Swal.fire({ icon: "warning", title: "เกินจำนวน", showConfirmButton: false, timer: 1500 });
}

// ฟังก์ชันจัดการการเพิ่ม/ลดจำนวนสินค้า
async function handleQuantityChange(button, isIncrement) {
    let quantityField = isIncrement ? button.prev() : button.next();
    let setVal = parseInt(quantityField.val()) + (isIncrement ? 1 : -1);
    let lotQty = button.data('lot_qty'), id = button.data('id');

    // if (setVal > lotQty) return showLimitWarning();
    await updateQuantity(id, setVal, quantityField);
    await showOrderItem();
}

// ตรวจสอบค่าที่กรอกใน input
$("body").on("input", ".qty", function () {
    const maxlot = $(this).data('maxlot');
    this.value = this.value.replace(/\D/g, '');
    if (parseInt(this.value) > maxlot) $(this).val(maxlot);
    
});

// จัดการปุ่มลดจำนวน
$("body").on("click", ".minus", function () {
    handleQuantityChange($(this), false);
});

// จัดการปุ่มเพิ่มจำนวน
$("body").on("click", ".plus", function () {
    handleQuantityChange($(this), true);
});

// กด Enter เพื่ออัปเดตจำนวน
// $("body").on("keypress", ".qty", function (e) {
//     if (e.which == 13) {
//         let id = $(this).attr("id"), qty = $(this).val();
//         updateQuantity(id, qty, $(this)).then(() => location.reload());
//     }
// });


// เมื่อกด Tab ให้ focus ที่ช่องถัดไป และ select ข้อความ
$("body").on("keydown", ".qty", function (e) {
    if (e.which == 9) { // กด Tab
        e.preventDefault(); 
        let id = $(this).attr("id");
        let qty = $(this).val();
        let inputs = $(".qty"); 
        let index = inputs.index(this);
        let nextInput = inputs.eq(index + 1);
        
        if (nextInput.length) {
            updateQuantity(id, qty, $(this)).then(() => {
                Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "อัปเดตจำนวนสำเร็จ",
                showConfirmButton: false,
                timer: 1500
            });
            });
            nextInput.focus().select(); // โฟกัส + เลือกข้อความ
        } else {
            inputs.eq(0).focus().select(); // วนกลับไปช่องแรก
        }
    }
});

// เมื่อคลิกที่ช่อง ให้ select ข้อความทั้งหมด
$("body").on("focus", ".qty", function () {
    $(this).select();
});

$("body").on("keypress", ".qty", function (e) {
    let id = $(this).attr("id");
    let qty = $(this).val();
    let td = $(this).closest("td");
    console.log('qty', qty);
    

    if (e.which == 13) { // เมื่อกด Enter
        updateQuantity(id, qty, $(this)).then(() => {
            // td.removeClass("bg-secondary"); // ลบสี bg-warning เมื่ออัปเดตเสร็จ
            location.reload();
        });
    }
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

          if (response.status == "success") {
            // await  $.pjax.reload({container:response.container, history:false,url:response.url});
            success("บันสำเร็จ!.");
          }

          if (response.status == "error") {
              $.pjax.reload({container:response.container, history:false});
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


  $("body").on("click", ".copy-item", function (e) {
    e.preventDefault();
         Swal.fire({
            title: "ยืนยัน?",
            text: "ต้องการเพิ่มรายการล็อตจากล็อตใหม่!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "ใช่, ยืนยัน!",
            cancelButtonText: "ยกเลิก",
        }).then( (result) => {
            if (result.value == true) {
             $.ajax({
                type: "get",
                url: $(this).attr('href'),
                dataType: "json",
                success: function (res) {
                    
                    if (res.status == "error") {
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: res.message,
                                footer: 'เกิดข้อผิดพลาด'
                            });
                        }

                  if (res.status == "success") {
                    location.reload();
                  }
                },
            });
            }
        });
  });



$("body").on("click", "#stockApprove", function (e) {
    e.preventDefault();
    Swal.fire({
        title: "ยืนยันเห็นชอบเทนหัวหน้า?",
        text: "เจ้าหน้าที่คลังเห็นชอบให้เบิกได้!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "ใช่, ยืนยัน!",
        cancelButtonText: "ยกเลิก",
    }).then((result) => {
        if (result.value == true) {
            Swal.fire({
                title: "กำลังดำเนินการ...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                type: "get",
                url: $(this).attr('href'),
                dataType: "json",
                success: function (res) {
                    if (res.status == "error") {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: res.message,
                            footer: 'เกิดข้อผิดพลาด'
                        });
                    }
                    if (res.status == "success") {
                        Swal.fire({
                            icon: "success",
                            title: "สำเร็จ!",
                            text: "เห็นชอบเรียบร้อยแล้ว",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: "error",
                        title: "เกิดข้อผิดพลาด",
                        text: "ไม่สามารถดำเนินการได้"
                    });
                }
            });
        }
    });
});


JS;
$this->registerJS($js,View::POS_END);
?>

<?php Pjax::end(); ?>