<?php

use yii\helpers\Html;

/** @var app\modules\amSurvey\models\AssetSurvey $survey */
/** @var array $byStatus */
/** @var int $locationMismatch */
/** @var int $departmentMismatch */
/** @var int $total */

$this->title = 'รายงานสรุป — ' . $survey->survey_name;
$this->params['breadcrumbs'][] = ['label' => 'การสำรวจครุภัณฑ์', 'url' => ['/am-survey/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
                <div class="d-flex flex-wrap gap-2">
                    <?= Html::a('ครุภัณฑ์ไม่พบ', ['missing', 'survey_id' => $survey->id], ['class' => 'btn btn-outline-danger']) ?>
                    <?= Html::a('ย้ายที่/หน่วยงาน', ['relocated', 'survey_id' => $survey->id], ['class' => 'btn btn-outline-warning']) ?>
                    <?= Html::a('นำเข้า CSV', ['/am-survey/import/index', 'survey_id' => $survey->id], ['class' => 'btn btn-outline-primary']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-primary"><?= number_format($total) ?></div>
                    <div class="text-secondary small">รายการสำรวจแล้ว</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-success"><?= number_format($byStatus['FOUND'] ?? 0) ?></div>
                    <div class="text-secondary small">พบครุภัณฑ์</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-danger"><?= number_format($byStatus['NOT_FOUND'] ?? 0) ?></div>
                    <div class="text-secondary small">ไม่พบ</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-warning"><?= number_format($locationMismatch) ?></div>
                    <div class="text-secondary small">สถานที่ไม่ตรง</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-info"><?= number_format($departmentMismatch) ?></div>
                    <div class="text-secondary small">หน่วยงานไม่ตรง</div>
                </div>
            </div>
        </div>
    </div>
</div>
