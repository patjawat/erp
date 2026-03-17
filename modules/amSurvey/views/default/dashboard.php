<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\modules\amSurvey\models\AssetSurvey|null $survey */
/** @var array $stats */
/** @var app\modules\amSurvey\models\AssetSurvey[] $surveys */

$this->title = 'การสำรวจครุภัณฑ์ประจำปี';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
                <div class="d-flex flex-wrap gap-2">
                    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> โครงการสำรวจ', ['/am-survey/survey/create'], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="fa-solid fa-clipboard-list me-1"></i> สำรวจ (Web)', ['/am-survey/scan/index'], ['class' => 'btn btn-outline-primary']) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($survey): ?>
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <label class="form-label text-secondary small">เลือกโครงการสำรวจ</label>
                    <form method="get" action="<?= Url::to(['/am-survey/default/dashboard']) ?>" class="d-flex gap-2 flex-wrap">
                        <select name="survey_id" class="form-select" style="max-width: 280px;" onchange="this.form.submit()">
                            <?php foreach ($surveys as $s): ?>
                                <option value="<?= $s->id ?>" <?= $s->id == $survey->id ? 'selected' : '' ?>>
                                    <?= Html::encode($s->survey_name) ?> (<?= $s->survey_year ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= Html::a('รายงานสรุป', ['/am-survey/report/summary', 'survey_id' => $survey->id], ['class' => 'btn btn-outline-secondary']) ?>
                        <?= Html::a('นำเข้า CSV', ['/am-survey/import/index', 'survey_id' => $survey->id], ['class' => 'btn btn-outline-secondary']) ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary fs-4 fw-bold"><?= number_format($stats['totalItems']) ?></div>
                    <div class="text-secondary small">รายการสำรวจแล้ว</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success fs-4 fw-bold"><?= number_format($stats['found']) ?></div>
                    <div class="text-secondary small">พบครุภัณฑ์</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-danger fs-4 fw-bold"><?= number_format($stats['notFound']) ?></div>
                    <div class="text-secondary small">ไม่พบ</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning fs-4 fw-bold"><?= number_format($stats['locationMismatch']) ?></div>
                    <div class="text-secondary small">สถานที่ไม่ตรง</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info fs-4 fw-bold"><?= number_format($stats['departmentMismatch']) ?></div>
                    <div class="text-secondary small">หน่วยงานไม่ตรง</div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="alert alert-info mb-0">
                ยังไม่มีโครงการสำรวจ <?= Html::a('สร้างโครงการสำรวจ', ['/am-survey/survey/create'], ['class' => 'alert-link']) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-3 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom d-flex align-items-center gap-2">
                    <h6 class="text-uppercase text-secondary m-0">ประวัติโครงการสำรวจ</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($surveys)): ?>
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ปี</th>
                                <th>ชื่อโครงการ</th>
                                <th>สถานะ</th>
                                <th class="text-end">ดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider align-middle">
                            <?php foreach ($surveys as $s): ?>
                            <tr>
                                <td><?= Html::encode($s->survey_year) ?></td>
                                <td><?= Html::encode($s->survey_name) ?></td>
                                <td>
                                    <span class="badge bg-<?= $s->status === 'active' ? 'success' : ($s->status === 'closed' ? 'secondary' : 'warning') ?> bg-opacity-10 text-<?= $s->status === 'active' ? 'success' : ($s->status === 'closed' ? 'secondary' : 'warning') ?> border border-<?= $s->status === 'active' ? 'success' : ($s->status === 'closed' ? 'secondary' : 'warning') ?>-subtle rounded-pill fw-medium px-2 py-1">
                                        <?= $s->status === 'active' ? 'กำลังสำรวจ' : ($s->status === 'closed' ? 'ปิดแล้ว' : 'ร่าง') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?= Html::a('ดู', ['/am-survey/survey/view', 'id' => $s->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                    <?= Html::a('รายงาน', ['/am-survey/report/summary', 'survey_id' => $s->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-muted mb-0">ยังไม่มีโครงการสำรวจ</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
