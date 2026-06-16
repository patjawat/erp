<?php

use yii\widgets\Pjax;
use app\components\widgets\DataSummaryWidget;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <span class="text-primary"><?= $icon ?></span>
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

<?= $this->render('_kpi', [
    'dataProvider' => $dataProvider,
    'statusSummary' => $statusSummary ?? [],
    'waitingAllocationCount' => $waitingAllocationCount ?? 0,
    'allocatedCount' => $allocatedCount ?? 0,
]) ?>

<div class="card shadow-sm border-0 mb-3 mt-3">
    <div class="card-body p-3">
        <?php echo $this->render('_search', ['model' => $searchModel, 'action' => $action]); ?>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 vehicle-list-header">
        <div class="d-flex align-items-center gap-2">
            <div class="vehicle-list-header-icon p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i>
            </div>
            <h5 class="m-0 fw-semibold">ทะเบียนการขอใช้รถยนต์</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                <?= number_format($dataProvider->getTotalCount(), 0) ?> รายการ
            </span>
        </div>
    </div>

    <div class="card-body p-0">
        <?php
        $cacheKey = ['booking-vehicle-index-list', 'v4-typography', Yii::$app->request->queryParams];
        if ($this->beginCache($cacheKey, ['duration' => 60])):
        ?>
            <?php echo $this->render('@app/modules/booking/views/vehicle/list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]) ?>
        <?php $this->endCache(); endif; ?>
    </div>

    <div class="card-footer bg-body border-top py-3 px-4">
        <?= DataSummaryWidget::widget([
            'dataProvider' => $dataProvider,
            'pagerOptions' => [],
        ]) ?>
    </div>
</div>

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

function initVehicleListDropdowns() {
    if (!window.bootstrap || !window.bootstrap.Dropdown) {
        return;
    }
    document.querySelectorAll('.vehicle-list-table .action-dropdown-toggle').forEach(function (el) {
        try {
            bootstrap.Dropdown.getOrCreateInstance(el);
        } catch (e) {
            console.warn('Dropdown init failed', e);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVehicleListDropdowns);
} else {
    initVehicleListDropdowns();
}

$(document).off('pjax:success.vehicleListDropdowns').on('pjax:success.vehicleListDropdowns', initVehicleListDropdowns);
$(document).off('shown.bs.modal.vehicleListDropdowns').on('shown.bs.modal.vehicleListDropdowns', initVehicleListDropdowns);

$(document).on('click', '.export-leave', function(e) {
    e.preventDefault();

    Swal.fire({
        title: 'ยืนยันการส่งออก Excel?',
        text: 'คุณต้องการดาวน์โหลดทะเบียนขอใช้รถยนต์เป็นไฟล์ Excel ใช่หรือไม่?',
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
