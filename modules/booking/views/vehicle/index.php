<?php



/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <?= $icon ?>
        <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/booking/vehicle_menu',['active' => 'official']) ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<?php echo $this->render('@app/modules/booking/views/vehicle/list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ])?>

<?php
$js = <<<JS
$(document).on('click', '.cancel-order', function(e) {
    e.preventDefault();
    let url = $(this).attr('href');
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณต้องการยกเลิกคำขอนี้หรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ยกเลิก!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'POST',
                success: function(response) {
                    Swal.fire(
                        'ยกเลิกสำเร็จ!',
                        'คำขอของคุณถูกยกเลิกแล้ว.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                },
                error: function() {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถยกเลิกคำขอได้.',
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