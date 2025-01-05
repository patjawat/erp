<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-chart-simple"></i> สรุปสถิติการลารายปี</h6>
                      
                </div>
        <div id="chartSummaryYear"></div>
    </div>
</div>


<?php

use yii\helpers\Url;
use yii\helpers\Json;
$dataSummary = Json::encode($searchModel->listYear()['summary']);
$categories = Json::encode($searchModel->listYear()['thai_year']);

$js = <<< JS

var options = {
        series:$dataSummary,
          chart: {
          type: 'bar',
          height: 338,
          stacked: true,
          stackType: "100%",
          toolbar: {
            show: true
          },
          zoom: {
            enabled: true
          }
        },
        responsive: [{
          breakpoint: 480,
          options: {
            legend: {
              position: 'top',
              offsetX: 0,
              offsetY: 0
            }
          }
        }],
        colors: ['#5655b7', '#3cebb4','#ffa73e',"#8ecae6","#219ebc","#023047","#ffb703","#fb8500"],
        plotOptions: {
          bar: {
            horizontal: false,
            borderRadius: 10,
            borderRadiusApplication: 'end', // 'around', 'end'
            borderRadiusWhenStacked: 'last', // 'all', 'last'
            dataLabels: {
              total: {
                enabled: true,
                style: {
                  fontSize: '13px',
                  fontWeight: 900
                },
                offsetY: -150, // ปรับค่า offsetY เพื่อนำ data label ขึ้นไปด้านบน
                formatter: function (val) {
                        return val.toLocaleString(); // รูปแบบตัวเลขคั่นหลักพัน
                    }
              }
            }
          },
        },
        xaxis: {
          // type: 'datetime',
          categories: $categories,
        },
        legend: {
          position: 'right',
          offsetY: 40
        },
        fill: {
          opacity: 1
        }
        };

  var chart = new ApexCharts(document.querySelector("#chartSummaryYear"), options);

  chart.render();
      
        

JS;
$this->registerJS($js);
?>