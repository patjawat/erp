<?php
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'แสดงรายละเอียดครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'ครุภัณฑ์', 'url' => ['/am/equip']];
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text">
            <path d="M13 16H8" />
            <path d="M14 8H8" />
            <path d="M16 12H8" />
            <path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z" />
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/views/asset/_action_menu',['model' => $model]) ?>
<?php $this->endBlock(); ?>



<?= $this->render('@app/modules/am/views/asset/_title', ['model' => $model]) ?>

<div class="card mt-4">
    <div class="card-header">
        <?= $this->render('@app/modules/am/views/asset/_view_menu', ['model' => $model, 'menu' => 'detail']) ?>
    </div>
    <div class="card-body">
        <?= $this->render('@app/modules/am/views/asset/_details', ['model' => $model]) ?>
    </div>
</div>


<?php
$js = <<< JS


$('.delete-asset').click(function (e) { 
    e.preventDefault();
    let url = $(this).attr('href');

    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลนี้จะถูกลบและไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                success: function (res) {
                    if (res.status == 'success') {
                        Swal.fire({
                            title: 'ลบข้อมูลสำเร็จ!',
                            text: 'รายการถูกลบเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 1000, // ตั้งค่าให้ Swal ปิดอัตโนมัติหลัง 1 วินาที
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '/am/asset'; // Redirect หลังจาก timer หมด
                        });
                    } else {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            res.message || 'ไม่สามารถลบข้อมูลได้',
                            'error'
                        );
                    }
                },
                error: function () {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        'error'
                    );
                }
            });
        }
    });
});

JS;
$this->registerJS($js, \yii\web\View::POS_END);

?>