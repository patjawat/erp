<?php
use yii\helpers\Html;
use yii\web\View;
use kartik\grid\GridView;
use yii\widgets\Pjax;
?>


<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'hover' => true,
        'pjax' => true,
        'layout' => '{items}',
        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'contentOptions' => ['class' => 'kartik-sheet-style'],
                'width' => '36px',
                'pageSummary' => 'Total',
                'pageSummaryOptions' => ['colspan' => 6],
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style'],
            ],
            [
                'attribute' => 'pq_number',
                'format' => 'raw',
                'width' => '120px',
                'header' => 'เลขทะเบียนคุม',
                'value' => function($model){
                    return $model->pq_number;
                }
            ],
            [
                'attribute' => 'po_number',
                'format' => 'raw',
                'width' => '120px',
                'header' => 'เลขที่สั่งซื้อ',
                // 'hAlign' => 'center',
                'vAlign' => 'middle',
                'value' => function($model){
                        return $model->po_number;
                }
            ],
            [
                'attribute' => 'order_type_name',
                'format' => 'raw',
                'width' => '180px',
                'header' => 'ประเภท',
                // 'hAlign' => 'center',
                'vAlign' => 'middle',
                'value' => function($model){
                        return isset($model->data_json['order_type_name']) ? $model->data_json['order_type_name'] : '';
                }
            ],
            [
                'attribute' => 'vendor_name',
                'format' => 'raw',
                'header' => 'บริษัท/ผู้ขาย',
                // 'hAlign' => 'center',
                'vAlign' => 'middle',
                'value' => function($model){
                    return isset($model->data_json['vendor_name']) ? $model->data_json['vendor_name'] : '';
                }
            ],
            [
                // 'attribute' => '',
                'format' => 'raw',
                'width' => '150px',
                'header' => 'มูลค่า',
                'vAlign' => 'middle',
                'hAlign' => 'right', 
                'value' => function($model){
                    return '<span class="fw-semibold">'.number_format($model->calculateVAT()['priceAfterVAT'],2).'</span>';
                }
            ],
            [
                'format' => 'raw',
                'width' => '120px',
                'header' => 'ดำเนินการ',
                'hAlign' => 'center',
                // 'vAlign' => 'middle',
                'value' => function($model){
                        return  Html::a('<i class="fa-regular fa-pen-to-square"></i> ตรวจรับ', ['/inventory/stock-in/create-by-po', 'id' =>  $model->id, 'receive_type' => 'purchase', 'title' => '<i class="fa-solid fa-file-circle-plus"></i> รับสินค้าจากใบสั่งซื้อ : '.$model->po_number], ['class' => 'btn btn-sm btn-primary shadow rounded-pill zoom-in open-modal', 'data' => ['size' => 'modal-md']]);
                }
            ],
           
            
        ],
    ]); ?>

<!-- 
<div class="card">
    <div class="card-body">
    <?php if(count($models) >=1): ?>
        <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">เลขทะเบียนคุม</th>
                <th scope="col">เลขที่สั่งซื้อ</th>
                <th scope="col">ประเภท</th>
                <th scope="col">บริษัท/ผู้ขาย</th>
                <th scope="col" style="width:100px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php foreach ($models as $model): ?>
                <tr class="">
                    <td style="width:100px" scope="row"><?= $model->pq_number ?></td>
                    <td style="width:100px"><?= $model->po_number ?></td>
                    <td><?= $model->data_json['order_type_name'] ?></td>
                    <td>xx</td>
                    <td class="text-end">
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
        <?php else:?>
            <h5 class="text-center">ไม่มีรายการ</h5>
            <?php endif;?>
            </div>
            </div>

 -->


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
