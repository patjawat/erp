<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;
// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

?>
<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-between">
            <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> จำนวนหนังสือ (จำแนกตามวันที่ส่ง)</h6>
            <div class="mb-3">
            <?php echo $this->render('_search_year', ['model' => $model]); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div id="ChartSend"></div>

            </div>
            
        </div>
    </div>
</div>



<?php
 $query = $model->getChartSummary('send');

try {
  $chartSummary = [$query['m10'], $query['m11'], $query['m12'], $query['m1'], $query['m3'], $query['m3'], $query['m4'], $query['m5'], $query['m6'], $query['m7'], $query['m8'], $query['m9']];
} catch (\Throwable $th) {
  $chartSummary = [];
}

$data = Json::encode($chartSummary);


$js = <<< JS
  var chartSendOptions = {
    series: [
            { name: "จำนวน", data: $data },
          ],
              chart: {
              type: 'bar',
              height: 300,
              fontFamily: "Prompt, sans-serif",
              parentHeightOffset: 0,
                toolbar: { show: false }
            },
            colors: ['#0866ad', '#ff9800','#ffa73e'],
            plotOptions: {
                bar: {
                borderRadius: 4,
                distributed: false,
                columnWidth: '40%',
                endingShape: 'rounded',
                startingShape: 'rounded',
            },
            },
            grid: {
                strokeDashArray: 7,
                padding: {
                  top: -1,
                  right: 0,
                  left: -12,
                  bottom: 5
                }
            },
            dataLabels: {
              enabled: false
            },
            stroke: {
              show: true,
              width: 2,
              colors: ['transparent']
            },
            xaxis: {
              categories: ['ต.ต.','พ.ย.','ธ.ค.','ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.',],
              tickPlacement: 'on',
                labels: { show: true },
                axisTicks: { show: false },
                axisBorder: { show: false }
            },
            yaxis: { 
              show: true,
              tickAmount: 4,
              labels: {
                offsetX: -17,
                formatter: function (val) {
                return val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) // Format y-axis labels to 2 decimal places
            }
              },
              
              
              title: {
                text: '\$ (thousands)'
              }
            },
            fill: {
              opacity: 1
            },
            tooltip: {
              y: {
            formatter: function (val) {
                return  val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + " ครั้ง";  // Format tooltip with commas and 2 decimal places
            }
        }
            }
            };

            var chartSend = new ApexCharts(document.querySelector('#ChartSend'), chartSendOptions);
            chartSend.render();

  JS;
$this->registerJS($js, View::POS_END);
?>