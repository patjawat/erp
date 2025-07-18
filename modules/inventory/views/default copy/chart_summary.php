<?php

use yii\helpers\Url;
use yii\web\View;
use yii\helpers\Json;
// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

?>
<div id="inventoryCharts"></div>

<?php
 $query = $model->SummaryChart();


try {
  $chartSummary = [
      'in' => [$query[0]['in10'], $query[0]['in11'], $query[0]['in12'], $query[0]['in1'], $query[0]['in3'], $query[0]['in3'], $query[0]['in4'], $query[0]['in5'], $query[0]['in6'], $query[0]['in7'], $query[0]['in8'], $query[0]['in9']],
      'out' => [$query[0]['out10'], $query[0]['out11'], $query[0]['out12'], $query[0]['out1'], $query[0]['out3'], $query[0]['out3'], $query[0]['out4'], $query[0]['out5'], $query[0]['out6'], $query[0]['out7'], $query[0]['out8'], $query[0]['out9']]
  ];
  //code...
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
            yaxis: { show: true,
    tickAmount: 4,
    labels: {
                  offsetX: -17,
                formatter: function (value) {
                  return value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
                  return val.toFixed(2) + " บาท"
                }
              }
            }
            };

            var chart = new ApexCharts(document.querySelector("#inventoryCharts"), orderOptions);
            chart.render();
      
 
  JS;
$this->registerJS($js, View::POS_END);
?>