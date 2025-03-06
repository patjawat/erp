<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
                    <h6>สรุปสถิติการลารายเดือน</h6>
                      
                </div>
        <div id="chartMonth"></div>
    </div>
</div>


<?php

use yii\helpers\Url;
use yii\helpers\Json;

$seriesSummary = [];
foreach($dataProvider->getModels() as $model){
    $seriesSummary[] = [
        'name' => $model->title,
        'data' => [$model->m10,$model->m11,$model->m12,$model->m1,$model->m2,$model->m3,$model->m4,$model->m5,$model->m6,$model->m7,$model->m8,$model->m9,]
    ];
    
}

// $seriesSummary = [
//   [
//       'name' => 'ทั้งหมด',
//       'data' => []
//   ],
//   [

//       'name' => 'ครุภัณฑ์',
//       'data' => $data2,
//   ],
//   [
//       'name' => 'จ้างเหมา',
//       'data' => $data3
//       ]
//   ];

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
            // colors: ['#5655b7', '#3cebb4','#ffa73e'],
            colors: ['#5655b7', '#3cebb4','#ffa73e',"#8ecae6","#219ebc","#023047","#ffb703","#fb8500"],
            plotOptions: {
                bar: {
                borderRadius: 4,
                distributed: false,
                columnWidth: '100%',
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
                  return value.toLocaleString(undefined,);
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
                  return val + " ครั้ง"
                }
              }
            }
            };

            var chart = new ApexCharts(document.querySelector("#chartMonth"), orderOptions);
            chart.render();
      
        

  JS;
$this->registerJS($js);
?>