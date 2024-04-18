<div class="card">
    <div class="card-body">
        <div id="positionNameChart"></div>
    </div>
</div>

<?php
use yii\helpers\Json;
use yii\web\View;

$title = [];
$value = [];

foreach ($dataProviderPositionName->getModels() as $model) {
    $title[] = $model['title'];
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
          text: 'ตำแหน่ง',
          style: {
          fontWeight:  'normal',
          fontFamily:  'prompt',
          color:  '#263238'
        },
        },

        // fill: {
        //         type: 'gradient',
        //         gradient: {
        //             shade: 'dark',
        //             type: 'vertical',
        //             shadeIntensity: 0.5,
        //             inverseColors: false,
        //             opacityFrom: 1,
        //             opacityTo: 0.8,
        //             stops: [0, 100]
        //         }
        //     },
        //     colors: ['#17ead9'],
        //     grid: {
        //         borderColor: "#40475d",
        //     },

        fill: {
          type: 'gradient',
          gradient: {
            type: 'vertical',
      shadeIntensity: 1,
      opacityFrom: 1,
      opacityTo: 0.8,
      colorStops: [
        {
          offset: 0,
          color: "#EB656F",
          opacity: 1
        },
       
        {
          offset: 100,
          color: "#95DA74",
          opacity: 1
        }
      ]
    }
        },

        plotOptions: {
          bar: {
            horizontal: false,
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
                offsetX: 0,
                offsetY: -10,
        },
        xaxis: {
          categories: $categories,

        }
        };

        var chart = new ApexCharts(document.querySelector("#positionNameChart"), options);
        chart.render();

JS;
$this->registerJS($js, View::POS_END);
?>
