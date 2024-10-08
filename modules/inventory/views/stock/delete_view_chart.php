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


  var orderOptions = {
    series: [
            { name: "เบิก", data: $chartSummeryIn },
            { name: "จ่าย", data: $chartSummeryOut },
          ],
              chart: {
              type: 'bar',
              height: 300,
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
            yaxis: { show: true,
    tickAmount: 4,
    labels: {
      offsetX: -17,
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
                  return "\$ " + val + " บาท"
                }
              }
            }
            };

            var chart = new ApexCharts(document.querySelector("#inventoryCharts"), orderOptions);
            chart.render();
      

  // var options = {
  //     plotOptions: {
  //           bar: { 
  //             horizontal: false,
  //              columnWidth: "50%", 
  //              endingShape: "rounded",
  //              startingShape: 'rounded',
  //              borderRadius: 10 
  //             },
  //         },
  //         series: [
  //           { name: "เข้า", data: $chartSummeryIn },
  //           { name: "ออก", data: $chartSummeryOut },
  //         ],
  //         colors: ["#0966ad", "#EA5455"],
  //         chart: {
  //           type: "bar",
  //           height: 380,
  //           stacked: true,
  //           zoom: { enabled: true },
  //         },
  //         responsive: [
  //           {
  //             breakpoint: 280,
  //             options: { legend: { position: "top", offsetY: 0 } },
  //           },
  //         ],

  //         xaxis: {
  //           categories: [
  //             "ต.ค.",
  //             "พ.ย.",
  //             "ธ.ค.",
  //             "ม.ค.",
  //             "ก.พ.",
  //             "มี.ค.",
  //             "เม.ย.",
  //             "พ.ย.",
  //             "มิ.ย.",
  //             "ก.ค.",
  //             "ส.ค.",
  //             "ก.ย.",
  //           ],
  //         },
  //         legend: { position: "bottom"},
  //         fill: { opacity: 1 },
  //       };
  //       var chart = new ApexCharts(
  //         document.querySelector("#inventoryCharts"),
  //         options
  //       );
  //       chart.render();    
  JS;
$this->registerJS($js, View::POS_END);
?>