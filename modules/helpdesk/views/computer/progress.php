<?php

?>

<div class="card">
            <div class="card-body">
                <div id="leaveChart"></div>
                <div class="d-flex">
                    <div class="flex-fill border-primary border-end" style="width: 129px;">
                        <div class="d-flex flex-column align-items-center justify-content-start">
                            <div class="position-relative">
                                <span class="h5">
                                    ดำเนินการ
                                </span>
                                <br>
                                <span class="text-muted mb-0">30 เรื่อง</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex flex-column align-items-center justify-content-start">
                            <div class="position-relative">
                                <span class="h5">
                                    เสร็จสิ้น
                                </span>
                                <br>
                                <span class="text-muted mb-0">70 เรื่อง</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


<?php
use yii\helpers\Url;
$url = Url::to(['/helpdesk/repair']);
$js = <<< JS


        var optionsleaveChart = {
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
                    size: '85%' // 
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
        labels: ['เป้าหมาย'],
        };

        var chart = new ApexCharts(document.querySelector("#leaveChart"), optionsleaveChart);
        chart.render();




JS;
$this->registerJS($js);
?>