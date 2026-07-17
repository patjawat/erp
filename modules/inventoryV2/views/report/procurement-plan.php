<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายงานแผนจัดซื้อวัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'คลังวัสดุ', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'รายงาน', 'url' => ['/inventory-v2/report/material-summary']];
$this->params['breadcrumbs'][] = $this->title;

$formatter = Yii::$app->formatter;
$exportParams = ['/inventory-v2/report/export-procurement-plan', 'fiscal_year' => $fiscalYear];
$exportParams['data_source'] = $dataSource;
if ($warehouseId) {
    $exportParams['warehouse_id'] = $warehouseId;
}
if ($categoryId !== '') {
    $exportParams['category_id'] = $categoryId;
}
if ($q !== '') {
    $exportParams['q'] = $q;
}

$totalPurchaseQty = array_sum(array_column($rows, 'estimated_purchase_quantity'));
$totalPurchaseValue = array_sum(array_column($rows, 'purchase_vol_in_year'));
$totalEstimatedUse = array_sum(array_column($rows, 'estimated_amount_used'));
$metricTooltips = [
    'ประมาณการใช้รวม' => 'ผลรวมประมาณการใช้ของรายการที่แสดง โดยแต่ละรายการคำนวณจากค่าเฉลี่ยปริมาณการใช้ย้อนหลังใน StockMonthlyReport ตามปีงบประมาณและแหล่งข้อมูลที่เลือก',
    'ประมาณการซื้อรวม' => 'ผลรวมจำนวนที่ควรจัดซื้อของรายการที่แสดง คำนวณจากประมาณการใช้หักด้วยยอดคงเหลือต้นงวดหรือยอดคงเหลือปัจจุบัน และไม่ให้ติดลบ',
    'มูลค่าจัดซื้อรวม' => 'ผลรวมมูลค่าจัดซื้อโดยประมาณของรายการที่แสดง คำนวณจากประมาณการซื้อคูณราคาเฉลี่ยต่อหน่วย',
];
$metricTooltipPrefixes = [
    'ประมาณการปริมาณใช้ในปี ' => 'ประมาณการใช้ของวัสดุแต่ละรายการ คำนวณจากค่าเฉลี่ยปริมาณการใช้ย้อนหลังใน StockMonthlyReport ตามปีงบประมาณและแหล่งข้อมูลที่เลือก',
    'ประมาณการปริมาณซื้อในปี ' => 'จำนวนที่ควรจัดซื้อของวัสดุแต่ละรายการ คำนวณจากประมาณการใช้หักด้วยยอดคงเหลือต้นงวดหรือยอดคงเหลือปัจจุบัน และไม่ให้ติดลบ',
    'มูลค่าจัดซื้อปี ' => 'มูลค่าจัดซื้อโดยประมาณของวัสดุแต่ละรายการ คำนวณจากประมาณการซื้อคูณราคาเฉลี่ยต่อหน่วย',
];
$tooltipLabel = static function (string $label) use ($metricTooltips, $metricTooltipPrefixes): string {
    $tooltip = $metricTooltips[$label] ?? null;
    if ($tooltip === null) {
        foreach ($metricTooltipPrefixes as $prefix => $prefixTooltip) {
            if (strpos($label, $prefix) === 0) {
                $tooltip = $prefixTooltip;
                break;
            }
        }
    }

    if ($tooltip === null) {
        return Html::encode($label);
    }

    return Html::tag(
        'span',
        Html::encode($label) . ' ' . Html::tag('i', '', [
            'class' => 'fa-solid fa-circle-info text-secondary',
            'aria-hidden' => 'true',
        ]),
        [
            'class' => 'd-inline-flex align-items-center gap-1',
            'tabindex' => '0',
            'role' => 'button',
            'data-bs-toggle' => 'tooltip',
            'data-bs-placement' => 'top',
            'data-bs-title' => $tooltip,
            'title' => $tooltip,
            'aria-label' => $label . ': ' . $tooltip,
        ]
    );
};

$activeFilters = [
    ['label' => 'ปีงบประมาณ', 'value' => (string) $fiscalYear],
    ['label' => 'คลังหลัก', 'value' => $warehouseId ? ($warehouses[$warehouseId] ?? 'ไม่ระบุ') : 'ทุกคลังหลัก'],
    ['label' => 'ประเภทพัสดุ', 'value' => $categoryId !== '' ? ($categories[$categoryId] ?? 'ไม่ระบุ') : 'ทุกประเภทพัสดุ'],
    ['label' => 'แหล่งข้อมูล', 'value' => $dataSources[$dataSource] ?? 'ไม่ระบุ'],
];
if ($q !== '') {
    $activeFilters[] = ['label' => 'คำค้นหา', 'value' => $q];
}

$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();

$this->beginBlock('sub-title');
echo 'คำนวณจากรายงานประจำเดือนและส่งออก Excel ตาม template ที่ตัดค่าคงที่แล้ว';
$this->endBlock();

