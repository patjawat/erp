<?php
use yii\helpers\Html;
use yii\web\View;
?>


<div class="card">

    <div class="card-body">
    <div class="d-flex justify-content-between">
        <h6><i class="bi bi-ui-checks"></i> รายการตรวจรับ</h6>
    </div>
    <?php if(count($models) >=1): ?>
        <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">เลขที่สั่งซื้อ</th>
                <th scope="col">ประเภท</th>
                <th scope="col" style="width:100px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php foreach ($models as $model): ?>
                <tr class="">
                    <td scope="row"><?= $model->po_number ?></td>
                    <td><?= $model->data_json['order_type_name'] ?></td>
                    <td class="text-end">
                    <?php   Html::a('ดำเนินการ', ['/inventory/receive/view-order', 'id' => $model->id, 'receive_type' => 'purchase', 'title' => '<i class="fa-solid fa-file-circle-plus"></i> รับสินค้าจากใบสั่งซื้อ'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill', 'data' => ['size' => 'modal-md']]) ?>
                        <?= Html::a('ดำเนินการ', ['/inventory/receive/create-order', 'category_id' => $model->po_number, 'receive_type' => 'po', 'title' => '<i class="fa-solid fa-file-circle-plus"></i> รับสินค้าจากใบสั่งซื้อ'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill create-confirm', 'data' => [
                            'size' => 'modal-md',
                            'title' => 'รับเข้า'
                            ]]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else:?>
            <h5 class="text-center">ไม่มีรายการ</h5>
            <?php endif;?>
            </div>
            </div>




<?php
// $Url = Url::to(['/inventory/receive/save-to-stock']);
$js = <<< JS

    \$('.create-confirm').click(function (e) { 
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
