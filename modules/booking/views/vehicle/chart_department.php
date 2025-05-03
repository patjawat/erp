
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
                <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> การขอใช้งานรถทั่วไปของหน่วยงานต่าง ๆ</h6>
                <div id="chartDepartment"></div>
            </div>
        </div>
       
<?php
$departmentCounts = $searchModel->getChartSummaryAmbulance('normal');  // รับ-ส่ง [ไม่ฉุกเฉิน]
$departmentData = Json::encode($searchModel->departmentSummary());
$js = <<< JS
      
    const departmentData = $departmentData;
    const categories = departmentData.map(item => item.name);
    const counts = departmentData.map(item => parseInt(item.total));
 
    const optionsDepartment = {
     series: [{
         name: 'ขอใช้งานรถยนต์',
         data: counts
     }],
     chart: {
         type: 'bar',
         height: 300,
         toolbar: {
             show: true
         }
     },
     chart: {
              type: 'bar',
              height: 800,
              fontFamily: "Prompt, sans-serif",
              parentHeightOffset: 0,
                toolbar: { show: false }
            },
            colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26a69a', '#D10CE8'],
            plotOptions: {
                bar: {
                horizontal:true,
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
              categories: categories,
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

 const chartDepartment = new ApexCharts(document.querySelector("#chartDepartment"), optionsDepartment);
 chartDepartment.render();
JS;
$this->registerJS($js, View::POS_END);
?>