<?php

use app\components\ThaiDateHelper;
use app\modules\health\models\HealthScreen;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var app\modules\health\models\HealthScreen $model
 */

$this->title = 'ผลตรวจสุขภาพ';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลสุขภาพ', 'url' => ['/health/health-screen/index']];
$this->params['breadcrumbs'][] = $this->title;

$data       = is_array($model->data_json) ? $model->data_json : [];
$labVals    = $data['lab_val'] ?? [];
$peResult   = $data['pe_result'] ?? [];
$peDetail   = $data['pe_detail'] ?? [];
$finalSummary = $data['final_summary'] ?? null;
$sumLabel   = $model::getFinalSummaryDisplay($finalSummary, 'label');
$sumColor   = $model::getFinalSummaryDisplay($finalSummary, 'color');
$sumIcon    = $model::getFinalSummaryDisplay($finalSummary, 'icon');
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="scan-heart"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/health/menu', ['active' => 'list']) ?>
<?php $this->endBlock(); ?>

<?= $this->render('patient_profile', ['model' => $model]) ?>

<?php if ($finalSummary): ?>
<div class="alert d-flex align-items-center gap-3 border-0 shadow-sm rounded-3 mb-4 bg-<?= $sumColor ?>-subtle">
    <i class="<?= $sumIcon ?> fs-3 text-<?= $sumColor ?>"></i>
    <div>
        <div class="fw-bold text-<?= $sumColor ?>">สรุปผลสุขภาพ: <?= Html::encode($sumLabel) ?></div>
        <div class="small text-muted"><?= Html::encode($model::getFinalSummaryDisplay($finalSummary, 'desc')) ?></div>
    </div>
    <div class="ms-auto">
        <?= $model->viewStatus() ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <!-- ข้อมูลพฤติกรรม -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary-gradient text-white py-2">
                <h6 class="mb-0 text-white small fw-normal"><i class="fas fa-clipboard-list me-1"></i> พฤติกรรมสุขภาพ</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <tbody class="align-middle table-group-divider">
                        <?php
                        $foodTasteMap = ['sweet' => 'หวาน', 'salty' => 'เค็ม', 'fatty' => 'มัน', 'sour' => 'เปรี้ยว', 'none' => 'ไม่ชอบทุกข้อ'];
                        $ftRaw = $data['food_taste'] ?? '';
                        $foodTasteDisplay = '-';
                        if (is_array($ftRaw)) {
                            $foodTasteDisplay = implode(', ', array_map(function ($v) use ($foodTasteMap) {
                                return $foodTasteMap[$v] ?? $v;
                            }, $ftRaw)) ?: '-';
                        } elseif ($ftRaw !== '' && isset($foodTasteMap[$ftRaw])) {
                            $foodTasteDisplay = $foodTasteMap[$ftRaw];
                        }
                        $behaviorItems = [
                            ['icon' => 'fas fa-smoking',         'label' => 'บุหรี่',        'value' => ['smoke' => 'สูบ', 'none' => 'ไม่สูบ', 'quit' => 'เคยสูบแต่เลิกแล้ว'][$data['smoking_status'] ?? ''] ?? '-'],
                            ['icon' => 'fas fa-glass-whiskey',   'label' => 'แอลกอฮอล์',    'value' => ['drink' => 'ดื่ม', 'none' => 'ไม่ดื่ม', 'quit' => 'เคยดื่มแต่เลิกแล้ว'][$data['alcohol_status'] ?? ''] ?? '-'],
                            ['icon' => 'fas fa-running',         'label' => 'ออกกำลังกาย',  'value' => [
                                'everyday'     => 'ออกกำลังทุกวัน',
                                '3_times_week' => 'ออก 3 ครั้ง/สัปดาห์',
                                'less_than_3'  => 'น้อยกว่า 3 ครั้ง/สัปดาห์',
                                'none'         => 'ไม่ออกกำลังกาย',
                            ][$data['exercise_status'] ?? ''] ?? '-'],
                            ['icon' => 'fas fa-utensils',        'label' => 'รสอาหาร',       'value' => $foodTasteDisplay],
                            ['icon' => 'fas fa-car',             'label' => 'ขับขี่ปลอดภัย', 'value' => ['none' => 'ไม่ขับขี่', 'always' => 'ทุกครั้ง', 'sometimes' => 'บางครั้ง', 'rarely' => 'นานๆ ครั้ง'][$data['driving_safety'] ?? ''] ?? '-'],
                        ];
                        foreach ($behaviorItems as $bi): ?>
                        <tr>
                            <td class="ps-3 text-muted small" style="width:140px;"><i class="<?= $bi['icon'] ?> me-2"></i><?= $bi['label'] ?></td>
                            <td class="fw-medium"><?= Html::encode($bi['value']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ประวัติเจ็บป่วยปีก่อน -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary-gradient text-white py-2">
                <h6 class="mb-0 text-white small fw-normal"><i class="fas fa-history me-1"></i> ประวัติเจ็บป่วยปีก่อน</h6>
            </div>
            <div class="card-body p-0">
                <?php
                $diseaseLabels = [
                    'h_diabetes' => 'เบาหวาน',      'h_hypertension' => 'ความดันสูง',
                    'h_liver'    => 'โรคตับ',         'h_stroke'       => 'อัมพาต',
                    'h_heart'    => 'โรคหัวใจ',       'h_dyslipidemia' => 'ไขมันเลือดผิดปกติ',
                    'h_gastric'  => 'แผลในกระเพาะ',  'h_fatigue'      => 'อ่อนเพลีย',
                ];
                $valueMap = [0 => ['label' => 'ไม่มี', 'color' => 'success'], 1 => ['label' => 'มี', 'color' => 'danger'], 2 => ['label' => 'ไม่เคยตรวจ', 'color' => 'secondary']];
                ?>
                <table class="table table-hover align-middle mb-0">
                    <tbody class="align-middle table-group-divider">
                        <?php foreach ($diseaseLabels as $key => $dLabel): ?>
                        <?php $val = $data[$key] ?? 0; $vMap = $valueMap[$val] ?? $valueMap[0]; ?>
                        <tr>
                            <td class="ps-3 small text-muted"><?= $dLabel ?></td>
                            <td>
                                <span class="badge bg-<?= $vMap['color'] ?> bg-opacity-10 text-<?= $vMap['color'] ?> border border-<?= $vMap['color'] ?>-subtle rounded-pill fw-medium px-2 py-1">
                                    <?= $vMap['label'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ผล LAB -->
<?php if (!empty($model->labs)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary-gradient text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-white small fw-normal"><i class="fas fa-vials me-1"></i> รายการตรวจ LAB</h6>
        <span class="badge bg-light bg-opacity-10 text-white border border-light-subtle rounded-pill fw-medium px-2 py-1">
            ค่าใช้จ่ายรวม: ฿<?= number_format($model->labTotalPrice(), 2) ?>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">รหัส</th>
                        <th>รายการ</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-end">ราคา/หน่วย</th>
                        <th class="text-end pe-3">รวม</th>
                        <th class="text-center">ผลตรวจ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php foreach ($model->labs as $lab): ?>
                    <tr>
                        <td class="ps-3"><code class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($lab->lab_code) ?></code></td>
                        <td class="fw-medium"><?= Html::encode($lab->lab ? $lab->lab->lab_name : $lab->lab_code) ?></td>
                        <td class="text-center"><?= $lab->qty ?></td>
                        <td class="text-end">฿<?= number_format($lab->lab_price, 2) ?></td>
                        <td class="text-end pe-3 fw-bold text-primary">฿<?= number_format($lab->lab_price * $lab->qty, 2) ?></td>
                        <td class="text-center">
                            <?php if ($lab->lab_result): ?>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($lab->lab_result) ?></span>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ผลการตรวจร่างกาย -->
<?php if (!empty($data['bp_1_sys']) || !empty($labVals) || !empty($data['history_status'])): ?>
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary-gradient text-white py-2">
                <h6 class="mb-0 text-white small fw-normal"><i class="fas fa-heartbeat me-1"></i> Vital Signs &amp; Lab Values</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <tbody class="align-middle table-group-divider">
                        <tr>
                            <td class="ps-3 text-muted small">ความดันโลหิต ครั้งที่ 1</td>
                            <td class="fw-medium"><?= Html::encode(($data['bp_1_sys'] ?? '-') . ' / ' . ($data['bp_1_dia'] ?? '-')) ?> mmHg</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted small">ความดันโลหิต ครั้งที่ 2</td>
                            <td class="fw-medium"><?= Html::encode(($data['bp_2_sys'] ?? '-') . ' / ' . ($data['bp_2_dia'] ?? '-')) ?> mmHg</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted small">รอบเอว</td>
                            <td class="fw-medium"><?= Html::encode($data['waistline'] ?? '-') ?> ซม.</td>
                        </tr>
                        <?php
                        $labDisplay = ['fbs' => 'FBS', 'chol' => 'CHOL', 'tg' => 'TG', 'hdl' => 'HDL', 'ldl' => 'LDL'];
                        foreach ($labDisplay as $lk => $ll): ?>
                        <tr>
                            <td class="ps-3 text-muted small"><?= $ll ?></td>
                            <td class="fw-medium"><?= Html::encode($labVals[$lk] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary-gradient text-white py-2">
                <h6 class="mb-0 text-white small fw-normal"><i class="fas fa-stethoscope me-1"></i> ความเห็นแพทย์</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($data['history_diseases'])): ?>
                <div class="mb-3">
                    <small class="text-muted d-block mb-2">โรคที่พบ</small>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($data['history_diseases'] as $disease): ?>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($disease) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($data['advice'])): ?>
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">คำแนะนำ</small>
                    <p class="mb-0 small"><?= nl2br(Html::encode($data['advice'])) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($data['management_plan'])): ?>
                <div class="mb-3">
                    <small class="text-muted d-block mb-2">แผนการดูแล</small>
                    <?php foreach ($data['management_plan'] as $plan): ?>
                    <div class="small"><i class="fas fa-check text-success me-1"></i><?= Html::encode($plan) ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($data['next_appointment'])): ?>
                <div>
                    <small class="text-muted d-block mb-1">นัดตรวจครั้งต่อไป</small>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">
                        <?= Html::encode(['1_w' => '1 สัปดาห์', '1_m' => '1 เดือน', '3_m' => '3 เดือน', '6_m' => '6 เดือน', '1_y' => '1 ปี'][$data['next_appointment']] ?? $data['next_appointment']) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="d-flex gap-3 mt-2">
    <?= Html::a('<i class="fas fa-arrow-left me-2"></i> กลับรายการ', ['/health/health-screen/index'], ['class' => 'btn btn-light py-2 px-4 rounded-3 border']) ?>
    <?= Html::a('<i class="fas fa-print me-2"></i> พิมพ์รายงาน', ['/health/health-screen/print', 'id' => $model->id], ['class' => 'btn btn-primary py-2 px-4 rounded-3 shadow-sm', 'target' => '_blank']) ?>
</div>
