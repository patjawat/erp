<?php
use yii\helpers\Html;

?>
<?php if (!empty($usageHistory)): ?>
                    <div class="card shadow-sm border-0 border-top border-primary border-2 mt-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 text-primary fw-bold">
                                <i class="bi bi-clock-history me-2"></i>ประวัติการตัดจ่าย/การใช้งาน (ล่าสุด)
                            </h6>
                            <div class="text-muted small mt-1">
                                คลังที่เลือก: <?= Html::encode($currentWarehouseId !== null ? (string) $currentWarehouseId : 'ทั้งหมด') ?>
                                <?php if (!empty($_GET['helpdesk_id'])): ?>
                                    | ค้นตาม helpdesk_id: <?= Html::encode((string) $_GET['helpdesk_id']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:22%">เลขที่เอกสาร</th>
                                            <th style="width:18%">วันที่</th>
                                            <th style="width:20%">งาน/Job</th>
                                            <th>อ้างอิง</th>
                                            <th style="width:14%" class="text-end">จำนวนรวม</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usageHistory as $o): ?>
                                            <?php
                                            $dj = $o->data_json;
                                            if (is_string($dj)) {
                                                $dj = json_decode($dj, true) ?: [];
                                            }
                                            $jobType = (string) ($dj['job_type'] ?? '');
                                            $reference = (string) ($dj['reference'] ?? '');

                                            // สำหรับเอกสารจากงานซ่อม (repair-parts) เราเก็บไว้ใน data_json[repair_number]
                                            if ($jobType === '' && isset($dj['repair_number']) && (string) $dj['repair_number'] !== '') {
                                                $jobType = 'ซ่อม #' . (string) $dj['repair_number'];
                                            }
                                            // สำหรับเอกสารจากงานซ่อม reference อาจไม่ได้ถูกเติมใน data_json['reference']
                                            if ($reference === '') {
                                                if (isset($dj['helpdesk_id']) && (string) $dj['helpdesk_id'] !== '') {
                                                    $reference = 'helpdesk_id=' . (string) $dj['helpdesk_id'];
                                                }
                                            }
                                            $totalQty = 0.0;
                                            $details = is_array($o->stockDetails) ? $o->stockDetails : [];
                                            foreach ($details as $d) {
                                                $totalQty += (float) ($d->qty ?? 0);
                                            }
                                            ?>
                                            <tr>
                                                <td class="fw-bold"><?= Html::encode($o->order_no) ?></td>
                                                <td class="text-nowrap">
                                                    <?php
                                                    $ts = $o->order_date ? strtotime($o->order_date) : null;
                                                    echo $ts ? Html::encode(date('d/m/Y H:i', $ts)) : '-';
                                                    ?>
                                                </td>
                                                <td><?= Html::encode($jobType !== '' ? $jobType : '-') ?></td>
                                                <td>
                                                    <?= Html::encode($reference !== '' ? $reference : '-') ?>
                                                    <?php if (!empty($_GET['helpdesk_id'])): ?>
                                                        <div class="text-muted small mt-1">
                                                            source_type: <?= Html::encode((string) ($o->source_type ?? '-')) ?>,
                                                            status: <?= Html::encode((string) ($o->status ?? '-')) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end fw-semibold"><?= number_format($totalQty, 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mt-4 mb-0" role="alert">
                        ยังไม่มีประวัติการตัดจ่าย/การใช้งานสำหรับคลังนี้
                    </div>
                <?php endif; ?>
        