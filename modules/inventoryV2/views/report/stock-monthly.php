<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายงานสรุปคงคลังรายเดือน (ปิดเดือน)';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$monthNames = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
];
$periodLabel = $monthNames[$reportMonth] . ' ' . ($reportYear + 543);
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-calendar-check fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0 small">Snapshot ระดับรายการพัสดุของเดือนที่กด "ปิดเดือน" แล้ว — ทั้ง V1 และ V2 ใช้ตารางเดียวกัน ต่อเนื่องอัตโนมัติ</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/menu', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0"><?= Yii::$app->session->getFlash('success') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0"><?= Yii::$app->session->getFlash('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('seed_import_report')): $rep = Yii::$app->session->getFlash('seed_import_report'); ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-info bg-opacity-10 d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bi bi-upload me-1"></i> ผลการนำเข้ายอดยกมา — <?= Html::encode($rep['period']) ?></h6>
                <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($rep['skipped_token'])): ?>
                        <a class="btn btn-sm btn-outline-warning" href="<?= Url::to(['/inventory-v2/report/stock-monthly-seed-skipped-download', 'token' => $rep['skipped_token']]) ?>">
                            <i class="bi bi-download me-1"></i> ดาวน์โหลด CSV รายการที่ข้าม
                        </a>
                    <?php endif; ?>
                    <span class="small text-muted">บันทึก = closing ของเดือนนี้ → opening ของเดือนถัดไป</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-2">
                    <div class="col"><div class="border rounded p-2 text-center"><div class="text-success fs-5 fw-bold"><?= number_format($rep['inserted']) ?></div><small class="text-muted">เพิ่มใหม่</small></div></div>
                    <div class="col"><div class="border rounded p-2 text-center"><div class="text-primary fs-5 fw-bold"><?= number_format($rep['updated']) ?></div><small class="text-muted">อัปเดตทับ</small></div></div>
                    <?php if (!empty($rep['auto_created'])): ?>
                    <div class="col"><div class="border rounded p-2 text-center"><div class="text-info fs-5 fw-bold"><?= number_format($rep['auto_created']) ?></div><small class="text-muted">สร้าง stock_item จาก legacy</small></div></div>
                    <?php endif; ?>
                    <div class="col"><div class="border rounded p-2 text-center"><div class="text-warning fs-5 fw-bold"><?= number_format($rep['skip_total']) ?></div><small class="text-muted">ข้ามแถวที่ผิด</small></div></div>
                    <div class="col"><div class="border rounded p-2 text-center"><div class="text-muted fs-5 fw-bold"><?= number_format($rep['skip_empty']) ?></div><small class="text-muted">ข้ามแถวว่าง</small></div></div>
                </div>
                <?php
                $skipCounts = [
                    'missing_wh' => $rep['skip_missing_wh_count'] ?? count($rep['skip_missing_wh'] ?? []),
                    'missing_item' => $rep['skip_missing_item_count'] ?? count($rep['skip_missing_item'] ?? []),
                    'bad_number' => $rep['skip_bad_number_count'] ?? count($rep['skip_bad_number'] ?? []),
                    'no_match' => $rep['skip_no_match_count'] ?? count($rep['skip_no_match'] ?? []),
                    'ambiguous' => $rep['skip_ambiguous_count'] ?? count($rep['skip_ambiguous'] ?? []),
                ];
                $hasSkips = array_sum($skipCounts) > 0;
                ?>
                <?php if ($hasSkips): ?>
                    <details class="mt-2">
                        <summary class="text-warning small" style="cursor:pointer;">ดูรายละเอียดแถวที่ข้าม (<?= number_format($rep['skip_total']) ?>)</summary>
                        <div class="mt-2 small">
                            <?php if ($skipCounts['missing_wh'] > 0): $shown = $rep['skip_missing_wh'] ?? []; $more = $skipCounts['missing_wh'] - count($shown); ?>
                                <div class="mb-2">
                                    <strong>ไม่พบคลัง (<?= number_format($skipCounts['missing_wh']) ?>)</strong>
                                    <ul class="mb-0">
                                        <?php foreach ($shown as $s): ?>
                                            <li>แถว <?= (int)$s['row'] ?> — warehouse_name: <code><?= Html::encode($s['warehouse_name']) ?></code>, item_code: <code><?= Html::encode($s['item_code']) ?></code></li>
                                        <?php endforeach; ?>
                                        <?php if ($more > 0): ?><li class="text-muted">... และอีก <?= number_format($more) ?> แถว</li><?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($skipCounts['missing_item'] > 0): $shown = $rep['skip_missing_item'] ?? []; $more = $skipCounts['missing_item'] - count($shown); ?>
                                <div class="mb-2">
                                    <strong>ไม่พบ item_code (<?= number_format($skipCounts['missing_item']) ?>)</strong>
                                    <ul class="mb-0">
                                        <?php foreach ($shown as $s): ?>
                                            <li>แถว <?= (int)$s['row'] ?> — item_code: <code><?= Html::encode($s['item_code']) ?></code></li>
                                        <?php endforeach; ?>
                                        <?php if ($more > 0): ?><li class="text-muted">... และอีก <?= number_format($more) ?> แถว</li><?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($skipCounts['no_match'] > 0): $shown = $rep['skip_no_match'] ?? []; $more = $skipCounts['no_match'] - count($shown); ?>
                                <div class="mb-2">
                                    <strong>ไม่พบคลังหลักที่รับประเภทวัสดุนี้ (<?= number_format($skipCounts['no_match']) ?>)</strong>
                                    <div class="text-muted">วิธีแก้: ระบุ <code>warehouse_name</code> ใน CSV หรือไปตั้งค่า "ประเภทวัสดุที่รับ" ที่หน้าคลังหลัก</div>
                                    <ul class="mb-0">
                                        <?php foreach ($shown as $s): ?>
                                            <li>แถว <?= (int)$s['row'] ?> — item_code: <code><?= Html::encode($s['item_code']) ?></code> (category: <code><?= Html::encode($s['category_id'] ?? '—') ?></code>)</li>
                                        <?php endforeach; ?>
                                        <?php if ($more > 0): ?><li class="text-muted">... และอีก <?= number_format($more) ?> แถว</li><?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($skipCounts['ambiguous'] > 0): $shown = $rep['skip_ambiguous'] ?? []; $more = $skipCounts['ambiguous'] - count($shown); ?>
                                <div class="mb-2">
                                    <strong>มีหลายคลังที่รับประเภทนี้ — ต้องระบุ warehouse_name (<?= number_format($skipCounts['ambiguous']) ?>)</strong>
                                    <ul class="mb-0">
                                        <?php foreach ($shown as $s): ?>
                                            <li>แถว <?= (int)$s['row'] ?> — item_code: <code><?= Html::encode($s['item_code']) ?></code> (category: <code><?= Html::encode($s['category_id'] ?? '—') ?></code>) → คลังที่รับ: <em><?= Html::encode(implode(', ', $s['candidates'] ?? [])) ?></em></li>
                                        <?php endforeach; ?>
                                        <?php if ($more > 0): ?><li class="text-muted">... และอีก <?= number_format($more) ?> แถว</li><?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($skipCounts['bad_number'] > 0): $shown = $rep['skip_bad_number'] ?? []; $more = $skipCounts['bad_number'] - count($shown); ?>
                                <div class="mb-2">
                                    <strong>จำนวน/มูลค่าไม่ใช่ตัวเลข (<?= number_format($skipCounts['bad_number']) ?>)</strong>
                                    <ul class="mb-0">
                                        <?php foreach ($shown as $s): ?>
                                            <li>แถว <?= (int)$s['row'] ?> — item_code: <code><?= Html::encode($s['item_code']) ?></code></li>
                                        <?php endforeach; ?>
                                        <?php if ($more > 0): ?><li class="text-muted">... และอีก <?= number_format($more) ?> แถว</li><?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="get" action="<?= Url::to(['/inventory-v2/report/stock-monthly']) ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-md-auto">
                        <label class="form-label small text-muted mb-1">เดือน</label>
                        <select name="report_month" class="form-select form-select-sm">
                            <?php foreach ($monthNames as $m => $n): ?>
                                <option value="<?= $m ?>" <?= (int)$reportMonth === $m ? 'selected' : '' ?>><?= $n ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <label class="form-label small text-muted mb-1">ปี</label>
                        <select name="report_year" class="form-select form-select-sm">
                            <?php for ($y = date('Y') + 1; $y >= date('Y') - 5; $y--): ?>
                                <option value="<?= $y ?>" <?= (int)$reportYear === $y ? 'selected' : '' ?>><?= $y + 543 ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">ประเภทวัสดุ</label>
                        <select name="asset_type_id" class="form-select form-select-sm">
                            <option value="">-- ทุกประเภท --</option>
                            <?php foreach ($assetTypeOptions as $code => $label): ?>
                                <option value="<?= Html::encode($code) ?>" <?= (string)$assetType === (string)$code ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">คลังหลัก</label>
                        <select name="warehouse_id" class="form-select form-select-sm">
                            <?php foreach ($warehouseOptions as $wid => $wname): ?>
                                <option value="<?= $wid === '' ? '' : (int)$wid ?>" <?= (string)$warehouseId === (string)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">ค้นหา</label>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="รหัส/ชื่อวัสดุ" value="<?= Html::encode($q) ?>">
                    </div>
                    <div class="col-md d-flex gap-2 justify-content-md-end flex-wrap">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> ค้นหา</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-seed-import"><i class="bi bi-upload me-1"></i> นำเข้ายอดยกมา (CSV)</button>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modal-generate"><i class="bi bi-calendar-check me-1"></i> ปิดเดือน</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-normal">
                <i class="bi bi-table me-1"></i> รายการพัสดุปิดเดือน <?= Html::encode($periodLabel) ?>
                <span class="badge text-bg-secondary ms-1"><?= number_format(count($rows)) ?></span>
            </h6>
            <small class="text-muted">มูลค่าคงเหลือรวม <strong class="text-primary"><?= number_format($summary['closing_value'], 2) ?></strong> บาท</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh;">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light text-center" style="position: sticky; top: 0; z-index: 5;">
                        <tr>
                            <th rowspan="2" style="width:40px;">#</th>
                            <th rowspan="2" style="width:110px;">รหัส</th>
                            <th rowspan="2">ชื่อวัสดุ</th>
                            <th rowspan="2" style="width:160px;">ประเภท</th>
                            <th rowspan="2" style="width:140px;">คลัง</th>
                            <th colspan="2" class="bg-info bg-opacity-25">ยกมา</th>
                            <th colspan="2" class="bg-success bg-opacity-25">รับเข้า</th>
                            <th colspan="3" class="bg-warning bg-opacity-25">จ่ายออก</th>
                            <th colspan="2" class="bg-primary bg-opacity-25">คงเหลือ</th>
                        </tr>
                        <tr>
                            <th class="bg-info bg-opacity-10">จำนวน</th>
                            <th class="bg-info bg-opacity-10">มูลค่า</th>
                            <th class="bg-success bg-opacity-10">จำนวน</th>
                            <th class="bg-success bg-opacity-10">มูลค่า</th>
                            <th class="bg-warning bg-opacity-10">รพ.สต.</th>
                            <th class="bg-warning bg-opacity-10">โรงพยาบาล</th>
                            <th class="bg-warning bg-opacity-10">มูลค่ารวม</th>
                            <th class="bg-primary bg-opacity-10">จำนวน</th>
                            <th class="bg-primary bg-opacity-10">มูลค่า</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="14" class="text-center text-muted py-4">
                            ยังไม่มีข้อมูลปิดเดือน — กรุณาเลือกคลังด้านบนแล้วกด <strong>ปิดเดือน</strong> เพื่อคำนวณ
                        </td></tr>
                    <?php else: foreach ($rows as $i => $r): ?>
                        <tr>
                            <td class="text-center text-muted"><?= $i + 1 ?></td>
                            <td class="font-monospace small"><?= Html::encode($r['item_code']) ?></td>
                            <td><?= Html::encode($r['item_name'] ?: $r['item_code']) ?></td>
                            <td class="small"><?= Html::encode($r['asset_type_name'] ?: '—') ?></td>
                            <td class="small"><?= Html::encode($r['warehouse_name'] ?: '—') ?></td>
                            <td class="text-end"><?= number_format((float)$r['opening_qty'], 2) ?></td>
                            <td class="text-end"><?= number_format((float)$r['opening_value'], 2) ?></td>
                            <td class="text-end text-success fw-semibold"><?= number_format((float)$r['in_qty'], 2) ?></td>
                            <td class="text-end"><?= number_format((float)$r['in_value'], 2) ?></td>
                            <td class="text-end"><?= number_format((float)$r['out_sub_qty'], 2) ?></td>
                            <td class="text-end"><?= number_format((float)$r['out_hosp_qty'], 2) ?></td>
                            <td class="text-end text-warning-emphasis"><?= number_format((float)$r['total_out_value'], 2) ?></td>
                            <td class="text-end fw-bold"><?= number_format((float)$r['closing_qty'], 2) ?></td>
                            <td class="text-end fw-bold text-primary"><?= number_format((float)$r['closing_value'], 2) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                    <?php if (!empty($rows)): ?>
                    <tfoot class="table-warning" style="position: sticky; bottom: 0;">
                        <tr class="fw-bold">
                            <td colspan="5" class="text-center">รวม</td>
                            <td class="text-end"><?= number_format($summary['opening_qty'], 2) ?></td>
                            <td class="text-end"><?= number_format($summary['opening_value'], 2) ?></td>
                            <td class="text-end text-success"><?= number_format($summary['in_qty'], 2) ?></td>
                            <td class="text-end"><?= number_format($summary['in_value'], 2) ?></td>
                            <td class="text-end"><?= number_format($summary['out_sub_qty'], 2) ?></td>
                            <td class="text-end"><?= number_format($summary['out_hosp_qty'], 2) ?></td>
                            <td class="text-end text-warning-emphasis"><?= number_format($summary['total_out_value'], 2) ?></td>
                            <td class="text-end"><?= number_format($summary['closing_qty'], 2) ?></td>
                            <td class="text-end text-primary"><?= number_format($summary['closing_value'], 2) ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal ปิดเดือน -->
