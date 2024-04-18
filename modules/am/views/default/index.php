<?php
/** @var yii\web\View $this */
use app\models\Categorise;
use app\modules\am\models\Asset;
use yii\db\Expression;
use yii\helpers\Json;
use yii\helpers\Url;

$this->title = 'บริหารทรัพย์สิน';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock();?>
<?php $this->beginBlock('sub-title');?>
<?php $this->endBlock();?><?php $this->beginBlock('sub-title');?>
Dashboard
<?php $this->endBlock();?>

<?php $this->beginBlock('page-action');?>
<?=$this->render('menu')?>
<?php $this->endBlock();?>

<?php
$querys = Yii::$app->db->createCommand("SELECT data_json->'$.asset_name',on_year FROM `asset` WHERE asset_group = 3
GROUP BY on_year
ORDER BY on_year DESC LIMIT 10")->queryAll();
?>




<div class="row">
    <div class="col-8">


        <div class="row">

            <div class="col-lg-4 col-md-4 col-sm-12 col-sx-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <a href="<?=Url::to(['/am/asset'])?>">
                                    <span class="text-muted text-uppercase fs-6">มูลค่าทรัพย์สินทั้งหมด</span>
                                </a>
                                <h2 class="text-muted text-uppercase fs-5"><?=number_format($totalPrice, 2)?> บาท</h2>
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
                                    <span class="text-muted text-uppercase fs-6">มูลค่าครุภัณฑ์</span>
                                </a>
                                <h2 class="text-muted text-uppercase fs-5"><?=number_format($totalPriceGroup3, 2)?> บาท
                                </h2>
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
                                <span class="text-muted text-uppercase fs-6">มูลค่าสิ่งก่อสร้าง</span>
                                <h2 class="text-muted text-uppercase fs-5"><?=number_format($totalPriceGroup2, 2)?> บาท
                                </h2>
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
                มูลค่าครุภัณฑ์ (ย้อนหลัง 10 ปี)
                <div id="line-chart" style="width:100%;height:550px;"></div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                มูลค่าประเภทงบการเงิน (ย้อนหลัง 10 ปี)
                <div id="line-stack" style="width:100%;height:550px;"></div>
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-body">
                ร้อยละของรายการครุภัณฑ์ และสิ่งก่อสร้าง (ย้อนหลัง 5 ปี)
                <div id="pie-chart-container" style="width:100%;height:550px;"></div>
            </div>
        </div>
    </div>
</div>
</div>


<?php
$seriesData = [];
$data = [];
$dataLabel = [];

?>
<?php
krsort($querys);
foreach ($querys as $item1) {
    $data[] = $item1['on_year'];

}

foreach (Categorise::find()->where(['name' => 'budget_type'])->all() as $item2) {
    $value = [];
    foreach ($querys as $item1) {
        $price = Asset::find()
            ->where(['on_year' => $item1['on_year']])
            ->andWhere(new Expression('JSON_EXTRACT(asset.data_json, "$.budget_type") = "' . $item2->code . '"'))
            ->sum('price');
        $p = isset($price) ? $price : 0;
        $value[] = $p;
    }

    $dataLabel[] = $item2->title;
    $seriesData[] = [
        'type' => 'line',
        'stack' => 'Total',
        'name' => $item2->title,
        'data' => $value,
    ];
}
?>


<?php
// echo "<pre>";
// print_r($seriesData);
// echo "</pre>";
// $a = ;
krsort($priceLastOfYear);
$getCategories = [];
$getTotal = [];
foreach ($priceLastOfYear as $item) {
    $getCategories[] = $item['on_year'];
    $getTotal[] = (float) $item['total'];

}

?>

<?php

$categories = Json::encode($getCategories);
$total = Json::encode($getTotal);
$series = Json::encode($seriesData);
$yData = Json::encode($data);
$getDataLabel = Json::encode($dataLabel);
// $dataLabel = Json::encode($data['name']);

use yii\web\View;
$js = <<< JS

var options = {
  labels: ['ครุภัณฑ์', 'สิ่งก่อสร้าง'],
  series: [$totalGroup3, $totalGroup2],
  chart: {
  type: 'donut',
},
responsive: [{
  breakpoint: 480,
  options: {
    chart: {
      width: 200
    },
    legend: {
      position: 'bottom'
    }
  }
}]
};

var chart = new ApexCharts(document.querySelector("#pie-chart-container"), options);
chart.render();
//=====================================
JS;
$this->registerJS($js, View::POS_END);

$js = <<< JS
 var optionsline = {
          series: [{
          name: 'มูลค่า',
          data: $total
        }],
          annotations: {
          points: [{
            x: 'Bananas',
            seriesIndex: 0,
            label: {
              borderColor: '#775DD0',
              offsetY: 0,
              style: {
                color: '#fff',
                background: '#775DD0',
              },
              text: 'Bananas are good',
            }
          }]
        },
        chart: {
          height: 350,
          type: 'bar',
        },
        plotOptions: {
          bar: {
            borderRadius: 10,
            columnWidth: '50%',
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          width: 2
        },

        grid: {
          row: {
            colors: ['#fff', '#f2f2f2']
          }
        },
        xaxis: {
          labels: {
            rotate: -45
          },
          categories: $categories,
          tickPlacement: 'on'
        },
        yaxis: {
          title: {
            text: 'มูลค่า (บาท)',
          },
        },
        fill: {
          type: 'gradient',
          gradient: {
            shade: 'light',
            type: "horizontal",
            shadeIntensity: 0.25,
            gradientToColors: undefined,
            inverseColors: true,
            opacityFrom: 0.85,
            opacityTo: 0.85,
            stops: [50, 0, 100]
          },
        }
        };

        var chart01 = new ApexCharts(document.querySelector("#line-chart"),optionsline);
        chart01.render();



//=====================================

var dom = document.getElementById('line-stack');
var myChart = echarts.init(dom, null, {
  renderer: 'canvas',
  useDirtyRect: false
});
var app = {};

var option;

option = {
  tooltip: {
    trigger: 'axis'
  },
  legend: {
    data: $getDataLabel
  },
  grid: {
    left: '3%',
    right: '4%',
    bottom: '3%',
    containLabel: true
  },
  toolbox: {
    feature: {
      saveAsImage: {}
    }
  },
  xAxis: {
    type: 'category',
    boundaryGap: false,
    data: $yData
  },
  yAxis: {
    type: 'value'
  },
  series: $series
};

if (option && typeof option === 'object') {
  myChart.setOption(option);
}
window.addEventListener('resize', myChart.resize);


JS;
$this->registerJS($js, View::POS_END);
?>