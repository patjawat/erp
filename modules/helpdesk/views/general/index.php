<?php
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\Html;
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
    <div class="col-8">
        <div class="row">
            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <a href="/hr/organization/diagram"><span
                                        class="text-muted text-uppercase fs-6">ร้องขอ</span></a>
                                <h6 class="mb-0 mt-1">35</h6>
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
                                        class="text-muted text-uppercase fs-6">รับเรื่อง</span></a>
                                <h6 class="mb-0 mt-1">35</h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-warning-subtle rounded p-3">
                                        <i class="fa-solid fa-file-circle-check text-warning fs-4"></i>
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
                                <h6 class="mb-0 mt-1">35</h6>
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
                                        class="text-muted text-uppercase fs-6">เสร็จสิ้น</span></a>
                                <h6 class="mb-0 mt-1">35</h6>
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
        <div id="viewJob"></div>

    </div>
    <div class="col-4">


        <div class="card">

            <div class="card-body">

                <div class="d-flex justify-content-between">
                    <h4 class="card-title">ร้องขอ</h4>
                    <?=Html::a('ดูทั้งหมด',['/helpdesk/repair'],['class' => 'btn btn-primary'])?>
                </div>
                <table class="table  m-b-0 transcations mt-2">
                    <tbody>
                        <tr>
                            <td style="width:20px;">
                                <div class="main-img-user avatar-md">
                                    <?=Html::img('@web/img/patjwat2.png',['class' => 'avatar avatar-md bg-primary text-white'])?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-middle ms-3">
                                    <div class="d-inline-block">
                                        <h6 class="mb-1">เครื่องคอมพิวเตอร์ขัดข้อง</h6>
                                        <p class="mb-0 fs-13 text-muted">OPD1</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-block">
                                    <h6 class="mb-2 fs-15 fw-semibold">ด่วน</h6>
                                    <p class="mb-0 fs-11 text-muted">12 ม.ค. 2567</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="main-img-user avatar-md">
                                    <?=Html::img('@web/img/patjwat2.png',['class' => 'avatar avatar-md bg-primary text-white'])?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-middle ms-3">
                                    <div class="d-inline-block">
                                        <h6 class="mb-1">น้ำไม่ไหล</h6>
                                        <p class="mb-0 fs-13 text-muted">IPD2</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-block">
                                    <h6 class="mb-2 fs-15 fw-semibold">ด่วนที่สุด</h6>
                                    <p class="mb-0 fs-11 text-muted">23 ม.ค. 2567</p>
                                </div>
                            </td>
                        </tr>


                    </tbody>
                </table>


            </div>
        </div>





        <?=$this->render('progress')?>
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
</div>


<?php // $this->render('barchart')?>

<?php
$url = Url::to(['/helpdesk/repair']);
$js = <<< JS

getJob();

function getJob()
{
    $.ajax({
        type: "get",
        url: "$url",
        data: "data",
        dataType: "json",
        success: function (res) {
            $('#viewJob').html(res.content);
        }
    });
}


        const options = {
          series: [44, 55, 41, 17],
    chart: {
      type: 'donut',
      // height: 150,
      // width: '100%',
      // offsetX: 50
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
              // offsetY: -10
            },
            // total: {
            //   show: true,
            //   showAlways: true,
            //   color: '#BCC1C8',
            //   fontFamily: 'Open Sans',
            //   fontWeight: 600,
            //   formatter: (w) => {
            //     const total = w.globals.seriesTotals.reduce(
            //       (a, b) => a + b,
            //       0
            //     );
            //     return total;
            //   }
            // }
          }
        }
      },
    },
    dataLabels: {
      enabled: false
    },
    labels: ['ร้องขอ', 'รับเรื่อง', 'ดำเนินการ', 'เสร็จสิ้น'],
    legend: {
      // show: false,
      // position: 'right',
      // offsetX: -30,
      // offsetY: 70,
      // formatter: (value, opts) => {
      //   return value + ' - ' + opts.w.globals.series[opts.seriesIndex];
      // },
      // markers: {
      //   onClick: undefined,
      //   offsetX: 0,
      //   offsetY: 25
      // }
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
$this->registerJS($js);
?>
<?php Pjax::end()?>