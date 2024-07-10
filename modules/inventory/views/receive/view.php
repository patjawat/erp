<?php

use app\modules\inventory\models\StockMovement;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Order $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
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
<div class="card">
    <div class="card-body d-flex justify-content-between align-items-center">
        <h5><i class="fa-solid fa-shop"></i> รับสินค้าเข้าคลัง</h5>
        <div>
            <?php Html::a('เลือกรายการ', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?php Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-xmark"></i> ยกเลิก', ['cancel-order', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'id' => 'cancelOrder',
                'data' => [
                    // 'confirm' => 'Are you sure you want to delete this item?',
                    // 'method' => 'post',
                ],
            ]) ?>

        </div>
    </div>
</div>



<div class="row">

    <div class="col-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-file-lines"></i> ข้อมูลตรวจรับ</h6>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-regular fa-eye me-1 text-primary"></i> แสดง', ['/inventory/receive/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                            <?= Html::a('<i class="bx bx-trash me-1 text-danger"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                                'class' => 'dropdown-item  delete-item',
                            ]) ?>
                        </div>
                    </div>
                </div>
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label' => 'เลขที่ขอซื้อ',
                            'value' => function ($model) {
                    // return $model->pr_number;
                }
                        ],
                        [
                            'label' => 'เลขที่สั่งซื้อ',
                            'value' => function ($model) {
                    // return $model->po_number;
                }
                        ],
                        [
                            'label' => 'สานะ',
                            'value' => function ($model) {
                    // return $model->viewStatus();
                }
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-5">
        <?= $this->render('list_committee', ['model' => $model]) ?>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if($model->order_status != 'success') : ?>
                <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-file-circle-plus"></i> รายการตรวจรับ</h6>
                    <div>
                        <?php if ($model->receive_type == 'normal'): ?>
                        <?= Html::a('<i class="fa-solid fa-plus"></i> เลือกรายการ', ['/inventory/receive/list-all-product', 'po_number' => $model->po_number, 'title' => '<i class="bi bi-ui-checks-grid"></i> รายการวัสดุ'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                        <?php else: ?>
                        <?= Html::a('<i class="fa-solid fa-plus"></i> เลือกรายการ', ['/inventory/receive/list-po-order', 'id' => $model->id, 'title' => '<i class="bi bi-ui-checks-grid"></i> รายการใบสั่งซื้อเลขที่ : '.$model->po_number], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                        <?php endif ?>
                    </div>

                </div>
                <?php else:?>
                <h6><i class="fa-solid fa-file-circle-plus"></i> รายการตรวจรับ</h6>
                <?php endif ?>
                <div class="table-responsive">
                    <table class="table table-primary">
                        <thead>
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">ประเภท</th>
                                <th scope="col">หน่วย</th>
                                <th scope="col">จำนวนสั่งซื้อ</th>
                                <th scope="col">จำนวนรับเข้า</th>
                                <th scope="col">ล็อต</th>
                                <th scope="col">วันที่ผลิต</th>
                                <th scope="col">วันที่หมดอายุ</th>
                                <?php if($model->order_status != 'success') : ?>
                                <th scope="col">ดำเนินการ</th>

                                <?php endif;?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($model->ListItemFormRcNumber() as $item): ?>
                            <tr class="">
                                <td scope="row">
                                    <?php
                                        try {
                                            echo $item->data_json['product_name'];
                                        } catch (\Throwable $th) {
                                            echo '-';
                                        }
                                        ?>
                                </td>
                                <td>
                                    <?php
                                        try {
                                            echo $item->data_json['product_type_name'];
                                        } catch (\Throwable $th) {
                                            echo '-';
                                        }
                                        ?>
                                </td>
                                <td>
                                    <?php
                                        try {
                                            echo $item->data_json['unit'];
                                        } catch (\Throwable $th) {
                                            echo '-';
                                        }
                                        ?>
                                </td>
                                <td><?= $item->getPoQty()->qty ?></td>
                                <td><?= $item->qty ?></td>
                                <td><?= $item->lot_number ?></td>
                                <td><?= $item->lot_number ?></td>
                                <td>R1C3</td>
                                <?php if($model->order_status != 'success') : ?>
                                <td>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/receive/update-item', 'id' => $item->id, 'name' => 'board', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    <?= Html::a('<i class="bx bx-trash me-1"></i>', ['/inventory/receive/delete', 'id' => $item->id, 'container' => 'rc_commitee'], [
                                            'class' => 'btn btn-sm btn-danger delete-item',
                                        ]) ?>
                                </td>
                                <?php endif;?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <!-- ถ้าหามีรายการรับเข้าให้แสดงปุ่ม บันทึกรับเข้าคลัง -->
    <?php if (count($model->ListItemFormRcNumber()) > 0 AND $model->order_status != 'success'): ?>
    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึกรับเข้าคลัง', ['class' => 'btn btn-primary', 'id' => 'toStock']) ?>
    </div>
    <?php endif ?>
</div>


<?php
use yii\helpers\Url;

$url = Url::to(['/inventory/receive/save-to-stock', 'id' => $model->id]);
$js = <<<JS
    \$('#toStock').click(function (e) { 
        e.preventDefault();
          Swal.fire({
            title: 'รับเข้าคลัง?',
            text: "ยืนยันรับเข้าคลัง!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
        
        \$.ajax({
            type: "get",
            url: "$url",
            dataType: "json",
            success: function (response) {
                if(response.status == 'success') {
                            success()
                            $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
            
            }
        })
    }); 



    $('body').on('click', '#cancelOrder', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        // $('#main-modal').modal('show');
        
        Swal.fire({
            title: 'ยกเลิก?',
            text: "ยกเลิกรายการรับเข้า!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: url,
                    dataType: "json",
                    success: async function (response) {
                        if(response.status == 'success'){
                            await closeModal()
                            await success('ถูกลบไปแล้ว.');
                            await $('#calendar').fullCalendar('refetchEvents');
                    }
                    }
                });
            
            }
        })
    }); 

    JS;

$this->registerJS($js);
?>
<?php Pjax::end(); ?>