<div class="modal fade" id="modal-generate" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= Url::to(['/inventory-v2/report/stock-monthly-generate']) ?>">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-check me-1"></i> ปิดเดือน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">ระบบจะคำนวณยอดจากธุรกรรมรับ-จ่ายทั้ง V1 (stock_events) และ V2 (stock_order) ในเดือนที่เลือก แล้วบันทึกลงตาราง stock_monthly_report</p>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label small text-muted">เดือน</label>
                            <select name="report_month" class="form-select">
                                <?php foreach ($monthNames as $m => $n): ?>
                                    <option value="<?= $m ?>" <?= (int)$reportMonth === $m ? 'selected' : '' ?>><?= $n ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label small text-muted">ปี</label>
                            <select name="report_year" class="form-select">
                                <?php for ($y = date('Y') + 1; $y >= date('Y') - 5; $y--): ?>
                                    <option value="<?= $y ?>" <?= (int)$reportYear === $y ? 'selected' : '' ?>><?= $y + 543 ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">คลัง</label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value="">-- เลือกคลัง --</option>
                            <option value="all">ปิดรวมทุกคลังหลัก</option>
                            <?php foreach ($warehouseOptions as $wid => $wname): ?>
                                <?php if ($wid !== ''): ?>
                                    <option value="<?= (int)$wid ?>" <?= (int)$warehouseId === (int)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-calendar-check me-1"></i> ปิดเดือน</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal นำเข้ายอดยกมา (CSV) -->
