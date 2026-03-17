<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\SqlDataProvider|null $dataProvider */
/** @var bool $tableExists */

$this->title = 'รายงานการเคลื่อนย้ายครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
        <?php if ($tableExists): ?>
        <?= Html::a('<i class="fa-solid fa-file-csv me-1"></i> Export CSV', ['movement-report', 'format' => 'csv'], ['class' => 'btn btn-outline-primary']) ?>
        <?php endif; ?>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!$tableExists): ?>
                <p class="text-muted mb-0">ตาราง am_asset_transactions ยังไม่มีในระบบ</p>
            <?php else: ?>
                <?php $models = $dataProvider->getModels(); ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสครุภัณฑ์</th>
                                <th>ประเภท</th>
                                <th>จากสถานที่</th>
                                <th>ถึงสถานที่</th>
                                <th>จากหน่วยงาน</th>
                                <th>ถึงหน่วยงาน</th>
                                <th>หมายเหตุ</th>
                                <th>วันที่</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php foreach ($models as $row): ?>
                            <tr>
                                <td><?= Html::encode($row['asset_code'] ?? '') ?></td>
                                <td><?= Html::encode($row['transaction_type'] ?? '') ?></td>
                                <td><?= Html::encode($row['from_location'] ?? '') ?></td>
                                <td><?= Html::encode($row['to_location'] ?? '') ?></td>
                                <td><?= Html::encode($row['from_department'] ?? '') ?></td>
                                <td><?= Html::encode($row['to_department'] ?? '') ?></td>
                                <td><?= Html::encode($row['remark'] ?? '') ?></td>
                                <td><?= Html::encode($row['created_at'] ?? '') ?></td>
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
