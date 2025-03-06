<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use app\components\AppHelper;
use app\components\UserHelper;
$me = UserHelper::GetEmployee();
/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
$this->title = 'ระบบลา';
$this->params['breadcrumbs'][] = ['label' => 'Leaves', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<div class="row">
    <div class="col-xl-8 col-sm-12">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <?= $model->employee->getAvatar(false) ?>
                            <div class="d-flex align-items-center gap-3">
                                <?php echo Html::a('<i class="fa-regular fa-circle-down me-1"></i> ดาน์โหลดเอกสาร', 
                            [$model->leave_type_id == 'LT4' ? '/hr/document/leavelt4' : '/hr/document/leavelt1', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> พิมพ์เอกสาร'], 
                            ['class' => 'btn btn-light rounded-pill download-leave','data' => [
                                // 'filename' => $model->leaveType->title.'-'.$model->employee->fullname
                            ]]) ?>
                                <?php if ($model->status !== 'Cancel'): ?>
                                <?php if ($model->status == 'Allow'): ?>
                                <i class="bi bi-person-check fs-3 text-primary"></i> อนุมัติให้ลาได้
                                <?php else: ?>

                                <?= ($model->status == 'Pending') ? Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/hr/leave/update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'], ['class' => 'btn btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) : '' ?>

                                <?php if ($model->status == 'ReqCancel'): ?>
                                <?php echo Html::a('<i class="fa-solid fa-circle-exclamation text-danger"></i> อนุมัติยกเลิกวันลา', ['/hr/leave/cancel', 'id' => $model->id], ['class' => 'btn btn-warning rounded-pill shadow cancel-btn']) ?>

                                <?php else: ?>
                                <?= ($model->status !== 'Reject' && $model->emp_id == $me->id) ? Html::a('<i class="bi bi-exclamation-circle"></i> ขอยกเลิก', ['/hr/leave/req-cancel', 'id' => $model->id], [
                        'class' => 'req-cancel-btn btn btn btn-danger rounded-pill shadow',
                    ]) : '' ?>
                                <?php endif; ?>
                                <?php endif; ?>
                                <?php endif; ?>


                            </div>
                        </div>


                        <?= $this->render('view_detail', ['model' => $model]) ?>
                    </div>
                </div>


            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?= $this->render('view_summary', ['model' => $model]) ?>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <div class="col-xl-4 col-sm-12">

        <?php echo $this->render('list_approve', ['model' => $model]) ?>
        <div class="d-flex justify-content-center">
            <?php echo Html::button('<i class="fa-solid fa-chevron-left"></i> ย้อนกลับ', ['class' => 'btn btn-secondary me-2','onclick' => 'window.history.back()',]);?>
        </div>

    </div>
</div>

<?php Pjax::end(); ?>

<?php
$js = <<< JS

    const confirmCancel = (e) => {
        e.preventDefault();
        const url = e.target.href;
        Swal.fire({
            title: 'ยืนยัน?',
            text: "อนุมัติให้ยกเลิกวันลาใช่หรือไม!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช้',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }

    const cancelButtons = document.querySelectorAll('.cancel-btn');
    cancelButtons.forEach(button => {
        button.addEventListener('click', confirmCancel);
    });

    const confirmReqCancel = (e) => {
        e.preventDefault();
        const url = e.target.href;
        Swal.fire({
            title: 'ยืนยัน?',
            text: "คุณต้องการขอยกเลิกใช่หรือไม!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }
    const reqCancelButtons = document.querySelectorAll('.req-cancel-btn');
    reqCancelButtons.forEach(button => {
        button.addEventListener('click', confirmReqCancel);
    });



        $("body").on("click", ".download-leave", function (e) {
        e.preventDefault();
        var filename = $(this).data('filename');
        $.ajax({
            url: $(this).attr('href'), // ตรวจสอบให้แน่ใจว่า URL ตรงกับ controller/action ของคุณ
            method: 'GET',
            xhrFields: {
                responseType: 'blob' // กำหนดให้ตอบกลับเป็น binary data
            },
            beforeSend: function() {
                $("#main-modal").modal("show");
                $("#main-modal-label").html("กำลังโหลด");
                $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                $(".modal-dialog").addClass("modal-sm");
                $("#modal-dialog").removeClass("fade");
                $(".modal-body").html(
                    '<div class="d-flex justify-content-center"><div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div></div><h6 class="text-center mt-3">Loading...</h6>'
                );
            },
            success: function(blob) { // ใช้ 'blob' เป็นชื่อพารามิเตอร์เพื่อหลีกเลี่ยงความสับสน
                var getFilename = filename+ '.docx'; // ชื่อไฟล์ที่ต้องการดาวน์โหลด
                const file = new Blob([blob], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
                
                // สร้างลิงก์ชั่วคราวสำหรับดาวน์โหลดไฟล์
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(file);
                link.download = getFilename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link); // ลบลิงก์ออกหลังจากใช้งานเสร็จ
                window.URL.revokeObjectURL(link.href); // ลบ URL Object เพื่อลดการใช้หน่วยความจำ

                $("#main-modal").modal("hide");
            },
            error: function() {
                alert('ไม่สามารถดาวน์โหลดไฟล์ได้');
            }
        });
    });

    JS;
$this->registerJS($js, View::POS_END);
?>