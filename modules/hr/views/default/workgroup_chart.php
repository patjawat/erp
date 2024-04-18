<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$dataCategories = [];
$series = [];
$dataPosition1 = [];
$dataPosition2 = [];
$dataPosition3 = [];
$dataPosition4 = [];
$dataPosition5 = [];
$dataPosition6 = [];
$dataPosition7 = [];



foreach ($dataProviderWorkGroup->getModels() as $model) {
    $dataCategories[] = $model['_groupname'];
    $dataPosition1[] = $model['_position1'];  
    $dataPosition2[] = $model['_position2'];  
    $dataPosition3[] = $model['_position3'];  
    $dataPosition4[] = $model['_position4'];  
    $dataPosition5[] = $model['_position5'];  
    $dataPosition6[] = $model['_position6'];  
    $dataPosition7[] = $model['_position7'];  
}

$dataSeries = [
  [
    'name' => 'ข้าราขการ',
    'data' => $dataPosition1
  ],
  [
    'name' => 'ลูกจ้างประจำ',
    'data' =>  $dataPosition2
  ],
  [
    'name' => 'พนักงานราชการ',
    'data' =>  $dataPosition3
  ],
  [
    'name' => 'พนักงานกระทรวงสาธารณสุข',
    'data' =>  $dataPosition4
  ],
  [
    'name' => 'ลูกจ้างชั่วคราว',
    'data' =>  $dataPosition5
  ],
  [
    'name' => 'ลูกจ้างรายวัน',
    'data' =>  $dataPosition6
  ],
  [
    'name' => 'อื่นๆ',
    'data' =>  $dataPosition7
    ]
];

?>
<style>

</style>
<div class="card">
    <div class="card-body">
        <div id="workgroupChart"></div>
    </div>
</div>

<?php
$categories = Json::encode($dataCategories);
$series = Json::encode($dataSeries);

use yii\helpers\Url;
use yii\web\View;
$js = <<< JS
var options = {
          series: $series,
          chart: {
          type: 'bar',
          height: 450,
          stacked: true,
          stackType: '100%'
        },
        plotOptions: {
          bar: {
            horizontal: false,
          },
        },
        stroke: {
          width: 1,
          colors: ['#fff']
        },
        title: {
          text: 'แผนกหน่วยงาน'
        },
        xaxis: {
          categories: $categories,
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + "คน"
            }
          }
        },
        fill: {
          opacity: 1
        
        },
        legend: {
          position: 'top',
          horizontalAlign: 'left',
          offsetX: 40
        }
        };

        var chart = new ApexCharts(document.querySelector("#workgroupChart"), options);
        chart.render();
      
      
    

JS;
$this->registerJS($js,View::POS_END);
?>
