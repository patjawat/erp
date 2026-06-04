<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายงานวัสดุคงคลังแยกรายการ';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'รายงานวัสดุคงคลัง', 'url' => ['/inventory-v2/report/material-summary']];
$this->params['breadcrumbs'][] = $this->title;

$monthNames = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
];
$periodLabel = isset($monthNames[$month]) ? $monthNames[$month] . ' ' . ($year + 543) : '';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-list-ul fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">รายงานแยกรายการสินค้า — ยอดยกมา / รับเข้า / จ่ายออก / คงเหลือสิ้นเดือน (จำนวนและมูลค่า)</p>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4">

    <?php
    $extraParams = ['year' => $year, 'month' => $month]
        + ($warehouseId ? ['warehouse_id' => $warehouseId] : [])
        + ($assetType ? ['asset_type_id' => $assetType] : []);
    ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary-gradient text-white py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="text-white mb-0 fw-normal"><i class="bi bi-search me-1"></i>การค้นหา</h6>
            <div>
                <a href="<?= Url::to(array_merge(['/inventory-v2/report/material-summary'], $extraParams)) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-pie-chart"></i> สรุปตามประเภท
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" action="<?= Url::to(['/inventory-v2/report/material-by-item']) ?>" class="row g-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0">เดือน</label>
                    <select name="month" class="form-select" style="min-width: 140px;">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= (int)$month === $m ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">ปี (พ.ศ.)</label>
                    <select name="year" class="form-select" style="min-width: 100px;">
                        <?php for ($y = date('Y') + 543; $y >= (date('Y') + 543 - 5); $y--): ?>
                            <option value="<?= $y - 543 ?>" <?= (int)$year === ($y - 543) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">คลัง</label>
                    <select name="warehouse_id" class="form-select" style="min-width: 180px;">
                        <?php foreach ($warehouses as $wid => $wname): ?>
                            <option value="<?= $wid === '' ? '' : (int)$wid ?>" <?= (string)$warehouseId === (string)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">ประเภทวัสดุ</label>
                    <select name="asset_type_id" class="form-select" style="min-width: 220px;">
                        <?php foreach ($assetTypes as $tcode => $tname): ?>
                            <option value="<?= $tcode === '' ? '' : Html::encode($tcode) ?>" <?= (string)$assetType === (string)$tcode ? 'selected' : '' ?>><?= Html::encode($tname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> แสดงรายงาน
                    </button>
                    <?php if ($hasData): ?>
                        <a href="<?= Url::to(array_merge(['/inventory-v2/report/export-excel-by-item'], $extraParams)) ?>" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (!$hasData): ?>
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>
            ยังไม่มีข้อมูลปิดเดือนสำหรับเดือน<?= isset($monthNames[$month]) ? ' ' . $monthNames[$month] . ' ' . ($year + 543) : '' ?> — ไปที่ <a href="<?= Url::to(['/inventory-v2/report/material-summary']) ?>">สรุปรายงานวัสดุคงคลัง</a> เพื่อปิดเดือนก่อน
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-secondary bg-opacity-10 py-2 px-3 d-flex flex-wrap justify-content-between align-items-center">
                <h6 class="mb-0 fw-normal">รายงานแยกรายการ — <?= Html::encode($periodLabel) ?></h6>
                <span class="text-muted">ทั้งหมด <?= number_format(count($rows)) ?> รายการ</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 3%;">ลำดับ</th>
                                <th style="width: 10%;">รหัสสินค้า</th>
                                <th style="width: 22%;">รายการสินค้า</th>
                                <th style="width: 15%;">ประเภทวัสดุ</th>
                                <th colspan="2" class="text-center">ยอดยกมา</th>
                                <th colspan="2" class="text-center">รับเข้า</th>
                                <th colspan="2" class="text-center">จ่ายออก</th>
                                <th colspan="2" class="text-center">คงเหลือสิ้นเดือน</th>
                            </tr>
                            <tr class="text-center text-muted">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>จำนวน</th>
                                <th>มูลค่า</th>
                                <th>จำนวน</th>
                                <th>มูลค่า</th>
                                <th>จำนวน</th>
                                <th>มูลค่า</th>
                                <th>จำนวนคงเหลือ</th>
                                <th>มูลค่าคงเหลือ</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php
                            $totOq = $totOv = $totIq = $totIv = $totOoq = $totOov = $totCq = $totCv = 0;
                            foreach ($rows as $i => $r):
                                $totOq += (float)$r['opening_qty'];
                                $totOv += (float)$r['opening_value'];
                                $totIq += (float)$r['in_qty'];
                                $totIv += (float)$r['in_value'];
                                $totOoq += (float)$r['total_out_qty'];
                                $totOov += (float)$r['total_out_value'];
                                $totCq += (float)$r['closing_qty'];
                                $totCv += (float)$r['closing_value'];
                            ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td><?= Html::encode($r['item_code']) ?></td>
                                    <td><?= Html::encode($r['item_name']) ?></td>
                                    <td><?= Html::encode($r['category_title']) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['opening_qty'], 2) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['opening_value'], 2) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['in_qty'], 2) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['in_value'], 2) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['total_out_qty'], 2) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['total_out_value'], 2) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['closing_qty'], 2) ?></td>
                                    <td class="text-end"><?= number_format((float)$r['closing_value'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-warning fw-bold">
                                <td class="text-center"></td>
                                <td colspan="3">รวมทั้งหมด</td>
                                <td class="text-end"><?= number_format($totOq, 2) ?></td>
                                <td class="text-end"><?= number_format($totOv, 2) ?></td>
                                <td class="text-end"><?= number_format($totIq, 2) ?></td>
                                <td class="text-end"><?= number_format($totIv, 2) ?></td>
                                <td class="text-end"><?= number_format($totOoq, 2) ?></td>
                                <td class="text-end"><?= number_format($totOov, 2) ?></td>
                                <td class="text-end"><?= number_format($totCq, 2) ?></td>
                                <td class="text-end"><?= number_format($totCv, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
