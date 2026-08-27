<?php
use yii\helpers\Html;

/** @var app\modules\am\models\Asset $model */
/** @var array $schedule */
/** @var string $calculationMethod */
/** @var array $calculationMethods */

$year = (int) ($model->useful_life ?? 0);
$rate = (float) ($model->depreciation_rate ?? 0);
$price = (float) ($model->price ?? 0);
$rows = $schedule['rows'] ?? [];
$selectedMethod = $calculationMethods[$calculationMethod];
$currentRow = null;
foreach ($rows as $row) {
    if (!empty($row['end_date']) && $row['end_date'] <= date('Y-m-d')) {
        $currentRow = $row;
    }
}
$currentNet = $currentRow ? (float) $currentRow['total'] : $price;
?>

<?php if ($year <= 0 || empty($model->receive_date) || $price <= 0): ?>
    <div class="alert alert-warning mb-0" role="alert">
        <div class="fw-semibold">ยังคำนวณค่าเสื่อมไม่ได้</div>
        <div>กรุณาตรวจสอบราคาทุน วันที่ตรวจรับ และอายุการใช้งานของครุภัณฑ์ให้ครบถ้วน</div>
    </div>
<?php else: ?>
    <section class="border rounded-3 bg-body-tertiary p-3 mb-3" aria-labelledby="depreciation-method-heading">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
            <div>
                <h6 id="depreciation-method-heading" class="fw-semibold mb-1">เลือกวิธีคำนวณสำหรับการแสดงผลและพิมพ์</h6>
                <p class="text-body-secondary mb-0">การเลือกนี้ใช้เฉพาะหน้ารายงาน ไม่แก้ไขเกณฑ์ถาวรหรือข้อมูลบัญชีที่บันทึกแล้ว</p>
            </div>
            <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill">กำลังใช้: <?= Html::encode($selectedMethod['label']) ?></span>
        </div>
        <div class="row g-2" role="list" aria-label="วิธีคำนวณค่าเสื่อมราคา">
            <?php foreach ($calculationMethods as $key => $method): ?>
                <div class="col-sm-6" role="listitem">
                    <?= Html::a(
                        '<span class="d-block fw-semibold mb-1">' . Html::encode($method['label']) . '</span>'
                        . '<span class="d-block small ' . ($key === $calculationMethod ? '' : 'text-body-secondary') . '">' . Html::encode($method['description']) . '</span>',
                        ['/am/asset/depreciation', 'id' => $model->id, 'calculation_method' => $key],
                        [
                            'class' => 'js-depreciation-method btn text-start w-100 h-100 p-3 ' . ($key === $calculationMethod ? 'btn-primary' : 'btn-outline-secondary'),
                            'aria-current' => $key === $calculationMethod ? 'true' : null,
                            'data' => ['size' => 'modal-lg'],
                        ]
                    ) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="alert alert-success mb-3" aria-label="สรุปค่าเสื่อมราคา">
        <div class="row g-3">
            <div class="col-md-6"><dl class="row mb-0">
                <dt class="col-6">หมายเลขครุภัณฑ์</dt><dd class="col-6 text-end text-danger"><?= Html::encode($model->code) ?></dd>
                <dt class="col-6">วันที่ตรวจรับ</dt><dd class="col-6 text-end"><?= Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium') ?></dd>
                <dt class="col-6">อัตราค่าเสื่อม</dt><dd class="col-6 text-end"><?= number_format($rate, 2) ?>%</dd>
                <dt class="col-6">อายุการใช้งาน</dt><dd class="col-6 text-end"><?= number_format($year) ?> ปี</dd>
            </dl></div>
            <div class="col-md-6"><dl class="row mb-0">
                <dt class="col-6">ราคาทุน</dt><dd class="col-6 text-end"><?= number_format($price, 2) ?> บาท</dd>
                <dt class="col-6">ค่าเสื่อมต่อปี</dt><dd class="col-6 text-end"><?= number_format((float) $schedule['annual_amount'], 2) ?> บาท</dd>
                <dt class="col-6">ค่าเสื่อมเต็มเดือน</dt><dd class="col-6 text-end"><?= number_format((float) $schedule['monthly_amount'], 2) ?> บาท</dd>
                <dt class="col-6">มูลค่าสุทธิปัจจุบัน</dt><dd class="col-6 text-end fw-semibold"><?= number_format($currentNet, 2) ?> บาท</dd>
            </dl></div>
        </div>
        <hr>
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-circle-info mt-1" aria-hidden="true"></i>
            <div><span class="fw-semibold"><?= Html::encode($selectedMethod['label']) ?>:</span> <?= Html::encode($selectedMethod['description']) ?></div>
        </div>
    </section>

    <?php if (!$schedule['can_calculate']): ?>
        <div class="alert alert-warning" role="alert"><?= Html::encode($schedule['message']) ?></div>
    <?php else: ?>
        <div class="table-responsive border rounded-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark"><tr>
                    <th class="text-center" scope="col">งวดที่</th><th class="text-center" scope="col">เดือน</th>
                    <th class="text-end" scope="col">จำนวนวัน</th><th class="text-end" scope="col">ค่าเสื่อมงวดนี้</th>
                    <th class="text-end" scope="col">ค่าเสื่อมสะสม</th><th class="text-end" scope="col">มูลค่าสุทธิ</th>
                    <th class="text-center" scope="col">พิมพ์</th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $data): ?>
                    <tr class="<?= $data['active'] === 'Y' ? 'table-primary' : '' ?>">
                        <td class="text-center"><?= number_format((int) $data['date_number']) ?></td>
                        <td class="text-center"><?= Yii::$app->thaiFormatter->asDate($data['end_date'], 'medium') ?></td>
                        <td class="text-end"><?= number_format((int) $data['count_days']) ?></td>
                        <td class="text-end"><?= number_format((float) $data['price_month'], 2) ?></td>
                        <td class="text-end"><?= number_format((float) $data['total_price'], 2) ?></td>
                        <td class="text-end fw-semibold"><?= number_format((float) $data['total'], 2) ?></td>
                        <td class="text-center"><?= Html::a(
                            '<i class="fa-solid fa-file-pdf" aria-hidden="true"></i><span class="visually-hidden">พิมพ์ PDF งวดที่ ' . (int) $data['date_number'] . '</span>',
                            ['/am/asset/depreciation-pdf', 'id' => $model->id, 'number' => $data['date_number'], 'date' => $data['end_date'], 'calculation_method' => $calculationMethod],
                            ['class' => 'btn btn-sm btn-outline-danger', 'target' => '_blank', 'rel' => 'noopener noreferrer', 'data-pjax' => 0]
                        ) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
$this->registerJs(<<<'JS'
if (!window.erpDepreciationMethodHandlerBound) {
    window.erpDepreciationMethodHandlerBound = true;
    document.addEventListener('click', function (event) {
        var link = event.target.closest('.js-depreciation-method');
        if (!link) return;
        var modal = link.closest('.modal');
        if (!modal) return;
        event.preventDefault();
        fetch(link.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                var title = modal.querySelector('.modal-title');
                var body = modal.querySelector('.modal-body');
                if (title && payload.title) title.innerHTML = payload.title;
                if (body && payload.content) body.innerHTML = payload.content;
            });
    });
}
JS);
?>
