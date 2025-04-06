<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;
// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

?>
<div id="AmbulanceCharts"></div>

<?php
 $query = $model->ChartSummaryAmbulance();


try {
  $chartSummary = [
      'refer' => [$query['refer_10'], $query['refer_11'], $query['refer_12'], $query['refer_1'], $query['refer_2'], $query['refer_3'], $query['refer_4'], $query['refer_5'], $query['refer_6'], $query['refer_7'], $query['refer_8'], $query['refer_9']],
      'ems' => [$query['ems_10'], $query['ems_11'], $query['ems_12'], $query['ems_1'], $query['ems_2'], $query['ems_3'], $query['ems_4'], $query['ems_5'], $query['ems_6'], $query['ems_7'], $query['ems_8'], $query['ems_9']],
      'normal' => [$query['normal_10'], $query['normal_11'], $query['normal_12'], $query['normal_1'], $query['normal_3'], $query['normal_3'], $query['normal_4'], $query['normal_5'], $query['normal_6'], $query['normal_7'], $query['normal_8'], $query['normal_9']],
    ];
} catch (\Throwable $th) {
  $chartSummary = [
      'refer' => [],
      'ems' => [],
      'normal' => [],
  ];
}

$chartDataRefer = Json::encode($chartSummary['refer']);
$chartDataEms = Json::encode($chartSummary['ems']);
$chartDataNormal = Json::encode($chartSummary['normal']);
$js = <<< JS

  var orderOptions = {
    series: [
            { name: "refer", data: $chartDataRefer },
            { name: "ems", data: $chartDataEms },
            { name: "รับ-ส่ง [ไม่ฉุกเฉิน]", data: $chartDataNormal },
          ],
              chart: {
              type: 'bar',
              height: 300,
              fontFamily: "Prompt, sans-serif",
              parentHeightOffset: 0,
                toolbar: { show: false }
            },
            colors: ['#e91e63','#ff9800','#0866ad'],
            plotOptions: {
                bar: {
                borderRadius: 2,
                distributed: false,
                columnWidth: '70%',
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
              // y: {
              //   formatter: function (val) {
              //     return "\$ " + val + " บาท"
              //   }
              // }
            }
            };

            var chart = new ApexCharts(document.querySelector("#AmbulanceCharts"), orderOptions);
            chart.render();
      
 
  JS;
$this->registerJS($js, View::POS_END);
?>