<div id="orderChartColumn"></div>
<?php
$js = <<< JS

  var orderOptions = {
            series: [{
            name: 'ขอซื้อ-ขอจ้าง',
            data: [44, 55, 57, 56, 61, 58, 63, 60, 66,43,34,54]
          }, {
            name: 'เห็นชอบ',
            data: [76, 85, 101, 98, 87, 105, 91, 114, 94,43,12,78]
          }, {
            name: 'อนุมัติ',
            data: [35, 41, 36, 26, 45, 48, 52, 53, 41,54,55,12]
          }, {
            name: 'ดำเนินการแล้ว',
            data: [31, 14, 63, 62, 54, 84, 25, 35, 11,76,54,23]
          }],
            chart: {
            type: 'bar',
            height: 350,
            parentHeightOffset: 0,
              toolbar: { show: false }
          },
          colors: ['#5655b7', '#3cebb4','#ffa73e'],
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
            categories: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.','ต.ต.','พ.ย.','ธ.ค.'],
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

          var chart = new ApexCharts(document.querySelector("#orderChartColumn"), orderOptions);
          chart.render();

  JS;
$this->registerJS($js);
?>