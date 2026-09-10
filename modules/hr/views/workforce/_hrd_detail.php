<?php

/**
 * ตารางรายชื่อเบื้องหลังตัวเลข KPI (แสดงใน modal) — drill-down ของ HRD overview
 *
 * @var yii\web\View $this
 * @var array $data ['title','columns','rows','empty'] จาก HrdMetricsService::detail()
 *   - เซลล์เป็น string ปกติ หรือ ['t' => ข้อความ, 'align' => 'end'] สำหรับจัดชิดขวา
 */

use yii\helpers\Html;

$columns = $data['columns'] ?? [];
$rows = $data['rows'] ?? [];
?>
<?php if (empty($rows)): ?>
    <div class="text-center text-body-secondary py-5">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        <div><?= Html::encode($data['empty'] ?? 'ไม่มีข้อมูล') ?></div>
    </div>
<?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="badge bg-primary-subtle text-primary-emphasis">รวม <?= number_format(count($rows)) ?> รายการ</span>
    </div>
    <div class="table-responsive" style="max-height:60vh;overflow:auto">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light" style="position:sticky;top:0;z-index:1">
                <tr>
                    <th style="width:2.5rem" class="text-end text-body-secondary">#</th>
                    <?php foreach ($columns as $col): ?>
                        <th class="<?= ($col['align'] ?? '') === 'end' ? 'text-end' : '' ?>"><?= Html::encode($col['label']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $i => $row): ?>
                    <tr>
                        <td class="text-end text-body-secondary small"><?= $i + 1 ?></td>
                        <?php foreach ($row as $cell): ?>
                            <?php if (is_array($cell)): ?>
                                <td class="<?= ($cell['align'] ?? '') === 'end' ? 'text-end' : '' ?>"><?= Html::encode($cell['t'] ?? '') ?></td>
                            <?php else: ?>
                                <td><?= Html::encode((string) $cell) ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
