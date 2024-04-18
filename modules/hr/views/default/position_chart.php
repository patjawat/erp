<div class="card">
    <div class="card-body">
        <div id="educationChart"></div>
    </div>
</div>

<?php
use yii\helpers\Url;
use yii\web\View;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$title = [];
$value = [];


foreach ($dataProviderPositionType->getModels() as $model)
{
  $title[] = $model->positionTypeName();
  $value[] = $model['cnt'];
}

?>
<?php
$data = Json::encode($value);
$categories = Json::encode($title);
$js = <<< JS


var options = {
  series: [{
				name: 'จำนวน',
				data: $data,
			}],
          chart: {
          type: 'bar',
          height: 350,
          events: {
        click(event, chartContext, config) {
            console.log(config.config.series[config.seriesIndex])
            console.log(config.config.series[config.seriesIndex].name)
            console.log(config.config.series[config.seriesIndex].data[config.dataPointIndex])
        }
    }
        },
        title: {
          text: 'สัดส่วนจำนวนบุคลากรจำแนกตามประเภท',
          style: {
          fontWeight:  'normal',
          fontFamily:  'prompt',
          color:  '#263238'
        },
        },
        fill: {
          type: 'gradient',
          gradient: {
            shade: 'dark',
            gradientToColors: [ '#ad5389'],
            shadeIntensity: 1,
            type: 'horizontal',
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100, 100, 100]
          },
        },

        plotOptions: {
          bar: {
            horizontal: true,
            borderRadius: 8,
            barHeight: '80%',
            dataLabels: {
            position: 'top'
            }
          },

        },
        dataLabels: {
          enabled: true,
          style: {
                    fontSize: '1em',
                    textOutline: 'none',
                    colors: ['#333'],

                },
                offsetX: 20,
                offsetY: 10,
        },
        xaxis: {
          categories: $categories,

        }
        };

        var chart = new ApexCharts(document.querySelector("#educationChart"), options);
        chart.render();

JS;
$this->registerJS($js, View::POS_END);
?>
