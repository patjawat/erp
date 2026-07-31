<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\modules\helpdesk2\models\Helpdesk;
$this->title = $title;
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = 'ภาพรวม';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">

        <?=$icon?> ภาพรวม<?= $this->title; ?>
  </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/helpdesk2/menu',['active' => $active]) ?>
<?php $this->endBlock(); ?>


<?= $this->render('@app/modules/helpdesk2/views/service/summary_status', ['model' => $searchModel]) ?>
<div class="row g-3">
    <div class="col-12 col-lg-8">
        <?php echo $this->render('@app/modules/helpdesk2/views/service/_chart_summary', ['searchModel' => $searchModel]) ?>
    </div>
    <div class="col-12 col-lg-4">
        <?php echo $this->render('@app/modules/helpdesk2/views/service/progress', ['model' => $searchModel]) ?>
    </div>
</div>

<?php // echo $this->render('@app/modules/helpdesk/views/repair/list_order', ['searchModel' => $searchModel,'dataProvider' => $dataProvider])?>

<div class="row g-3 mt-1">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h4 class="card-title mb-3">ปริมาณการมอบหมายงาน</h4>
                <div id="viewUserJob"></div>
                <?php echo $this->render('@app/modules/helpdesk2/views/service/user_job', ['searchModel' => $searchModel]) ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <?php echo $this->render('@app/modules/helpdesk2/views/service/view_rating', ['repair_group' => $searchModel->repair_group]) ?>
    </div>
</div>

<?php
$urlSummary = Url::to(['/helpdesk/repair/summary', 'repair_group' =>  $searchModel->repair_group]);
$urlUserJob = Url::to(['/helpdesk/repair/user-job', 'repair_group' => $searchModel->repair_group, 'auth_item' => 'technician']);
$repairStatusOptions = Helpdesk::repairStatusOptions();
$statusChartLabels = json_encode([
    $repairStatusOptions['pending'],
    $repairStatusOptions['receive'],
    $repairStatusOptions['in_progress'],
    $repairStatusOptions['success'],
], JSON_UNESCAPED_UNICODE);

$js = <<< JS

  getSummary();
  loadUserJob();


  jQuery(document).on("pjax:end", function () {
      getJob();
      getSummary()
      loadUserRequestOrder();
      loadUserJob();

  });


   //แสดงปริมาณการมอบหมายงาน
   async function loadUserJob()
  {
      await \$.ajax({
          type: "get",
          url: "$urlUserJob",
          dataType: "json",
          success: function (res) {
              \$('#viewUserJob ').html(res.content);
          }
      });
  }


  async function getSummary()
  {
      await \$.ajax({
          type: "get",
          url: "$urlSummary",
          dataType: "json",
          success: function (res) {
              console.log(res);
              \$.each( res, function( key, i ) {
                  // console.log(value.code);
                  \$('#status'+i.code).text(i.total)
                  });
          }
      });
  }


  const options = {
            series: [44, 55, 41, 17],
      chart: {
        type: 'donut',
      },
      plotOptions: {
        pie: {
          // startAngle: 10,
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
                fontWeight: 500,
                color: '#ffffff',
              },
            }
          }
        },
      },
      dataLabels: {
        enabled: false
      },
      labels: {$statusChartLabels},
      legend: {
      },
      fill: {
        type: 'solid',
        colors: ['#8BD742', '#BCC1C8', '#78AEFF', '#F74D52']
      },
      stroke: {
        width: 0
      },
      colors: ['#8BD742', '#BCC1C8', '#78AEFF', '#F74D52']
  };

  var chart = new ApexCharts(document.querySelector("#workChart"), options);
  chart.render();


  JS;
$this->registerJS($js, View::POS_READY);
?>
