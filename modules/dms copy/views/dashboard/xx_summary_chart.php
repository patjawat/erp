<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;
// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);
$chartName = 'Chart'.$name;
$chartId = '"#Chart'.$name.'"';
?>

<div id="donut-chart"></div>
<div id="bar-chart"></div>
<div class="card">
            <div class="card-body">
                    
<div class="d-flex justify-content-between">
                    <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> <?php echo $title?></h6>
                    <div class="mb-3">
                      </div>
                </div>
                <div id="<?php echo $chartName?>"></div>
            </div>
        </div>

                

<?php
 $chartData = Json::encode([
  'series' => [[10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120]],
  'categories' => ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'],
]);

$donutData = Json::encode([
  'series' => [44, 55, 13, 33], // ตัวอย่างข้อมูลโดนัท
  'labels' => ['สินค้า A', 'สินค้า B', 'สินค้า C', 'สินค้า D'],
]);

$js = <<< JS
// Bar Chart Options
var barOptions = {
  series: $chartData.series,
  chart: {
    type: 'bar',
    height: 300,
    fontFamily: "Prompt, sans-serif",
    parentHeightOffset: 0,
    toolbar: { show: false },
  },
  colors: ['#0866ad', '#ff9800','#ffa73e'],
  plotOptions: {
    bar: {
      borderRadius: 4,
      distributed: false,
      columnWidth: '40%',
      endingShape: 'rounded',
      startingShape: 'rounded',
    },
  },
  grid: {
    strokeDashArray: 7,
    padding: { top: -1, right: 0, left: -12, bottom: 5 },
  },
  dataLabels: { enabled: false },
  stroke: {
    show: true,
    width: 2,
    colors: ['transparent'],
  },
  xaxis: {
    categories: $chartData.categories,
    tickPlacement: 'on',
    labels: { show: true },
    axisTicks: { show: false },
    axisBorder: { show: false },
  },
  yaxis: { 
    show: true,
    tickAmount: 4,
    labels: {
      offsetX: -17,
      formatter: function (val) {
        return val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
      },
    },
    title: { text: '\$ (thousands)' },
  },
  fill: { opacity: 1 },
  tooltip: {
    y: {
      formatter: function (val) {
        return val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + " ครั้ง";
      },
    },
  },
};

// Render Bar Chart
var barChart = new ApexCharts(document.querySelector("#bar-chart"), barOptions);
barChart.render();

// Donut Chart Options
var donutOptions = {
  series: $donutData.series,
  labels: $donutData.labels,
  chart: {
    type: 'donut',
    height: 250,
    fontFamily: "Prompt, sans-serif",
  },
  colors: ['#0866ad', '#ff9800', '#ffa73e', '#28a745'],
  dataLabels: { enabled: false },
  legend: {
    position: 'right',
    markers: { radius: 12 },
    itemMargin: { vertical: 2 },
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + " ครั้ง";
      },
    },
  },
};

// Render Donut Chart
var donutChart = new ApexCharts(document.querySelector("#donut-chart"), donutOptions);
donutChart.render();

// CSS for positioning the charts
var chartStyle = document.createElement('style');
chartStyle.innerHTML = `
  #chart-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    position: relative;
  }
  #bar-chart {
    flex: 2;
    margin-right: 20px;
  }
  #donut-chart {
    flex: 1;
  }
`;
document.head.appendChild(chartStyle);
JS;
$this->registerJS($js, View::POS_END);
?>