<?php
use yii\helpers\Html;
use app\components\AppHelper;

?>
<div class="card mb-3">
    <div class="row g-0">
        <div class="col-md-4">
            <div class="position-relative p-2 d-flex">
                <?php // Html::img('@web/images/imac.png',['class' => 'img-fluid rounded-start p-5']);?>

                <?= Html::img($model->showImg(),['class' => 'avatar-profile object-fit-cover rounded m-auto','style' =>'max-width:100%;min-width: 320px;']) ?>

            </div>
        </div>
        <div class="col-md-8">

        
            <div class="card border-0 shadow-none h-75">
                <div class="card-body">
                    
                
                    <div class="d-flex justify-content-between align-item-middle">
                        <div>

                            <h5 class="card-title mb-0">
                                <?=isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-'?></h5>
                            <i class="bi bi-check2-circle text-primary fs-5"></i>
                            <span class="text-danger"><?=$model->code?><span>
                        </div>
                        <div>
                            <?=Html::a('<i class="fa-regular fa-money-bill-1"></i> ค่าเสื่อม',['depreciation','id'=> $model->id],['class' => 'open-modal btn btn-primary rounded-pill shadow','data' => ['size' => 'modal-lg']])?>
                            <?=Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข',['update','id'=> $model->id],['class' => 'btn btn-warning rounded-pill shadow'])?>
                        </div>

                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                            <ul class="list-inline">
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ประเภท : สิ่งก่อสร้าง </span>
                                    <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">มูลค่า</span>
                                    <span class="text-white bg-primary badge rounded-pill fs-6">
                                        <?=number_format(isset($model->price) ?  $model->price : 0,2)?> 
                                    </span>
                                        บาท</li>

                                    <?=$model->type_name?></li>
                                <li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">แบบเลขที่</span> <?=isset($model->data_json['building_number']) ? $model->data_json['building_number'] : ''?>
                                </li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i> 
                                   
                                    <span class="fw-semibold">วันที่สร้าง</span> : <?=Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium') ?>
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i> 
                                    <span class="fw-semibold">วันที่แล้วเสร็จ</span> : <?=isset($model->data_json['end_date']) ? $model->data_json['end_date'] : ''?>
                                </li>
                                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">สัญญาเลขที่</span> <?=isset($model->data_json['building_contract_number']) ? $model->data_json['building_contract_number'] : ''?></li>
                                       
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">ทะเบียนอาคารสิ่งปลูกสร้าง </span> <?=isset($model->data_json['building_regis']) ? $model->data_json['building_regis'] : ''?>
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">วิศวกร</span> :
                                    <?=isset($model->data_json['engineer_name']) ? $model->data_json['engineer_name'] : '-'?>
                                </li>

                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">วิธีได้มา</span> :
                                    <?=$model->method_get?>
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">การจัดซื้อ</span> :
                                    <?=$model->purchase_text?>
                                </li>
                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">ประเภทเงิน</span> :
                                    <?=$model->budget_type?>
                                </li>

                                <li>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                    <span class="fw-semibold">สถานะ</span> :
                                    <?=$model->statusName()?>
                                </li>
                               
                                    <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                        class="fw-semibold">ลักษณะ/คุณสมบัติ</span> :
                                        <p>
                                <?=isset($model->data_json['asset_option']) ? $model->data_json['asset_option'] : ''?>
                            </p>
                                </li>

                            </ul>



                          


                        </div>
                        <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">


<div class="alert alert-primary bprder-0 d-flex justify-content-between" role="alert">
                    <span><i class="fa-solid fa-hourglass-end"></i> อัตราค่าเสื่อม
                        <?=isset($model->data_json['depreciation']) ? $model->data_json['depreciation'] : ''?>
                        ต่อปี</span>
                    <span><i class="fa-regular fa-clock"></i> อายุการใช้งาน
                        <?=isset($model->data_json['service_life']) ? $model->data_json['service_life'] : ''?></span>
                </div>

                <?php  if(isset($model->Retire()['progress'])):?>
                <div class="progress progress-sm mt-3 w-100">
                    <div class="progress-bar" role="progressbar"
                        <?= "style='width:". $model->Retire()['progress'] ."%; background-color:".$model->Retire()['color'].";  '" ?>
                        aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-2" style="width:100%;">
                    <div>
                        <i class="fa-regular fa-clock"></i> <span class="fw-semibold">เหลือเวลา</span> :
                        <?= AppHelper::CountDown($model->Retire()['date'])[0] != '-' ? AppHelper::CountDown($model->Retire()['date']) : "หมดอายุการใช้งาน" ?>
                    </div>
                    |
                    <div>
                        <i class="fa-solid fa-calendar-xmark"></i> <span class="fw-semibold">หมดอายุ</span> <span
                            class="text-danger"><?=$model->Retire()['date'];?></span>
                    </div>
                </div>
                <?php  endif;?>



                        <div
                                class="d-flex justify-content-between total font-weight-bold mt-4 bg-secondary-subtle rounded p-2">
                                <?=$model->getOwner()?>

                            </div>

                        </div>

                    </div>


                </div>
                <!-- End Card body -->

            </div>
            <!-- End card -->


            <div class="col-12">
    
            </div>
            
        </div>


    </div>
</div>




