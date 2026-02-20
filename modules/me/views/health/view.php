<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\health\models\HealthScreen;

/**
 * @var yii\web\View $this
 * @var app\modules\health\models\HealthScreen $model
 */

$d = $model->data_json ?? [];
$labVal = $d['lab_val'] ?? [];
$bp2 = isset($d['bp_2_sys']) && isset($d['bp_2_dia']) ? trim($d['bp_2_sys']) . '/' . trim($d['bp_2_dia']) : (isset($d['bp_1_sys']) && isset($d['bp_1_dia']) ? trim($d['bp_1_sys']) . '/' . trim($d['bp_1_dia']) : null);
$waist = $d['waistline'] ?? null;
$fbs = $labVal['fbs'] ?? null;
$chol = $labVal['chol'] ?? null;
$finalSummary = $d['final_summary'] ?? null;
$summaryLabel = $finalSummary ? HealthScreen::getFinalSummaryDisplay($finalSummary, 'label') : null;
$bmiResult = $model->getBmiResult();
?>
<div class="health-result-view">
    <div class="text-center mb-4">
        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
            <i class="fas fa-heart-pulse fa-2x"></i>
        </div>
        <h4 class="fw-bold mb-1">รายงานผลการตรวจสุขภาพประจำปี</h4>
        <p class="text-muted mb-0">ปีงบประมาณ <?= Html::encode($model->thai_year) ?></p>
    </div>

    <!-- ข้อมูลผู้รับการตรวจ -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="text-muted text-uppercase small fw-bold mb-3"><i class="far fa-user me-1"></i> ข้อมูลผู้รับการตรวจ</h6>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="small text-muted">ชื่อ-นามสกุล</div>
                    <div class="fw-bold"><?= Html::encode($model->employee->fullname ?? '-') ?></div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="small text-muted">อายุ</div>
                    <div class="fw-bold"><?= Html::encode($model->employee->age ?? '-') ?> ปี</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="small text-muted">น้ำหนัก</div>
                    <div class="fw-bold"><?= $model->weight ? Html::encode($model->weight) . ' kg' : '-' ?></div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="small text-muted">ส่วนสูง</div>
                    <div class="fw-bold"><?= $model->height ? Html::encode($model->height) . ' cm' : '-' ?></div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="small text-muted">BMI</div>
                    <div class="fw-bold <?= (float)$model->bmi >= 25 ? 'text-danger' : 'text-primary' ?>">
                        <?= $model->bmi !== null && $model->bmi !== '' ? number_format((float)$model->bmi, 1) : '-' ?>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="small text-muted">ดัชนีมวลกาย</div>
                    <span class="badge bg-<?= $bmiResult['color'] ?> bg-opacity-10 text-<?= $bmiResult['color'] ?> border border-<?= $bmiResult['color'] ?>-subtle rounded-pill"><?= $bmiResult['label'] ?></span>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small text-muted">วันที่ตรวจ</div>
                    <div class="fw-bold"><?= $model->date_checkup ? Yii::$app->formatter->asDate($model->date_checkup) : '-' ?></div>
                </div>
                <?php if (!empty($model->appointment_date)): ?>
                <div class="col-6 col-md-3">
                    <div class="small text-muted">วันที่นัดหมาย</div>
                    <div class="fw-bold"><?= Yii::$app->formatter->asDate($model->appointment_date) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ค่าตรวจหลัก -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="text-muted text-uppercase small fw-bold mb-3"><i class="fas fa-vials me-1"></i> ค่าตรวจหลัก</h6>
            <div class="row g-2">
                <?php
                $items = [
                    ['BMI', $model->bmi !== null && $model->bmi !== '' ? number_format((float)$model->bmi, 1) : '-', null],
                    ['รอบเอว (ซม.)', $waist !== null && $waist !== '' ? $waist : '-', null],
                    ['ความดัน (mmHg)', $bp2 ?: '-', null],
                    ['น้ำตาล FBS (mg%)', $fbs !== null && $fbs !== '' ? $fbs : '-', null],
                    ['ไขมัน CHOL (mg%)', $chol !== null && $chol !== '' ? $chol : '-', null],
                ];
                foreach ($items as $s):
                ?>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 rounded-3 bg-light border text-center">
                            <div class="small text-muted mb-1"><?= $s[0] ?></div>
                            <div class="fw-bold text-dark"><?= Html::encode($s[1]) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- รายการ Lab ที่ยืนยัน -->
    <?php $labs = $model->labs; if (!empty($labs)): ?>
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="text-muted text-uppercase small fw-bold mb-3"><i class="fas fa-flask me-1"></i> รายการ Lab</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>รายการ</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-end">ราคา/หน่วย</th>
                            <th class="text-end">รวม</th>
                            <th>ผลตรวจ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($labs as $lab): 
                            $labName = ($lab->lab !== null) ? $lab->lab->lab_name : $lab->lab_code;
                        ?>
                        <tr>
                            <td><?= Html::encode($labName) ?></td>
                            <td class="text-center"><?= (int)$lab->qty ?></td>
                            <td class="text-end">฿<?= number_format((float)$lab->lab_price, 2) ?></td>
                            <td class="text-end">฿<?= number_format((float)$lab->qty * (float)$lab->lab_price, 2) ?></td>
                            <td><?= Html::encode($lab->lab_result !== null && $lab->lab_result !== '' ? $lab->lab_result : '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-2 small text-muted">รวมค่าตรวจ Lab ฿<?= number_format($model->labTotalPrice(), 2) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- บันทึกการวินิจฉัยและแผนการดูแล -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="text-muted text-uppercase small fw-bold mb-3"><i class="fas fa-clipboard-list me-1"></i> บันทึกการวินิจฉัยและแผนการดูแล</h6>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="small fw-bold text-muted d-block mb-1">โรคที่ตรวจพบ / โรคประจำตัว</label>
                    <?php
                    $history = $d['history_diseases'] ?? [];
                    $historyOther = $d['history_other_detail'] ?? '';
                    if (is_array($history)) {
                        $historyText = implode(', ', array_filter($history));
                        if ($historyText !== '' || $historyOther !== '') {
                            echo Html::encode($historyText . ($historyOther ? ' ' . $historyOther : ''));
                        } else {
                            echo '-';
                        }
                    } else {
                        echo Html::encode($historyOther ?: '-');
                    }
                    ?>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold text-muted d-block mb-1">คำแนะนำ</label>
                    <p class="mb-0"><?= !empty($d['advice']) ? nl2br(Html::encode($d['advice'])) : '-' ?></p>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold text-muted d-block mb-1">แผนการดูแล</label>
                    <?php
                    $planOptions = [
                        'fbs_6_m' => 'ไขมันในเลือดสูง และ CVD Risk>10% นัดตรวจ lipid profile 6 เดือน',
                        'lipid_6_m' => 'FBS สูง เสี่ยงต่อเบาหวาน นัดตรวจ FBS 3-6 เดือน',
                        'pre_ht' => 'Pre HT นัดตรวจ BP 3 เดือน',
                        'bv_carrier' => 'BV carrier นัดตรวจ ALT',
                        'bmi_25' => 'BMI>25',
                        'cxr_afb' => 'CXR ผิดปกติ นัดติดตาม AFB',
                        'cancer_ref' => 'ญาติป่วยเป็นมะเร็งชนิดเกี่ยวข้องพันธุกรรม นัดคัดกรองมะเร็ง',
                        'other' => 'ผิดปกติอื่น ๆ รอผลตรวจเพิ่มเติมเพื่อยืนยัน',
                    ];
                    $plan = $d['management_plan'] ?? [];
                    if (is_array($plan) && !empty($plan)) {
                        echo '<ul class="mb-0 ps-3 small">';
                        foreach ($plan as $key) {
                            echo '<li>' . Html::encode($planOptions[$key] ?? $key) . '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '-';
                    }
                    ?>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold text-muted d-block mb-1">นัดตรวจครั้งต่อไป</label>
                    <?php
                    $nextLabels = ['1_w' => '1 สัปดาห์', '1_m' => '1 เดือน', '3_m' => '3 เดือน', '6_m' => '6 เดือน', '1_y' => '1 ปี'];
                    $next = $d['next_appointment'] ?? '';
                    echo Html::encode($nextLabels[$next] ?? ($next ?: '-'));
                    ?>
                </div>
            </div>
            <?php if ($summaryLabel): ?>
            <div class="mt-4 pt-4 border-top">
                <label class="small fw-bold text-muted d-block mb-1">สรุปผลการตรวจสุขภาพ (Overall Summary)</label>
                <span class="badge bg-<?= HealthScreen::getFinalSummaryDisplay($finalSummary, 'color') ?> bg-opacity-10 text-<?= HealthScreen::getFinalSummaryDisplay($finalSummary, 'color') ?> border border-<?= HealthScreen::getFinalSummaryDisplay($finalSummary, 'color') ?>-subtle rounded-pill px-3 py-2 fw-bold"><?= $summaryLabel ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
