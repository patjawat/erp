
<!-- <div class="card">
    <div class="card-body">
        <div id="chart1"></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="chart2"></div>
    </div>
</div> -->

<div class="card">
    <div class="card-body">
        <div id="chart3"></div>
    </div>
</div>


<?php
use yii\web\View;

$genB = $dataProviderGenB->getTotalCount();
$genX = $dataProviderGenX->getTotalCount();
$genY = $dataProviderGenY->getTotalCount();
$genZ = $dataProviderGenZ->getTotalCount();
$js = <<< JS



var options3 = {
        chart: { height: 320, parentHeightOffset: 0, type: "donut" },
               labels: ['GenB', 'GenX', 'GenY', 'GenZ'],
      series: [$genB , $genX,$genY, $genZ ],
      stroke: { width: 0 },
      dataLabels: {
        enabled: !1,
        formatter: function (e, t) {
          return parseInt(e) + "%";
        },
      },
      legend: {
        position: "left",
        show: !0,
        offsetY: 10,
        markers: { width: 8, height: 8, offsetX: -3 },
        itemMargin: { horizontal: 15, vertical: 5 },
        fontSize: "13px",
        fontFamily: "prompt",
        fontWeight: 400,
      },
    //   tooltip: { theme: o },
    title: {
          text: 'จำแนกตามช่วงวัย',
          style: {
          fontWeight:  'normal',
          fontFamily:  'prompt',
          color:  '#263238'
        },
        },
        grid: { padding: { top: 0,left:100 } },
      plotOptions: {
        pie: {
          donut: {
            size: "85%",
            labels: {
              show: !0,
              value: {
                fontSize: "26px",
                fontFamily: "prompt",
                // color: t,
                fontWeight: 500,
                offsetY: -30,
                formatter: function (e) {
                  return parseInt(e) + "%";
                },
              },
              name: { offsetY: 20, fontFamily: "prompt" },
              total: {
                      show: !0,
                fontSize: "0.9rem",
                label: "ทั้งหมด",
                  showAlways: true,
                  show: true
                },
            },
          },
        },
      },
      responsive: [{ breakpoint: 420, options: { chart: { height: 360 } } }],
    };


    var chart3 = new ApexCharts(document.querySelector("#chart3"), options3);
        chart3.render();

    //     var options3 = {
    //     chart: { height: 420, parentHeightOffset: 0, type: "donut" },
    //     labels: ['GenB', 'GenX', 'GenY', 'GenZ'],
    //   series: [$genB , $genX,$genY, $genZ ],
    // //   colors: [
    // //     s.donut.series1,
    // //     s.donut.series2,
    // //     s.donut.series3,
    // //     s.donut.series4,
    // //   ],
    //   stroke: { width: 0 },
    //   dataLabels: {
    //     enabled: !1,
    //     formatter: function (e, t) {
    //       return parseInt(e) + "%";
    //     },
    //   },
    //   legend: {
    //     show: !0,
    //     position: "left",
    //     offsetY: 10,
    //     markers: { width: 8, height: 8, offsetX: -3 },
    //     itemMargin: { horizontal: 15, vertical: 5 },
    //     fontSize: "13px",
    //     fontFamily: "prompt",
    //     fontWeight: 400,
    //   },
    // //   tooltip: { theme: o },
    //   grid: { padding: { top: 15 } },
    //   plotOptions: {
    //     pie: {
    //       donut: {
    //         size: "75%",
    //         labels: {
    //           show: !0,
    //           value: {
    //             fontSize: "26px",
    //             fontFamily: "prompt",
    //             // color: t,
    //             fontWeight: 500,
    //             offsetY: -30,
    //             formatter: function (e) {
    //               return parseInt(e) + "%";
    //             },
    //           },
    //           name: { offsetY: 20, fontFamily: "prompt" },
    //           total: {
    //                   show: !0,
    //             fontSize: "0.9rem",
    //             label: "ทั้งหมด",
    //               showAlways: true,
    //               show: true
    //             }
    //         //   total: {
    //         //     show: !0,
    //         //     fontSize: "0.9rem",
    //         //     label: "ทั้งหมด",
    //         //     // color: r,
    //         //     formatter: function (e) {
    //         //       return "30%";
    //         //     },
    //         //   },
    //         },
    //       },
    //     },
    //   },
    //   responsive: [{ breakpoint: 420, options: { chart: { height: 360 } } }],
    // };


    // var chart3 = new ApexCharts(document.querySelector("#chart3"), options3);
    //     chart3.render();
JS;
$this->registerJS($js, View::POS_END);
?>
