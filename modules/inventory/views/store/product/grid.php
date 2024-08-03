<?php
use yii\helpers\Html;
use app\modules\inventory\models\Product;
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <p><i class="fa-solid fa-circle-info"></i> สต๊อกวัสดุครุภัณฑ์</p>
            <div class="dropdown float-end">
                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa-solid fa-ellipsis"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right" style="">
                    <?= Html::a('<i class="bi bi-gear me-2"></i>ตั้งค่า', ['/sm/product'], ['class' => 'dropdown-item']) ?>
                </div>
            </div>
        </div>
     
        <div class="row">
                <?php foreach($dataProvider->getModels() as $model): ?>
                <div class="col-2">
                    <div class="card">
                        <?=Html::img($model->product->ShowImg(),['class' => 'object-fit-cover border rounded','style'=>"height: 150px;"])?>
                    
                        <div class="card-body">
                                <div class="d-flex flex-column justify-content-start" style="height: 80px;">
                                    <h6 class="text-center"><?=$model->product->title?></h6>
                                </div>
                                <div class="d-flex flex-column justify-content-center" style="height: 50px;">
                                    <h5 class="text-center">เหลือ 10 ชิ้น</h5>
                                </div>
                                <!-- <span class="badge text-bg-primary ">วัสดุสำนักงาน</span> -->
                            <div class="d-grid gap-2">
                                <?=Html::a('<i class="fa-solid fa-cart-plus"></i> เพิ่ม',['/inventory/store/add-to-cart','id' => $model->asset_item],['class' => 'add-cart btn btn-sm btn-primary shadow rounded-pill'])?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                </div>



    </div>
</div>