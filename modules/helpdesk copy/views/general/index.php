<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

$this->title = 'งานซ่อมบำรุง';
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench fs-2"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ระบบงานซ่อมบำรุง

<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'helpdesk-container', 'timeout' => 5000, 'enablePushState' => true]); ?>

<div class="row">
    <div class="col-8">
        <?= $this->render('../default/box_summary', ['group' => 1]) ?>
        <div id="viewJob">
            <h6 class="text-center mt-5">กำลังโหลด...</h6>
        </div>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h4 class="card-title"><i class="fa-solid fa-triangle-exclamation"></i> ร้องขอ </h4>
                </div>
                <div id="viewUserRequestOrder"></div>
            </div>
        </div>

        <?= $this->render('../default/progress', ['repair_group' => 1]) ?>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">ปริมาณการมอบหมายงาน</h4>
                <div id="viewUserJob"></div>
            </div>
        </div>
        <div id="ViewRating"></div>
        <?php echo $this->render('../repair/view_rating', ['repair_group' => 1]) ?>
    </div>
</div>
<?php Pjax::end() ?>
<?php // $this->render('barchart') ?>


<?php
$urlAccept = Url::to(['/helpdesk/repair/list-accept', 'repair_group' => 1]);
$urlSummary = Url::to(['/helpdesk/repair/summary', 'repair_group' => 1]);
$urlUserRequestOrder = Url::to(['/helpdesk/repair/user-request-order', 'repair_group' => 1, 'status' => 1]);
$urlUserJob = Url::to(['/helpdesk/repair/user-job', 'repair_group' => 1, 'auth_item' => 'technician']);

$js = <<< JS

  getSummary();
  loadUserRequestOrder();
  loadUserJob();

  getJob();

  jQuery(document).on("pjax:end", function () {
      getJob();
      getSummary()
      loadUserRequestOrder();
      loadUserJob();

  });

  async function getJob()
  {
      await \$.ajax({
          type: "get",
          url: "$urlAccept",
          dataType: "json",
          success: function (res) {
              \$('#viewJob').html(res.content);
              console.log('load-job');
          }
      });
  }


   //แสดงรายการแจ้งซ่อม (ร้องขอ)
  async function loadUserRequestOrder()
  {
      await \$.ajax({
          type: "get",
          url: "$urlUserRequestOrder ",
          dataType: "json",
          success: function (res) {
              \$('#viewUserRequestOrder ').html(res.content);
          }
      });
  }

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
