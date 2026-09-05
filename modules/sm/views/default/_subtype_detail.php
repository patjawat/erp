<?php

/**
 * รายละเอียดตามประเภทพัสดุย่อย — เลือกดูทั้งปี หรือรายเดือน
 * สไตล์ Dashboard V2 · Bootstrap theme classes
 *
 * @var yii\web\View $this
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 */

use app\modules\sm\services\PurchaseDashboardService;
use yii\helpers\Url;

$months = PurchaseDashboardService::FISCAL_MONTHS;
$labels = PurchaseDashboardService::MONTH_LABELS;
$ajaxUrl = Url::to(['/sm/default/subtype-detail']);
$year = $dashboard->year;
?>
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-primary bg-opacity-10">
                <i class="bi bi-list-nested text-primary"></i>
            </div>
            <div>
                <h6 class="text-body-secondary m-0">รายละเอียดตามประเภทพัสดุ</h6>
                <p class="small text-body-secondary mb-0 d-none d-md-block">ตรวจรับแล้วควรเข้าคลังในเดือนเดียวกัน — ช่อง "ค้างเข้าคลัง" คือส่วนที่ยังไม่ลงคลัง</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label for="smSubtypeMonth" class="small text-body-secondary mb-0">ช่วงเวลา</label>
            <select id="smSubtypeMonth" class="form-select form-select-sm" style="width:auto;">
                <option value="">ทั้งปี <?= $year ?></option>
                <?php foreach ($months as $i => $m): ?>
                    <option value="<?= $m ?>"><?= $labels[$i] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div id="smSubtypeBody">
            <?= $this->render('_subtype_table', ['dashboard' => $dashboard, 'month' => null]) ?>
        </div>
    </div>
</div>
<?php
$js = <<< JS
(function () {
    var sel = document.getElementById('smSubtypeMonth');
    var body = document.getElementById('smSubtypeBody');
    if (!sel || !body) return;
    sel.addEventListener('change', function () {
        body.style.opacity = '0.5';
        \$.ajax({
            type: 'get',
            url: '$ajaxUrl',
            data: { thai_year: '$year', month: sel.value },
            dataType: 'json',
            success: function (res) { body.innerHTML = res.content; },
            complete: function () { body.style.opacity = '1'; }
        });
    });
})();
JS;
$this->registerJS($js);
