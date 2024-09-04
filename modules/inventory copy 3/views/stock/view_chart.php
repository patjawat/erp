
<?php
use yii\helpers\Url;
use yii\web\View;
use yii\helpers\Json;
// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);
?>
  <div id="inventoryCharts"></div>

<?php
$chartSummeryIn = Json::encode($chartSummary['in']);
$chartSummeryOut = Json::encode($chartSummary['out']);
$js = <<< JS
  // getPendingOrder()
  // getlistOrderRequest()


  var options = {
      plotOptions: {
            bar: { 
              horizontal: false,
               columnWidth: "50%", 
               endingShape: "rounded",
               startingShape: 'rounded',
               borderRadius: 10 
              },
          },
          series: [
            { name: "เข้า", data: $chartSummeryIn },
            { name: "ออก", data: $chartSummeryOut },
          ],
          colors: ["#0966ad", "#EA5455"],
          chart: {
            type: "bar",
            height: 380,
            stacked: true,
            zoom: { enabled: true },
          },
          responsive: [
            {
              breakpoint: 280,
              options: { legend: { position: "top", offsetY: 0 } },
            },
          ],

          xaxis: {
            categories: [
              "ต.ค.",
              "พ.ย.",
              "ธ.ค.",
              "ม.ค.",
              "ก.พ.",
              "มี.ค.",
              "เม.ย.",
              "พ.ย.",
              "มิ.ย.",
              "ก.ค.",
              "ส.ค.",
              "ก.ย.",
            ],
          },
          legend: { position: "bottom"},
          fill: { opacity: 1 },
        };
        var chart = new ApexCharts(
          document.querySelector("#inventoryCharts"),
          options
        );
        chart.render();    
  JS;
$this->registerJS($js, View::POS_END);
?>