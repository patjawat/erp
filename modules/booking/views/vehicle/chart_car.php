
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
                <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> รถที่มีการใช้งานบ่อย</h6>
                <div id="chartCar"></div>
            </div>
        </div>
       
<?php

$CarData = Json::encode($searchModel->CarSummary());
$js = <<< JS
      
    const CarData = $CarData;
    const categoriesCar = CarData.map(item => item.license_plate);
    const countsCar = CarData.map(item => parseInt(item.total));
 
    const optionsCar = {
     series: [{
         name: 'จำนวนการใช้งาน',
         
         data: countsCar
     }],
     chart: {
         type: 'bar',
         height: 350,
         toolbar: {
             show: true
         }
     },
     chart: {
              type: 'bar',
              height: 300,
              fontFamily: "Prompt, sans-serif",
              parentHeightOffset: 0,
                toolbar: { show: false }
            },
            colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26a69a', '#D10CE8'],
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
              categories: categoriesCar,
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

 const chartCar = new ApexCharts(document.querySelector("#chartCar"), optionsCar);
 chartCar.render();
JS;
$this->registerJS($js, View::POS_END);
?>