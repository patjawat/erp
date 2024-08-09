<?php
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
?>
<style>
.popover-x {
    display: none !Important;
}
</style>
<?php Pjax::begin(['id' => 'inventory']); ?>

<div class="row">
    <div class="col-6">

        <div class="card border border-primary">
            <div class="d-flex p-3">
                <img class="avatar" src="/img/placeholder-img.jpg" alt="">
                <div class="avatar-detail">
                    <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top">
                        <?= isset($model->data_json['vendor_name']) ? $model->data_json['vendor_name'] : '' ?>
                    </h6>
                    <p class="text-primary mb-0 fs-13">
                        <?=isset($model->data_json['vendor_address']) ? $model->data_json['vendor_address'] : '-'?></p>
                </div>
            </div>
            <div class="card-body pb-1">
                <table class="table table-sm table-striped-columns">
                    <tbody>
                        <tr class="">
                            <td style="width: 150px;">กำหนดวันส่งมอบ</td>
                            <td><?php // $model->data_json['delivery_date']?></td>
                        </tr>
                        <tr class="">
                            <td style="width: 108px;">ใบสั่งซื้อเลขที่</td>
                            <td><?=$order->po_number?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted d-flex justify-content-between">
                <p>ผู้ขาย</p>

            </div>
        </div>

        <?php
                            try {
                                $orderTypeName =  $model->data_json['order_type_name'];
                            } catch (\Throwable $th) {
                                $orderTypeName = '';
                            }
                        ?>


    </div>
    <div class="col-6">

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <?=$model->getMe('ผู้ตรวจรับเข้าคลัง')['avatar']?>

                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข', ['/inventory/receive/update','id' => $model->id,'title' => 'แก้ไขใบรับเข้า'], ['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-md']]) ?>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-between">
                <span>ผู้ดำเนินการ</span>
                    <?php echo Html::a('รับเข้าคลัง',['/inventory/receive/save-to-stock','id' => $model->id],['class' => 'btn btn-sm btn-primary rounded-pill shadow stock-confirm','data' => [
                        'title' => 'ยืนยัน',
                        'text' => 'ยืนยันรับเข้าคลัง'
                    ]])?>
            </div>
        </div>

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
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6><i class="bi bi-ui-checks"></i> รายการตรวจรับ</h6>
      

        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-primary">
                    <tr>
                        <th style="width:500px">
                            รายการ
                            <?= Html::a('<i class="fa-solid fa-circle-plus text-white"></i> เลือกรายการ', ['/inventory/receive/product-list', 'id' => $model->id, 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เลือกรายการ ' ], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal shadow', 'data' => ['size' => 'modal-lg']]) ?>
                        </th>
                        <th class="text-center">หน่วย</th>
                        <th class="text-end">มูลค่า</th>
                        <th class="text-center">จำนวนรับ</th>
                        <th class="text-end">ราคาต่อหน่วย</th>
                        <th class="text-center">ล็อตผลิต</th>
                        <th class="text-center">วันผลิต</th>
                        <th class="text-center">วันหมดอายุ</th>
                        <th class="text-center" scope="col" style="width: 120px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($order->ListOrderItems() as $item): ?>
                    <tr class="">
                        <td class="align-middle">
                            <?php
                            try {
                                echo $item->product->Avatar();
                            } catch (\Throwable $th) {}
                            ?>
                        </td>

                        <td class="align-middle text-center">
                            <?php
                        try {
                            echo $item->product->data_json['unit'];
                        } catch (\Throwable $th) {

                        }
                        ?>
                        </td>
                        <td class="align-middle text-end fw-semibold">
                            <?php
                            try {
                                echo number_format($item->price, 2);
                            } catch (\Throwable $th) {

                            }
                            ?>
                        </td>
                        <td class="align-middle text-center"><?= $item->qty ?> >
                            <?= isset($item->data_json['qty']) ? $item->data_json['qty'] : '-' ?></td>
                        <td class="align-middle text-center">
                            <?=   isset($item->price) ? number_format($item->price, 2) : '-' ?></td>
                        <td class="align-middle text-center">
                            <?= isset($item->data_json['lot_number']) ? $item->data_json['lot_number'] : '-' ?></td>
                        <td class="align-middle text-center">
                            <?= isset($item->data_json['mfg_date']) ? $item->data_json['mfg_date'] : '-' ?></td>
                        <td class="align-middle text-center">
                            <?= isset($item->data_json['exp_date']) ? $item->data_json['exp_date'] : '-' ?></td>

                        <td class="align-middle">
                            <div class="d-flex justify-content-center gap-2">
                                
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/receive/update-order-item', 'id' => $item->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> รับเข้า'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>

        </div>


    </div>
</div>



<?php
// $Url = Url::to(['/inventory/receive/save-to-stock']);
$js = <<< JS

    \$('.stock-confirm').click(function (e) { 
        e.preventDefault();
        var title = \$(this).data('title');
        var text = \$(this).data('text');
        var imageUrl = \$(this).data('img');
        var warehouse_id = \$(this).data('warehouse_id');
        Swal.fire({
            imageUrl: imageUrl,
            imageWidth: 400,
            imageHeight: 200,
            title: title,
            text: text,
            icon: "info",

            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "<i class='bi bi-x-circle'></i> ยกเลิก",
            confirmButtonText: "<i class='bi bi-check-circle'></i> ยืนยัน"
            }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                \$.ajax({
                    type:"get",
                    url: $(this).attr('href'),
                    // data: {id:warehouse_id},
                    dataType: "json",
                    success: async function (response) {
                       
                    }
                });
            }
            });
        
    });

    JS;
$this->registerJS($js, View::POS_END);

?>

<?php Pjax::end() ?>