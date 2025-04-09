<?php
use yii\web\View;
use yii\helpers\Html;

$this->title = 'ระบบขอใช้ห้องประชุม';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-person-chalkboard fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ทะเบียนขอใช้ห้องประชุม
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('menu')?>
<?php $this->endBlock(); ?>


<div class="container-fluid">

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
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            <?= $this->render('@app/modules/booking/views/meeting/list',[
                  'searchModel' => $searchModel,
                  'dataProvider' => $dataProvider,
                  'url' => '/me/booking-meeting/',
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
              title: 'Confirmed!',
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