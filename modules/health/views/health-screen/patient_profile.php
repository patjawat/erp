<?php

use yii\helpers\Html;
?>
<div class="mb-4">
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="bg-primary py-1"></div>
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
                <div class="d-flex gap-4">
                    <div class="profile-avatar shadow-sm d-flex align-items-center justify-content-center bg-light rounded-3" style="width: 100px; height: 100px; border: 1px solid #eee;">
                        <?= Html::img($model->employee->showAvatar() ?? '/images/default-avatar.png', ['class' => 'img-fluid']) ?>
                    </div>
                    <div>
                        <div class="mb-1">
                            <h3 class="text-primary py-1 mb-2 text-uppercase">Physical Examination</h3>
                        </div>
                        <h3 class="mb-2 text-dark" style="letter-spacing: -0.5px;">
                            <?= $model->employee->fullname ?? 'ไม่ระบุชื่อ' ?>
                        </h3>
                        <div class="d-flex gap-3 text-muted">
                            <span><i class="fas fa-id-card-alt me-1"></i> อายุ: <strong><?= $model->employee->age ?></strong></span>
                            <span><i class="fas fa-building me-1"></i> แผนก: <strong><?= $model->employee->departmentName() ?? 'ไม่ระบุ' ?></strong></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class="vitals-card px-5 py-4 rounded-3 bg-light text-center border">
                        <div class="small text-muted uppercase tracking-tighter" style="font-size: 10px;">Weight</div>
                        <div class="fw-bold mb-0"><?= $model->weight ?> <small class="fw-normal">kg</small></div>
                    </div>
                    <div class="vitals-card px-5 py-4 rounded-3 bg-light text-center border">
                        <div class="small text-muted uppercase tracking-tighter" style="font-size: 10px;">Height</div>
                        <div class="fw-bold mb-0"><?= $model->height ?> <small class="fw-normal">cm</small></div>
                    </div>
                    <div class="vitals-card px-5 py-4 rounded-3 bg-light text-center border">
                        <div class="small text-muted uppercase tracking-tighter" style="font-size: 10px;">BMI</div>
                        <?php $bmi = (float)($model->bmi); ?>
                        <div class="fw-bold mb-0 <?= $bmi >= 25 ? 'text-danger' : 'text-primary' ?>"><?= number_format($bmi, 1) ?></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                <div class="d-flex gap-4">
                    <div class="small text-muted d-flex align-items-center gap-2">
                        <div class="erp-icon-box">
                            <i class="far fa-calendar-check"></i>
                        </div>
                        ปีงบประมาณ: <span class="text-dark fw-bold"><?= $model->thai_year ?></span>
                    </div>
                    <div class="small text-muted d-flex align-items-center gap-2">
                        <div class="erp-icon-box">
                            <i class="far fa-clock me-1"></i>
                        </div> วันที่ตรวจสุขภาพ: <span class="text-dark fw-bold"><?= Yii::$app->formatter->asDate($model->date_checkup) ?></span>
                    </div>
                </div>

                <div class="text-end">
                    <?php
                    $bmiResult = $model->getBmiResult();
                    ?>
                    <span class="small text-muted me-2">ดัชนีมวลกาย:</span>
                    <span class="badge bg-<?= $bmiResult['color']; ?> bg-opacity-10 text-<?= $bmiResult['color']; ?> border border-<?= $bmiResult['color']; ?>-subtle rounded-pill fw-medium px-2 py-1"> <?= $bmiResult['label']; ?></span>

                </div>
            </div>
        </div>
    </div>
</div>