<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;
// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

?>
<div id="inventoryCharts"></div>

<?php
 $query = $model->SummaryChart();

try {
  $chartSummary = [
      'in' => [$query['in10'], $query['in11'], $query['in12'], $query['in1'], $query['in2'], $query['in3'], $query['in4'], $query['in5'], $query['in6'], $query['in7'], $query['in8'], $query['in9']],
      'out' => [$query['out10'], $query['out11'], $query['out12'], $query['out1'], $query['out2'], $query['out3'], $query['out4'], $query['out5'], $query['out6'], $query['out7'], $query['out8'], $query['out9']]
  ];
} catch (\Throwable $th) {
  $chartSummary = [
      'in' => [],
      'out' => [],
  ];
}

$chartSummeryIn = Json::encode($chartSummary['in']);
$chartSummeryOut = Json::encode($chartSummary['out']);
$js = <<< JS

  var orderOptions = {
    series: [
            { name: "รับเข้า", data: $chartSummeryIn },
            { name: "จ่าย", data: $chartSummeryOut },
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
                return  val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " บาท";  // Format tooltip with commas and 2 decimal places
            }
        }
              // y: {
              //   formatter: function (val) {
              //     return "\$ " + val + " บาท"
              //   }
              // }
            }
            };

            var chart = new ApexCharts(document.querySelector("#inventoryCharts"), orderOptions);
            chart.render();
      
 
  JS;
$this->registerJS($js, View::POS_END);
?>