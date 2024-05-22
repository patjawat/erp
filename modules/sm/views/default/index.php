<?php

/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'บริหารพัสดุ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-9">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p>ภาพรวมการสั่งซื้อ</p>
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
                <?= $this->render('order_summary') ?>
                <?= $this->render('order_chart_column') ?>
            </div>
        </div>

    </div>
    <div class="col-3">
        <?= $this->render('budget_balanced') ?>
    </div>
</div>
<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <p><i class="fa-solid fa-circle-info"></i> อันดับวัสดุจัดซื้อมากที่สุด</p>
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


                <div class="testimonial-group overflow-auto">
                    <div class="row flex-nowrap">
                        <?php for ($i = 0; $i < 12; $i++): ?>
                        <div class="col-3">
                            <div class="card">
                                <img class="card-img-top"
                                    src="https://angular.spruko.com/vexel/preview/assets/images/shop/1.png"
                                    alt="Title" />
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex flex-column">
                                            <span>เก้าอี้นั่ง</span>
                                            <span class="text-light">วัสดุสำนักงาน</span>
                                        </div>
                                        <span class="badge text-bg-primary ">วัสดุสำนักงาน</span>
                                    </div>
                                    <span>คงเหลือ 10 ชิ้น</span>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>

                </div>



            </div>
        </div>
    </div>
    <div class="col-3">
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
    </div>
    <div class="col-3">
        <div class="card" style="height: 435px;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p><i class="bi bi-rocket text-black-50"></i> อนุมัติแล้ว</p>
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
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p><i class="bi bi-plus-circle-fill text-black-50"></i> รายการขอซื้อขอจ้าง</p>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="bi bi-plus-circle-fill  text-primary me-2"></i> สร้างใบขอซื้อ-ขอจ้าง', ['/sm/order/create'], ['class' => 'dropdown-item']) ?>
                            <?= Html::a('<i class="fa-solid fa-circle-info text-primary me-2"></i> เพิ่มเติม', ['/sm/order'], ['class' => 'dropdown-item']) ?>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-primary">
                        <thead>
                            <tr>
                                <th scope="col">เลขที่ใบขอ</th>
                                <th scope="col">วันที่</th>
                                <th scope="col">ผู้ขอ/เรื่อง</th>
                                <th scope="col">หน่วยงาน</th>
                                <th scope="col">สถานะ</th>
                                <th scope="col">มูลค่า</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="align-middle">
                                <td>PO-560001</td>
                                <td>
                                    <p class="m-0">
                                        13 เม.ย. 2567
                                    </p>
                                    <span class="fs-13 text-muted-">13:35:00</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="main-img-user avatar-sm">
                                                <?= Html::img('@web/img/patjwat2.png', ['class' => 'avatar avatar-sm bg-primary text-white']) ?>
                                            </div>
                                        </div>
                                        <div class="d-flex align-middle">
                                            <div class="d-inline-block">
                                                <span class="mb-1">ปัจวัฒน์ ศรีบุญเรือง</span>
                                                <p class="mb-0 fs-13 text-muted">ขอซื้อวัสดุฑ์คอมพิวเตอร์</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>ศูนย์คอมพิวเตอร์</td>
                                <td><label
                                        class="badge rounded-pill text-primary-emphasis bg-warning-subtle p-2 fs-6 text-truncate fw-semibold"><i
                                            class="fa-regular fa-clock"></i> รออนุมัติ</label></td>
                                <td>3,000</td>
                            </tr>
                            <tr class="align-middle">
                                <td>PO-560001</td>
                                <td>
                                    <p class="m-0">
                                        13 เม.ย. 2567
                                    </p>
                                    <span class="fs-13 text-muted-">13:35:00</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="main-img-user avatar-sm">
                                                <?= Html::img('@web/img/patjwat2.png', ['class' => 'avatar avatar-sm bg-primary text-white']) ?>
                                            </div>
                                        </div>
                                        <div class="d-flex align-middle">
                                            <div class="d-inline-block">
                                                <span class="mb-1">ปัจวัฒน์ ศรีบุญเรือง</span>
                                                <p class="mb-0 fs-13 text-muted">ขอซื้อวัสดุฑ์คอมพิวเตอร์</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>ศูนย์คอมพิวเตอร์</td>
                                <td><label
                                        class="badge rounded-pill text-primary-emphasis bg-warning-subtle p-2 fs-6 text-truncate fw-semibold"><i
                                            class="fa-regular fa-clock"></i> รออนุมัติ</label></td>
                                <td>3,000</td>
                            </tr>
                            <tr class="align-middle">
                                <td>PO-560001</td>
                                <td>
                                    <p class="m-0">
                                        13 เม.ย. 2567
                                    </p>
                                    <span class="fs-13 text-muted-">13:35:00</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="main-img-user avatar-sm">
                                                <?= Html::img('@web/img/patjwat2.png', ['class' => 'avatar avatar-sm bg-primary text-white']) ?>
                                            </div>
                                        </div>
                                        <div class="d-flex align-middle">
                                            <div class="d-inline-block">
                                                <span class="mb-1">ปัจวัฒน์ ศรีบุญเรือง</span>
                                                <p class="mb-0 fs-13 text-muted">ขอซื้อวัสดุฑ์คอมพิวเตอร์</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>ศูนย์คอมพิวเตอร์</td>
                                <td><label
                                        class="badge rounded-pill text-primary-emphasis bg-warning-subtle p-2 fs-6 text-truncate fw-semibold"><i
                                            class="fa-regular fa-clock"></i> รออนุมัติ</label></td>
                                <td>3,000</td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>




<?php
use yii\web\View;

$js = <<< JS
           
    var options = {
              series: [{
              name: 'มูลคาการจัดซื้อจัดจ้างตามแผน',
              data: [2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2]
            }, {
              name: 'มูลคาการจัดซื้อจัดจ้างทั้งหมด',
              data: [2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2]
            }],
              chart: {
              type: 'bar',
              height: 350,
              fontFamily: 'kanit,sans-serif',
                  
            },
            plotOptions: {
              bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
              },
            },
            dataLabels: {
              enabled: false
            },
            stroke: {
              show: true,
              width: 2,
              colors: ['transparent']
            },
            xaxis: {
              categories: ['ปี 2563','ปี 2564','ปี 2565','ปี 2566','ปี 2567','ปี 2568','ปี 2569','ปี 2570'],
            },
            yaxis: {
              title: {
                text: 'มูลค่า (ล้านบาท)'
              }
            },
            fill: {
              opacity: 1
            },
            tooltip: {
              y: {
                formatter: function (val) {
                  return  val + " ล้านบาท"
                }
              }
            }
            };

            var chart = new ApexCharts(document.querySelector("#line-chart-container"), options);
            chart.render();



    JS;
$this->registerJS($js, View::POS_END);
?>