<?php
use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
$order =  Yii::$app->session->get('order');
// echo "<pre>";
// print_r($order);
// echo "</pre>";
$this->title = 'ใบเบิกวัสดุคลังหลัก';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-shop"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'order-container','enablePushState' => false]); ?>
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6>แสดงรายการเบิกวัสดุ</h6>
                    <!-- ปุ่มลบที่เลือก -->
                     <?php // echo Html::a('<i class="fa-solid fa-circle-exclamation"></i> คำนวนวัดุเหลือน้อย',['/me'],['class' => 'btn btn-primary rounded-pill'])?>
                    <button class="btn btn-danger mt-2" id="delete-selected" style="display: none;">ลบที่เลือก</button>


                </div>
     
        <div
            class="table-responsive"
        >
            <table
                class="table table-striped table-hover align-middle"
            >
                <thead class="">
    
                    <tr>
                        <th class="text-center" style="width: 30px;">#</th>
                        <th>รายการ</th>
                        <th class="text-center" style="width:125px">หน่วยนับ</th>
                        <th class="text-center" style="width:180px">เบิก</th>
                        <th class="text-center" style="width:100px">อนุมัติ</th>
                        <th class="text-center" style="width:100px">ดําเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                <?php foreach($model->listItems() as $item):?>
                    <tr class="">
                        <td>
                        <?php if($model->order_status == 'none'):?>
                        <div class="col-md-1 p-1 d-flex justify-content-center">
                            <input type="checkbox" class="product-checkbox" value="<?php echo $item->id; ?>">
                        </div>
                        <?php endif;?>
                        </td>
                        <td scope="row">
                            <div class="d-flex gap-3">
                            <?php echo Html::img($item->product->ShowImg(),['class' => 'img-fluid object-fit-cover rounded-1','style' => 'width:60px'])?>

                                <div class="avatar-detail">
                                <h6 class="mb-1"><?php echo $item->product->title?></h6>
                                <p class="text-muted mb-0">
                              คงเหลือ  <span class="fw-semibold text-primary"><?php echo $item->stock->SumQty() ?></span>  <?php echo $item->product->unit_name?>
                            </p>
                                </div>
                            </div>
                        </td>
                        <td class="text-center"><?php echo $item->product->unit_name?></td>
                        <td><div class="d-flex align-items-center gap-1">
                            <?php if($model->order_status == 'none'):?>
                                <button class="btn btn-sm btn-light"
                                    onclick="updateQuantity(<?php echo $item->id; ?>, -1)">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" class="form-control quantity-input"
                                    id="qty-<?php echo $item->id; ?>" value="<?php echo $item->qty?>" min="1"
                                    data-item-id="<?php echo $item->id; ?>">
                                <button class="btn btn-sm btn-light"
                                    onclick="updateQuantity(<?php echo $item->id; ?>, 1)">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                                <?php else:?>
                                    <input type="number" class="form-control quantity-input" value="<?php echo $item->qty?>" disabled>
                                <?php endif;?>
                            </div></td>
                            <td>
                            <input type="number" class="form-control quantity-input" value="<?php echo $model->order_status == 'success' ? $item->qty : 0?>" disabled>
                            </td>
                        <td class="text-center">
                        <?php if($model->order_status == 'none'):?>
                            <?php echo Html::a('<i class="bi bi-trash remove-btn text-danger fs-3"></i>',['/me/store-v2/delete-item','id'=> $item->id],['class' => 'delete-product-item']);?>
                            <?php else:?>
                                <i class="bi bi-trash remove-btn text-secondary fs-3"></i>
                                <?php endif;?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
                <tfoot>
                    
                </tfoot>
            </table>
        </div>

        </div>
        </div>

        

    </div>
    <div class="col-4">

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>

                    <?php
                                try {
                                   echo $model->CreateBy($model->fromWarehouse->warehouse_name.' | '.$model->viewCreated())['avatar'];
                                } catch (\Throwable $th) {
                                }
                                ?>
                                
                    </div>
                    <div>
                        <p class="text-muted mb-0 fs-13"><?php echo $model->viewCreatedAt()?></p>
                        <p class="text-primary mb-0 fs-13 text-end fw-semibold"><?php echo $model->code?></p>
                    </div>
                
                </div>
                <hr>

                
                <div class="d-flex justify-content-between">
                    <p class="">ประเภทวัสดุ</p>
                    <p class=""><?php echo $model->data_json['asset_type_name'] ?? 'ไม่ระบุประเภท'?></p>
                </div>
                <div class="d-flex justify-content-between">
                    <p class="">คลัง</p>
                    <p class=""><?php echo $model->warehouse->warehouse_name?></p>
                </div>
                <div class="d-flex justify-content-between">
                    <p>จำนวน</p>
                    <p><?php echo $model->OrderSummary()['total_item']?></p>
                </div>
                <div class="d-flex justify-content-between">
                    <p>มูลค่า</p>
                    <p><?php echo $model->OrderSummary()['total']?></p>
                </div>

                <div class="d-grid gap-2 mx-auto">
                    <?php if($model->order_status == 'none'):?>
                    <?php echo Html::a('ส่งข้อมูลใบเบิก',['/me/store-v2/checkout','id' => $model->id],['class' => 'btn btn-primary rounded-pill shadow','id' => 'checkout'])?>
                    <?php endif;?>

                    <?php if($model->order_status == 'pending'):?>
                        <?php if($model->created_by == Yii::$app->user->id):?>
                    <?php echo Html::a('แก้ไข',['/me/store-v2/edit','id' => $model->id],['class' => 'btn btn-warning rounded-pill shadow','id' => 'edit-order'])?>
                    <?php else:?>
                        <button class="btn btn-secondary rounded-pill shadow">แก้ไข</button>
                    <?php endif;?>
                    <?php endif;?>
                </div>

            </div>
        </div>
    </div>
