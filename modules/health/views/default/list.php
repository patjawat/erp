<?php

use app\components\AppHelper;
use app\modules\health\models\HealthScreen;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\health\models\HealthScreenSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายการตรวจสุขภาพ';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลสุขภาพ', 'url' => ['/health']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="scan-heart"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/health/menu', ['active' => 'list']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <?php echo $this->render('@app/modules/health/views/health-screen/_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary-gradient text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-white small fw-normal">
            <i class="bi bi-ui-checks me-1"></i> ทะเบียนตรวจสุขภาพพนักงาน
            <span class="badge bg-light bg-opacity-10 text-white border border-light-subtle rounded-pill fw-medium px-2 py-1">
                <?= number_format($dataProvider->getTotalCount(), 0) ?> รายการ
            </span>
        </h6>
        <?= Html::a(
            '<i class="fas fa-plus me-1"></i> บันทึกข้อมูลสุขภาพ',
            ['/health/health-screen/create'],
            ['class' => 'btn btn-light btn-sm rounded-pill px-3']
        ) ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ปีที่ตรวจ</th>
                        <th>วันที่คัดกรอง</th>
                        <th>พนักงาน</th>
                        <th>หน่วยงาน</th>
                        <th class="text-center">สรุปผล</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center" style="width:200px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php if (empty($dataProvider->getModels())): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i data-lucide="inbox" class="mb-2 d-block mx-auto" style="width:40px;height:40px;opacity:.3;"></i>
                                ไม่พบข้อมูลการตรวจสุขภาพ
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($dataProvider->getModels() as $item): ?>
                        <?php
                        $sumKey   = $item->data_json['final_summary'] ?? null;
                        $sumLabel = $sumKey ? $item::getFinalSummaryDisplay($sumKey, 'label') : '-';
                        $sumColor = $sumKey ? $item::getFinalSummaryDisplay($sumKey, 'color') : 'secondary';
                        $sumIcon  = $sumKey ? $item::getFinalSummaryDisplay($sumKey, 'icon') : 'bi bi-dash';
                        ?>
                        <tr>
                            <td class="ps-3 fw-bold"><?= Html::encode($item->thai_year) ?></td>
                            <td class="small text-muted"><?= AppHelper::convertToThai($item->date_checkup) ?></td>
                            <td><?= $item->employee ? $item->employee->getAvatar(false) : '<span class="text-muted">-</span>' ?></td>
                            <td class="small text-truncate" style="max-width:150px;">
                                <?= Html::encode($item->employee ? $item->employee->departmentName() : '-') ?>
                            </td>
                            <td class="text-center">
                                <?php if ($sumKey): ?>
                                <span class="badge bg-<?= $sumColor ?> bg-opacity-10 text-<?= $sumColor ?> border border-<?= $sumColor ?>-subtle rounded-pill fw-medium px-2 py-1">
                                    <i class="<?= $sumIcon ?> me-1"></i><?= Html::encode($sumLabel) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $item->viewStatus() ?></td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
                                    <?= Html::a('<i class="fas fa-eye"></i>', ['/health/health-screen/view', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary border-0 rounded-pill px-2', 'title' => 'ดูผลตรวจ']) ?>
                                    <?= Html::a('<i class="fas fa-vial"></i>', ['/health/health-screen/lab-confirm', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-info border-0 rounded-pill px-2', 'title' => 'ลงผล LAB']) ?>
                                    <?= Html::a('<i class="fas fa-stethoscope"></i>', ['/health/health-screen/physical-exam', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-success border-0 rounded-pill px-2', 'title' => 'ลงความเห็นแพทย์']) ?>
                                    <?= Html::a('<i class="fas fa-print"></i>', ['/health/health-screen/print', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary border-0 rounded-pill px-2', 'target' => '_blank', 'title' => 'พิมพ์']) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3 border-top-0">
        <div class="d-flex justify-content-between align-items-center small text-muted">
            <div>แสดง <?= count($dataProvider->getModels()) ?> รายการ</div>
            <div>
                <?= LinkPager::widget([
                    'pagination'         => $dataProvider->pagination,
                    'options'            => ['class' => 'pagination pagination-sm mb-0'],
                    'listOptions'        => ['class' => 'pagination pagination-sm mb-0'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions'        => ['class' => 'page-link'],
                ]); ?>
            </div>
        </div>
    </div>
</div>
