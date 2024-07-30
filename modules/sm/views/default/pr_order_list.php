<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

?>
<!-- แสดงรายการคำขอซื้อ -->
<div class="card" style="height: 435px;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p><i class="fa-solid fa-user-check text-black-50"></i> เห็นชอบ</p>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-solid fa-circle-info text-primary me-2"></i> เพิ่มเติม', ['/sm/order'], ['class' => 'dropdown-item']) ?>
                        </div>
                    </div>
                </div>
                <table class="table  m-b-0 transcations mt-2">
                    <tbody>
                        <tr>
                            <td style="width:20px;">
                                <div class="main-img-user avatar-sm">
                                    <?= Html::img('@web/img/patjwat2.png', ['class' => 'avatar avatar-sm bg-primary text-white']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-middle">
                                    <div class="d-inline-block">
                                        <h6 class="mb-1">ปัจวัฒน์ ศรีบุญเรือง</h6>
                                        <p class="mb-0 fs-13 text-muted">ขอซื้อคอมพิวเตอร์</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-block">
                                    <h6 class="mb-2 fs-15 fw-semibold">$26,000 บาท</h6>
                                    <p class="mb-0 fs-11 text-muted">12 ม.ค. 2567</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="main-img-user avatar-sm">
                                    <?= Html::img('@web/img/patjwat2.png', ['class' => 'avatar avatar-sm bg-primary text-white']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-middle">
                                    <div class="d-inline-block">
                                        <h6 class="mb-1">ปัจวัฒน์ ศรีบุญเรือง</h6>
                                        <p class="mb-0 fs-13 text-muted">ขอซื้อ HDD 100TB</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-block">
                                    <h6 class="mb-2 fs-15 fw-semibold">$2,000.00 บาท</h6>
                                    <p class="mb-0 fs-11 text-muted">23 Jan 2020</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="main-img-user avatar-sm">
                                    <?= Html::img('@web/img/patjwat2.png', ['class' => 'avatar avatar-sm bg-primary text-white']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-middle">
                                    <div class="d-inline-block">
                                        <h6 class="mb-1">ปัจวัฒน์ ศรีบุญเรือง</h6>
                                        <p class="mb-0 fs-13 text-muted">ขอจ้างเหมาติดตั้ง wifi</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-block">
                                    <h6 class="mb-2 fs-15 fw-semibold">$7,000 บาท</h6>
                                    <p class="mb-0 fs-11 text-muted">4 Apr 2020</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="main-img-user avatar-sm">
                                    <?= Html::img('@web/img/patjwat2.png', ['class' => 'avatar avatar-sm bg-primary text-white']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-middle">
                                    <div class="d-inline-block">
                                        <h6 class="mb-1">ปัจวัฒน์ ศรีบุญเรือง</h6>
                                        <p class="mb-0 fs-13 text-muted">Milestone2</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-block">
                                    <h6 class="mb-2 fs-15 fw-semibold">$37.285</h6>
                                    <p class="mb-0 fs-11 text-muted">4 Apr 2020</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
<?php

$prOrderUrl = Url::to(['/purchase/pr-order/list']);
$js = <<< JS

function loadPrOrder(){
    $.ajax({
        type: "get",
        url: "$prOrderUrl",
        data: "data",
        dataType: "dataType",
        success: function (response) {
            
        }
    });
}
JS;
$this->registerJS($js,View::POS_END);
?>