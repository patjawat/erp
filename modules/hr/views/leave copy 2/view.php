<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\approve\models\Approve;

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
<div class="col-8">
    <?= $this->render('view_detail', ['model' => $model]) ?>
</div>
<div class="col-4">
    <?= $this->render('view_summary', ['model' => $model]) ?>
    <div class="d-flex justify-content-center">

        <button class="btn btn-primary rounded-pill shadow" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-clock-history"></i> ดูประวัติเพิ่มเติม
        </button>  
    </div>
</div>
</div>
<div class="collapse" id="collapseExample">
        <!-- <div id="viewHistory"></div> -->
        <?php echo $this->render('history', ['model' => $model]) ?>
</div>

<?php  echo $this->render('timeline_approve', ['model' => $model]) ?>


<div class="d-flex justify-content-center gap-3">
    <?php echo ($model->status == 'ReqCancel' && ($me->user_id != $model->created_by)) ? Html::a('<i class="fa-solid fa-rotate-left"></i> คืนวันลา', ['/hr/leave/cancel', 'id' => $model->id], ['class' => 'btn btn-warning rounded-pill shadow req-cancel-btn', 'data' => ['title' => 'คุณต้องการคืนวันลาใช่หรือไม!']]) : '' ?>
    <?php if ($me->user_id == $model->created_by): ?>
        <?= $model->status !== 'ReqCancel' ? Html::a('<i class="fa-solid fa-xmark"></i> ขอยกเลิก', ['/me/leave/req-cancel', 'id' => $model->id], [
            'class' => 'req-cancel-btn btn btn-sm btn-danger rounded-pill shadow', 'data' => ['title' => 'คุณต้องการขอยกเลิกใช่หรือไม!']
        ]) : '' ?>
    <?php echo ($model->status !== 'Allow' && $model->status !== 'ReqCancel') ? Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/me/leave/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-warning rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']]) : '' ?>
    <?php endif; ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i class="fa-regular fa-circle-xmark"></i> ปิด</button>
    <?php // echo Html::button('<i class="fa-regular fa-circle-xmark"></i> ปิด', ['class' => ' <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>me-2','onclick' => 'window.history.back()',]); ?>
</div>


<?php Pjax::end(); ?>

<?php
$urlHistory = Url::to(['/hr/leave/view-history', 'emp_id' => $model->emp_id, 'title' => 'ประวัติการลา']);
$urlApprove = Url::to(['/me/approve/leave-approve']);
// $urlApprove = Url::to(['/approve/leave/approve']);
$js = <<<JS

            // ขอยกเลิกวันลา
            \$("body").on("click", ".req-cancel-btn", function (e) {
                e.preventDefault();
                var title = \$(this).data('title')
                Swal.fire({
                title: 'ยืนยัน?',
                text: title+"!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                    if (result.isConfirmed) {
                        \$.ajax({
                            type: "post",
                            url: \$(this).attr('href'),
                            dataType: "json",
                            success: function (res) {
                                console.log(res);
                                
                            }
                        });
                    }
                })
            });
            

            \$("body").on("click", ".download-leave", function (e) {
            e.preventDefault();
            var filename = \$(this).data('filename');
            \$.ajax({
                url: \$(this).attr('href'), // ตรวจสอบให้แน่ใจว่า URL ตรงกับ controller/action ของคุณ
                method: 'GET',
                xhrFields: {
                    responseType: 'blob' // กำหนดให้ตอบกลับเป็น binary data
                },
                beforeSend: function() {
                    \$("#main-modal").modal("show");
                    \$("#main-modal-label").html("กำลังโหลด");
                    \$(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                    \$(".modal-dialog").addClass("modal-sm");
                    \$("#modal-dialog").removeClass("fade");
                    \$(".modal-body").html(
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

                    \$("#main-modal").modal("hide");
                },
                error: function() {
                    alert('ไม่สามารถดาวน์โหลดไฟล์ได้');
                }
            });
        });



    JS;
$this->registerJS($js, View::POS_END);
?>