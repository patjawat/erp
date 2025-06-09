<?php
use yii\helpers\Html;

?>
<div class="card mb-3">
    <div class="row g-0">
        <div class="col-md-4">
            <div class="position-relative p-2">
                <?php // Html::img('@web/images/imac.png',['class' => 'img-fluid rounded-start p-5']);?>
                <?= Html::img($model->showImg(),['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;']) ?>

            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-none h-75">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-item-middle">
                        <h5 class="card-title"><?=$model->data_json['asset_name']?></h5>
                        <?=Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข',['update','id'=> $model->id],['class' => 'btn btn-warning rounded-pill'])?>
                    </div>
                    <div>
                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                    <?=$model->code?>
                        <!-- <i class="fa-solid fa-sack-dollar"></i> ราคาต่อหน่วย <span
                        class="fw-semibold"><?=isset($model->price) ? number_format($model->price,2) : ''?> </span> -->
                        
                        <!-- บาท
                        | <span class="badge rounded-pill bg-secondary text-white">มูลค่าคงเหลือ 10,000 </span> -->
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <ul class="list-inline">

                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">รหัส</span> <span
                                        class="text-danger"><?=$model->code?><span></li>
                                <?php if(isset($model->data_json['fsn_old']) && $model->data_json['fsn_old'] != ''):?>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">รหัสเดิม</span> <span
                                        class="text-danger"><?=$model->data_json['fsn_old']?><span></li>
                                <?php endif;?>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ประเภท </span>

                                    <?php 
                                // echo "<pre>";
                                // print_r($model->data_json['vendor']);
                                // echo "</pre>";
                                // if(isset($model->data_json['item']['data_json']['asset_type'])){
                                //         echo $model->data_json['item']['data_json']['asset_type']['title'];
                                // }
                                ?>
                                    <?=$model->type_name?></li>
                                <!-- <li><i class="bi bi-check2-circle text-primary"></i> <span class="fw-semibold">ลักษณะ/คุณสมบัติ</span></li> -->
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">S/N</span> <?=$model->serial_number?></li>
                            </ul>


                        </div>
                        <div class="col-6">
                            <ul class="list-inline">
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ผู้ขาย/ผู้จำหน่าย/ผู้บริจาค</span> :
                                    <?=$model->vendor_name?></li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ประเภทเงิน</span> : <?=$model->budget_type?> </li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">วันเดือนปีทีซื้อ</span> : 01/01/2566</li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">วิธีได้มา</span> <?=$model->method_get?></li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">การจัดซื้อ</span> <?=$model->purchase_text?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <span class="fw-semibold">ลักษณะ/คุณสมบัติ</span>
                            <p class="card-text"><?=$model->asset_option?></p>


                        </div>
                        <div class="col-6">
                            <span class="fw-semibold">หน่วยนับ</span>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">ขนาด</th>
                                        <th scope="col">ความจุ/ปริมาณ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="">
                                        <td scope="row">กล่อง</td>
                                        <td>1</td>
                                    </tr>
                                    <tr class="">
                                        <td scope="row">Item</td>
                                        <td>Item</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- End Card body -->

            </div>
            <!-- End card -->
            <div class="d-flex justify-content-between p-3">
                <span><i class="fa-regular fa-clock "></i> อายุการใช้งาน : 12 ปี 3 เดือน 4 วัน</span>

                <span><i class="fa-solid fa-hourglass-end"></i> อัตราค่าเสื่อม <?=$model->data_json['depreciation']?></span>

                <div class="d-flex">
                    <img class="avatar rounded-circle" src="/img/patjwat2.png" width="30" alt="">
                    <div class="flex-grow-1 overflow-hidden">
                        <h6 class="mb-0 pr-4 text-truncate">
                            <a href="/hr/workgroup/view?id=4670" class="text-dark">ผู้รับผิดชอบ
                            </a>
                        </h6>
                        <p class="text-muted mb-0">นายปัจวัฒน์ ศรีบุญเรือง</p>
                    </div>
                </div>
                </span>
            </div>
        </div>
    </div>
</div>