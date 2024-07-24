<?php
use yii\helpers\Html;
?>
<style>
    .apexcharts-title-text {
  font-weight: 300
}
</style>
<div id="leaveChart"></div>
<div class="d-flex justify-content-center">

    <span class="h5 text-center">
        ออกคำสั่งซืื้อ
    </span>
</div>

<?php
use yii\web\View;
$js = <<< JS
var options = {
          series: [70],
          chart: {
          height: 180,
          fontFamily: 'Prompt, sans-serif',
          type: 'radialBar',
          dropShadow: {
        enabled: false,
        enabledOnSeries: undefined,
        top: 0,
        left: 0,
        blur: 3,
        color: '#000',
        opacity: 0.35
    }
        },
        plotOptions: {
            radialBar: {
                hollow: {
                    margin: 15,
                    size: '80%' // 
                },
                track: {
                dropShadow: {
                    enabled: true,
                    top: 2,
                    left: 5,
                    blur: 10,
                    opacity: 0.05,
                }
            },
                dataLabels: {
                    showOn: 'always',
                    name: {
                        offsetY: -10,
                        show: true,
                        color: '#888',
                        fontSize: '16px',
                        fontWeight:'300'
                    },
                    value: {
                        color: '#111',
                        fontSize: '32px',
                        show: true
                    }
                }
            }
        },
        stroke: {
            lineCap: 'round'
        },
        labels: ['ความคืบหน้า'],
        };

        var chart = new ApexCharts(document.querySelector("#leaveChart"), options);
        chart.render();

JS;
$this->registerJS($js);
?>