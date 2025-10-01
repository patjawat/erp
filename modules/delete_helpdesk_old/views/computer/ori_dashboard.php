<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'งานซ่อมบำรุง';
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench fs-2"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ระบบงานซ่อมบำรุง
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'helpdesk-container', 'timeout' => 5000, 'enablePushState' => true]); ?>

<?php //  $this->render('../repair/summary_status', ['model' => $searchModel]) ?>

<div class="row">
    <div class="col-8">
        <?php // $this->render('../repair/_chart_summary',[ 'searchModel' => $searchModel,])?>
    </div>
    <div class="col-4"> <?= $this->render('../default/progress', ['repair_group' => $searchModel->repair_group]) ?></div>
</div>
<?php // $this->render('../repair/index', ['searchModel' => $searchModel,'dataProvider' => $dataProvider])?>
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">ปริมาณการมอบหมายงาน</h4>
                <div id="viewUserJob"></div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <?php // echo $this->render('../repair/view_rating', ['repair_group' =>  $searchModel->repair_group]) ?>
    </div>
</div>
<?php Pjax::end() ?>
<?php
$urlSummary = Url::to(['/helpdesk/repair/summary', 'repair_group' =>  $searchModel->repair_group]);
$urlUserJob = Url::to(['/helpdesk/repair/user-job', 'repair_group' => 1, 'auth_item' => 'technician']);

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
      labels: ['ร้องขอ', 'รับเรื่อง', 'ดำเนินการ', 'เสร็จสิ้น'],
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