<div class="modal fade" id="modal-seed-import" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" action="<?= Url::to(['/inventory-v2/report/stock-monthly-seed-import']) ?>" enctype="multipart/form-data">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-upload me-1"></i> นำเข้ายอดยกมา (CSV)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <strong>วิธีใช้:</strong> CSV ที่นำเข้าจะเขียนเป็น <strong>ยอด closing ของเดือนที่เลือก</strong>
                        เพื่อให้เดือนถัดไป (ตอน "ปิดเดือน") ดึงไปเป็น <strong>ยอดยกมา (opening)</strong> โดยอัตโนมัติ
                        <br>
                        <strong>คอลัมน์ที่ต้องมี:</strong> <code>item_code</code>, <code>closing_qty</code>, <code>closing_value</code>
                        <br>
                        <strong>คอลัมน์ optional:</strong> <code>warehouse_name</code> — ถ้าเว้นว่าง ระบบจะ map คลังหลักให้อัตโนมัติตามประเภทวัสดุที่คลังตั้งค่าไว้ใน "ประเภทวัสดุที่รับ"
                        <br>
                        <a href="<?= Url::to(['/inventory-v2/report/stock-monthly-seed-template']) ?>" class="btn btn-sm btn-outline-info mt-2">
                            <i class="bi bi-download me-1"></i> ดาวน์โหลดเทมเพลต CSV
                        </a>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">เดือนของยอดยกมา</label>
                            <select name="report_month" class="form-select" required>
                                <?php foreach ($monthNames as $m => $n): ?>
                                    <option value="<?= $m ?>" <?= (int)$reportMonth === $m ? 'selected' : '' ?>><?= $n ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">ปี</label>
                            <select name="report_year" class="form-select" required>
                                <?php for ($y = date('Y') + 1; $y >= date('Y') - 5; $y--): ?>
                                    <option value="<?= $y ?>" <?= (int)$reportYear === $y ? 'selected' : '' ?>><?= $y + 543 ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">ไฟล์ CSV</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,text/csv" required>
                        <div class="form-text small">ไฟล์ต้องเข้ารหัส UTF-8 (รองรับ BOM) — แถวที่ไม่ตรงกับฐานข้อมูลจะถูกข้ามและสรุปให้</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i> นำเข้า</button>
                </div>
            </div>
        </form>
    </div>
</div>
