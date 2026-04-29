<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\AppHelper;

$this->title = 'รายงานวันลา';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-pie-chart"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/menu_admin', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-bottom">
        <h6 class="mb-0"><i class="bi bi-search me-1"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-ui-checks me-1"></i> ทะเบียนประวัติการลา
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1"><?= number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
            <span class="btn btn-success shadow export-report"><i class="bi bi-file-earmark-excel me-1"></i> Excel</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="fw-semibold text-center">ลำดับ</th>
                        <th>ชื่อ นามสกุล</th>
                        <th>ตำแหน่ง</th>
                        <th class="fw-semibold text-center">เลขบัตรประชาชน</th>
                        <th>ฝ่าย/แผนก</th>
                        <th class="fw-semibold text-center">ประเภท</th>
                        <th class="fw-semibold text-center">ลาป่วย</th>
                        <th class="fw-semibold text-center">ลากิจ</th>
                        <th class="fw-semibold text-center">ลาคลอดบุตร</th>
                        <th class="fw-semibold text-center">ลาพักผ่อน</th>
                        <th class="fw-semibold text-center">รวมได้ลาแล้ว</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php
                    $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
                    $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
                    ?>
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center"><?= ($dataProvider->pagination->offset + 1) + $key ?></td>
                        <td><?= Html::encode($item->employee->fullname ?? '-') ?></td>
                        <td><?= $item->employee ? $item->employee->positionName() : '-' ?></td>
                        <td class="text-center"><?= Html::encode($item->employee->cid ?? '-') ?></td>
                        <td><?= $item->employee ? $item->employee->departmentName() : '-' ?></td>
                        <td><?= $item->employee ? $item->employee->positionTypeName() : '-' ?></td>
                        <td class="text-center fw-bolder">
                            <?= Html::a($item->sum_lt1 ?? 0, ['/leave/report/leave-history', 'emp_id' => $item->emp_id, 'thai_year' => $searchModel->thai_year, 'date_start' => $dateStart, 'date_end' => $dateEnd, 'status' => $searchModel->status, 'leave_type_id' => 'LT1'], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        </td>
                        <td class="text-center fw-bolder">
                            <?= Html::a($item->sum_lt3 ?? 0, ['/leave/report/leave-history', 'emp_id' => $item->emp_id, 'thai_year' => $searchModel->thai_year, 'date_start' => $dateStart, 'date_end' => $dateEnd, 'status' => $searchModel->status, 'leave_type_id' => 'LT3'], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        </td>
                        <td class="text-center fw-bolder">
                            <?= Html::a($item->sum_lt2 ?? 0, ['/leave/report/leave-history', 'emp_id' => $item->emp_id, 'thai_year' => $searchModel->thai_year, 'date_start' => $dateStart, 'date_end' => $dateEnd, 'status' => $searchModel->status, 'leave_type_id' => 'LT2'], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        </td>
                        <td class="text-center fw-bolder">
                            <?= Html::a($item->sum_lt4 ?? 0, ['/leave/report/leave-history', 'emp_id' => $item->emp_id, 'thai_year' => $searchModel->thai_year, 'date_start' => $dateStart, 'date_end' => $dateEnd, 'status' => $searchModel->status, 'leave_type_id' => 'LT4'], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        </td>
                        <td class="text-center fw-bolder">
                            <?= ($item->sum_lt1 ?? 0) + ($item->sum_lt2 ?? 0) + ($item->sum_lt3 ?? 0) + ($item->sum_lt4 ?? 0) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-4">
            <?= \yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => ['class' => 'pagination pagination-sm'],
            ]) ?>
        </div>
    </div>
</div>

<?php
$reportUrl = Url::to(['/leave/report/index']);
$js = <<< JS
$("body").on("click", ".export-report", function (e) {
    e.preventDefault();
    var form = $('#search-leave').serialize();
    $('#leavesearch-export').val('true');

    Swal.fire({
        title: "ยืนยันการดาวน์โหลด?",
        text: "คุณต้องการดาวน์โหลดรายงานวันลาใช่หรือไม่?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก",
        confirmButtonText: "ใช่, ดาวน์โหลด!"
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'กำลังดาวน์โหลด...',
                text: 'โปรดรอสักครู่',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            $.ajax({
                type: "get",
                url: "{$reportUrl}",
                data: $('#search-leave').serialize(),
                xhrFields: { responseType: 'blob' },
                success: function(response) {
                    $('#leavesearch-export').val('');
                    Swal.close();
                    var blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'รายงานวันลา.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    URL.revokeObjectURL(url);
                    Swal.fire({ icon: 'success', title: 'ดาวน์โหลดเสร็จสิ้น', showConfirmButton: false, timer: 1500 });
                },
                error: function(xhr, status, error) {
                    $('#leavesearch-export').val('');
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถดาวน์โหลดไฟล์ได้' });
                }
            });
        } else {
            $('#leavesearch-export').val('');
        }
    });
});
JS;
$this->registerJs($js, View::POS_END);
?>
