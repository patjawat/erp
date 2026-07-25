<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeeDetail $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Employee Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="health-screening-form py-5">
    <div class="container">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 900px; border-radius: 20px;">
            <div class="card-body p-4 p-md-5 bg-white" style="border-radius: 20px;">
                <div class="step-content text-center" data-step="9">
                    <div class="py-5">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 70px; height: 70px;">
                            <i class="fas fa-check fa-2x"></i>
                        </div>
                        <h3 class="fw-bold mb-1">รายงานสรุปผลการตรวจสุขภาพประจำปี</h3>
                        <p class="text-muted fw-medium">ประจำปีงบประมาณ 2026</p>
                    </div>

                    <div class="row g-2 mb-4 px-md-4">
                       
                        <?php
                        $summary = [
                            ['BMI', 'out-bmi', $model->data_json['bmi'] ?? '-'],
                            ['รอบเอว', 'out-waist', $model->data_json['waistCircumference'] ?? '-'],
                            ['ความดัน', 'out-bp', $model->data_json['bloodPressure'] ?? '-'],
                            ['น้ำตาล', 'out-fbs', $model->data_json['bloodSugar'] ?? '-'],
                            ['ไขมัน', 'out-chol', $model->data_json['cholesterol'] ?? '-'],
                            ['ภาวะซีด', 'out-anemia', $model->data_json['anemiaStatus'] ?? '-']
                        ];
                        foreach ($summary as $s):
                        ?>
                            <div class="col-4 col-md-2">
                                <div class="summary-card">
                                    <p class="small text-muted fw-bold text-uppercase mb-1" style="font-size: 10px;"><?= $s[0] ?></p>
                                    <p class="h5 mb-0 text-dark" id="<?= $s[1] ?>"><?= $s[2] ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="diagnosis-box text-start px-4">
                        <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                            <i class="fas fa-clipboard-list text-primary me-2"></i> <span class="fw-bold">บันทึกการวินิจฉัยและแผนการดูแล</span>
                        </div>
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted text-uppercase d-block mb-1" style="font-size: 10px;">โรคที่ตรวจพบ</label>
                                <p class="fw-bold mb-0" id="res-diag"><?= $model->data_json['diagnosis'] ?? '-'?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted text-uppercase d-block mb-1" style="font-size: 10px;">แผนการดูแล</label>
                                <p class="mb-0" id="res-plan"><?= $model->data_json['carePlan'] ?? '-'?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted text-uppercase d-block mb-1" style="font-size: 10px;">ปัจจัยเสี่ยง</label>
                                <p class="fw-bold mb-0" id="res-risk"><?= $model->data_json['riskFactors'] ?? '-'?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted text-uppercase d-block mb-1" style="font-size: 10px;">คำแนะนำ</label>
                                <p class="mb-0" id="res-advice"><?= $model->data_json['medicalAdvice'] ?? '-'?></p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-top">
                            <p class="small fw-bold text-primary text-uppercase mb-1" style="font-size: 10px;">สรุปผลการตรวจสุขภาพ (OVERALL SUMMARY)</p>
                            <p class="fw-bold text-dark mb-0" id="res-summary"><?= $model->data_json['overallSummary'] ?? '-'?></p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-5">
                        <a href="<?= Url::to(['/health/health-screen/index', 'HealthScreenSearch[emp_id]' => $model->emp_id]) ?>" class="btn btn-light flex-grow-1 py-3 fw-bold rounded-4 border">
                            กลับหน้าโปรไฟล์
                        </a>

                        <?= Html::submitButton('<i class="fas fa-print me-2"></i> พิมพ์รายงานผล', ['class' => 'btn btn-primary flex-grow-1 py-3 fw-bold rounded-4 shadow-lg']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
