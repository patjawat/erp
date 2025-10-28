<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveEntitlementsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'กำหนดเวร 8';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/hr/views/leave/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'leave']); ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body ">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between  align-top align-items-center">
            <h6 class="text-white">
                <i class="bi bi-ui-checks"></i> <?= $this->title ?>
                <span class="badge rounded-pill text-bg-primary"><?php echo $dataProvider->getTotalCount() ?></span>
                รายการ
            </h6>
            <div class="d-flex gap-2">
                <?= Html::a('<i class="bi bi-plus-circle-fill"></i> กำหนดสิทธิรายบุคคล', ['create', 'title' => 'กำหนดสิทธิลาพักผ่อน', 'thai_year' => 1], ['class' => 'btn btn-light open-modal rounded-pill shadow', 'data' => ['size' => 'modal-md']]) ?>
                <?= Html::a('<i class="fa-solid fa-user-clock"></i> กำหนดสิทธิทั้งหมด', ['create-all', 'title' => 'กำหนดสิทธิลาพักผ่อนทั้งหมด'], ['class' => 'btn btn-warning create-all  rounded-pill shadow', 'data' => ['size' => 'modal-md']]) ?>

                <div class="dropdown">
                    <button class="btn btn-success shadow dropdown-toggle" type="button"
                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-excel"></i> Excel
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">

                        <li><button id="download-button" class="dropdown-item export-leave"><i class="fa-solid fa-file-arrow-down me-2"></i> ส่งออก</button></li>
                        <li><?= Html::a('<i class="fa-solid fa-file-csv me-2"></i>นำเข้าด้วย CSV', ['/hr/leave-entitlements/form-import', 'title' => '<i class="fas fa-file-csv text-white"></i> นำเข้าไฟล์ CSV'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                        <li><?= Html::a('<i class="fa-solid fa-file me-2"></i> ตัวอย่างไฟล์นำเข้า', 'https://docs.google.com/spreadsheets/d/1nQPDRemheHkvQvaqXVcbxKnB9c3Lt8KRXzdQjKxqriw/edit?usp=sharing', ['class' => 'dropdown-item', 'target' => '_blank']) ?></li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
    <div class="card-body">

        <div class="d-flex justify-content-between">

        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th rowspan="2">ลำดับ</th>
                    <th scope="col" class="fw-semibold">ชื่อ-นามสกุล</th>
                    <th scope="col" class="fw-semibold">ประเภท</th>
                    <th scope="col" class="fw-semibold text-center">แผนก/ฝ่าย</th>
                    <th scope="col" class="text-start fw-semibold" style="width: 100px;">ใช้เวร 8</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td><?php echo $item->getAvatar(false) ?></td>
                        <td><?= $item->positionType->title ?></td>
                        <td class="text-center fw-semibold"><?= $item->departmentName() ?></td>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch">
                                <?= Html::checkbox('work_shift', true, [
                                    'class' => 'form-check-input',
                                    'id' => 'use-shift8'
                                ]) ?>
                            </div>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-center">

            <?php echo  yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>

    </div>
</div>

<?php
$url = Url::to(['/hr/leave-entitlements/export']);
$createAllUrl = Url::to(['/hr/leave-entitlements/create-all']);
$js = <<< JS

$('.create-all').click(function (e) { 
    var thaiYear = $('#leaveentitlementssearch-thai_year').val();
    e.preventDefault();

    Swal.fire({
      title: "ยืนยัน?",
      text: "กำหนดสิทธิวันลาทั้งหมดในปีงบประมาณ " + thaiYear + "!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      cancelButtonText: "ยกเลิก!",
      confirmButtonText: "ใช่, ยืนยัน!"
    }).then((result) => {
      if (result.isConfirmed) {

        // แสดง loading ก่อนยิง ajax
        Swal.fire({
          title: 'กำลังประมวลผล...',
          text: 'โปรดรอสักครู่',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading()
          }
        });

        $.ajax({
          url: "$createAllUrl",
          type: 'post',
          data: {
            thai_year: $('#leaveentitlementssearch-thai_year').val(),
          },
          dataType: 'json',
          success: async function (res) {
            if(res.status == 'success'){
              Swal.close(); // ปิด loading
              $.pjax.reload({ 
                container: res.container, 
                history: false,
                replace: false,
                timeout: false
              });
            }else{
              Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: res.message,
              });
            }
          },
          error: function () {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้',
            });
          }
        });

      }
    });
});


$("body").on("click", "#download-button", function (e) {
    e.preventDefault();

    var monthName = $('#stockeventsearch-receive_month').find(':selected').text();
    var year = $('#stockeventsearch-thai_year').find(':selected').text();

    console.log($('#w0').serialize());
    
    Swal.fire({
        title: "ยืนยันการดาวน์โหลด?",
        text: "คุณต้องการดาวน์โหลดรายงานประจำเดือน " + monthName + " ปี " + year + " หรือไม่?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก",
        confirmButtonText: "ใช่, ดาวน์โหลด!"
    }).then((result) => {
        if (result.isConfirmed) {
          
 
            // แสดง loading ก่อนเริ่มโหลดไฟล์
            Swal.fire({
                title: 'กำลังดาวน์โหลด...',
                text: 'โปรดรอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            $.ajax({
                url: '$url', // URL ที่ชี้ไป Controller Action
                method: 'GET',
                xhrFields: {
                    responseType: 'blob' // ต้องใช้เพื่อรองรับ binary file
                },
                data:$('#w0').serialize(),
                success: function(data) {
                    Swal.close(); // ปิด Loading
                    var filename = 'รายงานสรุปวันลา ปี ' + year + '.xlsx';
                    const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();

                    Swal.fire({
                        icon: 'success',
                        title: 'ดาวน์โหลดเสร็จสิ้น',
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถดาวน์โหลดไฟล์ได้'
                    });
                }
            });
        }
    });
});




JS;
$this->registerJS($js, View::POS_END);
?>

<?php Pjax::end(); ?>