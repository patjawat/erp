<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="row align-items-stretch">

    <!-- Begin total revenue chart -->
    <div class="col-md-12 col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <h5 class="card-title">ปริมาณการใช้เงินงบประมาณ</h5>
            </div>
            <div class="card-body" id="chartIndex2">

            </div>
        </div>
    </div> <!-- End total revenue chart -->

    
</div>



<?php
use yii\web\View;
$js = <<< JS

var options = {
          series: [{
          name: 'เงินงบประมาณ',
          data: [44, 55, 57, 56, 61, 58, 63, 60, 66,70,55,62]
        }, {
          name: 'เงินบำรุง',
          data: [76, 85, 101, 98, 87, 105, 91, 114, 94,85,66,79]
        }, {
          name: 'เงินบริจาค',
          data: [35, 41, 36, 26, 45, 48, 52, 53, 41,64,53,60]
        }],
          chart: {
          type: 'bar',
          height: 290
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            // barHeight: '30%',
            endingShape: 'rounded',
            borderRadius: 4,
            borderRadiusApplication: 'end',
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
          categories: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ย.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
        },
        yaxis: {
          title: {
            text: '$ (thousands)'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return "$ " + val + " thousands"
            }
          }
        }
        };

        var chart = new ApexCharts(document.querySelector("#chartIndex2"), options);
        chart.render();


JS;
$this->registerJS($js, View::POS_END);
?>