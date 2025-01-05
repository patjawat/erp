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
            <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> หนังสือรับแยกรายเดือน (จำแนกตามวันที่รับ)</h6>
            <div class="mb-3">
            
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <div id="ChartReceive"></div>

            </div>
            <div class="col-4">
                <div id="donut-chart"></div>
                <div id="donut-type-chart"></div>
            </div>
        </div>
    </div>
</div>



<?php
$query = $model->getChartSummary('receive');

try {
  $chartSummary = [$query['m10'], $query['m11'], $query['m12'], $query['m1'], $query['m3'], $query['m3'], $query['m4'], $query['m5'], $query['m6'], $query['m7'], $query['m8'], $query['m9']];
} catch (\Throwable $th) {
  $chartSummary = [
    'total' => [],
  ];
}

//ขั้นความเร่งด่วนทะเบียนรับ
$docSpeedLabel = [];
$docSpeedSeries = [];

// foreach ($model->summaryDocSpeed() as $docSpeedItem) {
//     $docSpeedLabel[] = $docSpeedItem['title'];
//     $docSpeedSeries[] =$docSpeedItem['total'];
// }
// $donutDocSpeed = Json::encode([
//   'series' => $docSpeedSeries,  // ตัวอย่างข้อมูลโดนัท
//   'labels' => $docSpeedLabel,
// ]);


//ประเภทหนังสือ
// $docTypeLabel = [];
// $docTypeSeries = [];

// foreach ($model->summaryDocSpeed() as $docTypeItem) {
//     $docTypeLabel[] = $docTypeItem['title'];
//     $docTypeSeries[] =$docTypeItem['total'];
// }
// $donutDocType = Json::encode([
//   'series' => $docTypeSeries,  // ตัวอย่างข้อมูลโดนัท
//   'labels' => $docTypeLabel,
// ]);


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
              categories: ['ต.ต.','พ.ย.','ธ.ค.','ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.',],
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
  // var donutDocSpeedOptions = {
  //   series: $donutDocSpeed.series,
  //   labels: $donutDocSpeed.labels,
  //   chart: {
  //     type: 'donut',
  //     height: 650,
  //     fontFamily: "Prompt, sans-serif",
  //   },
  //   colors: ['#0866ad', '#ff9800', '#ffa73e', '#28a745'],
  //   dataLabels: { enabled: false },
  //   legend: {
  //     position: 'right',
  //     markers: { radius: 12 },
  //     itemMargin: { vertical: 2 },
  //   },
  //   tooltip: {
  //     y: {
  //       formatter: function (val) {
  //         return val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + " ครั้ง";
  //       },
  //     },
  //   },
  // };

  // // Render Donut Chart
  // var donutChart = new ApexCharts(document.querySelector("#donut-chart"), donutDocSpeedOptions);
  // donutChart.render();

  // // Donut Chart Options
  // var donutTypeOptions = {
  //   series: $donutDocType.series,
  //   labels: $donutDocType.labels,
  //   chart: {
  //     type: 'donut',
  //     height: 650,
  //     fontFamily: "Prompt, sans-serif",
  //   },
  //   colors: ['#0866ad', '#ff9800', '#ffa73e', '#28a745'],
  //   dataLabels: { enabled: false },
  //   legend: {
  //     position: 'right',
  //     markers: { radius: 12 },
  //     itemMargin: { vertical: 2 },
  //   },
  //   tooltip: {
  //     y: {
  //       formatter: function (val) {
  //         return val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + " ครั้ง";
  //       },
  //     },
  //   },
  // };

  // // Render Donut Chart
  // var donutTypeChart = new ApexCharts(document.querySelector("#donut-type-chart"), donutTypeOptions);
  // donutTypeChart.render();
  

   
  JS;
$this->registerJS($js, View::POS_END);
?>