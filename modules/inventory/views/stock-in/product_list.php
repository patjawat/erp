<?php

use app\modules\sm\models\Product;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['enablePushState' => false,'timeout' => 999999999]); ?>

<?php echo $this->render('_search_product', ['searchModel' => $searchModel, 'model' => $model]); ?>


<?php if (count($dataProvider->getModels()) == 0): ?>
<div class="row">
    <div class="row d-flex justify-content-center">
        <div class="col-5">
                <h4 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> ไม่พบข้อมูล</h4>
        </div>
    </div>
</div>

<?php else: ?>



<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th scope="col"><i class="bi bi-ui-checks"></i> จำนวน <span class="badge rounded-pill text-bg-primary"> <?=number_format($dataProvider->getTotalCount(),0)?></span> รายการ</th>
                <th scope="col">หน่วย</th>
                <th scope="col"  style="width:90px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataProvider->getModels() as $item): ?>
            <tr class="">
                <td scope="row">
                    <?=$item->Avatar()?>
                </td>
                <td><?=(isset($item->data_json['unit']) ? '<span class="badge rounded-pill bg-success-subtle">'.$item->data_json['unit'].'</span>' : '<span class="badge rounded-pill bg-danger-subtle">ไม่ได้ตั้ง</span>')?></td>
                <td class="align-middle">
                    <?php //  Html::a('<i class="bi bi-bag-plus"></i> เลือก', ['/inventory/stock-in/add-item', 'title' => $item->title, 'asset_item' => $item->id,'order_id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill shadow text-center open-modal']) ?>
                    <?= Html::a('<i class="bi bi-bag-plus"></i> เลือก', ['/inventory/stock-in/create','name' => 'order_item','title' => $item->title, 'asset_item' => $item->code,'order_id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill shadow text-center open-modal']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center">
                    <div class="text-muted">
                        <?= LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'firstPageLabel' => 'หน้าแรก',
                            'lastPageLabel' => 'หน้าสุดท้าย',
                            'options' => [
                                'listOptions' => 'pagination pagination-sm',
                                'class' => 'pagination-sm',
                            ],
                        ]); ?>
                    </div>
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