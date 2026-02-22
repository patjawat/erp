<?php
use yii\helpers\Json;
use app\components\SiteHelper;

$totalCount = isset($totalCount) && (int) $totalCount > 0 ? (int) $totalCount : 1;
$ageCategories = [];
$ageRangeF = [];
$ageRangeM = [];
foreach ($dataProviderGender->getModels() as $age) {
    $ageCategories[] = $age['_age_generation'];
    $ageRangeF[] = (float) $age['_female'];
    $ageRangeM[] = (float) $age['_male'];
}

$companyName = SiteHelper::getInfo()["company_name"];
$ageRangeMale = Json::encode($ageRangeM);
$ageRangeFeMale = Json::encode($ageRangeF);
$categories = Json::encode($ageCategories);
$totalCountJs = (int) $totalCount;
?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div id="chart"></div>
    </div>
</div>
<?php
$js = <<< JS
        var totalCount = $totalCountJs;
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
            borderRadiusApplication: 'end',
            borderRadiusWhenStacked: 'all',
            horizontal: true,
            barHeight: '80%',
          },
        },
        dataLabels: {
          enabled: true,
          formatter: function(val, opt) {
            return Math.abs(totalCount ? Math.round(val * 100 / totalCount) : 0) + "%";
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
