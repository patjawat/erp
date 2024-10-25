<?php
use yii\helpers\Json;
use yii\helpers\Url;

$data = $model->pointYear();

$data1  = [];
$data2  = [];
$data3  = [];
      
foreach ($model->pointYear() as $key => $value) {
       $data1[] = $value['thai_year'];
       $data2[] = [
        'x' => "2021-06-08",
        'y' => ($model->point($value['thai_year'])[0]['point']) ?? 0,
       ];
       $data3[] = [
        'x' => "2021-06-08",
        'y' => ($model->point($value['thai_year'])[1]['point']) ?? 0,
       ];
       
}
$seriesSummary = [
  [
      'name' => 'ครั้งที่ 1',
      'data' => $data2,
  ],
  [

      'name' => 'ครั้งที่ 2',
      'data' => $data3,
  ],
  ];

  $series = Json::encode($seriesSummary);
  $categories = Json::encode($data1);
  $data1 = Json::encode($data1);
  $data2 = Json::encode($data2);
?>
<?php
?>
<div id="pointChart"></div>
<?php

$js = <<< JS

    var options = {
        series:$series,
          chart: {
          type: 'bar',
          height: 350
        },
        colors: ['#5655b7', '#3cebb4','#ffa73e'],
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded',
            startingShape: 'rounded',
            borderRadius: 0
          },
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
          categories: $categories,
        },
        yaxis: {
          title: {
            text: '(ผลการประเมิน)'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return "ผลการประเมิน " + val
            }
          },
        }
        };

        var chart = new ApexCharts(document.querySelector("#pointChart"), options);
        chart.render();


  JS;
$this->registerJS($js);
?>
