<?php
use yii\helpers\Html;
?>
<style>
    .apexcharts-title-text {
  font-weight: 300
}
</style>
<div id="leaveChart"></div>

<div class="d-flex">
    <div class="flex-fill border-primary border-end" style="width: 129px;">
        <div class="d-flex flex-column align-items-center justify-content-start">
            <div class="position-relative">
                <span class="h5">
                    ใช้ไปแล้ว
                </span>
                <br>
                <span class="text-muted mb-0">11 วัน</span>
            </div>
        </div>
    </div>
    <div class="flex-fill">
    <div class="d-flex flex-column align-items-center justify-content-start">
            <div class="position-relative">
                <span class="h5">
                    วันลาคงเหลือ
                </span>
                <br>
                <span class="text-muted mb-0">10 วัน</span>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    <?=Html::a('<i class="fa-solid fa-plus"></i> ยื่นใบลา',['/me'],['class' => 'btn btn-primary round shadow'])?>
</div>
<?php
use yii\web\View;
$js = <<< JS
var options = {
          series: [70],
          chart: {
          height: 250,
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
                    size: '85%' // bar 굵기
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
        labels: ['วันลาสะสม'],
        };

        var chart = new ApexCharts(document.querySelector("#leaveChart"), options);
        chart.render();

JS;
$this->registerJS($js);
?>