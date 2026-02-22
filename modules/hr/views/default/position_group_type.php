<?php
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\View;

$positionTypeLabels = isset($positionTypeLabels) && is_array($positionTypeLabels) ? $positionTypeLabels : [];
$numCategories = count($positionTypeLabels) ?: 7;
if (empty($positionTypeLabels)) {
    for ($i = 1; $i <= 7; $i++) {
        $positionTypeLabels[] = 'ประเภท ' . $i;
    }
}

$models = $dataProviderWorkGroup->getModels();
$series = [];
foreach ($models as $row) {
    $data = [];
    for ($i = 1; $i <= 7; $i++) {
        $key = '_position' . $i;
        $val = ArrayHelper::getValue($row, $key, 0);
        $data[] = (int) $val;
    }
    $data = array_slice($data, 0, $numCategories);
    $groupName = ArrayHelper::getValue($row, '_groupname', 'ไม่ระบุ');
    $series[] = [
        'name' => $groupName,
        'data' => $data,
    ];
}

$categories = array_slice($positionTypeLabels, 0, $numCategories);
$categoriesJson = Json::encode($categories);
$seriesJson = Json::encode($series);
?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <?php if (empty($series)): ?>
            <p class="text-muted mb-0 small">ไม่มีข้อมูลกลุ่มงานหรือประเภทการจ้างในระบบ</p>
            <div id="positionGroupType" class="mt-2"></div>
        <?php else: ?>
            <div id="positionGroupType"></div>
        <?php endif; ?>
    </div>
</div>
<?php
$js = <<< JS
        var options = {
          series: $seriesJson,
          chart: {
            type: 'bar',
            height: 350,
            stacked: false
          },
          title: {
            text: 'จำแนกตามกลุ่มงานและประเภทการจ้าง',
            style: {
              fontWeight: 'normal',
              fontFamily: 'prompt',
              color: '#263238'
            }
          },
          plotOptions: {
            bar: {
              horizontal: false,
              columnWidth: '70%',
              endingShape: 'rounded'
            }
          },
          dataLabels: {
            enabled: true,
            formatter: function(val) {
              return val > 0 ? val : '';
            }
          },
          stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
          },
          xaxis: {
            categories: $categoriesJson
          },
          yaxis: {
            title: {
              text: 'จำนวนคน'
            }
          },
          fill: { opacity: 1 },
          tooltip: {
            y: {
              formatter: function (val) {
                return val + ' คน';
              }
            }
          },
          legend: {
            position: 'top',
            horizontalAlign: 'left'
          }
        };
        var chart = new ApexCharts(document.querySelector("#positionGroupType"), options);
        chart.render();
JS;
$this->registerJS($js, View::POS_END);
?>
