<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p style="margin-bottom:75px;">เงินงบประมาณ</p>
                    <i class="fa-solid fa-wallet fs-1 text-secondary"></i>
                </div>
                <div class="row">
                    <div class="col-6">
                        <span class="h5 fw-semibold">$800000000.6k</span>
                        <p class="fw-lighter">ใช้จ่ายไปแล้วประมาณ 25% ของงบประมาณประจำปี</p>
                    </div>
                    <div class="col-6">
                        <div id="orderBudget"></div>
                    </div>
                </div>

            </div>
        </div>

    </div>
   -->
    <!-- <div class="col-6">
        <?php //  $this->render('chart_order_success') ?>
    </div> -->
</div>
<?php
$js = <<< JS


    var orderBudgetOption = {
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

                    var chart = new ApexCharts(document.querySelector("#orderBudget"), orderBudgetOption);
                        chart.render();
    JS;
$this->registerJS($js);
?>