<?php
use yii\helpers\Html;
use app\modules\inventory\models\Product;
?>

<style>
    .product-box {
    border: 2px solid #f5f6f8;
    position: relative;
}
.product-content .product-buy-icon {
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    border-radius: 50%;
    position: absolute;
    right: 0;
}
.pricing-badge {
    position: absolute;
    top: 0;
    z-index: 9;
    right: 0;
    width: 100%;
    display: block;
    font-size: 15px;
    padding: 0;
    overflow: hidden;
    height: 100px;
}
</style>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <p><i class="fa-solid fa-circle-info"></i> สต๊อกวัสดุครุภัณฑ์</p>
            <div class="dropdown float-end">
                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa-solid fa-ellipsis"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <?= Html::a('<i class="bi bi-gear me-2"></i>ตั้งค่า', ['/sm/product'], ['class' => 'dropdown-item']) ?>
                </div>
            </div>
        </div>
     
        <div class="row">
            
            <div class="col-xl-3 col-lg-4">
                
                </div>
                <div class="col-xl-9 col-sm-6">
                    <div class="row">
                    <?php foreach($dataProvider->getModels() as $model): ?>
                        <div class="col-xl-4 col-sm-6">
                

                <div class="product-box rounded p-3 mt-4">
                                                                    <div class="pricing-badge">
                                                                        <span class="badge">New</span>
                                                                    </div>
                                                                    <div class="product-img bg-light p-3 rounded">
                                                                    <?=Html::img($model->product->ShowImg(),['class' => 'img-fluid mx-auto d-block'])?>
                                                                    </div>
                                                                    <div class="product-content pt-3">
                                                                        <p class="text-muted font-size-13 mb-0"><?=$model->product->productType->title?></p>
                                                                        <h5 class="mt-1 mb-0"><a href="#" class="text-dark font-size-16"><?=$model->product->title?></a></h5>
                                                                        <p class="text-muted mb-0">
                                                                            <i class="bx bxs-star text-warning font-size-12"></i>
                                                                            <i class="bx bxs-star text-warning font-size-12"></i>
                                                                            <i class="bx bxs-star text-warning font-size-12"></i>
                                                                            <i class="bx bxs-star text-warning font-size-12"></i>
                                                                            <i class="bx bxs-star-half text-warning font-size-12"></i>
                                                                        </p>
                                                                        <a href="" class="product-buy-icon bg-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Add To Cart">
                                                                            <i class="fa-solid fa-cart-plus text-white font-size-16"></i>
                                                                        </a>
                                                                        <h5 class="font-size-20 text-primary mt-3 mb-0">$260 <del class="text-muted font-size-15 fw-medium ps-1">$280</del></h5>     
                                                                    </div>
                                                                </div>


                                                            </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            </div>

                </div>



    </div>
</div>