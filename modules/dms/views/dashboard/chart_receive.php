<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;

// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

?>
<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-between">
            <h6 class="card-title"><i class="fa-solid fa-download text-primary"></i> หนังสือรับแยกรายเดือน (จำแนกตามวันที่รับ)</h6>
            <div class="mb-3">
            
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <div id="ChartReceive"></div>

            </div>
            <div class="col-4">
              <div id="donut-type-chart"></div>
                <div id="donut-chart"></div>
            </div>
        </div>
    </div>
</div>



<?php
$query = isset($chartReceive) ? $chartReceive : $model->getChartSummary('receive');
try {
  $chartSummary = [$query['m1'] ?? 0, $query['m2'] ?? 0, $query['m3'] ?? 0, $query['m4'] ?? 0, $query['m5'] ?? 0, $query['m6'] ?? 0, $query['m7'] ?? 0, $query['m8'] ?? 0, $query['m9'] ?? 0, $query['m10'] ?? 0, $query['m11'] ?? 0, $query['m12'] ?? 0];
} catch (\Throwable $th) {
  $chartSummary = [];
}

$docSpeedRows = isset($summaryDocSpeed) ? $summaryDocSpeed : $model->summaryDocSpeed();
$docSpeedLabel = [];
$docSpeedSeries = [];
foreach ($docSpeedRows as $docSpeedItem) {
    $docSpeedLabel[] = $docSpeedItem['title'];
    $docSpeedSeries[] = $docSpeedItem['total'];
}
$donutDocSpeedSeriesJson = Json::encode($docSpeedSeries);
$donutDocSpeedLabelsJson = Json::encode($docSpeedLabel);

$docTypeRows = isset($summaryDocType) ? $summaryDocType : $model->summaryDocType();
$docTypeLabel = [];
$docTypeSeries = [];
foreach ($docTypeRows as $docTypeItem) {
    $docTypeLabel[] = $docTypeItem['title'];
    $docTypeSeries[] = $docTypeItem['total'];
}
$donutDocTypeSeriesJson = Json::encode($docTypeSeries);
$donutDocTypeLabelsJson = Json::encode($docTypeLabel);


$data = Json::encode($chartSummary);



$js = <<< JS
  var orderOptions = {
    series: [
            { name: "จำนวน", data: $data },
          ],
              chart: {
              type: 'bar',
              height: 300,
              fontFamily: "Prompt, sans-serif",
              parentHeightOffset: 0,
                toolbar: { show: false }
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
                padding: {
                  top: -1,
                  right: 0,
                  left: -12,
                  bottom: 5
                }
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
              categories: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.','ต.ต.','พ.ย.','ธ.ค.'],
              tickPlacement: 'on',
                labels: { show: true },
                axisTicks: { show: false },
                axisBorder: { show: false }
            },
            yaxis: { 
              show: true,
              tickAmount: 4,
              labels: {
                offsetX: -17,
                formatter: function (val) {
                return val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) // Format y-axis labels to 2 decimal places
            }
              },
              
              
              title: {
                text: '\$ (thousands)'
              }
            },
            fill: {
              opacity: 1
            },
            tooltip: {
              y: {
            formatter: function (val) {
                return  val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + " ครั้ง";  // Format tooltip with commas and 2 decimal places
            }
        }
            }
            };

            var chart = new ApexCharts(document.querySelector('#ChartReceive'), orderOptions);
            chart.render();
      

  // Donut Chart Options
  var donutDocSpeedOptions = {
    series: $donutDocSpeedSeriesJson,
    labels: $donutDocSpeedLabelsJson,
    chart: {
      type: 'donut',
      height: 650,
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
  var donutDocSpeed = new ApexCharts(document.querySelector("#donut-chart"), donutDocSpeedOptions);
  donutDocSpeed.render();

  // Donut Chart Options
  var donutTypeOptions = {
    series: $donutDocTypeSeriesJson,
    labels: $donutDocTypeLabelsJson,
    chart: {
      type: 'donut',
      height: 650,
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
  var donutTypeChart = new ApexCharts(document.querySelector("#donut-type-chart"), donutTypeOptions);
  donutTypeChart.render();
  

   
  JS;
$this->registerJS($js, View::POS_END);
?>