<?php
use yii\web\View;
$this->title = 'Meetings';
$this->params['breadcrumbs'][] = $this->title;
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<div class="container">
<?=$this->render('navbar')?>

<?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <!-- Navigation Pills -->
    <ul class="nav nav-pills mb-4" id="meetingTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-tab-pane" type="button" role="tab" aria-controls="all-tab-pane" aria-selected="true">ทั้งหมด</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-tab-pane" type="button" role="tab" aria-controls="pending-tab-pane" aria-selected="false" tabindex="-1">รอการอนุมัติ</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-tab-pane" type="button" role="tab" aria-controls="approved-tab-pane" aria-selected="false" tabindex="-1">อนุมัติแล้ว</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected-tab-pane" type="button" role="tab" aria-controls="rejected-tab-pane" aria-selected="false" tabindex="-1">ปฏิเสธ/ยกเลิก</button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="meetingTabsContent">
      <!-- All Meetings Tab -->
      <div class="tab-pane fade active show" id="all-tab-pane" role="tabpanel" aria-labelledby="all-tab" tabindex="0">
        <?=$this->render('list', ['dataProvider' => $dataProvider,'status' => 'All'])?>
      </div>

      <!-- Pending Approval Tab -->
      <div class="tab-pane fade" id="pending-tab-pane" role="tabpanel" aria-labelledby="pending-tab" tabindex="0">
      <?=$this->render('list', ['dataProvider' => $dataProvider,'status' => 'Pending'])?>
      </div>

      <!-- Approved Tab -->
      <div class="tab-pane fade" id="approved-tab-pane" role="tabpanel" aria-labelledby="approved-tab" tabindex="0">
      <?=$this->render('list', ['dataProvider' => $dataProvider,'status' => 'Pass'])?>
      </div>

      <!-- Rejected/Cancelled Tab -->
      <div class="tab-pane fade" id="rejected-tab-pane" role="tabpanel" aria-labelledby="rejected-tab" tabindex="0">
      <?=$this->render('list', ['dataProvider' => $dataProvider,'status' => 'Cancel'])?>
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