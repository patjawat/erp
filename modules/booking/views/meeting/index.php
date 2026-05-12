<?php

use app\components\DateFilterHelper;
use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;
use yii\web\View;

$this->title = 'ทะเบียนขอใช้ห้องประชุม';
$this->params['breadcrumbs'][] = ['label' => 'จองห้องประชุม', 'url' => ['/booking/meeting/index']];
$this->params['breadcrumbs'][] =  $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-presentation-icon lucide-presentation">
      <path d="M2 3h20" />
      <path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3" />
      <path d="m7 21 5-5 5 5" />
    </svg>
    <?= $this->title; ?>
  </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/booking/meeting_menu', ['active' => 'list']) ?>
<?php $this->endBlock(); ?>

<?php echo $this->render('_search', ['model' => $searchModel]); ?>

<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between">
      <h6 class="mt-2">
        <i class="bi bi-ui-checks"></i> ทะเบียนขอใช้ห้องประชุม
        <span class="badge text-bg-light">
          <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
      </h6>
      <div class="d-flex justify-content-between">
        <button class="btn btn-success export-leave"><i class="fa-solid fa-file-excel"></i> ส่งออก</button>
      </div>
    </div>
  </div>

  <div class="card-body">
    <?= $this->render('list', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'url' => '/booking/meeting/',
    ]); ?>
  </div>
  <div class="card-footer bg-body border-top py-3 px-4">
    <?php
    echo DataSummaryWidget::widget([
      'dataProvider' => $dataProvider,
      'pagerOptions' => [],
    ]);
    ?>
  </div>
</div>

<?php
$js = <<< JS

  $('body').on('click', '.confirm-meeting', function (e) {
    e.preventDefault();

    var status = $(this).data('status');
    var id = $(this).data('id');
    var text = $(this).data('text');
    Swal.fire({
      title: "ยืนยัน!",
      text:text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'ยกเลิก',
      confirmButtonText: 'ใช่, ยืนยัน!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          type: "post",
          url: '/me/booking-meeting/confirm',
          data: {
            id: id,
            status: status
          },
          dataType: "json",
          success: function (res) {
            if (res.status == 'success') {
              $('.modal').modal('hide');
              Swal.fire({
              icon: 'success',
              title: 'บันทึกสำเร็จ!',
              text: res.message || 'ดำเนินการเรียบร้อยแล้ว',
              timer: 1000,
              showConfirmButton: false
              }).then(() => {
              location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: res.message || 'Something went wrong.',
              });
            }
          }
        });
      }
    });
  });
  JS;
$this->registerJS($js, View::POS_END);
?>