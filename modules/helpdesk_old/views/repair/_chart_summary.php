
<?php 
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;
?>
<div class="card">
            <div class="card-header d-flex justify-content-between">
                <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> สรุปปริมาณงาน</h6>
                <div class="mb-3">
                  
                    <?php echo $this->render('_search_year', ['model' => $searchModel]); ?></div>
            </div>
            <div class="card-body" id="chartIndex2">

            </div>
        </div>
<?php

$query = $searchModel->SummaryOfYear();
try {
$dataOrder = [
    'm' => [$query['m10'], $query['m11'], $query['m12'], $query['m1'], $query['m3'], $query['m3'], $query['m4'], $query['m5'], $query['m6'], $query['m7'], $query['m8'], $query['m9']],
    'cancel' => [$query['m10_cancel'], $query['m11_cancel'], $query['m12_cancel'], $query['m1_cancel'], $query['m3_cancel'], $query['m3_cancel'], $query['m4_cancel'], $query['m5_cancel'], $query['m6_cancel'], $query['m7_cancel'], $query['m8_cancel'], $query['m9_cancel']],
    'success' => [$query['m10_success'], $query['m11_success'], $query['m12_success'], $query['m1_success'], $query['m3_success'], $query['m3_success'], $query['m4_success'], $query['m5_success'], $query['m6_success'], $query['m7_success'], $query['m8_success'], $query['m9_success']]
];
} catch (\Throwable $th) {
    $dataOrder = [
        'm' => [],
        'cancel' => [],
        'success' => [],
    ];
  }

$data = Json::encode($dataOrder['m']);
$dataCancel = Json::encode($dataOrder['cancel']);
$dataSuccess = Json::encode($dataOrder['success']);

$js = <<< JS

  var options2 = {
          series: [{
          name: 'อยู่ระหว่างดำเนินการ',
          data: $data
        },{
          name: 'เสร็จสิ้น',
          data:$dataSuccess
        },{
          name: 'ยกเลิก',
          data: $dataCancel
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
          categories: ['ต.ค.', 'พ.ย.', 'ธ.ค.','ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ย.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'],
        },
        yaxis: {
          title: {
            text: 'จำนวนตรั้ง'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return  val + " ครั้ง"
            }
          }
        }
        };

        var chart = new ApexCharts(document.querySelector("#chartIndex2"), options2);
        chart.render();
        
  JS;
$this->registerJS($js, View::POS_READY);
?>