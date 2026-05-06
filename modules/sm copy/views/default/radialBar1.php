<?php
use yii\helpers\Html;
?>
<style>
.apexcharts-title-text {
    font-weight: 300
}
</style>

<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <div>
                <h5>การจัดซื้อจัดจ้างแยกตามประเภทวัสดุ</h5>
            </div>
            <div>
                <div id="radialBar1"></div>
            </div>
        </div>
    </div>
</div>

<span class="text-center">
    <!-- ข้อมูลจัดซื้อจัดจ้าง แยกตามประเภทวัสดุ -->
</span>

<?php
use yii\web\View;
$js = <<< JS
var options = {
          series: [70],
          chart: {
          height: 150,
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
                    size: '70%' // 
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
                        offsetY: 0,
                        show: true,
                        color: '#888',
                        fontSize: '16px',
                        fontWeight:'300'
                    },
                    value: {
                        color: '#111',
                        // fontSize: '32px',
                        show: true
                    }
                }
            }
        },
        stroke: {
            lineCap: 'round'
        },
        labels: ['ร้อยละ'],
        };

        var chart = new ApexCharts(document.querySelector("#radialBar1"), options);
        chart.render();

JS;
$this->registerJS($js);
?>