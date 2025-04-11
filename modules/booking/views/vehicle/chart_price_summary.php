
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
                <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> ค่าใช้จ่ายยานพาหนะแยกรายเดือน</h6>
                <div id="ChartPriceSummary"></div>
            </div>
        </div>

<?php

$queryPrice = $searchModel->getPriceSummary();

try {
  $priceSummary = [$queryPrice['m10'], $queryPrice['m11'], $queryPrice['m12'], $queryPrice['m1'], $queryPrice['m2'], $queryPrice['m3'], $queryPrice['m4'], $queryPrice['m5'], $queryPrice['m6'], $queryPrice['m7'], $queryPrice['m8'], $queryPrice['m9'] ];
} catch (\Throwable $th) {
  $priceSummary = [];
}

$dataPriceSummary = Json::encode($priceSummary);
// $dataAmbulance = Json::encode($ambulanceSummary);

$js = <<< JS
  var priceSummaryOptions = {
    series: [{
          name: 'ค่าใช้จ่าย',
          data: $dataPriceSummary
        }
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
                return  val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + " บาท";  // Format tooltip with commas and 2 decimal places
            }
        }
            }
            };

            var chart = new ApexCharts(document.querySelector('#ChartPriceSummary'), priceSummaryOptions);
            chart.render();

            
      
   
  JS;
$this->registerJS($js, View::POS_END);
?>