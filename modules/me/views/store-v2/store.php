<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;
use app\modules\inventory\models\Warehouse;

?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php // echo $this->render('../default/menu'); ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'store-container','enablePushState' => false]); ?>
<?php

$cart = Yii::$app->cartMain;
$products = $cart->getItems();

?>
<div class="row">
    <div class="col-4"><?php echo $this->render('_search', ['model' => $searchModel]); ?></div>
    <div class="col-8">
        
    </div>
</div>

<div class="d-flex flex-wrap">
    <?php foreach ($dataProvider->getModels() as $model) { ?>
    <div class="p-2 col-2">
        <div class="card position-relative">
            <p class="position-absolute top-0 end-0 p-2">
                <i class="fa-solid fa-circle-info fs-4"></i>
            </p>
            <?php echo Html::img($model->product->ShowImg(), ['class' => 'card-top object-fit-cover','style' => 'max-height: 155px;']); ?>
            <div class="card-body w-100">
                <div class="d-flex justify-content-start align-items-center">
                    <?php if($model->SumQty() >= 1):?>
                    <span class="badge text-bg-primary  mt--45"><?php echo $model->SumQty(); ?>
                        <?php echo $model->product->unit_name; ?></span>
                    <?php else:?>
                    <span class="btn btn-sm btn-secondary fs-13 mt--45 rounded-pill"> หมด</span>
                    <?php endif;?>
                </div>
                <p class="text-truncate mb-0"><?php echo $model->product->title; ?></p>

                <div class="d-flex justify-content-between">
                    <div class="fw-semibold text-danger">
                        <?php echo number_format($model->unit_price,2); ?>
                    </div>
                    <?php
                                                try {
                                                    echo Html::a('<i class="fa-solid fa-circle-plus"></i> เลือก', ['/me/store-v2/add-card', 'id' => $model->getLotQty()['id']], ['class' => 'btn btn-sm btn-primary rounded-pill add-card']); 
                                                } catch (Throwable $th) {
                                                    // throw $th;
                                                }
                                ?>
                </div>
            </div>
        </div>

    </div>
    <?php }?>
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

<?php Pjax::end(); ?>

<?php
$js = <<< JS


$("body").on("click", ".add-card", function (e) {
    e.preventDefault();
    
    let url = $(this).attr('href'); 

    $.ajax({
        type: "POST",
        url: url,
        dataType: "json",
        success: function (res) {
            if (res.status === "success") {
                $.pjax.reload({container:'#order-container', history:false,url:res.url});
                // ใช้ SweetAlert2 Toast
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: "success",
                    title: "เพิ่มสินค้าในตะกร้าเรียบร้อย!",
                    showConfirmButton: false,
                    timer: 500
                }).then(() => {
                    // location.reload();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "เกิดข้อผิดพลาด",
                    text: res.message
                });
            }
        },
        error: function (xhr, status, error) {
            Swal.fire({
                icon: "error",
                title: "การร้องขอล้มเหลว",
                text: error
            });
        }
    });
  
});




JS;
$this->registerJS($js, View::POS_END);

?>