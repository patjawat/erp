<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-chart-simple"></i> สรุปสถิติการลารายปี</h6>
                    <?php echo $this->render('_search_year', ['model' => $searchModel]); ?>
                </div>
        <div id="chartSummaryYear"></div>
    </div>
</div>


<?php

use yii\helpers\Url;
use yii\helpers\Json;

$querys = Yii::$app->db->createCommand("SELECT thai_year,
COUNT(CASE WHEN leave_type_id = 'LT1' AND status = 'Approve' THEN 1 END) as LT1,
COUNT(CASE WHEN leave_type_id = 'LT2' AND status = 'Approve' THEN 1 END) as LT2,
COUNT(CASE WHEN leave_type_id = 'LT3' AND status = 'Approve' THEN 1 END) as LT3,
COUNT(CASE WHEN leave_type_id = 'LT4' AND status = 'Approve' THEN 1 END) as LT4,
COUNT(CASE WHEN leave_type_id = 'LT5' AND status = 'Approve' THEN 1 END) as LT5,
COUNT(CASE WHEN leave_type_id = 'LT6' AND status = 'Approve' THEN 1 END) as LT6,
COUNT(CASE WHEN leave_type_id = 'LT7' AND status = 'Approve' THEN 1 END) as LT7,
COUNT(CASE WHEN leave_type_id = 'LT8' AND status = 'Approve' THEN 1 END) as LT8,
COUNT(CASE WHEN leave_type_id = 'LT9' AND status = 'Approve' THEN 1 END) as LT9,
COUNT(CASE WHEN leave_type_id = 'LT10' AND status = 'Approve' THEN 1 END) as LT10,
COUNT(CASE WHEN leave_type_id = 'LT11' AND status = 'Approve' THEN 1 END) as LT11,
COUNT(CASE WHEN leave_type_id = 'LT12' AND status = 'Approve' THEN 1 END) as LT12
FROM `leave` GROUP BY thai_year;")->queryAll();

$year = [];
$leaveType1 = [];
$leaveType2 = [];
$leaveType3 = [];
$leaveType4 = [];
$leaveType5 = [];
$leaveType6 = [];
$leaveType7 = [];
$leaveType8 = [];
$leaveType9 = [];
$leaveType10 = [];
$leaveType11 = [];
$leaveType12 = [];

foreach($querys as $item)
{
$year[] = $item['thai_year'];
$leaveType1[] = $item['LT1'];
$leaveType2[] = $item['LT2'];
$leaveType3[] = $item['LT3'];
$leaveType4[] = $item['LT4'];
$leaveType5[] = $item['LT5'];
$leaveType6[] = $item['LT6'];
$leaveType7[] = $item['LT7'];
$leaveType8[] = $item['LT8'];
$leaveType9[] = $item['LT9'];
$leaveType10[] = $item['LT10'];
$leaveType11[] = $item['LT11'];
$leaveType12[] = $item['LT12'];
}

$dataSummary = Json::encode($searchModel->listYear()['summary']);
$categories = Json::encode($year);
$lt1 = Json::encode($leaveType1);
$lt2 = Json::encode($leaveType2);
$lt3 = Json::encode($leaveType3);
$lt4 = Json::encode($leaveType4);
$lt5 = Json::encode($leaveType5);
$lt6 = Json::encode($leaveType6);
$lt7 = Json::encode($leaveType7);
$lt8 = Json::encode($leaveType8);
$lt9 = Json::encode($leaveType9);
$lt10 = Json::encode($leaveType10);
$lt11 = Json::encode($leaveType11);
$lt12 = Json::encode($leaveType12);

$js = <<< JS
 var options = {
          series: [{
          name: 'ลาป่วย',
          data: $lt1
        }, {
          name: 'ลาคลอดบุตร',
          data: $lt2
        }, {
          name: 'ลากิจ',
          data: $lt3
        }, {
          name: 'ลาพักผ่อน',
          data: $lt4
        }, {
          name: 'ลาอุปสมบท',
          data: $lt5
        }, {
          name: 'ลาช่วยภริยาคลอด',
          data: $lt6
        }, {
          name: 'ลาเกณฑ์ทหาร',
          data: $lt7
        }, {
          name: 'ลาศึกษา ฝึกอบรม',
          data: $lt8
        }, {
          name: 'ลาทำงานต่างประเทศ',
          data: $lt9
        }, {
          name: 'ลาติดตามคู่สมรส',
          data: $lt10
        }, {
          name: 'ลาฟื้นฟูอาชีพ',
          data: $lt11
        }, {
          name: 'ลาออก',
          data: $lt12
        }],
          chart: {
          type: 'bar',
          height: 350,
          stacked: true,
          stackType: '100%'
        },
        colors: ['#e91e63','#5655b7','#ffa73e', '#4caf50',"#8ecae6","#219ebc","#023047","#ffb703","#fb8500"],
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