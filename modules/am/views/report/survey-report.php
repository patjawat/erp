<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\SqlDataProvider|null $dataProvider */
/** @var bool $tableExists */
/** @var array $surveys */

$this->title = 'รายงานสำรวจครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        <?php if ($tableExists): ?>
        <?= Html::a('<i class="fa-solid fa-file-csv me-1"></i> Export CSV', ['survey-report', 'format' => 'csv'] + $this->context->request->queryParams, ['class' => 'btn btn-outline-primary']) ?>
        <?php endif; ?>
    </div>
    <?php if ($tableExists && !empty($surveys)): ?>
    <div class="mb-3">
        <form method="get" action="<?= \yii\helpers\Url::to(['survey-report']) ?>" class="d-inline">
            <select name="survey_id" class="form-select form-control d-inline-block w-auto">
                <option value="">-- ทุกโครงการ --</option>
                <?php foreach ($surveys as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= (int) ($this->context->request->get('survey_id') ?? 0) === (int) $s['id'] ? 'selected' : '' ?>><?= Html::encode($s['survey_name'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary">กรอง</button>
        </form>
    </div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!$tableExists): ?>
                <p class="text-muted mb-0">ตาราง am_asset_survey_items ยังไม่มีในระบบ</p>
            <?php else: ?>
                <?php $models = $dataProvider->getModels(); ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>โครงการสำรวจ</th>
                                <th>หมายเลขที่สแกน</th>
                                <th>สถานะ</th>
                                <th>สถานที่ตรง</th>
                                <th>หน่วยงานตรง</th>
                                <th>วิธีสำรวจ</th>
                                <th>วันเวลาสำรวจ</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php foreach ($models as $row): ?>
                            <tr>
                                <td><?= Html::encode($row['survey_name'] ?? '') ?></td>
                                <td><?= Html::encode($row['scanned_asset_number'] ?? '') ?></td>
                                <td><?= Html::encode($row['found_status'] ?? '') ?></td>
                                <td><?= isset($row['location_match']) ? ($row['location_match'] ? 'ใช่' : 'ไม่') : '-' ?></td>
                                <td><?= isset($row['department_match']) ? ($row['department_match'] ? 'ใช่' : 'ไม่') : '-' ?></td>
                                <td><?= Html::encode($row['survey_method'] ?? '') ?></td>
                                <td><?= Html::encode($row['scanned_at'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
