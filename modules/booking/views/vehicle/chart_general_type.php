
<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Pjax;
use yii\db\Expression;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\ThaiDateHelper;
use app\modules\booking\models\Vehicle;
?>
<div class="card">
                <div class="card-body">
                <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> จำนวนการใช้งานรถทั่วไป</h6>
                <div id="ChartVehicle"></div>
            </div>
        </div>

<?php

$queryOfficial = $searchModel->getChartSummary('official');
$queryPersonal = $searchModel->getChartSummary('personal');

try {
  $officialSummary = [$queryOfficial['m1'], $queryOfficial['m2'], $queryOfficial['m3'], $queryOfficial['m4'], $queryOfficial['m5'], $queryOfficial['m6'], $queryOfficial['m7'], $queryOfficial['m8'], $queryOfficial['m9'],$queryOfficial['m10'], $queryOfficial['m11'], $queryOfficial['m12'], ];
  $personalSummary = [$queryPersonal['m1'], $queryPersonal['m2'], $queryPersonal['m3'], $queryPersonal['m4'], $queryPersonal['m5'], $queryPersonal['m6'], $queryPersonal['m7'], $queryPersonal['m8'], $queryPersonal['m9'],$queryPersonal['m10'], $queryPersonal['m11'], $queryPersonal['m12'], ];
} catch (\Throwable $th) {
  $officialSummary = [];
  $personalSummary = [];
}

$dataOfficial = Json::encode($officialSummary);
$dataPersonal = Json::encode($personalSummary);
// $dataAmbulance = Json::encode($ambulanceSummary);

$js = <<< JS
  var orderOptions = {
    series: [{
          name: 'รถยนต์ราชการ',
          data: $dataOfficial
        }, {
          name: 'รถยนต์ส่วนตัว',
          data: $dataPersonal
        },  
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
              categories: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.','ต.ต.','พ.ย.','ธ.ค.'],
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
            }
            };

            var chart = new ApexCharts(document.querySelector('#ChartVehicle'), orderOptions);
            chart.render();

            
      
   
  JS;
$this->registerJS($js, View::POS_END);
?>