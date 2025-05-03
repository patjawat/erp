
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
                <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> จำนวนการใช้งานรถฉุกเฉิน</h6>
        <div id="ChartAmbulance"></div>
            </div>
        </div>
       
<?php



$queryNormal = $searchModel->getChartSummaryAmbulance('NORMAL');  // รับ-ส่ง [ไม่ฉุกเฉิน]
$queryEms = $searchModel->getChartSummaryAmbulance('EMS');  // EMS
$queryRefer = $searchModel->getChartSummaryAmbulance('REFER');  // REFER

// try {
  $normalSummary = [
    $queryNormal['m10'],
    $queryNormal['m11'],
    $queryNormal['m12'],
    $queryNormal['m1'],
    $queryNormal['m2'],
    $queryNormal['m3'],
    $queryNormal['m4'],
    $queryNormal['m5'],
    $queryNormal['m6'],
    $queryNormal['m7'],
    $queryNormal['m8'],
    $queryNormal['m9'],
    
  ];
  $emsSummary = [
    $queryEms['m10'],
    $queryEms['m11'],
    $queryEms['m12'],
    $queryEms['m1'],
    $queryEms['m2'],
    $queryEms['m3'],
    $queryEms['m4'],
    $queryEms['m5'],
    $queryEms['m6'],
    $queryEms['m7'],
    $queryEms['m8'],
    $queryEms['m9'],
   
  ];
  $referSummary = [
    $queryRefer['m10'],
    $queryRefer['m11'],
    $queryRefer['m12'],
    $queryRefer['m1'],
    $queryRefer['m2'],
    $queryRefer['m3'],
    $queryRefer['m4'],
    $queryRefer['m5'],
    $queryRefer['m6'],
    $queryRefer['m7'],
    $queryRefer['m8'],
    $queryRefer['m9'],
  
  ];
// } catch (\Throwable $th) {
//   $normalSummary = [];
//   $emsSummary = [];
//   $referSummary = [];
// }

$dataNormal = Json::encode($normalSummary);
$dataEms = Json::encode($emsSummary);
$dataRefer = Json::encode($referSummary);

$js = <<<JS
  var ambulanceOptions = {
    series: [{
          name: 'รับ-ส่ง [ไม่ฉุกเฉิน]',
          data: $dataNormal
        }, {
          name: 'EMS',
          data: $dataEms
        }, {
          name: 'REFER',
          data: $dataRefer
        }
    ],
              chart: {
              type: 'bar',
              height: 300,
              fontFamily: "Prompt, sans-serif",
              parentHeightOffset: 0,
                toolbar: { show: false }
            },
            colors: ['#0866ad', '#ff5722','#ffa73e'],
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
              categories: ['ต.ต.','พ.ย.','ธ.ค.','ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'],
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

            var chart = new ApexCharts(document.querySelector('#ChartAmbulance'), ambulanceOptions);
            chart.render();

            
      
   
  JS;
$this->registerJS($js, View::POS_END);
?>