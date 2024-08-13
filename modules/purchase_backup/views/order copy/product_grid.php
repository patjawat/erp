<?php

use app\modules\sm\models\Product;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'sm-container']); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between">
                <?php echo $this->render('_search_product', ['model' => $searchModel, 'order' => $order]); ?>

            </div>
            <div class="card-footer">
                
            </div>
        </div>
        <div class="row">

        <?php if (count($dataProvider->getModels()) == 0): ?>

<div class="row d-flex justify-content-center">
    <div class="col-5">

        <div class="alert alert-primary" role="alert">
            <h4 class="alert-heading"><i class="fa-solid fa-triangle-exclamation text-primary"></i>
                ไม่พบข้อมูล</h4>
            <p>ไม่พบคำว่า (<span class="text-danger"> <?= $searchModel->title ?> </span>) ในฐานข้อมูล
            </p>
            <hr>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/purchase/product/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        </div>
    </div>
</div>


<?php endif; ?>

            <?php foreach ($dataProvider->getModels() as $model): ?>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="text-truncate"><?= $model->title ?></h6>
                            <div class="dropdown float-end">
                                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" style="">
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/purchase/product/update', 'id' => $model->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                    <?= Html::a('<i class="fa-solid fa-trash"></i> ลบ', ['/purchase/asset-type/delete', 'id' => $model->id], [
                                        'class' => 'dropdown-item  delete-item',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <?= Html::img($model->ShowImg(), ['class' => ' card-img-top ', 'style' => 'max-width:100%;height:280px;max-height: 280px;']) ?>
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex flex-column">
                                <span class="badge text-bg-primary "><?= $model->productType->title ?></span>
                            </div>
                            <?= Html::a('<i class="bi bi-bag-plus"></i> เลือก', ['/purchase/order/add-item', 'title' => $model->title, 'product_id' => $model->id, 'code' => $order->code, 'order_id' => $order->id], ['class' => 'btn btn-sm btn-primary rounded-pill shadow text-center open-modal']) ?>

                        </div>
                        <div class="d-flex justify-content-between">

                            <span
                                class=""><?= isset($model->data_json['unit']) ? $model->data_json['unit'] : '-' ?></span>
                        </div>
                        <hr>
                        <!-- <span>คงเหลือ 10 ชิ้น</span> -->
                        <div class="d-flex justify-content-center">
                         
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
$js = <<< JS

    // \$('.product-add').click(function (e) { 
    //     e.preventDefault();
    //     var url = \$(this).attr('href')
    //     \$.ajax({
    //         type: "get",
    //         url: url,
    //         dataType: "json",
    //         success: function (res) {
                
    //         }
    //     });
    // });
    JS;
$this->registerJS($js)

?>
<?php Pjax::end(); ?>