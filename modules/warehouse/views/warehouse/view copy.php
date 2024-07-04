<?php

/**
 * @var yii\web\View $this
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $model->warehouse_name;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card" style="height: 208px;">
            <div class="card-body">
                <h4 class="card-title">ปริมาณรวม</h4>
                <p class="card-text">Text</p>
            </div>
        </div>

    </div>
    <div class="col-lg-3 col-md-6 col-sm-12">

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">ปริมาณรวม</h4>
                <p class="card-text">Text</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">ปริมาณรวม</h4>
                <p class="card-text">Text</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-sm-12">

        <div class="card" style="height: 208px;">
            <div class="card-body">
            <div class="d-flex justify-content-between">
                    <h4 class="card-title">ปริมาณรวม</h4>
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
            </div>

          
        </div>
    </div>

    <div class="col-6 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h4 class="card-title">ปริมาณรวม</h4>
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

                <p class="card-text">Text</p>
                <div id="inventoryCharts"></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-sm-12">
        <div class="card" style="height: 308px;">
            <div class="card-body">
                <?= $this->render('order_show') ?>
            </div>
        </div>
    </div>
</div>


<?php
use yii\web\View;

$js = <<< JS


    var options = {
        plotOptions: {
              bar: { 
                horizontal: false,
                 columnWidth: "50%", 
                 endingShape: "rounded",
                 startingShape: 'rounded',
                 borderRadius: 10 
                },
            },
            series: [
              { name: "รับเข้า", data: [50, 45, 60, 70, 50, 45, 60, 70,30,40,23,19] },
              { name: "จ่ายออก", data: [-21, -54, -45, -35, -21, -54, -45, -35,-87,-40,-23,-34] },
            ],
            colors: ["#0966ad", "#EA5455"],
            chart: {
              type: "bar",
              height: 500,
              stacked: true,
              zoom: { enabled: true },
            },
            responsive: [
              {
                breakpoint: 280,
                options: { legend: { position: "top", offsetY: 0 } },
              },
            ],

            xaxis: {
              categories: [
                "ม.ค.",
                "ก.พ.",
                "มี.ค.",
                "เม.ย.",
                "พ.ย.",
                "มิ.ย.",
                "ก.ค.",
                "ส.ค.",
                "ก.ย.",
                "ต.ค.",
                "พ.ย.",
                "ธ.ค.",
              ],
            },
            legend: { position: "right", offsetY: 40 },
            fill: { opacity: 1 },
          };
          var chart = new ApexCharts(
            document.querySelector("#inventoryCharts"),
            options
          );
          chart.render();    
    JS;
$this->registerJS($js, View::POS_END);
?>