<?php


use yii\widgets\Pjax;


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


<?php Pjax::begin([
    'id' => 'vehicle-index-pjax',
    'timeout' => 10000,
    'enablePushState' => true,
]); ?>
<div class="card">
    <div class="card-header">
        <h6 class="mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel, 'action' => $action]); ?>
    </div>
</div>

<?php
$cacheKey = ['booking-vehicle-index-list', Yii::$app->request->queryParams];
if ($this->beginCache($cacheKey, ['duration' => 60])):
?>
    <?php echo $this->render('@app/modules/booking/views/vehicle/list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            'statusSummary' => $statusSummary ?? [],
            'waitingAllocationCount' => $waitingAllocationCount ?? 0,
            'allocatedCount' => $allocatedCount ?? 0,
            ]) ?>
<?php $this->endCache(); endif; ?>
<?php Pjax::end(); ?>

<?php
$js = <<<'JS'
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

$(document).on('click', '.export-leave', function(e) {
    e.preventDefault();

    Swal.fire({
        title: 'ยืนยันการส่งออก Excel?',
        text: 'คุณต้องการดาวน์โหลดทะเบียนขอใช้รถยนต์ทั่วไปเป็นไฟล์ Excel ใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ส่งออก',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        let $form = $('.vehicle-search-form').first();
        if (!$form.length) {
            return;
        }

        $form.find('#vehicle-export-excel-input').remove();
        $form.append('<input type="hidden" name="export" value="excel" id="vehicle-export-excel-input">');
        $form[0].submit();

        setTimeout(function () {
            $('#vehicle-export-excel-input').remove();
        }, 500);
    });
});
JS;
$this->registerJS($js, \yii\web\View::POS_END);
?>
