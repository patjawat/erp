<div class="card-body">
                อัตราส่วนมูลค่าการจัดซื้อจัดจ้าง
                <div id="donutChart1"></div>
            </div>
<?php
$js = <<<  JS


var optionsDonutChart1 = {
          labels: ['มูลค่าการจัดซื้อวัสดุ', 'มูลค่าการจัดซื้อครุภัณฑ์', 'มูลค่าการงานจ้างเหมา', 'มูลค่าการงานก่อสร้าง'],
          series: [1048, 580, 484, 300],        
            chart: {
                  type: 'donut',
                   height: 350,
      width: '100%',
      // offsetX: 50
                  fontFamily: 'kanit,sans-serif',
                },
                plotOptions: {
      pie: {
        donut: {
          size: '90%',
          dataLabels: {
            enabled: false
          },
          labels: {
            show: true,
            name: {
              show: true,
              offsetY: 38,
              formatter: () => 'Completed'
            },
            value: {
              show: true,
              fontSize: '48px',
              fontFamily: 'Open Sans',
              fontWeight: 300,
              color: '#ffffff',
              offsetY: -10
            },
          }
        }
      },
    },
                
                responsive: [{
                  breakpoint: 480,
                  options: {
                    chart: {
                      width: 10
                    },
                    legend: {
                      position: 'bottom'
                    }
                  }
                }]
                };

                var chart = new ApexCharts(document.querySelector("#donutChart1"), optionsDonutChart1);
                chart.render();

JS;
$this->registerJS($js);
?>