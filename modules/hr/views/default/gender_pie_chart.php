
<div class="card">
    <div class="card-body">
        <div id="genderPieCahrt"></div>
    </div>
</div>

<?php
use yii\helpers\Json;
use yii\web\View;

$m = $dataProviderGenderM->getTotalCount();
$w = $dataProviderGenderW->getTotalCount();
$title = [];
$value = [];

foreach ($dataProviderPositionType->getModels() as $model) {
    $title[] = $model['title'];
    $value[] = $model['cnt'];
}

?>

<?php
$data = Json::encode($value);
$labels = Json::encode($title);
// $url = Url::to(['/hr/default/data-summary']);
$js = <<< JS


        var options3 = {
        chart: { height: 420, parentHeightOffset: 0, type: "donut",    
          events: {
      click: function(event, chartContext, config) {
        console.log('click');
      }
    }, 
  },
   

  
        labels: ['ชาย','หญิง'],
      series: [$m,$w],
      stroke: { width: 0 },


      dataLabels: {
        enabled: !1,
        formatter: function (e, t) {
          return parseInt(e) + "%";
        },
      },
      legend: {
        position: "left",
        show: !0,
        offsetY: 10,
        markers: { width: 8, height: 8, offsetX: -3 },
        itemMargin: { horizontal: 15, vertical: 5 },
        fontSize: "13px",
        fontFamily: "prompt",
        fontWeight: 400,
      },
    //   tooltip: { theme: o },
    title: {
          text: 'จำแนกตามเพศ',
          style: {
          fontWeight:  'normal',
          fontFamily:  'prompt',
          color:  '#263238'
        },
        },
      grid: { padding: { top: 0,left:100 } },
      plotOptions: {
        pie: {
          donut: {
            size: "85%",
            labels: {
              show: !0,
              value: {
                fontSize: "26px",
                fontFamily: "prompt",
                // color: t,
                fontWeight: 500,
                offsetY: -30,
                formatter: function (e) {
                  return parseInt(e) + "%";
                },
              },
              name: { offsetY: 20, fontFamily: "prompt" },
              total: {
                      show: !0,
                fontSize: "0.9rem",
                label: "ทั้งหมด",
                  showAlways: true,
                  show: true
                },
            },
          },
        },
      },
      responsive: [{ breakpoint: 420, options: { chart: { height: 360 } } }],
    };


    var chart3 = new ApexCharts(document.querySelector("#genderPieCahrt"), options3);
        chart3.render();

JS;
$this->registerJS($js, View::POS_END);
?>
