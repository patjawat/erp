<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;

// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

?>
<div id="GeneralChartsTopten"></div>

<?php
//  $query = $model->ChartSummaryGeneral();
$data = $model->TopTenDriverService()['total'];
$categorise = $model->TopTenDriverService()['categorise'];

$chartData = Json::encode($data);
$chartCategorise = Json::encode($categorise);

echo "<pre>";
print_r($chartData);
print_r($chartCategorise);
echo "</pre>";
$js = <<<JS

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
              borderRadiusApplication: 'end',
              horizontal: true,
                distributed: false,
                  columnWidth: '40%',
                  endingShape: 'rounded',
                  startingShape: 'rounded',
            }
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
            colors: ['#0866ad', '#08a1ad', '#084cad'], // ใช้ 3 เฉดสีที่เข้ากันได้
            dataLabels: {
              enabled: false
            },
            stroke: {
              show: true,
              width: 2,
              colors: ['transparent']
            },
            xaxis: {
              categories: $chartCategorise,
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
                // text: '\$ (thousands)'
              }
            },
            // fill: {
            //     type: 'gradient',
            //     gradient: {
            //       shade: 'dark',
            //       type: 'vertical',
            //       shadeIntensity: 0.5,
            //       gradientToColors: ['#0866ad', '#08a1ad', '#084cad'], // ไล่สีตามเฉดที่เลือก
            //       inverseColors: false,
            //       opacityFrom: 1,
            //       opacityTo: 0.8,
            //       stops: [0, 100]
            //     }
            //   },
            tooltip: {
              y: {
            formatter: function (val) {
                return  val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + " ครั้ง";  // Format tooltip with commas and 2 decimal places
            }
        }
            }
            };

            var chart = new ApexCharts(document.querySelector("#GeneralChartsTopten"), orderOptions);
            chart.render();
      
   
  JS;
$this->registerJS($js, View::POS_END);
?>