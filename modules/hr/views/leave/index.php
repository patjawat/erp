<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\hr\models\Leave;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ระบบการลา';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/hr/views/leave/menu',['active' => 'index'])?>
<?php $this->endBlock(); ?>
<?php  Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>



<style>
.hover-card-under {

    transition: border-color 0.3s ease, transform 0.3s ease;
}

.hover-card-under:hover {
    border: 3px solid transparent !important;
    border-color: #dc3545 !important;
    border-top: 0 !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-left: 0 !important;
    transform: scale(1.04);
}
</style>

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
                <i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา
                <?php echo number_format($dataProvider->getTotalCount(), 0) ?> ระบบการ
            </h6>

            <div class="d-flex justify-content-between">

                <button class="btn btn-success export-leave"><i class="fa-solid fa-file-excel"></i> ส่งออก</button>
            </div>
        </div>



    </div>
    <div class="card-body">



        <?php
        echo $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
        ?>

    </div>
</div>

<?php  Pjax::end(); ?>
<?php
$js = <<< JS

    \$('.filter-status').click(function (e) { 
        e.preventDefault();
        var id = \$(this).data('id');
        \$('#leavesearch-status').val(id);
        \$('#w0').submit();
        console.log(id);
    });

        $("body").on("click", ".export-leave", function (e) {
            e.preventDefault();
            let form = $('#search-leave');
            let action = form.attr('action');
            let data = form.serialize();

            Swal.fire({
                title: 'ยืนยันการส่งออกข้อมูล?',
                text: 'คุณต้องการส่งออกข้อมูลหรือไม่',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ส่งออก',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังส่งออกข้อมูล...',
                        text: 'กรุณารอสักครู่',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        type: "get",
                        url: '/hr/leave/export-leave',
                        data: form.serialize(),
                        xhrFields: {
                            responseType: 'blob' 
                        },
                        success: function (response) {
                            Swal.close();

                            const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'ทะเบียนวันลา.xlsx'; // The default file name
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);

                            Swal.fire({
                                icon: 'success',
                                title: 'ส่งออกสำเร็จ',
                                text: 'ไฟล์ถูกดาวน์โหลดเรียบร้อยแล้ว',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function (xhr, status, error) {
                            Swal.close();
                            $('#page-content').show();
                            $('#loader').hide();
                            warning(xhr.responseText);
                            console.log('Error occurred:', error);
                            console.log('Status:', status);
                            console.log('Response:', xhr.responseText);
                        }
                    });
                }
            });
        });
    JS;
$this->registerJs($js);
?>