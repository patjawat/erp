<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var app\modules\inventory\models\StockMovement $model */
/** @var yii\web\View $this */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stock Movements', 'url' => ['index']];
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

<!-- <div class="card">
    <div class="card-body">
<h5>ขอเบิกวัสดุ</h5>
    </div>
</div> -->
<?php // $this->render('list_product',['model' => $model])?>
<?php yii\widgets\Pjax::begin(['id' =>'add-item']); ?>
<div class="row d-flex justify-content-center">

    <div class="col-10">
 
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-file-lines"></i> ขอเบิกวัสดุ</h6>
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

                <div class="row">

                <div class="col-6">
                            </div>
                <div class="col-6">

                <table class="table table-striped-columns">
                    <tbody>
                        <tr class="bg-primary-subtle">
                            <td colspan="6">รายละเอียดกาเบิก</td>
                        </tr>
                        <tr>
                            <td class="text-end">รหัสขอเบิก</td>
                            <td colspan="2"><?=$model->rq_number?></td>
                            <td class="text-end">วันที่</td>
                            <td colspan="2"><?=$model->viewCreateDate()?></td>
                        </tr>
                        <tr>
                            <td class="text-end">ผู้ขอ</td>
                            <td colspan="5"><?=$model->CreateBy()['avatar']?></td>

                        </tr>
                    </tbody>
                </table>

                </div>

</div>


                <?=$this->render('list_item_requet',['model' => $model])?>
            </div>
        </div>
    </div>
</div>



</div>
<?php yii\widgets\Pjax::begin(); ?>
<?php
$url = Url::to(['/inventory/stock-request/add-item']);
$js = <<< JS

$(document).ready(function () {
    
});

$("body").on("click", ".add-product", function (e) {
    e.preventDefault();
    var product_id = $(this).data('product_id');
    var id = $(this).data('id');
    var rq_number = $(this).data('rq_number');
    var total = $(this).data('total');
    // var qty =  $('#qty-'+id).val();  
    var qty =  $('#qty-'+id).val();  

    console.log(qty);

    if((total - qty) < 0){
        alert('xx')
        $('#qty-'+id).val(total)
    }

    $.ajax({
        type: "post",
        url: "$url",
        data:{
            id:id,
            product_id:product_id,
            qty:qty,
            total:total,
            rq_number:rq_number
        },
        dataType: "json",
        success: function (response) {
            console.log(response);
            $.pjax.reload({container:'#add-item', history:false});
            
        }
    });
});
JS;
$this->registerJS($js,View::POS_END);
?>