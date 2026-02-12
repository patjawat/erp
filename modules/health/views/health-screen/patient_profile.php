<?php
use yii\helpers\Html;
?>
<div class="patient-info-banner mb-4">
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="bg-success py-1"></div>
        
        <div class="card-body bg-white p-4">
            <div class="d-flex align-items-start justify-content-between">
                
                <div class="d-flex gap-4">
                    <div class="profile-avatar shadow-sm d-flex align-items-center justify-content-center bg-light rounded-3" style="width: 100px; height: 100px; border: 1px solid #eee;">
                        <?= Html::img($model->employee->showAvatar() ?? '/images/default-avatar.png', ['class' => 'img-fluid rounded-circle']) ?>
                    </div>
                    
                    <div>
                        <div class="mb-1">
                            <span class="badge bg-soft-success text-success px-2 py-1 mb-2 fw-bold text-uppercase">Physical Examination</span>
                        </div>
                        <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">
                            <?php  echo Html::encode($model->employee->fullname ?? 'ไม่ระบุชื่อ') ?>
                        </h3>
                        <div class="d-flex gap-3 text-muted">
                            <span><i class="fas fa-id-card-alt me-1"></i> ID: <strong><?= $model->emp_id ?></strong></span>
                            <span><i class="fas fa-building me-1"></i> แผนก: <strong><?= $model->department ?? 'ไม่ระบุ' ?></strong></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class="vitals-card px-3 py-2 rounded-3 bg-light text-center border">
                        <div class="small text-muted uppercase tracking-tighter" style="font-size: 10px;">Weight</div>
                        <div class="fw-bold h5 mb-0"><?= $model->data_json['weight'] ?? '-' ?> <small class="fw-normal">kg</small></div>
                    </div>
                    <div class="vitals-card px-3 py-2 rounded-3 bg-light text-center border">
                        <div class="small text-muted uppercase tracking-tighter" style="font-size: 10px;">Height</div>
                        <div class="fw-bold h5 mb-0"><?= $model->data_json['height'] ?? '-' ?> <small class="fw-normal">cm</small></div>
                    </div>
                    <div class="vitals-card px-3 py-2 rounded-3 bg-light text-center border">
                        <div class="small text-muted uppercase tracking-tighter" style="font-size: 10px;">BMI</div>
                        <?php $bmi = (float)($model->data_json['bmi'] ?? 0); ?>
                        <div class="fw-bold h5 mb-0 <?= $bmi >= 25 ? 'text-danger' : 'text-primary' ?>"><?= number_format($bmi, 1) ?></div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                <div class="d-flex gap-4">
                    <div class="small text-muted">
                        <i class="far fa-calendar-check me-1"></i> Checkup Year: <span class="text-dark fw-bold"><?= $model->thai_year ?></span>
                    </div>
                    <div class="small text-muted">
                        <i class="far fa-clock me-1"></i> Date: <span class="text-dark fw-bold"><?= Yii::$app->formatter->asDate($model->date_checkup) ?></span>
                    </div>
                </div>
                
                <div class="text-end">
                     <span class="small text-muted me-2">Progress:</span>
                     <div class="progress d-inline-flex" style="width: 150px; height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 60%; shadow: none;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>