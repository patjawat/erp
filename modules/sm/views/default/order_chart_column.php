<div id="orderChartColumn"></div>
<?php
use yii\helpers\Url;
$url = Url::to('/sm/default/chart');
$js = <<< JS

  $.ajax({
    type: "get",
    url: "$url",
    dataType: "json",
    success: function (res) {

    var orderOptions = {
              series: res,
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
    labels: {
      offsetX: -17,
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
                  return "\$ " + val + " บาท"
                }
              }
            }
            };

            var chart = new ApexCharts(document.querySelector("#orderChartColumn"), orderOptions);
            chart.render();
      
          }
  });

  JS;
$this->registerJS($js);
?>