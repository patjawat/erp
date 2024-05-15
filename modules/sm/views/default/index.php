<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use yii\helpers\Url;


$this->title = 'บริหารพัสดุ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-8">
      <div class="row">
        <div class="col-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted text-uppercase fs-6">รายการขอซื้อ/ขอจ้าง</span>
                            <h2 class="text-muted text-uppercase fs-4">5 รายการ</h2>
                        </div>
                        <div class="text-center" style="position: relative;">
                            <div id="t-rev" style="min-height: 45px;">
                                <div>
                                    <i class="fa-solid fa fa-list-ul fs-1"></i>
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
        <div class="col-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted text-uppercase fs-6">กำลังดำเนินการ</span>
                            <h2 class="text-muted text-uppercase fs-4">4 รายการ</h2>
                        </div>
                        <div class="text-center" style="position: relative;">
                            <div>
                                <i class="fa-solid fa fa-undo fs-1"></i>
                                <div class="apexcharts-legend"></div>

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
        <div class="col-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <a href="#">
                                <span class="text-muted text-uppercase fs-6">ดำเนินการเรียบร้อย</span>
                            </a>
                            <h2 class="text-muted text-uppercase fs-4">1 รายการ</h2>
                        </div>

                        <div class="text-center" style="position: relative;">
                            <div>
                                <i class="fa-solid fa-check-square fs-1"></i>
                                <div class="apexcharts-legend"></div>
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


    </div>
    <div class="card">
        <div class="card-body">
            มูลค่าการจัดซื้อจัดจ้าง (ย้อนหลัง 10 ปี)
            <div id="line-chart-container" style="width:100%;height:340px;"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Title</h4>
                    <p class="card-text">Text</p>
                </div>
            </div>

        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Title</h4>
                    <p class="card-text">Text</p>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4>วัสดุที่สี่งซื้อมากที่สุด</h4>

                    <div class="table-responsive">
                        <table class="table table-primary">
                            <thead>
                                <tr>
                                    <th scope="col">Column 1</th>
                                    <th scope="col">Column 2</th>
                                    <th scope="col">Column 3</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="">
                                    <td scope="row">R1C1</td>
                                    <td>R1C2</td>
                                    <td>R1C3</td>
                                </tr>
                                <tr class="">
                                    <td scope="row">Item</td>
                                    <td>Item</td>
                                    <td>Item</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>



    <div class="card rounded-4 border-0 w-100">
        <?php // $this->render('donutChart1')?>
    </div>



</div>
<div class="col-4">

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h4 class="card-title">รายการขอซื้อขอจ้าง</h4>
                <?=Html::a('ดูทั้งหมด',['/sm/order'],['class' => 'btn btn-primary'])?>
            </div>
            <table class="table  m-b-0 transcations mt-2">
                <tbody>
                    <tr>
                        <td style="width:20px;">
                            <div class="main-img-user avatar-md">
                                <?=Html::img('@web/img/patjwat2.png',['class' => 'avatar avatar-md bg-primary text-white'])?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-middle ms-3">
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
                            <div class="main-img-user avatar-md">
                                <?=Html::img('@web/img/patjwat2.png',['class' => 'avatar avatar-md bg-primary text-white'])?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-middle ms-3">
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
                            <div class="main-img-user avatar-md">
                                <?=Html::img('@web/img/patjwat2.png',['class' => 'avatar avatar-md bg-primary text-white'])?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-middle ms-3">
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
                            <div class="main-img-user avatar-md">
                                <?=Html::img('@web/img/patjwat2.png',['class' => 'avatar avatar-md bg-primary text-white'])?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-middle ms-3">
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
    <?=$this->render('radialBar1')?>
    <?=$this->render('radialBar2')?>
    <?=$this->render('radialBar3')?>
    <?=$this->render('radialBar4')?>

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
                $this->registerJS($js,View::POS_END);
?>