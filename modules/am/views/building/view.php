<?php
use yii\helpers\Html;
use app\components\AppHelper;

?>
<div class="card mb-3">
    <div class="row g-0">
        <div class="col-md-4">
            <div class="position-relative p-2 d-flex">
                <?php // Html::img('@web/images/imac.png',['class' => 'img-fluid rounded-start p-5']);?>

                <?= Html::img($model->showImg()['image'],['class' => 'avatar-profile object-fit-cover rounded m-auto','style' =>'max-width:100%;min-width: 320px;']) ?>

            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-none h-75">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-item-middle">
                        <div>

                            <h5 class="card-title mb-0"><?=isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-'?></h5>
                            <i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">เลขโฉนดที่ดิน</span>
                            <span class="text-danger"><?=isset($model->data_json['code']) ? $model->data_json['code'] : ''?><span>
                        </div>
                        <div>
                            <?=Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข',['update','id'=> $model->id],['class' => 'btn btn-warning rounded-pill shadow'])?>
                            <?= Html::a('<i class="fa-solid fa-trash"></i> ลบ', ['delete', 'id' => $model->id], ['class' => 'btn btn-secondary rounded-pill shadow delete-asset']) ?>
                        </div>

                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                            <ul class="list-inline">
                            <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ประเภท </span>
                                        <?=isset($model->data_json['asset_type_text']) ? $model->data_json['asset_type_text'] : '-'?>
                                    <?=$model->type_name?></li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">เลขระวาง</span> : <?=isset($model->data_json['tonnage_number']) ? $model->data_json['tonnage_number'] : ''?></li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">หมายเลขที่ดิน</span> <?=isset($model->data_json['land_number']) ? $model->data_json['land_number'] : ''?></li>
                            </ul>

                        </div>
                        <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">

                            <ul class="list-inline">
                            <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">เนื้อที่</span>
                                    <?=isset($model->data_json['land_size']) ? $model->data_json['land_size'].' ' : ''?>
                                    <?=isset($model->data_json['land_size_ngan']) ? $model->data_json['land_size_ngan'].' งาน' : ''?>
                                    <?=isset($model->data_json['land_size_tarangwa']) ? $model->data_json['land_size_tarangwa'].' เนื้อที่ตารางวา' : ''?> 
                                    
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">ผู้ถือครอง</span> <?=isset($model->data_json['land_owner']) ? $model->data_json['land_owner'].' ' : ''?>
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">ที่ตั้งบ้านเลขที่</span> :
                                    <?=isset($model->data_json['land_add']) ? $model->data_json['land_add'].' ' : ''?>
                                </li>
                            </ul>
                        </div>

                    </div>


                </div>
                <!-- End Card body -->

            </div>
            <!-- End card -->

        </div>
    </div>
</div>