<?php

use app\modules\inventory\models\Stock;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Order $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$warehouse = Yii::$app->session->get('warehouse');
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
                <div class="d-flex justify-content-between align-items-center">
                    <h5><i class="fa-solid fa-shop"></i> รับเข้า<?=$warehouse['warehouse_name']?></h5>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?=Html::a('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-pen-line"><path d="m18 5-2.414-2.414A2 2 0 0 0 14.172 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2"/><path d="M21.378 12.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/><path d="M8 18h1"/></svg> แก้ไข', ['update', 'id' => $model->id,'title' => 'แก้ไข'],['class' => 'dropdown-item open-modal me-5','data' => ['size' => 'modal-md']]) ?>

                        </div>
                    </div>

                    <!-- <div> -->
                    <?php /// Html::a('เลือกรายการ', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?php // Html::a('<i class="bi bi-trash fw-bold"></i> ยกเลิกรายการ',['/purchase/po-order/update','id' => $model->id,'title' => '<i class="bi bi-pencil-square"></i> แก้ไขคำสั่งซื้อ'],['class' => 'btn btn-danger rounded-pill shadow text-center open-modal shadow me-5','data' => ['size' => 'modal-md']])?>

                    <!-- </div> -->

                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="card border border-primary">
                           
                            <div class="card-body pb-1">
                                <table class="table table-sm table-striped-columns">
                                    <tbody>
                                        <tr class="">
                                            <td style="width: 150px;">กำหนดวันส่งมอบ</td>
                                            <td><?=$model->order->data_json['delivery_date']?></td>
                                        </tr>
                                        <tr class="">
                                            <td style="width: 108px;">ใบสั่งซื้อเลขที่</td>
                                            <td><?=$model->order->po_number?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer text-muted d-flex justify-content-between p-1">
                            <div class="d-flex p-1">
                                <img class="avatar avatar-sm bg-primary text-white" src="/img/placeholder-img.jpg" alt="">
                                <div class="avatar-detail">
                                    <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <?= $model->order->data_json['vendor_name'] ?>
                                    </h6>
                                    <p class="text-primary mb-0 fs-13">
                                        <?=isset($model->data_json['vendor_address']) ? $model->order->data_json['vendor_address'] : '-'?>
                                    </p>
                                </div>
                            </div>
                                <p>ผู้จำหน่าย</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">

                        <div class="card border border-primary">

                            <div class="card-body">
                                <table class="table table-sm table-striped-columns">
                                    <tbody>
                                        <tr class="">
                                            <td style="width: 150px;">วันรับเข้าคลัง</td>
                                            <td><?=$model->order->data_json['delivery_date']?></td>
                                            <td style="width: 150px;">เลขที่รับ</td>
                                            <td><?=$model->rc_number?></td>
                                        </tr>
                                        <tr class="">
                                        </tr>
                                        <tr class="">
                                            <td style="width: 108px;">ตรวจรับ</td>
                                            <td><?=$model->data_json['checked_date']?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer text-muted d-flex justify-content-between">
                                <?=$model->createBy()['avatar']?>
                                <p>ผู้ดำเนินการ</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if($model->order_status != 'success') : ?>
                <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-file-circle-plus"></i> รายการตรวจรับ</h6>
                </div>
                <?php else:?>
                <h6><i class="fa-solid fa-file-circle-plus"></i> รายการตรวจรับ</h6>
                <?php endif ?>
                <div class="table-responsive">
                    <table class="table table-primary">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <div>
                                        <?php if ($model->receive_type == 'receive'): ?>
                                        <?= Html::a('<i class="fa-solid fa-plus"></i> เลือกรายการ', ['/inventory/receive/list-product','id' => $model->id, 'po_number' => $model->po_number, 'title' => '<i class="bi bi-ui-checks-grid"></i> รายการวัสดุ'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                        <?php else: ?>
                                        <?= Html::a('<i class="fa-solid fa-plus"></i> เลือกรายการ', ['/inventory/receive/list-product-order', 'id' => $model->id, 'title' => '<i class="bi bi-ui-checks-grid"></i> รายการใบสั่งซื้อเลขที่ : '.$model->po_number], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                        <?php endif ?>
                                    </div>
                                </th>
                                <th scope="col">ประเภท</th>
                                <?php if ($model->receive_type == 'purchase'): ?>
                                <th scope="col">จำนวนสั่งซื้อ</th>
                                <?php endif ?>
                                <th scope="col">จำนวนรับเข้า</th>
                                <th scope="col">ล็อต</th>
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
                                            echo $item->orderItem->product->Avatar(false);
                                        } catch (\Throwable $th) {
                                            echo '-';
                                        }
                                        ?>
                                </td>
                                <td>
                                    <?php
                                        try {
                                            echo $item->orderItem->data_json['product_type_name'];
                                        } catch (\Throwable $th) {
                                            echo '-';
                                        }
                                        ?>
                                </td>
                                <?php if ($model->receive_type == 'purchase'): ?>
                                <td><?php //  $item->getPoQty()->qty ?></td>
                                <?php endif;?>
                                <td><?= $item->qty ?></td>
                                <td>R1C3</td>
                                <?php if($model->order_status != 'success') : ?>
                                <td>
                                    <?= Html::a('<i class="bi bi-trash"></i>', ['/inventory/receive/delete', 'id' => $item->id, 'container' => 'rc_commitee'], [
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
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-person-circle"></i> กรรมการตรวจรับวัสดุเข้าคลัง</h6>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?= Html::a('<i class="bi bi-plus-circle-fill me-2"></i> เพิ่มกรรมการ', [
                            '/inventory/receive/add-committee','rc_number' => $model->rc_number,'name' => 'receive_committee','title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง'
                        ], ['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-md']]) ?>
                    <?=Html::a('<i class="fa-solid fa-list-ul me-2"></i> แสดงรายชื่อ',['/inventory/receive/list-committee','id' => $model->id,'title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับวัสดุ'],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?>    
                    </div>
                    </div>
                </div>
                <?=$model->StackComittee()?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                วันที่ตรวจรับ :  <?=$model->data_json['checked_date']?>
                
               
            </div>
        </div>
        <div id="showProductOrder"></div>

        <?php // $this->render('list_committee', ['model' => $model]) ?>


    </div>
</div>



<div class="row">

    <div class="col-12">

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
$listProductOrderUrl = Url::to(['/inventory/receive/list-product-order', 'id' => $model->id]);
$js = <<<JS

        getProductOrder()
        async function getProductOrder()
                {
                    await \$.ajax({
                        type: "get",
                        url: "$listProductOrderUrl",
                        dataType: "json",
                        success: function (res) {
                            \$('#showProductOrder').html(res.content)
                        }
                    });
                }

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