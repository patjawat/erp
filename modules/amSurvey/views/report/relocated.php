<?php

use yii\helpers\Html;

/** @var app\modules\amSurvey\models\AssetSurvey $survey */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ย้ายที่/หน่วยงาน — ' . $survey->survey_name;
$this->params['breadcrumbs'][] = ['label' => 'การสำรวจครุภัณฑ์', 'url' => ['/am-survey/default/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รายงานสรุป', 'url' => ['summary', 'survey_id' => $survey->id]];
$this->params['breadcrumbs'][] = 'ย้ายที่/หน่วยงาน';
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>หมายเลขครุภัณฑ์</th>
                                <th>สถานที่ไม่ตรง</th>
                                <th>หน่วยงานไม่ตรง</th>
                                <th>หน่วยงานที่สำรวจ</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider align-middle">
                            <?php foreach ($dataProvider->getModels() as $i => $item): ?>
                            <tr>
                                <td><?= $dataProvider->pagination->offset + $i + 1 ?></td>
                                <td><?= Html::encode($item->scanned_asset_number) ?></td>
                                <td><?= $item->location_match === false ? '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">ไม่ตรง</span>' : '-' ?></td>
                                <td><?= $item->department_match === false ? '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">ไม่ตรง</span>' : '-' ?></td>
                                <td><?= $item->surveyDepartment ? Html::encode($item->surveyDepartment->name) : '-' ?></td>
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
