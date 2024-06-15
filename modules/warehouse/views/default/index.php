<?php

/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ระบบคลัง';
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('./menu') ?>
<?php $this->endBlock(); ?>


<body>

        <div class="row">
              <div class="col-8">

                
<div class="row">

<div class="col-lg-4 col-md-4 col-sm-12 col-sx-12">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <a href="<?= Url::to(['/hr/employees']) ?>">
                        <span class="text-muted text-uppercase fs-6">เบิกวัสดุ</span>
                    </a>
                    <h2 class="text-muted text-uppercase fs-4">694 เรื่อง</h2>
                </div>
                <div class="text-center" style="position: relative;">
                    <div id="t-rev" style="min-height: 45px;">
                        <div id="apexchartsdlqwjkgl"
                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                            style="width: 90px; height: 45px;">
                            <i class="fa-solid fa fa-recycle fs-1"></i>
                            <div class="apexcharts-legend"></div>
                            
                        </div>
                    </div>
                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 91px; height: 70px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End-col -->

<div class="col-lg-4 col-md-12 col-sm-12 col-sx-12">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                <a href="#">
                        <span class="text-muted text-uppercase fs-6">ผ่านการตรวจสอบ</span>
                    </a>
                    <h2 class="text-muted text-uppercase fs-4">50 รายการ</h2>
                </div>
                
                <div class="text-center" style="position: relative;">
                    <div id="t-rev" style="min-height: 45px;">
                        <div id="apexchartsdlqwjkgl"
                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                            style="width: 90px; height: 45px;">
                            <i class="fa-solid fa-cash-register fs-1"></i>
                            <div class="apexcharts-legend"></div>
                            
                        </div>
                    </div>
                   
                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 91px; height: 70px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End-col -->

<!-- End-col -->
<div class="col-lg-4 col-md-12 col-sm-12 col-sx-12">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <span class="text-muted text-uppercase fs-6">อนุมัติ</span>
                    <h2 class="text-muted text-uppercase fs-4">316 รายการ</h2>
                </div>
                <div class="text-center" style="position: relative;">
                    <div id="t-rev" style="min-height: 45px;">
                        <div id="apexchartsdlqwjkgl"
                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                            style="width: 90px; height: 45px;">
                            <i class="fa-solid fa-building fs-1"></i>
                            <div class="apexcharts-legend"></div>
                            
                        </div>
                    </div>
                  
                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 91px; height: 70px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End-col -->

</div>               
                          <div class="card">
                              <div class="card-body">
                              จำนวนมูลค่าการเบิก และจ่ายวัสดุ
                                <div id="sales_charts"></div>
                              </div>
                          </div>
                          <!-- <div class="card">
                              <div class="card-body">
                              มูลค่าประเภทงบการเงิน (ย้อนหลัง 10 ปี)
                              <div id="line-stack" style="width:100%;height:550px;"></div>
                              </div>
                          </div> -->
                </div>
             
                <div class="col-4">
                <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <span class="text-muted text-uppercase fs-6">จำนวนวัสดุน้อยกว่ากำหนด</span>
                    <h2 class="text-muted text-uppercase fs-4">85 รายการ</h2>
                </div>
                <div class="text-center" style="position: relative;">
                    <div id="t-rev" style="min-height: 45px;">
                        <div id="apexchartsdlqwjkgl"
                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                            style="width: 90px; height: 45px;">
                            <i class="fa-solid fa-building fs-1"></i>
                            <div class="apexcharts-legend"></div>
                            
                        </div>
                    </div>
                  
                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 91px; height: 70px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <span class="text-muted text-uppercase fs-6">จำนวนวัสดุมากว่ากำหนด</span>
                    <h2 class="text-muted text-uppercase fs-4">54 รายการ</h2>
                </div>
                <div class="text-center" style="position: relative;">
                    <div id="t-rev" style="min-height: 45px;">
                        <div id="apexchartsdlqwjkgl"
                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                            style="width: 90px; height: 45px;">
                            <i class="fa-solid fa-building fs-1"></i>
                            <div class="apexcharts-legend"></div>
                            
                        </div>
                    </div>
                  
                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 91px; height: 70px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
                        <div class="card">
                            <div class="card-body">
                                <div class="list-group border-0">
                                      <a href="<?= Url::to(['/warehouse/whcheck']) ?>" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                                          <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i class="fa-solid fa-server avatar-title text-primary"></i> </div>
                                          <div class="d-flex gap-2 w-100 justify-content-between">
                                              <div>
                                                  <h6 class="mb-0 text-primary">ตรวจรับ</h6>
                                                  <p class="mb-0 opacity-75 fw-light">100 รายการ</p>
                                              </div>
                                          </div>
                                      </a>
                                      <a href="<?= Url::to(['/warehouse/whsup']) ?>" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                                          <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i class="fa-solid fa-server avatar-title text-primary"></i> </div>
                                          <div class="d-flex gap-2 w-100 justify-content-between">
                                              <div>
                                                  <h6 class="mb-0 text-primary">คลังหลัก</h6>
                                                  <p class="mb-0 opacity-75 fw-light">6 คลัง</p>
                                              </div>
                                          </div>
                                      </a>
                                      <a href="<?= Url::to(['/warehouse/whdep']) ?>" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                                          <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i class="fa-solid fa-server avatar-title text-primary"></i> </div>
                                          <div class="d-flex gap-2 w-100 justify-content-between">
                                              <div>
                                                  <h6 class="mb-0 text-primary">คลังหน่วยงาน</h6>
                                                  <p class="mb-0 opacity-75 fw-light">50 คลัง</p>
                                              </div>
                                          </div>
                                      </a>
                                      <a href="<?= Url::to(['/warehouse/whwithdrow']) ?>" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                                          <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i class="fa-solid fa-server avatar-title text-primary"></i> </div>
                                          <div class="d-flex gap-2 w-100 justify-content-between">
                                              <div>
                                                  <h6 class="mb-0 text-primary">เบิกวัสดุ</h6>
                                                  <p class="mb-0 opacity-75 fw-light">180 รายการ</p>
                                              </div>
                                          </div>
                                      </a>
                                   
                                  </div>
                                </div>
                            </div>
                        </div>
                  </div>
              </div>
           
</body>

<?php
use yii\web\View;

$js = <<< JS
    var options = {
            series: [
              { name: "Sales", data: [50, 45, 60, 70, 50, 45, 60, 70] },
              { name: "Purchase", data: [-21, -54, -45, -35, -21, -54, -45, -35] },
            ],
            colors: ["#28C76F", "#EA5455"],
            chart: {
              type: "bar",
              height: 300,
              stacked: true,
              zoom: { enabled: true },
            },
            responsive: [
              {
                breakpoint: 280,
                options: { legend: { position: "bottom", offsetY: 0 } },
              },
            ],
            plotOptions: {
              bar: { horizontal: false, columnWidth: "20%", endingShape: "rounded" },
            },
            xaxis: {
              categories: [
                " Jan ",
                "feb",
                "march",
                "april",
                "may",
                "june",
                "july",
                "auguest",
              ],
            },
            legend: { position: "right", offsetY: 40 },
            fill: { opacity: 1 },
          };
          var chart = new ApexCharts(
            document.querySelector("#sales_charts"),
            options
          );
          chart.render();
        


          
    JS;
$this->registerJS($js, View::POS_END);
?>

