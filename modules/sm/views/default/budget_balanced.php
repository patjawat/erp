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
    <div class="col-6">
        <?= $this->render('chart_order_accept') ?>
    </div>
    <div class="col-6">
        <?= $this->render('chart_order_success') ?>
    </div>
</div>
<?php
$js = <<< JS

    var orderBudgetOption = {
                  series: [
                    { name: "Start", data: [44, 55, 41, 25, 22, 56] },
                    { name: "End", data: [13, 23, 20, 60, 13, 16] },
                  ],
                  grid: {
                    show: !1,
                    padding: { left: 0, right: 10, bottom: -12, top: 0 },
                  },
                  yaxis: {
                    show: !1,
                    axisBorder: { show: !1 },
                    axisTicks: { show: !1 },
                    labels: { show: !1 },
                  },
                  xaxis: {
                    show: !1,
                    axisBorder: { show: !1 },
                    axisTicks: { show: !1 },
                    labels: { show: !1 },
                  },
                  chart: {
                    type: "bar",
                    height: 120,
                    parentHeightOffset: 0,
                    toolbar: { show: !1 },
                    stacked: !0,
                    stackType: "100%",
                  },
                  dataLabels: { enabled: !1 },
                  fill: { colors: ["#0EA5E9", "#e2e8f0"] },
                  plotOptions: {
                    bar: { borderRadius: 2, horizontal: !1, columnWidth: 8 },
                  },
                  legend: { show: !1 },
                };

                    var chart = new ApexCharts(document.querySelector("#orderBudget"), orderBudgetOption);
                        chart.render();
    JS;
$this->registerJS($js);
?>