$this->beginBlock('page-action');
echo $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'report-procurement-plan']);
$this->endBlock();
?>

<div class="d-grid gap-3">
    <section class="card shadow-sm">
        <div class="card-header bg-light">
            <h2 class="h6 fw-semibold mb-0">ตัวกรองรายงาน</h2>
        </div>
        <div class="card-body">
            <?= Html::beginForm(['/inventory-v2/report/procurement-plan'], 'get', ['class' => 'row g-3 align-items-end']) ?>
                <label class="col-12 col-md-6 col-xl-2">
                    <span class="form-label small fw-semibold text-secondary">ปีงบประมาณ</span>
                    <?= Html::input('number', 'fiscal_year', $fiscalYear, [
                        'class' => 'form-control',
                        'min' => 2400,
                        'max' => 2800,
                        'step' => 1,
                    ]) ?>
                </label>

                <label class="col-12 col-md-6 col-xl-2">
                    <span class="form-label small fw-semibold text-secondary">คลังหลัก</span>
                    <?= Html::dropDownList('warehouse_id', $warehouseId, $warehouses, [
                        'class' => 'form-select',
                    ]) ?>
                </label>

                <label class="col-12 col-md-6 col-xl-2">
                    <span class="form-label small fw-semibold text-secondary">ประเภทพัสดุ</span>
                    <?= Html::dropDownList('category_id', $categoryId, $categories, [
                        'class' => 'form-select',
                    ]) ?>
                </label>

                <label class="col-12 col-md-6 col-xl-2">
                    <span class="form-label small fw-semibold text-secondary">แหล่งข้อมูล</span>
                    <?= Html::dropDownList('data_source', $dataSource, $dataSources, [
                        'class' => 'form-select',
                    ]) ?>
                </label>

                <label class="col-12 col-xl-3">
                    <span class="form-label small fw-semibold text-secondary">ค้นหารายการ</span>
                    <span class="input-group">
                        <span class="input-group-text bg-white text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <?= Html::textInput('q', $q, [
                            'class' => 'form-control',
                            'placeholder' => 'ชื่อวัสดุ รหัสวัสดุ หรือประเภท',
                        ]) ?>
                    </span>
                </label>

                <div class="col-12 col-xl-3 d-grid d-sm-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill text-nowrap">
                        <i class="bi bi-funnel me-1"></i>
                        แสดงรายงาน
                    </button>
                    <a href="<?= Url::to(['/inventory-v2/report/procurement-plan']) ?>" class="btn btn-outline-secondary flex-fill text-nowrap">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        ล้างตัวกรอง
                    </a>
                </div>
            <?= Html::endForm() ?>
        </div>
    </section>

    <section class="card shadow-sm">
        <div class="card-header bg-light d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3">
            <div>
                <h2 class="h6 fw-semibold mb-1">แผนจัดซื้อวัสดุ ปีงบประมาณ <?= Html::encode($fiscalYear) ?></h2>
                <p class="small text-secondary mb-2">ค่าเฉลี่ยการใช้ย้อนหลัง 3 ปีงบประมาณ</p>
                <div class="d-flex flex-wrap gap-2" aria-label="ตัวกรองที่ใช้">
                    <?php foreach ($activeFilters as $filter): ?>
                        <span class="badge rounded-pill bg-white text-dark border fw-semibold">
                            <span class="text-secondary fw-normal"><?= Html::encode($filter['label']) ?>:</span>
                            <?= Html::encode($filter['value']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                <span class="badge rounded-pill bg-white text-dark border py-2 px-3"><?= $formatter->asInteger(count($rows)) ?> รายการ</span>
                <a href="<?= Url::to($exportParams) ?>" class="btn btn-success text-nowrap" data-procurement-plan-export data-fallback-filename="procurement-plan-<?= Html::encode($fiscalYear) ?>.xlsx">
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    ส่งออก Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="border rounded bg-light p-3 mb-3">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="small text-secondary mb-1"><?= $tooltipLabel('ประมาณการใช้รวม') ?></div>
                        <div class="fw-semibold font-monospace"><?= $formatter->asDecimal($totalEstimatedUse, 2) ?></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="small text-secondary mb-1"><?= $tooltipLabel('ประมาณการซื้อรวม') ?></div>
                        <div class="fw-semibold font-monospace"><?= $formatter->asDecimal($totalPurchaseQty, 2) ?></div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="small text-secondary mb-1"><?= $tooltipLabel('มูลค่าจัดซื้อรวม') ?></div>
                        <div class="fw-semibold font-monospace"><?= $formatter->asDecimal($totalPurchaseValue, 2) ?> บาท</div>
                    </div>
                </div>
            </div>

            <?php if (empty($rows)): ?>
                <div class="d-flex align-items-center gap-3 border rounded bg-light p-4">
                    <i class="bi bi-inbox fs-3 text-secondary"></i>
                    <div>
                        <h3 class="h6 fw-semibold mb-1">ไม่มีข้อมูลรายงาน</h3>
                        <p class="small text-secondary mb-0">ลองเปลี่ยนปีงบประมาณ คลังหลัก หรือคำค้นหา</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive border rounded">
                    <table class="table table-sm table-hover table-bordered align-middle mb-0 small">
                        <caption class="visually-hidden">รายงานแผนจัดซื้อวัสดุ ปีงบประมาณ <?= Html::encode($fiscalYear) ?></caption>
                        <thead class="table-light">
                            <tr>
                                <?php foreach ($headers as $header): ?>
                                    <th scope="col" class="text-nowrap"><?= $tooltipLabel($header) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td class="text-center text-nowrap font-monospace"><?= $formatter->asInteger($row['seq']) ?></td>
                                    <td><?= Html::encode($row['plan_type']) ?></td>
                                    <td><?= Html::encode($row['category_plan']) ?></td>
                                    <td class="text-break fw-medium text-dark"><?= Html::encode($row['item_name']) ?></td>
                                    <td><?= Html::encode($row['packaging_size']) ?></td>
                                    <td><?= Html::encode($row['unit_name']) ?></td>
                                    <td class="text-end text-nowrap font-monospace"><?= $formatter->asDecimal($row['usage_year_1'], 2) ?></td>
                                    <td class="text-end text-nowrap font-monospace"><?= $formatter->asDecimal($row['usage_year_2'], 2) ?></td>
                                    <td class="text-end text-nowrap font-monospace"><?= $formatter->asDecimal($row['usage_year_3'], 2) ?></td>
                                    <td class="text-end text-nowrap font-monospace"><?= $formatter->asDecimal($row['opening_inventory_qty'], 2) ?></td>
                                    <td class="text-end text-nowrap font-monospace"><?= $formatter->asDecimal($row['estimated_amount_used'], 2) ?></td>
                                    <td class="text-end text-nowrap font-monospace"><?= $formatter->asDecimal($row['estimated_purchase_quantity'], 2) ?></td>
                                    <td class="text-end text-nowrap font-monospace"><?= $formatter->asDecimal($row['unit_price'], 2) ?></td>
                                    <td class="text-end text-nowrap font-monospace"><?= $formatter->asDecimal($row['purchase_vol_in_year'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
$this->registerJs(<<<'JS'
(function () {
    if (window.bootstrap && window.bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
            window.bootstrap.Tooltip.getOrCreateInstance(element);
        });
    }

    var exportButton = document.querySelector('[data-procurement-plan-export]');
    if (!exportButton) {
        return;
    }

    var getFileName = function (response, fallbackFileName) {
        var disposition = response.headers.get('Content-Disposition') || response.headers.get('content-disposition') || '';
        var utf8Match = disposition.match(/filename\*=UTF-8''([^;]+)/i);
        if (utf8Match && utf8Match[1]) {
            return decodeURIComponent(utf8Match[1].replace(/"/g, ''));
        }

        var asciiMatch = disposition.match(/filename="?([^"]+)"?/i);
        if (asciiMatch && asciiMatch[1]) {
            return asciiMatch[1];
        }

        return fallbackFileName;
    };

    exportButton.addEventListener('click', function (event) {
        event.preventDefault();

        if (!window.Swal) {
            window.location.href = exportButton.href;
            return;
        }

        Swal.fire({
            icon: 'question',
            title: 'ส่งออก Excel',
            text: 'ต้องการส่งออกรายงานแผนจัดซื้อวัสดุเป็นไฟล์ Excel หรือไม่',
            showCancelButton: true,
            confirmButtonText: 'ส่งออก',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#198754',
            reverseButtons: false
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            Swal.fire({
                title: 'กำลังส่งออกไฟล์',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });

            fetch(exportButton.href, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('export_failed');
                    }

                    return response.blob().then(function (blob) {
                        return {
                            blob: blob,
                            fileName: getFileName(response, exportButton.dataset.fallbackFilename || 'procurement-plan.xlsx')
                        };
                    });
                })
                .then(function (payload) {
                    var url = window.URL.createObjectURL(payload.blob);
                    var link = document.createElement('a');
                    link.href = url;
                    link.download = payload.fileName;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.URL.revokeObjectURL(url);

                    Swal.fire({
                        icon: 'success',
                        title: 'ส่งออกสำเร็จ',
                        text: 'ดาวน์โหลดไฟล์ Excel เรียบร้อยแล้ว',
                        timer: 1800,
                        showConfirmButton: false
                    });
                })
                .catch(function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'ส่งออกไม่สำเร็จ',
                        text: 'ไม่สามารถส่งออกไฟล์ Excel ได้ กรุณาลองใหม่อีกครั้ง'
                    });
                });
        });
    });
})();
JS);
?>
