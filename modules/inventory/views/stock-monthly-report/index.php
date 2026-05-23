<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\Categorise;
use app\modules\inventory\models\StockMonthlyReport;
use app\modules\inventory\models\Warehouse;

/** @var yii\web\View $this */
/** @var \app\modules\inventory\models\StockMonthlyReportSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายงานสรุปคงคลังรายเดือน';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$monthOptions = [];
for ($m = 1; $m <= 12; $m++) {
    $monthOptions[$m] = StockMonthlyReport::thaiMonthName($m);
}

$currentYear = (int) date('Y');
$yearOptions = [];
for ($y = $currentYear + 1; $y >= $currentYear - 5; $y--) {
    $yearOptions[$y] = $y . ' (พ.ศ. ' . ($y + 543) . ')';
}

$warehouseOptions = ArrayHelper::map(
    Warehouse::find()
        ->where(['warehouse_type' => 'MAIN'])
        ->orderBy(['warehouse_name' => SORT_ASC])
        ->all(),
    'id',
    'warehouse_name'
);

$assetTypeOptions = ArrayHelper::map(
    Categorise::find()
        ->where(['name' => 'asset_type', 'category_id' => 4])
        ->orderBy(['code' => SORT_ASC])
        ->all(),
    'code',
    function ($m) { return '(' . $m->code . ') ' . $m->title; }
);

$rows = $dataProvider->getModels();
$num = 1;
$sumOpenQty = $sumOpenVal = $sumInQty = $sumInVal = 0;
$sumOutSub = $sumOutHosp = $sumOutVal = $sumClosingQty = $sumClosingVal = 0;

$fmt = static function ($v) {
    return number_format((float) ($v ?? 0), 2);
};
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="calendar-days"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= Yii::$app->session->getFlash('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= Yii::$app->session->getFlash('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-primary-gradient text-white">
                <h6 class="text-white mt-2 mb-0"><i class="fa-solid fa-gears"></i> สรุปข้อมูลรายเดือน</h6>
            </div>
            <div class="card-body">
                <?= $this->render('_generate', [
                    'searchModel' => $searchModel,
                    'monthOptions' => $monthOptions,
                    'yearOptions' => $yearOptions,
                    'warehouseOptions' => $warehouseOptions,
                    'assetTypeOptions' => $assetTypeOptions,
                ]) ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-primary-gradient text-white">
                <h6 class="text-white mt-2 mb-0"><i class="fa-solid fa-magnifying-glass"></i> ค้นหา/กรองข้อมูล</h6>
            </div>
            <div class="card-body">
                <?= $this->render('_search', [
                    'model' => $searchModel,
                    'monthOptions' => $monthOptions,
                    'yearOptions' => $yearOptions,
                    'warehouseOptions' => $warehouseOptions,
                    'assetTypeOptions' => $assetTypeOptions,
                ]) ?>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-primary-gradient text-white d-flex align-items-center justify-content-between">
        <h6 class="text-white mb-0"><i class="fa-solid fa-table"></i> รายการสรุปคงคลังรายเดือน</h6>
        <a class="btn btn-success btn-sm shadow"
            href="<?= Url::to(array_merge(['stock-monthly-report/export-excel'], Yii::$app->request->queryParams)) ?>">
            <i class="fa-solid fa-file-excel me-1"></i> Excel
        </a>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-bordered table-striped mb-0">
            <thead class="align-middle text-center">
                <tr class="table-light">
                    <th rowspan="2">#</th>
                    <th rowspan="2">เดือน</th>
                    <th rowspan="2">คลังหลัก</th>
                    <th rowspan="2">รหัสพัสดุ</th>
                    <th rowspan="2">รายการ</th>
                    <th rowspan="2">หน่วย</th>
                    <th colspan="2">ยอดยกมา</th>
                    <th colspan="2">รับเข้า</th>
                    <th colspan="2">จ่ายออก (จำนวน)</th>
                    <th rowspan="2">รวมจ่าย (มูลค่า)</th>
                    <th colspan="2">คงเหลือ</th>
                    <th rowspan="2" style="width:90px;">จัดการ</th>
                </tr>
                <tr class="table-light">
                    <th>จำนวน</th>
                    <th>มูลค่า</th>
                    <th>จำนวน</th>
                    <th>มูลค่า</th>
                    <th>รพ.สต.</th>
                    <th>โรงพยาบาล</th>
                    <th>จำนวน</th>
                    <th>มูลค่า</th>
                </tr>
            </thead>
            <tbody class="align-middle">
                <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="16" class="text-center text-muted py-4">
                        ยังไม่มีข้อมูลในช่วงที่เลือก กรุณากดปุ่ม "สรุปข้อมูลเดือนนี้" เพื่อสร้างรายงาน
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($rows as $row): ?>
                <?php
                    $sumOpenQty   += (float) $row->opening_qty;
                    $sumOpenVal   += (float) $row->opening_value;
                    $sumInQty     += (float) $row->in_qty;
                    $sumInVal     += (float) $row->in_value;
                    $sumOutSub    += (float) $row->out_sub_qty;
                    $sumOutHosp   += (float) $row->out_hosp_qty;
                    $sumOutVal    += (float) $row->total_out_value;
                    $sumClosingQty += (float) $row->closing_qty;
                    $sumClosingVal += (float) $row->closing_value;
                ?>
                <tr<?= $row->isAdjusted() ? ' class="table-warning"' : '' ?>>
                    <td class="text-center"><?= $num++ ?></td>
                    <td class="text-nowrap"><?= Html::encode($row->getMonthLabel()) ?></td>
                    <td><?= Html::encode($row->warehouse->warehouse_name ?? '-') ?></td>
                    <td>
                        <?= Html::encode($row->item_code) ?>
                        <?php if ($row->isAdjusted()): ?>
                            <span class="badge bg-warning text-dark ms-1"
                                title="ปรับยอดเมื่อ <?= date('d/m/Y H:i', $row->adjusted_at) ?>&#10;<?= Html::encode($row->adjustment_note) ?>">
                                <i class="fa-solid fa-pen"></i> ปรับยอด
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?= Html::encode($row->item->item_name ?? ($row->asset->title ?? '-')) ?></td>
                    <td><?= Html::encode($row->unit_name ?? '-') ?></td>
                    <td class="text-end"><?= $fmt($row->opening_qty) ?></td>
                    <td class="text-end"><?= $fmt($row->opening_value) ?></td>
                    <td class="text-end"><?= $fmt($row->in_qty) ?></td>
                    <td class="text-end"><?= $fmt($row->in_value) ?></td>
                    <td class="text-end"><?= $fmt($row->out_sub_qty) ?></td>
                    <td class="text-end"><?= $fmt($row->out_hosp_qty) ?></td>
                    <td class="text-end"><?= $fmt($row->total_out_value) ?></td>
                    <td class="text-end fw-bold"><?= $fmt($row->closing_qty) ?></td>
                    <td class="text-end fw-bold"><?= $fmt($row->closing_value) ?></td>
                    <td class="text-center">
                        <button type="button"
                            class="btn btn-sm btn-outline-warning btn-adjust"
                            data-url="<?= Url::to(['adjust', 'id' => $row->id, 'modal' => 1]) ?>"
                            data-item="<?= Html::encode($row->item_code) ?>"
                            title="ปรับยอด">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($rows)): ?>
            <tfoot>
                <tr class="table-warning fw-bold">
                    <td colspan="6" class="text-end">รวม</td>
                    <td class="text-end"><?= $fmt($sumOpenQty) ?></td>
                    <td class="text-end"><?= $fmt($sumOpenVal) ?></td>
                    <td class="text-end"><?= $fmt($sumInQty) ?></td>
                    <td class="text-end"><?= $fmt($sumInVal) ?></td>
                    <td class="text-end"><?= $fmt($sumOutSub) ?></td>
                    <td class="text-end"><?= $fmt($sumOutHosp) ?></td>
                    <td class="text-end"><?= $fmt($sumOutVal) ?></td>
                    <td class="text-end"><?= $fmt($sumClosingQty) ?></td>
                    <td class="text-end"><?= $fmt($sumClosingVal) ?></td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
    <div class="card-footer text-muted small">
        แสดงทั้งหมด <?= number_format(count($rows)) ?> รายการ
    </div>
</div>

<!-- Offcanvas: ปรับยอดคงเหลือ -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="adjustOffcanvas" aria-labelledby="adjustOffcanvasLabel" style="width: 520px;">
    <div class="offcanvas-header bg-warning-subtle">
        <h5 class="offcanvas-title" id="adjustOffcanvasLabel">
            <i class="fa-solid fa-edit"></i> ปรับยอดคงเหลือ
            <small class="text-muted ms-2" id="adjustOffcanvasItem"></small>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="adjustOffcanvasBody">
        <div class="text-center py-5 text-muted">
            <span class="spinner-border text-warning"></span>
            <div class="mt-2 small">กำลังโหลด...</div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
(function() {
    var offcanvasEl = document.getElementById('adjustOffcanvas');
    var offcanvas   = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
    var \$body      = $('#adjustOffcanvasBody');
    var loadingHtml = '<div class="text-center py-5 text-muted"><span class="spinner-border text-warning"></span><div class="mt-2 small">กำลังโหลด...</div></div>';

    // คลิกปุ่ม "ปรับยอด" ในแถวตาราง → โหลดฟอร์มเข้า offcanvas
    $(document).on('click', '.btn-adjust', function(e) {
        e.preventDefault();
        var url  = $(this).data('url');
        var item = $(this).data('item') || '';
        \$body.html(loadingHtml);
        $('#adjustOffcanvasItem').text(item ? '— ' + item : '');
        offcanvas.show();
        $.get(url, function(html) { \$body.html(html); })
         .fail(function() { \$body.html('<div class="alert alert-danger m-3">โหลดฟอร์มไม่สำเร็จ</div>'); });
    });

    // Helper: confirm dialog (Swal ถ้ามี ไม่งั้น confirm())
    function confirmAction(opts) {
        opts = opts || {};
        var title   = opts.title   || 'ยืนยันการดำเนินการ';
        var text    = opts.text    || '';
        var confirm = opts.confirm || 'ยืนยัน';
        var cancel  = opts.cancel  || 'ยกเลิก';
        var icon    = opts.icon    || 'question';
        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                title: title, text: text, icon: icon,
                showCancelButton: true,
                confirmButtonText: confirm,
                cancelButtonText: cancel,
                confirmButtonColor: '#f0ad4e',
            }).then(function(r) { return r.isConfirmed; });
        }
        return Promise.resolve(window.confirm(title + (text ? '\\n\\n' + text : '')));
    }

    function showToast(type, message) {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({icon: type, title: message, timer: 1600, showConfirmButton: false});
        } else {
            alert(message);
        }
    }

    // Submit ฟอร์มปรับยอด (AJAX + confirm)
    $(document).on('submit', '.form-adjust-ajax', function(e) {
        e.preventDefault();
        var \$form  = $(this);
        var newQty = \$form.find('[name$="[closing_qty]"]').val();
        var newVal = \$form.find('[name$="[closing_value]"]').val();
        var note   = (\$form.find('[name$="[adjustment_note]"]').val() || '').trim();

        if (!note) {
            showToast('warning', 'กรุณากรอกเหตุผลการปรับยอด');
            return;
        }

        confirmAction({
            title: 'ยืนยันการปรับยอดคงเหลือ?',
            text: 'จำนวนใหม่: ' + newQty + ' | มูลค่าใหม่: ' + newVal
                  + '\\nระบบจะคุ้มครองค่านี้ไม่ให้ถูกทับเมื่อ re-generate และจะส่งผลต่อ opening ของเดือนถัดไป',
            confirm: 'ยืนยันบันทึก',
            icon: 'warning',
        }).then(function(ok) {
            if (!ok) return;
            var \$btn = \$form.find('button[type=submit]').prop('disabled', true);
            \$.ajax({
                url: \$form.attr('action'),
                type: 'POST',
                data: \$form.serialize(),
                dataType: 'json',
            }).done(function(res) {
                if (res && res.success) {
                    offcanvas.hide();
                    showToast('success', res.message);
                    setTimeout(function() { window.location.reload(); }, 800);
                } else {
                    showToast('error', (res && res.message) || 'บันทึกไม่สำเร็จ');
                    \$btn.prop('disabled', false);
                }
            }).fail(function(xhr) {
                showToast('error', 'เกิดข้อผิดพลาด: ' + xhr.status);
                \$btn.prop('disabled', false);
            });
        });
    });

    // Submit ฟอร์มยกเลิกการปรับยอด (AJAX + confirm)
    $(document).on('submit', '.form-reset-adjust-ajax', function(e) {
        e.preventDefault();
        var \$form = $(this);

        confirmAction({
            title: 'ยกเลิกการปรับยอด?',
            text: 'closing จะกลับเป็นค่าที่ระบบคำนวณตามปกติ (opening + in − out)',
            confirm: 'ยืนยันคืนค่าเดิม',
            icon: 'warning',
        }).then(function(ok) {
            if (!ok) return;
            \$.ajax({
                url: \$form.attr('action'),
                type: 'POST',
                data: \$form.serialize(),
                dataType: 'json',
            }).done(function(res) {
                if (res && res.success) {
                    offcanvas.hide();
                    showToast('success', res.message);
                    setTimeout(function() { window.location.reload(); }, 800);
                } else {
                    showToast('error', (res && res.message) || 'ไม่สำเร็จ');
                }
            }).fail(function(xhr) {
                showToast('error', 'เกิดข้อผิดพลาด: ' + xhr.status);
            });
        });
    });
})();
JS;
$this->registerJs($js);
?>
