<?php
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\Html;
use yii\web\View;
$this->title = "งานซ่อมบำรุง";
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench fs-2"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ระบบงานซ่อมบำรุง

<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'helpdesk-container','timeout' => 5000 ]); ?>

<div class="row">
    
    <div class="col-4">
        <?php
        $reqSummary = Yii::$app->db->createCommand('SELECT count(id) as total FROM `helpdesk` WHERE status = 1')->queryScalar();
        ?>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h4 class="card-title"><i class="fa-solid fa-triangle-exclamation"></i> ร้องขอ </h4>
                    <?=Html::a('ดูทั้งหมด <span class="badge text-bg-secondary">'. $reqSummary.'</span>',['/helpdesk/repair'],['class' => 'btn btn-primary'])?>

                </div>
                <table class="table  m-b-0 transcations mt-2">
                    <tbody>
                    <?php foreach ($dataProviderStatus1->getModels() as $model): ?>
                        <tr class="align-middle">
                            <td class="align-middle" style="width:15px;">
                                    <?=$model->showAvatarCreate();?>
                            </td>
                            <td>
                                <div class="d-flex align-middle ms-3">
                                    <div class="d-inline-block">
                                        <?=Html::a($model->data_json['title'],['/helpdesk/repair/view','id' => $model->id,'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'],['class' => 'h6 mb-1','data' => ['pjax' => false]])?>
                                        <p class="mb-0 fs-13 text-muted"><?=$model->data_json['location']?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-block">
                                    <h6 class="mb-2 fs-15 fw-semibold"><?=$model->viewUrgency()?></h6>
                                    <p class="mb-0 fs-11 text-muted"><?=$model->viewCreateDate()?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach?>
                    </tbody>
                </table>
            </div>
        </div>
        <?=$this->render('../default/progress')?>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">ปริมาณการมอบหมายงาน</h4>
                <?php for ($x = 0; $x <= 3; $x++):?>
                <?=$this->render('../default/technician_item')?>
                <?php endfor;?>
            </div>
        </div>
        <?=$this->render('../default/ratring')?>
    </div>
    <div class="col-8">
        <div class="row">
            
            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <a href="/hr/organization/diagram"><span
                                        class="text-muted text-uppercase fs-6">รับเรื่อง</span></a>
                                <h6 class="mb-0 mt-1" id="status2">0</h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-warning-subtle rounded p-3">
                                        <i class="fa-solid fa-user-check text-warning fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <a href="/hr/organization/diagram"><span
                                        class="text-muted text-uppercase fs-6">ดำเนินการ</span></a>
                                <h6 class="mb-0 mt-1" id="status3">0</h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-primary-subtle rounded p-3">
                                        <i class="fa-solid fa-person-digging text-primary fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <a href="/hr/organization/diagram"><span
                                        class="text-muted text-uppercase fs-6">ยกเลิก</span></a>
                                <h6 class="mb-0 mt-1" id="status5">0</h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-danger-subtle rounded p-3">
                                        <i class="fa-solid fa-triangle-exclamation text-danger fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <a href="/hr/organization/diagram"><span
                                        class="text-muted text-uppercase fs-6">เสร็จสิ้น</span></a>
                                <h6 class="mb-0 mt-1" id="status4">0</h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-success-subtle rounded p-3">
                                        <i class="fa-regular fa-circle-check text-success fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="viewJob"><h6 class="text-center mt-5">กำลังโหลด...</h6></div>

    </div>
</div>

<?php // $this->render('barchart')?>
<?php Pjax::end()?>

<?php
$urlAccept = Url::to(['/helpdesk/repair/list-accept']);
$urlSummary = Url::to(['/helpdesk/general/summary']);
$js = <<< JS

getJob();
getSummary();

jQuery(document).on("pjax:end", function () {
    getJob();
    getSummary()
});

async function getJob()
{
    await $.ajax({
        type: "get",
        url: "$urlAccept",
        dataType: "json",
        success: function (res) {
            $('#viewJob').html(res.content);
        }
    });
}

async function getSummary()
{
    await $.ajax({
        type: "get",
        url: "$urlSummary",
        dataType: "json",
        success: function (res) {
            console.log(res);
            $.each( res, function( key, i ) {
                // console.log(value.code);
                $('#status'+i.code).text(i.total)
                // alert( key + ": " + value );
                });
            // $('#viewJob').html(res.content);
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
$this->registerJS($js,View::POS_READY);
?>