</div>
<?php Pjax::end()?>
<?php if($model->order_status == 'none'):?>
<div id="viewStore"></div>
<?php endif;?>
<?php
$js = <<< JS
loadStore()

$("body").on("click", "#checkout", function (e) {
    e.preventDefault();
    Swal.fire({
            title: "ยืนยัน?",
            text: "ข้อมูลใบเบิก!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "ใช่, ยืนยืน!",
            cancelButtonText: "ยกเลิก"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:$(this).attr('href'),
                    type: "POST",
                    dataType: "json",
                    success: function(res) {
                        if(res.status == 'success'){
                            Swal.fire({
                                title: "ส่งใบเบิกสำเร็จ!",
                                text: "รอผลการตรวจสอบและคำอนุมัติจะส่งไปที่คลัง",
                                icon: "success",
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                // location.reload();
                                window.location.href = "/me/store-v2/order-in";
                            });
                        }else{
                            Swal.fire({
                                title: "เกิดข้อผิดพลาด!",
                                text: "ไม่สามารถส่งใบเบิกได้",
                                icon: "error"
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: "เกิดข้อผิดพลาด!",
                            text: "ไม่สามารถส่งใบเบิกได้",
                            icon: "error"
                        });
                    }
                });
            }
        });
    
});


$('#edit-order').click(function (e) { 
    e.preventDefault();
    Swal.fire({
            title: "ยืนยัน?",
            text: "แก้ไขใบเบิก!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "ใช่, ยืนยืน!",
            cancelButtonText: "ยกเลิก"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:$(this).attr('href'),
                    type: "POST",
                    dataType: "json",
                    success: function(res) {
                        if(res.status == 'success'){
                            location.reload();
                        }else{
                            Swal.fire({
                                title: "เกิดข้อผิดพลาด!",
                                text: "ไม่สามารถแก้ไขเบิกได้",
                                icon: "error"
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: "เกิดข้อผิดพลาด!",
                            text: "ไม่สามารถแก้ไขใบเบิกได้",
                            icon: "error"
                        });
                    }
                });
            }
        });
    
});

function updateQuantity(itemId, change) {
    let inputField = $("#qty-" + itemId);
    let currentQuantity = parseInt(inputField.val()) || 1;
    
    let newQty = currentQuantity + change;
    if (newQty < 1) newQty = 1; // ป้องกันค่าต่ำกว่า 1

    inputField.val(newQty);
    sendUpdateRequest(itemId, newQty);
}

