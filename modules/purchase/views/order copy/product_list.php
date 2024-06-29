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
<?php Pjax::begin(); ?>


                <?php echo $this->render('_search_product', ['model' => $searchModel, 'order' => $order]); ?>


                <?php if (count($dataProvider->getModels()) == 0): ?>
        <div class="row">
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
        </div>

        <?php else: ?>
      


<div class="table-responsive">
    <table class="table table-primary">
        <thead>
            <tr>
                <th scope="col">รูปภาพ</th>
                <th scope="col">รายการ</th>
                <th scope="col">หน่วย</th>
                <th scope="col">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataProvider->getModels() as $model): ?>
            <tr class="">
                <td class="align-middle" scope="row">
                    <?= Html::img($model->ShowImg(), ['class' => ' card-img-top ', 'style' => 'height:80px;width:80px;']) ?>
                </td>
                <td class="align-middle">
                    <p><?= $model->title ?></p>
                    <span class="badge text-bg-primary "><?= $model->productType->title ?></span>
                </td>
                <td>
                    <?= isset($model->data_json['unit']) ? $model->data_json['unit'] : '-' ?>
                </td>
                <td class="align-middle">
                    <?= Html::a('<i class="bi bi-bag-plus"></i> เลือก', ['/purchase/order/add-item', 'title' => $model->title, 'product_id' => $model->id, 'code' => $order->code, 'order_id' => $order->id], ['class' => 'btn btn-sm btn-primary rounded-pill shadow text-center open-modal']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
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