<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;

$totalPrice = 0;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */

$this->title = 'เลขที่รับเข้า : ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="layout-grid"></i>  
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'inventory-container', 'enablePushState' => true, 'timeout' => 88888888]); ?>
<div class="row">

    <div class="col-lg-6 col-md-6 col-sm-12">
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
                            <!-- ถ้ารอดำเนินการ หรือ รับเข้าสำเร็จแล้วและยังไม่มีการเบิกให้งานสามารถยกเลิกได้ รอแปรึกษาพี่โด้ -->
                            <?php if (in_array($model->order_status, ['pending', 'success'])): ?>
                              <?php Html::a('<i class="fa-solid fa-rotate-left me-2"></i> ยกเลิก', ['/inventory/stock-in/undo-status', 'title' => 'คืนสถานะรอดำเนินการ'], ['class' => 'dropdown-item cancel-stock-order', 'data' => ['size' => 'modal-md', 'id' => $model->id,'title' => 'คืนสถานะเป็นรอ (ดำเนินการ)']]) ?>
                              <?php endif?>
                            <?php if($model->po_number):?>
                                <?php // Html::a('<i class="fa-solid fa-circle-xmark me-2"></i> ยกเลิก', ['/inventory/stock-in/cancel', 'title' => 'ยกเลิกใบรับเข้า'], ['class' => 'dropdown-item cancel-stock-order', 'data' => ['size' => 'modal-md', 'id' => $model->id,'title' => 'ยกเลิกใบรับเข้านี้']]) ?>
                        <?php else:?>
                        <?php endif;?>
                        </div>
                    </div>
                </div>

                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label' => 'วันที่รับเข้า',
                            'value' => $model->viewMoveMentDate()
                        ],
                        [
                            'label' => 'ประเภทวัสดุ',
                            'value' => $model->viewAssetType()
                        ],
                          [
                            'label' => 'เลขที่สั่งซื้อ',
                            'value' => $model->po_number
                        ],
                        [
                            'label' => 'มูลค่า',
                            'value' => $model->getTotalOrderPrice()
                        ],
                        [
                            'label' => 'สถานะ',
                            'format' => 'raw',
                            'value' => $model->viewStatus()
                        ],

                    ],
                ]) ?>

            </div>
        </div>
    </div>
    <!-- End Card -->

    <div class="col-lg-6 col-md-6 col-sm-12">
        <!-- committee -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง</h6>
                </div>
                <?= $model->StackComittee() ?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <?= Html::a('รายการ', [
                    '/inventory/committee/list',
                    'id' => $model->id,
                    'title' => '<i class="bi bi-person-circle"></i> กรรมการกำหนดรายละเอียด'
                ], ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                <div>
                    <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่มกรรมการ', ['/inventory/committee/create', 'id' => $model->id, 'action' => 'create', 'name' => 'receive_committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับเข้าคลัง'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>


                </div>
            </div>
        </div>
        <!-- End Committee -->
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> รับเข้า <span
                            class="badge rounded-pill text-bg-primary"><?= count($model->getItems()) ?> </span> รายการ
                    </h6>
                    <?php if ($model->order_status == 'success'): ?>

                    <?php else: ?>
                        <div class="d-flex flex-row gap-3">
                            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ', ['/inventory/stock-in/product-list', 'id' => $model->id, 'name' => 'order_item', 'title' => 'รายการวัสดุ'], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <div class="dropdown">
                                <button class="btn btn-secondary rounded-pill shadow dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-gear"></i> จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><?= Html::a('<i class="fa-solid fa-file-csv me-2"></i>นำเข้าด้วย CSV', ['/inventory/import', 'order_id' => $model->id, 'title' => 'นำเข้าไฟล์ CSV'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?></li>
                                    <li><?= Html::a('<i class="fa-solid fa-file me-2"></i> ตัวอย่างไฟล์นำเข้า', 'https://docs.google.com/spreadsheets/d/1MmLegsxjTNXpeYnQf47XBZgGKIDNgQXXUdLOuOpO-60/edit?usp=sharing', ['class' => 'dropdown-item', 'target' => '_blank']) ?></li>
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-regular fa-trash-can me-2"></i> ลบรายการทั้งหมด',
                                            '#',
                                            ['class' => 'dropdown-item delete-all-item', 'data-order-id' => $model->id]
                                        ) ?>

                                    </li>
                                </ul>
                            </div>
                        </div>
                    <?php endif ?>
                    <!-- <?php if ($model->order_status == 'success'): ?>
                   
                    <?= Html::a('<i class="fa-solid fa-xmark"></i> ยกเลิก', ['/inventory/stock-event/cancel-order', 'id' => $model->id], ['class' => 'btn btn-sm btn-danger rounded-pill shadow confirm-order', 'data' => ['title' => 'ยืนยัน', 'text' => 'ยืนยันยกเลิกรายการนี้']]) ?>
                    <?php else: ?>
                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เลือกรายการ', ['/inventory/stock-in/create', 'order_id' => $model->id, 'name' => 'order_item', 'title' => 'เพิ่มรายการ'], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>
                <?php endif ?>  -->
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
                            <th class="text-end">รวม</th>
                            <th class="text-center" scope="col" style="width: 120px;">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($model->getItems() as $item): ?>
                            <tr class="<?= $item->order_status == 'pending' ? 'bg-warning-subtle' : '' ?>">
                                <td class="align-middle">
                                    <?php
                                    try {
                                        echo $item->product->Avatar();
                                    } catch (\Throwable $th) {
                                    }
                                    ?>
                                </td>

                                <td class="align-middle text-center">
                                    <?= isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-' ?>
                                </td>
                                <td class="align-middle text-center">
                                    <?= isset($item->data_json['item_type']) ? $item->data_json['item_type'] : '-' ?></td>
                                <td class="align-middle text-end">
                                    <?= isset($item->unit_price) ? $item->unit_price : '-' ?></td>

                                <td class="align-middle text-center"><?= $item->qty ?></td>

                                <td class="align-middle text-center"><?= $item->lot_number ?></td>
                                <td class="align-middle text-center">
                                    <?= $item->mfgDate; ?></td>
                                <td class="align-middle text-center">
                                    <?= $item->expDate ?></td>
                                <td class="align-middle text-end">
                                  <span class="fw-semibold">
                                     <?php 
                                            $price = $item->itemSumTotalPrice();
                                            // ตรวจสอบว่าเป็นตัวเลขหรือไม่ และไม่เป็นค่าว่าง
                                            if (is_numeric($price)) {
                                                echo number_format($price, 2);
                                            } else {
                                                echo "0.00";
                                            }
                                        ?>
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($item->order_status == 'pending'): ?>
                                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/stock-in/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                            <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['/inventory/stock-in/delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger shadow rounded-pill delete-item']) ?>
                                        <?php else: ?>
                                            <span>ดำเนินการแล้ว</span>
                                        <?php endif ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        </div>
        <div class="form-group mt-3 d-flex justify-content-center">
            <?= ($model->isPending() >= 1) ? Html::a('<i class="bi bi-check2-circle"></i> บันทึกรับเข้า', ['/inventory/stock-in/confirm-order', 'id' => $model->id], ['class' => 'btn btn-primary rounded-pill shadow confirm-order', 'data' => ['title' => 'รับวัสดุเข้าคลัง', 'text' => 'ยืนยันการรับวัสดุเข้าคลัง']]) : '' ?>
        </div>
    </div>

</div>

<?php

$js = <<< JS



$('.cancel-stock-order').click(function (e) {
  e.preventDefault();
  const el = $(this);
  const url = el.attr('href');
  const id = el.data('id');
  const title = el.data('title');

  Swal.fire({
    title: 'ยืนยัน?',
    text: 'คุณต้องการ'+title+'?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ยืนยัน!',
    cancelButtonText: 'ยกเลิก'
  }).then((result) => {
    if (result.isConfirmed) {

      // แสดง loading ขณะรอ AJAX
      Swal.fire({
        title: 'กำลังดำเนินการ...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      $.ajax({
        type: "POST",
        url: url,
        data: { id: id },
        dataType: "json",
        success: function (res) {
          if (res.status === "success") {
            Swal.fire({
              title: 'สำเร็จ!',
              text: 'ดำเนินการลบสำเร็จ!',
              icon: 'success',
              showConfirmButton: false,
              timer: 1000
            });

            setTimeout(() => {
              location.reload();
            }, 1000);
          } else {
            Swal.fire(
              'ผิดพลาด!',
              res.msg || 'ไม่สามารถดำเนินการได้',
              'error'
            );
          }
        },
        error: function () {
          Swal.fire(
            'ผิดพลาด!',
            'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้',
            'error'
          );
        }
      });
    }
  });
});


$("body").on("click", ".delete-order-item", async function (e) {
  e.preventDefault();
  var url = $(this).attr("href");

  // แสดง Swal ยืนยันการลบ
  const result = await Swal.fire({
    title: "คุณแน่ใจไหม?",
    text: "ลบรายการที่เลือก!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ลบเลย!",
    cancelButtonText: "ยกเลิก",
  });

  if (result.isConfirmed) {
    // ✅ แสดง Loading หลังจากกดยืนยัน
    Swal.fire({
      title: 'กำลังลบ...',
      text: 'กรุณารอซักครู่',
      icon: 'info',
      showConfirmButton: false,
      allowOutsideClick: false,
      willOpen: () => {
        Swal.showLoading();
      }
    });

    setTimeout(async () => {
      try {
        await $.ajax({
          type: "post",
          url: url,
          dataType: "json",
          success: function (response) {
            if (response.status == "success") {
              Swal.fire({
                title: 'สำเร็จ!',
                text: 'ดำเนินการลบสำเร็จ!',
                icon: 'success',
                showConfirmButton: false,
                timer: 1000 // ✅ ปิด Swal อัตโนมัติใน 1 วินาที
              });

              setTimeout(() => {
                location.reload(); // ✅ รีโหลดหน้าเว็บหลังจาก Swal ปิด
              }, 1000);
            } else {
              Swal.fire(
                'ผิดพลาด!',
                'ไม่สามารถลบรายการได้',
                'error'
              );
            }
          },
          error: function () {
            Swal.fire(
              'ผิดพลาด!',
              'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้',
              'error'
            );
          }
        });
      } catch (error) {
        Swal.fire(
          'ข้อผิดพลาด!',
          'เกิดข้อผิดพลาดในการดำเนินการ',
          'error'
        );
      }
    }, 2000); // ✅ Loading 2 วินาทีก่อนส่งคำขอ
  }
});

       
$('.delete-all-item').click(function (e) { 
    e.preventDefault();
    var orderId = $(this).data('order-id');

    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "คุณแน่ใจว่าต้องการลบรายการทั้งหมด!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่ ลบเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            // ส่ง AJAX ลบรายการ
            $.ajax({
                type: "post",
                url: '/inventory/stock-in/delete-all-item',
                data: { order_id: orderId, _csrf: $('meta[name="csrf-token"]').attr("content") },
                dataType: "json",
                success: function (response) {
                    if(response.status == 'success') {
                        Swal.fire(
                            'ลบเรียบร้อย!',
                            'รายการทั้งหมดถูกลบแล้ว',
                            'success'
                        ).then(() => {
                            location.reload(); 
                        });
                    } else {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            response.message || 'ไม่สามารถลบรายการได้',
                            'error'
                        );
                    }
                },
                error: function () {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถลบรายการได้',
                        'error'
                    );
                }
            });
        }
    });
});

JS;
$this->registerJS($js)
?>

<?php Pjax::end() ?>