<?php
use yii\web\View;
use yii\helpers\Html;
$this->title = 'ทะเบียนขอใช้ห้องประชุม';
$this->params['breadcrumbs'][] = ['label' => 'ระบบจัดการห้องประชุม', 'url' => ['/booking/meeting/index']];
$this->params['breadcrumbs'][] = '<i class="bi bi-ui-checks me-1"></i>'.$this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-ui-checks"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('menu',['active' => 'index'])?>
<?php $this->endBlock(); ?>

<!-- https://www.canva.com/ai/code/thread/52a8afb0-5caf-4151-a563-8a2106920508 -->

<?php // $this->render('@app/modules/booking/views/meeting/summary',['model' => $searchModel]) ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


    <div class="card">
      <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนขอใช้ห้องประชุม
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> ระบบการ
            </h6>
            <div class="d-flex justify-content-between">
                <button class="btn btn-success export-leave"><i class="fa-solid fa-file-excel"></i> ส่งออก</button>
            </div>
        </div>
    </div>

        <div class="card-body">
            <?= $this->render('list',[
                  'searchModel' => $searchModel,
                  'dataProvider' => $dataProvider,
                  'url' => '/booking/meeting/',
              ]);?>

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