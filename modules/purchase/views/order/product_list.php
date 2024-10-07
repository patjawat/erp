<?php

use app\models\Categorise;
use kartik\depdrop\DepDrop;
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\bootstrap5\LinkPager;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

kartik\editable\EditableAsset::register($this);

/* @var yii\web\View $this */
/* @var app\modules\sm\models\ProductSearch $searchModel */
/* @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['enablePushState' => false]); ?>
<?php
// $this->registerCss('.popover-x {display:none !important;} .popover { display:none !important; } ');

?>

<?php echo $this->render('_search_product', ['searchModel' => $searchModel,'dataProvider' => $dataProvider, 'model' => $model]); ?>


<?php if (count($dataProvider->getModels()) == 0) { ?>
<div class="row">
    <div class="row d-flex justify-content-center">
        <div class="col-5">
                <h4 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> ไม่พบข้อมูล</h4>
        </div>
    </div>
</div>

<?php } else { ?>

<div class="table-responsive">
    <table class="table table-primary">
        <thead>
            <tr>
                <th scope="col">รายการ</th>
                <th scope="col" style="width:400px">หน่วย</th>
                <th scope="col"  style="width:180px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="align-middle">
            <?php foreach ($dataProvider->getModels() as $item) { ?>
            <tr class="">
                <td scope="row"><?php echo $item->Avatar(); ?></td>
                <td>
                
                <?=(isset($item->data_json['unit']) ? '<span class="badge rounded-pill bg-success-subtle">'.$item->data_json['unit'].'</span>' : '<span class="badge rounded-pill bg-danger-subtle">ไม่ได้ตั้ง</span>')?></td>
                <td class="align-middle">
                    <?php echo Html::a('<i class="fa-solid fa-circle-plus"></i> เลือก', ['/purchase/order/add-item', 'title' => $item->title, 'asset_item' => $item->id, 'code' => $model->code, 'order_id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill shadow text-center open-modal']); ?> | 
                    <?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/purchase/order/add-item', 'title' => $item->title, 'asset_item' => $item->id, 'code' => $model->code, 'order_id' => $model->id], ['class' => 'btn btn-sm btn-warning rounded-pill shadow text-center open-modal']); ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center">
                    <div class="text-muted">
                        <?php echo LinkPager::widget([
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

<?php } ?>
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
$this->registerJS($js);

?>
<?php Pjax::end(); ?>