<?php
use yii\helpers\Url;
use yii\helpers\Json;
use app\components\SiteHelper;
?>
<div id="chart"></div>
<!-- <div id="chart2"></div> -->
  <?php
   $ageCategories = [];
   $ageRangeF = [];
   $ageRangeM = [];
   $ageTotal = [];
          foreach ($dataProviderGender->getModels() as $age) {
            $ageCategories[] = $age['_age_generation'];
            $ageTotal[] = $age['cnt'];
            // $ageRangeF[] = round((float)$age['f_percen'],2);
            // $ageRangeM[] = round((float)$age['m_percen'],2);
            $ageRangeF[] = (float) $age['_female'];
            $ageRangeM[] = (float) $age['_male'];

        }
  
  ?>

<?php

$companyName = SiteHelper::getInfo()["company_name"];
$ageRangeMale = Json::encode($ageRangeM);
$ageRangeFeMale = Json::encode($ageRangeF);
$categories = Json::encode($ageCategories);
$js = <<< JS
// var options = {
//           series: [{
//           name: 'ชาย',
//           // borderRadius: 8,
//           data:$ageRangeMale
//         },
//         {
//           name: 'หญิง',
//           data:$ageRangeFeMale
//         }
//         ],
//           chart: {
//           type: 'bar',
//           height: 440,
//           stacked: true,
        
//         },
//         colors: ['#0866ad', '#B0578D'],
//         plotOptions: {
//           bar: {
//             horizontal: true,
//             borderRadius: 8,
//             barHeight: '100%',
//             dataLabels: {
//             position: 'top',
            
//             }
//           },

//         },
//         dataLabels: {
//         enabled: true,
//         formatter: function(val, opt) {
//     //   return opt.w.globals.labels[opt.dataPointIndex] + ":  " + val
//       return Math.abs(Math.round(val*100 / 264) ) + "%"
//   },
//         style: {
//             colors: ['#333']
//         },
//          offsetX: 3
//   },
//         stroke: {
//           width: 1,
//           colors: ["#fff"]
//         },

//         grid: {
//           xaxis: {
//             lines: {
//               show: false
//             }
//           },
//           yaxis: {
//             lines: {
//               show: false
//             }
//           },
//         },
//         yaxis: {
//           min: -50,
//           max: 50,
//           title: {
//             text: 'ช่วงของอายุ',
//           },
//         },
//         tooltip: {
//           shared: false,
//           x: {
//             formatter: function (val) {
//               return val
//             }
//           },
//           y: {
//             formatter: function (val) {
//               return Math.abs(val)
//             }
//           }
//         },
//         title: {
//           text: 'ประชากร$companyName',
//           style: {
//           fontWeight:  'normal',
//           fontFamily:  'prompt',
//           color:  '#263238'
//         },
//         },
//         xaxis: {
//           categories:$categories,
//           title: {
//             // text: 'Percent'
//           },
//           labels: {
//             formatter: function (val) {
//               return Math.abs(Math.round(val)) + "%"
//             },
            
//           }
//         },
//         };

//         var chart = new ApexCharts(document.querySelector("#chart"), options);
//         chart.render();


        var options = {
          series: [{
            name: 'ชาย',
          data:$ageRangeMale
        },
        {
          name: 'หญิง',
          data:$ageRangeFeMale
        }
        ],
          chart: {
          type: 'bar',
          height: 340,
          stacked: true
        },
        colors: ['#008FFB', '#FF4560'],
        plotOptions: {
          bar: {
            borderRadius: 5,
            borderRadiusApplication: 'end', // 'around', 'end'
            borderRadiusWhenStacked: 'all', // 'all', 'last'
            horizontal: true,
            barHeight: '80%',
          },
        },
               dataLabels: {
        enabled: true,
        formatter: function(val, opt) {
      // return opt.w.globals.labels[opt.dataPointIndex] + ":  " + val
      return Math.abs(Math.round(val*100 / 264) ) + "%"
  },
  },
        stroke: {
          width: 1,
          colors: ["#fff"]
        },
        
        grid: {
          xaxis: {
            lines: {
              show: false
            }
          }
        },
        yaxis: {
          stepSize: 1
        },
        tooltip: {
          shared: false,
          x: {
            formatter: function (val) {
              return val
            }
          },
          y: {
            formatter: function (val) {
              return Math.abs(val) + "%"
            }
          }
        },
        title: {
          text: 'ประชากร$companyName',
        },
        xaxis: {

          categories:$categories,
          // categories: ['85+', '80-84', '75-79', '70-74', '65-69', '60-64', '55-59', '50-54',
          //   '45-49', '40-44', '35-39', '30-34', '25-29', '20-24', '15-19', '10-14', '5-9',
          //   '0-4'
          // ],
          title: {
            text: 'Percent'
          },
          labels: {
            formatter: function (val) {
              return Math.abs(Math.round(val)) + "%"
            }
          }
        },
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();

JS;
$this->registerJS($js);
?>
