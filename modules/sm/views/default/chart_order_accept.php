<div class="card">
            <div class="card-body">
                <div id="viewOrderAccept"></div>
            </div>
        </div>
<?php
$js = <<< JS


    var orderOptions = {
              series: [30],
              chart: {
              height: 220,
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
                        size: '75%' // 
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
                            fontSize: '12px',
                            show: true
                        }
                    }
                }
            },
            stroke: {
                lineCap: 'round'
            },
            labels: ['ดำเนินการ'],
            };

            var chartOrder = new ApexCharts(document.querySelector("#viewOrderAccept"), orderOptions);
            chartOrder.render();

    JS;
$this->registerJS($js);
?>
