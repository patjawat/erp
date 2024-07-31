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

<div class="row">
    <div class="col-8">

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">

                    <h5><i class="fa-solid fa-shop"></i> รับวัสดุ</h5>
                    <div>
                        <?php Html::a('เลือกรายการ', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?php Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?=Html::a('<i class="bi bi-trash fw-bold"></i> ยกเลิกรายการ',['/purchase/po-order/update','id' => $model->id,'title' => '<i class="bi bi-pencil-square"></i> แก้ไขคำสั่งซื้อ'],['class' => 'btn btn-danger rounded-pill shadow text-center open-modal shadow me-5','data' => ['size' => 'modal-md']])?>
                        
                    </div>
                    
                </div>


<div class="row">
<div class="col-6">
    <div class="card border border-primary">
        <div class="card-body">
            <h4 class="card-title">Title</h4>
            <p class="card-text">Text</p>
        </div>
    </div>
    
</div>
<div class="col-6">

<div class="card border border-primary">
        <div class="card-body">
            <h4 class="card-title">Title</h4>
            <p class="card-text">Text</p>
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
                                            echo $item->product->Avatar(false);
                                        } catch (\Throwable $th) {
                                            echo '-';
                                        }
                                        ?>
                                </td>
                                <td>
                                    <?php
                                        // try {
                                        //     echo $item->data_json['product_type_name'];
                                        // } catch (\Throwable $th) {
                                        //     echo '-';
                                        // }
                                        ?>
                                </td>
                                <?php if ($model->receive_type == 'purchase'): ?>
                                <td><?php //  $item->getPoQty()->qty ?></td>
                                <?php endif;?>
                                <td><?= $item->qty ?></td>
                                <td>R1C3</td>
                                <?php if($model->order_status != 'success') : ?>
                                <td>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/receive/update-item', 'id' => $item->id, 'name' => 'board', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?>
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

                </div>
                <?=$model->StackComittee()?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <h6>กรรมการ</h6>
                <?= Html::a('ดำเนินการ', [
                            '/purchase/order-item/committee-detail','title' => '<i class="bi bi-person-circle"></i> กรรมการกำหนดรายละเอียด'
                        ], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
            </div>
        </div>

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