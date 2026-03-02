<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\UserHelper;

$me = UserHelper::GetEmployee();
/** @var yii\web\View $this */
/** @var app\modules\leave\models\Leave $model */
$this->title = 'ระบบลา';
$this->params['breadcrumbs'][] = ['label' => 'การลา', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<?= $this->render('view_detail', ['model' => $model]) ?>

<div class="d-flex flex-wrap justify-content-center gap-2 gap-md-3">
    <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์ใบลา', ['/leave/leave/print', 'id' => $model->id], ['class' => 'btn btn-outline-primary rounded-pill shadow', 'target' => '_blank', 'rel' => 'noopener']) ?>
    <?php echo ($model->status == 'ReqCancel' && ($me->id != $model->emp_id)) ? Html::a('<i class="fa-solid fa-rotate-left"></i> คืนวันลา', ['/leave/leave/cancel', 'id' => $model->id], ['class' => 'btn btn-warning rounded-pill shadow req-cancel-btn', 'data' => ['title' => 'คุณต้องการคืนวันลาใช่หรือไม!']]) : '' ?>
    <?php if ($me->id == $model->emp_id): ?>
        <?= ($model->status !== 'Cancel' && $model->status !== 'ReqCancel') ? Html::a('<i class="fa-solid fa-xmark"></i> ขอยกเลิก', ['/me/leave/req-cancel', 'id' => $model->id], [
            'class' => 'req-cancel-btn btn btn-danger rounded-pill shadow', 'data' => ['title' => 'คุณต้องการขอยกเลิกใช่หรือไม!']
        ]) : '' ?>
        <?php echo $model->levelStatusCount() == 0 ? Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/me/leave/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-warning rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']]) : '' ?>
    <?php endif; ?>
    <button type="button" class="btn btn-secondary rounded-pill shadow" data-bs-dismiss="modal"><i class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>

<?php Pjax::end(); ?>

<?php
$js = <<<JS
    $("body").on("click", ".req-cancel-btn", function (e) {
        e.preventDefault();
        var title = $(this).data('title');
        var btn = $(this);
        Swal.fire({
            title: 'ยืนยัน?',
            text: title + "!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#main-modal").modal("hide");
                Swal.fire({
                    title: 'กำลังดำเนินการ...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                $.ajax({
                    type: "post",
                    url: btn.attr('href'),
                    dataType: "json",
                    success: function (res) {
                        if (res.status == 'success') {
                            Swal.fire({
                                title: 'สำเร็จ',
                                text: 'ดำเนินการเรียบร้อยแล้ว',
                                icon: 'success',
                                timer: 1000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                didClose: () => { location.reload(); }
                            });
                        } else {
                            Swal.close();
                            Swal.fire('เกิดข้อผิดพลาด', res.message || '', 'error');
                        }
                    },
                    error: function () {
                        Swal.close();
                        Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                    }
                });
            }
        });
    });

    $("body").on("click", ".download-leave", function (e) {
        e.preventDefault();
        var filename = $(this).data('filename');
        $.ajax({
            url: $(this).attr('href'),
            method: 'GET',
            xhrFields: { responseType: 'blob' },
            beforeSend: function() {
                $("#main-modal").modal("show");
                $("#main-modal-label").html("กำลังโหลด");
                $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl").addClass("modal-sm");
                $("#modal-dialog").removeClass("fade");
                $(".modal-body").html('<div class="d-flex justify-content-center"><div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div></div><h6 class="text-center mt-3">Loading...</h6>');
            },
            success: function(blob) {
                var getFilename = filename + '.docx';
                const file = new Blob([blob], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(file);
                link.download = getFilename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(link.href);
                $("#main-modal").modal("hide");
            },
            error: function() { alert('ไม่สามารถดาวน์โหลดไฟล์ได้'); }
        });
    });
JS;
$this->registerJs($js, View::POS_END);
?>
