<?php
use yii\helpers\Html;
?>

<div class="alert alert-success" role="alert">
    <div class="d-flex justify-content-between">
        <!-- <h5 class="alert-heading"><i class="fa-solid fa-tag"></i> พ.ร.บ./ต่อภาษี</h5> -->
        <span class="fw-semibold">
            <i class="fa-solid fa-car-on fs-4"></i> ข้อมูลการต่อภาษี
        </span>
        <span>
                   วันที่ 11 มี.ค. 2568
                </span>
    </div>
    <hr>
    <div class="row">
    <div class="col-6">
    <span class="fw-semibold"><i class="fa-solid fa-user-injured"></i> พรบ.</span>

            <ul class="list-inline">
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">บริษัท</span> : วิริยะประกันภัย <?=isset($model->data_json['company']) ? $model->data_json['company'] : '-'?></li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">กรมธรรม์เลขที่</span>: 65304-E141799</li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">เริ่มต้น</span> 11 มี.ค. 2567 : 16:30</li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">สิ้นสุด</span> 11 มี.ค. 2568 : 16:30</li>
               
            </ul>
    </div>
 
    <div class="col-6">
    <span class="fw-semibold"><i class="fa-solid fa-car-burst"></i> ประกันภัย</span>

            <ul class="list-inline">
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">บริษัท</span> : วิริยะประกันภัย <?=isset($model->data_json['company']) ? $model->data_json['company'] : '-'?></li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">กรมธรรม์เลขที่</span>: 65304-E141799</li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">เริ่มต้น</span> 11 มี.ค. 2567 : 16:30</li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">สิ้นสุด</span> 11 มี.ค. 2568 : 16:30</li>
            </ul>
    </div>

    </div>


    <hr>
    <div class="d-flex justify-content-between">
        <ul class="list-inline mb-0">
            <li><i class="fa-regular fa-calendar-xmark fs-5"></i> <span class="">วันที่ครบกำหนดชำระ</span> :
                <span class="text-danger fw-semibold">
                    11 มี.ค. 2569
                </span>
            </li>

        </ul>

        <?=Html::a('<i class="fa-solid fa-gear"></i> ดำเนินการ',['/am/asset-detail/','id'=> $model->id,'name' => 'tax_car','title' => '<i class="fa-solid fa-car-on"></i> ข้อมูลการต่อภาษี'],['class' => 'btn btn-primary rounded-pill border border-white open-modal','data' => ['size' => 'modal-xl']])?>

    </div>


</div>