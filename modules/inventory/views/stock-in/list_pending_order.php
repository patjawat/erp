<?php
use yii\helpers\Html;
use yii\web\View;
use kartik\grid\GridView;
use yii\widgets\Pjax;
?>
<?php Pjax::begin(['enablePushState' => false]); ?>

        <div class="d-flex justify-content-between">
        <h6>รอรับเข้าจำนวน <span class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?></span> รายการ</h6>
            <?php echo $this->render('_search_order', ['model' => $searchModel]); ?>
        </div>

        <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">เลขทะเบียนคุม</th>
                <th scope="col">เลขที่สั่งซื้อ</th>
                <th scope="col">ประเภท</th>
                <th scope="col">บริษัท/ผู้ขาย</th>
                <th class="text-end">มูลค่า</th>
                <th class="text-center" style="width:150px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="table-group-divider align-middle">

            <?php foreach ($dataProvider->getModels() as $model): ?>
                <tr class="">
                    <td style="width:120px" scope="row"><?= $model->pq_number ?></td>
                    <td style="width:100px"><?= $model->po_number ?></td>
                    <td><?= $model->data_json['order_type_name'] ?></td>
                    <td><?=isset($model->data_json['vendor_name']) ? $model->data_json['vendor_name'] : '';?></td>
                    <td class="text-end">
                        <span class="fw-semibold"><?=number_format($model->calculateVAT()['priceAfterVAT'],2)?></span>
                    </td>
                    <td class="text-center">
                    <?php   echo Html::a('ดำเนินการ', ['/inventory/stock-in/create-by-po', 'id' =>  $model->id, 'receive_type' => 'purchase', 'title' => '<i class="fa-solid fa-file-circle-plus"></i> รับสินค้าจากใบสั่งซื้อ : '.$model->po_number], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    <?php   Html::a('ดำเนินการ', ['/inventory/receive/view-order', 'id' => $model->id, 'receive_type' => 'purchase', 'title' => '<i class="fa-solid fa-file-circle-plus"></i> รับสินค้าจากใบสั่งซื้อ'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill create-confirm', 'data' => ['size' => 'modal-md']]) ?>
                        <?php Html::a('ดำเนินการ', ['/inventory/receive/create', 'category_id' => $model->po_number, 'receive_type' => 'po', 'title' => '<i class="fa-solid fa-file-circle-plus"></i> รับสินค้าจากใบสั่งซื้อ'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill create-confirm', 'data' => [
                            'size' => 'modal-md',
                            'title' => 'รับเข้า'
                            ]]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
       

        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'class' => 'pagination pagination-sm',
        ],
    ]); ?>
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
                window.location.href = $(this).attr('href')
                // Swal.showLoading();
                // \$.ajax({
                //     type:"get",
                //     url: $(this).attr('href'),
                //     // data: {id:warehouse_id},
                //     dataType: "json",
                //     success: async function (response) {
                       
                //     }
                // });
            }
            });
        
    });

    JS;
$this->registerJS($js, View::POS_END);

?>

<?php Pjax::end(); ?>