<div class="card">
            <div class="card-body">
                <div id="viewOrderAccept"></div>
            </div>
        </div>
<?php
$js = <<< JS


    var orderOptions = {
          series: [{
          data: [400, 430, 448, 470, 540, 580, 690, 1100, 1200, 1380]
        }],
          chart: {
          type: 'bar',
          height: 350
        },
        plotOptions: {
          bar: {
            borderRadius: 4,
            borderRadiusApplication: 'end',
            horizontal: true,
          }
        },
        dataLabels: {
          enabled: false
        },
        xaxis: {
          categories: ['South Korea', 'Canada', 'United Kingdom', 'Netherlands', 'Italy', 'France', 'Japan',
            'United States', 'China', 'Germany'
          ],
        }
        };

            var chartOrder = new ApexCharts(document.querySelector("#viewOrderAccept"), orderOptions);
            chartOrder.render();

    JS;
$this->registerJS($js);
?>
