<?php

use yii\helpers\Json;

 $data = [];
 $categorise = [];
 foreach ($model->SummaryBudgetType() as $item) {
  $data[] = $item['total'];
  $categorise[] = $item['title'];
  // echo  '<p>'.$item['total'].'</p>';
 }
 $seriesSummary =  [
  'data' => $data,
  'categorise' => $categorise
 ];
 $dataSummary = Json::encode($data);
 $categoriseSummary = Json::encode( $categorise);

//  echo "<pre>";
//  print_r( $seriesSummary);
//  echo "</pre>";

?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between">
                    <p style="margin-bottom:0px;">รวมเป็นเงินทั้งสิ้น</p>
                    <i class="fa-solid fa-wallet fs-1 text-secondary"></i>
                </div>
                <div class="">
                    <span class="h5 fw-semibold"><?=$model->SummaryTotal()?> บาท</span>
                    <!-- <p class="fw-lighter">ใช้จ่ายไปแล้วประมาณ 25% ของงบประมาณประจำปี</p> -->
                </div>
                <div id="orderBudget"></div>
            </div>
        </div>

    </div>
</div>
<?php
use yii\helpers\Url;
// $url = Url::to('/sm/default/budget-chart');
$js = <<< JS

    var orderBudgetOption = {
            series: [{
            data: $dataSummary
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
            categories:  $categoriseSummary,
          },
          yaxis: { show: true,
              tickAmount: 4,
              // labels: {
                // },
                labels: {
                  offsetX: -17,
                formatter: function (value) {
                  return value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
              },
              title: {
                // text: '\$ (thousands)'
              }
            },
            tooltip: {
              y: {
                formatter: function (val) {
                  return val.toFixed(2) + " บาท"
                }
              }
            }
          };

                    var chart = new ApexCharts(document.querySelector("#orderBudget"), orderBudgetOption);
                        chart.render();
       


  JS;
$this->registerJS($js);