// ตรวจจับการเปลี่ยนแปลงจากคีย์บอร์ด
$("body").on("input", ".quantity-input", function() {
    let itemId = $(this).data("item-id");
    let newQty = parseInt($(this).val()) || 1;

    if (newQty < 1) {
        newQty = 1;
        $(this).val(1);
    }

    sendUpdateRequest(itemId, newQty);
    console.log('update');
    
});

// ฟังก์ชันส่งค่าปรับปรุงไปยังเซิร์ฟเวอร์
function sendUpdateRequest(itemId, qty) {
    $.ajax({
        url: "/me/store-v2/update-quantity", // เปลี่ยนเป็น URL ของคุณ
        type: "POST",
        data: { id: itemId, qty: qty },
        success: function(response) {
            console.log("อัปเดตจำนวนสำเร็จ");
        },
        error: function() {
            alert("เกิดข้อผิดพลาดในการอัปเดตจำนวน");
        }
    });
}



    function toggleDeleteButton() {
        if ($(".product-checkbox:checked").length > 0) {
            $("#delete-selected").fadeIn();
        } else {
            $("#delete-selected").fadeOut();
        }
    }

    // ตรวจจับการคลิก checkbox
    $("body").on("change", ".product-checkbox", function (e) {
        toggleDeleteButton();
    });

    $("body").on("click", ".delete-product-item", function (e) {
        e.preventDefault();
        Swal.fire({
            title: "ยืนยัน?",
            text: "สินค้าที่เลือกจะถูกลบอย่างถาวร!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "ใช่, ลบเลย!",
            cancelButtonText: "ยกเลิก"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:$(this).attr('href'),
                    type: "POST",
                    dataType: "json",
                    success: function(res) {
                        if(res.status == 'success'){
                            Swal.fire({
                                title: "ลบสำเร็จ!",
                                text: "สินค้าที่เลือกถูกลบแล้ว",
                                icon: "success",
                                timer: 1000,
                                showConfirmButton: false
                            }).then(() => {
                                // location.reload();
                                // $.pjax.reload({container:'#order-container', history:false,url:response.url});
                                $.pjax.reload({container:'#order-container'});
                            });
                        }else{
                            Swal.fire({
                                title: "เกิดข้อผิดพลาด!",
                                text: "ไม่สามารถลบสินค้าได้",
                                icon: "error"
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: "เกิดข้อผิดพลาด!",
                            text: "ไม่สามารถลบสินค้าได้",
                            icon: "error"
                        });
                    }
                });
            }
        });
    });

    // เมื่อกดปุ่มลบที่เลือก
    $("body").on("click", "#delete-selected", function (e) {
        let selectedIds = [];
        
        $(".product-checkbox:checked").each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            return; // ป้องกันการกดปุ่มถ้าไม่มี checkbox ถูกเลือก
        }

   
        Swal.fire({
            title: "คุณแน่ใจหรือไม่?",
            text: "สินค้าที่เลือกจะถูกลบอย่างถาวร!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "ใช่, ลบเลย!",
            cancelButtonText: "ยกเลิก"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/me/store-v2/delete-multiple-items",
                    type: "POST",
                    data: { ids: selectedIds },
                    success: function(response) {
                        Swal.fire({
                            title: "ลบสำเร็จ!",
                            text: "สินค้าที่เลือกถูกลบแล้ว",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // location.reload();
                            $.pjax.reload({container:'#order-container'});
                        });
                    },
                    error: function() {
                        Swal.fire({
                            title: "เกิดข้อผิดพลาด!",
                            text: "ไม่สามารถลบสินค้าได้",
                            icon: "error"
                        });
                    }
                });
            }
        });
    });


function loadStore(){
    $.ajax({
        url: '/me/store-v2/store',
        type: 'GET',
        data: {
            title: 'เลือกคลังสินค้า'
        },
        dataType: 'json',
        success: function (res) {
            $('#viewStore').html(res.content);
        }
    })
}

JS;
$this->registerJs($js,View::POS_END);
?>