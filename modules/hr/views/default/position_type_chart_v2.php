
<style>

</style>
<div class="card">
    <div class="card-body">
        <div id="positionTypeBarV2"></div>
    </div>
</div>

<?php
use yii\helpers\Json;
use yii\web\View;
use yii\helpers\Url;

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
  
  var options = {
          series: [76, 67, 61, 90],
          chart: {
          height: 390,
          type: 'radialBar',
        },
        plotOptions: {
          radialBar: {
            offsetY: 0,
            startAngle: 0,
            endAngle: 270,
            hollow: {
              margin: 5,
              size: '30%',
              background: 'transparent',
              image: undefined,
            },
            dataLabels: {
              name: {
                show: false,
              },
              value: {
                show: false,
              }
            }
          }
        },
        colors: ['#1ab7ea', '#0084ff', '#39539E', '#0077B5'],
        labels: ['Vimeo', 'Messenger', 'Facebook', 'LinkedIn'],
        legend: {
          show: true,
          floating: true,
          fontSize: '16px',
          position: 'left',
          offsetX: 16,
          offsetY: 15,
          labels: {
            useSeriesColors: true,
          },
          markers: {
            size: 0
          },
          formatter: function(seriesName, opts) {
            return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex]
          },
          itemMargin: {
            vertical: 3
          }
        },
        responsive: [{
          breakpoint: 480,
          options: {
            legend: {
                show: false
            }
          }
        }]
        };

        var chart = new ApexCharts(document.querySelector("#positionTypeBarV2"), options);
        chart.render();


JS;
$this->registerJS($js,View::POS_END);
?>
