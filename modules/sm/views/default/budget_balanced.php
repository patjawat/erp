<?php
$sql = "SELECT FORMAT(IFNULL(sum(i.price * i.qty),0),2) as total FROM orders o 
INNER JOIN orders as i ON i.category_id = o.id AND i.name = 'order_item'
WHERE o.thai_year = :thai_year";
$query = Yii::$app->db->createCommand($sql)
->bindValue(':thai_year','2568')
->queryScalar();
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
                    <span class="h5 fw-semibold"><?=$query?> บาท</span>
                    <!-- <p class="fw-lighter">ใช้จ่ายไปแล้วประมาณ 25% ของงบประมาณประจำปี</p> -->
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
       
          }
  });

  JS;
$this->registerJS($js);