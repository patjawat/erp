<div id="orderChartColumn"></div>
<?php

use yii\helpers\Json;
use yii\helpers\Url;

$url = Url::to('/sm/default/chart');

$data1  = [];
$data2  = [];
$data3  = [];


$arr = [10,11,12,1,2,3,4,5,6,7,8,9];
                  
foreach ($arr as $key => $value) {
       $data1[] = $model->SummaryMaterial($value);
       $data2[] = $model->SummaryAsset($value);
       $data3[] = $model->SummaryOutsource($value);  
}

$seriesSummary = [
  [
      'name' => 'วัสดุ',
      'data' => $data1,
  ],
  [

      'name' => 'ครุภัณฑ์',
      'data' => $data2,
  ],
  [
      'name' => 'จ้างเหมา',
      'data' => $data3
      ]
  ];

  $series = Json::encode($seriesSummary);
  // $total = Json::encode($getTotal);
$js = <<< JS

    var orderOptions = {
              series: $series,
              chart: {
              type: 'bar',
              height: 350,
              parentHeightOffset: 0,
                toolbar: { show: false }
            },
            colors: ['#5655b7', '#3cebb4','#ffa73e'],
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
              // labels: {
                // },
                labels: {
                  offsetX: -17,
                formatter: function (value) {
                  return value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
              },
              title: {
                // text: '\$ (thousands)'
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

            var chart = new ApexCharts(document.querySelector("#orderChartColumn"), orderOptions);
            chart.render();
      
        

  JS;
$this->registerJS($js);
?>