      

<div class="card">
    <div class="card-body">
        <div id="positionGroupType"></div>
    </div>
</div>

<?php
use yii\helpers\Json;
use yii\web\View;
use yii\helpers\Url;

$title = [];
$value = [];

// foreach ($dataProviderPositionType->getModels() as $model) {
//     $title[] = $model['title'];
//     $value[] = $model['cnt'];
// }

?>

<?php
$data = Json::encode($value);
$labels = Json::encode($title);
// $url = Url::to(['/hr/default/data-summary']);
$js = <<< JS
        var options = {
          series: [{
          name: 'อำนวยการ',
          data: [44, 55, 57, 56, 61, 58]
        }, {
          name: 'บริหาร',
          data: [76, 85, 101, 98, 87, 105]
        }, {
          name: 'สนับสนุน',
          data: [35, 41, 36, 26, 45, 48]
        },{
        name: 'xxx',
          data: [35, 0, 0, 0, 0, 0]
        }],
          chart: {
          type: 'bar',
          height: 350
        },
        title: {
          text: 'จำแนกตามกลุ่มงาน (อยู่ระหว่างกำลังดำเนินการ)',
          style: {
          fontWeight:  'normal',
          fontFamily:  'prompt',
          color:  '#263238'
        },
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '100%',
            endingShape: 'rounded'
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
          categories: ['ข้าราชการ', 'พนักงานราชการ', 'พกส.', 'ลูกจ้างชั่งคราวรายเดือน', 'ลูกจ้างชั่งคราวรายวัน', 'ลูกจ้างประจำ'],
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

        var chart = new ApexCharts(document.querySelector("#positionGroupType"), options);
        chart.render();


JS;
$this->registerJS($js,View::POS_END);
?>
