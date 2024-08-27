<?php

?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <p style="margin-bottom:0px;">เงินงบประมาณ</p>
                    <i class="fa-solid fa-wallet fs-1 text-secondary"></i>
                </div>
                <div class="">
                        <span class="h5 fw-semibold">$800000000.6k</span>
                        <p class="fw-lighter">ใช้จ่ายไปแล้วประมาณ 25% ของงบประมาณประจำปี</p>
                    </div>
                    <div id="orderBudget"></div>

            </div>
        </div>

    </div>
</div>
<?php
use yii\helpers\Url;
$url = Url::to('/sm/default/budget-chart');
$js = <<< JS

  $.ajax({
    type: "get",
    url: "$url",
    dataType: "json",
    success: function (res) {

    var orderBudgetOption = {
            series: [{
            data: res.data
          }],
            chart: {
            type: 'bar',
            height: 350
          },
          plotOptions: {
            bar: {
              borderRadius: 8,
              borderRadiusApplication: 'end',
              horizontal: true,
            }
          },
          dataLabels: {
            enabled: false
          },
          xaxis: {
            categories: res.categorise,
          }
          };

                    var chart = new ApexCharts(document.querySelector("#orderBudget"), orderBudgetOption);
                        chart.render();
       
          }
  });

  JS;
$this->registerJS($js);