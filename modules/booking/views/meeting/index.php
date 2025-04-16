<?php
use yii\web\View;
use yii\helpers\Html;
$this->title = 'ระบบจัดการห้องประชุม(ผู้ดูแลระบบ)';
$this->params['breadcrumbs'][] = $this->title;
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ทะเบียนการจอง
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('menu') ?>
<?php $this->endBlock(); ?>


<div class="container-fluid">
<?=$this->render('@app/modules/booking/views/meeting/summary',['model' => $searchModel]) ?>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h6>
                    <i class="bi bi-ui-checks"></i> ทะเบียนขอใช้ห้องประชุม
                    <span
                        class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                    รายการ
                </h6>
            </div>
            <div class="d-flex justify-content-between  align-top align-items-center mt-2">
                <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            </div>
            <?= $this->render('list',[
                  'searchModel' => $searchModel,
                  'dataProvider' => $dataProvider,
                  'url' => '/booking/meeting/',
              ]);?>

        </div>
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
  $this->registerJS($js,View::POS_END);
  ?>