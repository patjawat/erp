<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\modules\amSurvey\models\AssetSurveySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'โครงการสำรวจครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'การสำรวจครุภัณฑ์', 'url' => ['/am-survey/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
                <?= Html::a('<i class="fa-solid fa-plus me-1"></i> สร้างโครงการ', ['create'], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
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
                            <?php foreach ($dataProvider->getModels() as $model): ?>
                            <tr>
                                <td><?= Html::encode($model->survey_year) ?></td>
                                <td><?= Html::encode($model->survey_name) ?></td>
                                <td>
                                    <span class="badge bg-<?= $model->status === 'active' ? 'success' : ($model->status === 'closed' ? 'secondary' : 'warning') ?> bg-opacity-10 text-<?= $model->status === 'active' ? 'success' : ($model->status === 'closed' ? 'secondary' : 'warning') ?> border border-<?= $model->status === 'active' ? 'success' : ($model->status === 'closed' ? 'secondary' : 'warning') ?>-subtle rounded-pill fw-medium px-2 py-1">
                                        <?= $model->status === 'active' ? 'กำลังสำรวจ' : ($model->status === 'closed' ? 'ปิดแล้ว' : 'ร่าง') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?= Html::a('ดู', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                    <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    <?= Html::a('รายงาน', ['/am-survey/report/summary', 'survey_id' => $model->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center mt-3">
                        <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
