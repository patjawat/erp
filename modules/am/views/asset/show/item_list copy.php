<?php
use yii\helpers\Html;
?>
<style>
    .product {
    height: 10rem;
    width: 12rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    margin-right: 1rem;
    font-weight: 600;
    font-size: 1.15rem;
}
</style>


<div class="d-flex align-items-center">
    <div class="flex-shrink-0">
        <?= Html::a(Html::img($model->showImg(),['class' => 'product rounded']), ['view','id' => $model->id],['class' => '', ]) ?>
    </div>
    <div class="flex-grow-1 ms-3">
        <div>
            <?= html::a($model->AssetitemName(),['/am/asset/view','id' => $model->id],['class' => 'fs-5']);?>
        </div>
            <ul class="list-inline">

                <li>  <i class="bi bi-check2-circle text-primary me-1 fs-5"></i> <span class="text-danger"><?=$model->code?></li>
          
                                <li class="text-truncate">
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">ประเภท</span> :
                                    <?=isset($model->data_json['asset_type_text']) ? $model->data_json['asset_type_text'] : '-'?>
                                </li>
                             
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i> 
                                    <span class="fw-semibold">มูลค่า</span> :
                                    <span class="text-white bg-primary badge rounded-pill fs-6 shadow fw-semibold"><?=isset($model->price) ? number_format($model->price,2) : ''?></span> บาท
                            </li>
                <!-- <li> <i class="bi bi-check2-circle text-primary me-1 fs-5"></i><?php // $model->AssetTypeName();?></li> -->
               
            </ul>

    </div>
</div>