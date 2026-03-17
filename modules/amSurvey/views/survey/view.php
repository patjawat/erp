<?php

use yii\helpers\Html;

/** @var app\modules\amSurvey\models\AssetSurvey $model */

$this->title = $model->survey_name;
$this->params['breadcrumbs'][] = ['label' => 'การสำรวจครุภัณฑ์', 'url' => ['/am-survey/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
                <div class="d-flex flex-wrap gap-2">
                    <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('รายงานสรุป', ['/am-survey/report/summary', 'survey_id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('สำรวจ (Web)', ['/am-survey/scan/index', 'survey_id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= Html::a('นำเข้า CSV', ['/am-survey/import/index', 'survey_id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-secondary">ปีสำรวจ</dt>
                        <dd class="col-sm-9"><?= Html::encode($model->survey_year) ?></dd>
                        <dt class="col-sm-3 text-secondary">สถานะ</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-<?= $model->status === 'active' ? 'success' : ($model->status === 'closed' ? 'secondary' : 'warning') ?> bg-opacity-10 text-<?= $model->status === 'active' ? 'success' : ($model->status === 'closed' ? 'secondary' : 'warning') ?> border border-<?= $model->status === 'active' ? 'success' : ($model->status === 'closed' ? 'secondary' : 'warning') ?>-subtle rounded-pill fw-medium px-2 py-1">
                                <?= $model->status === 'active' ? 'กำลังสำรวจ' : ($model->status === 'closed' ? 'ปิดแล้ว' : 'ร่าง') ?>
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
