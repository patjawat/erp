<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;
// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

?>
<div id="GeneralCharts"></div>

<?php
 $query = $model->ChartSummaryGeneral();


try {
  $chartSummary = [
      'data' => [$query['general_10'], $query['general_11'], $query['general_12'], $query['general_1'], $query['general_3'], $query['general_3'], $query['general_4'], $query['general_5'], $query['general_6'], $query['general_7'], $query['general_8'], $query['general_9']],
    ];
} catch (\Throwable $th) {
  $chartSummary = [
      'data' => [],
  ];
}
echo "<pre>";
// print_r($chartSummary['data']);

echo "</pre>";
$chartData = Json::encode($chartSummary['data']);
$js = <<< JS

  var orderOptions = {
    series: [
            { name: "ใช้งาน", data: $chartData },
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
              // y: {
              //   formatter: function (val) {
              //     return "\$ " + val + " บาท"
              //   }
              // }
            }
            };

            var chart = new ApexCharts(document.querySelector("#GeneralCharts"), orderOptions);
            chart.render();
      
 
  JS;
$this->registerJS($js, View::POS_END);
?>