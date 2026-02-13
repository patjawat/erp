<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap5\LinkPager;

$this->title = 'จัดการข้อมูลตรวจสุขภาพพนักงาน';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลสุขภาพ', 'url' => ['/health']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="scan-heart"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/health/menu', ['active' => 'list'])
?>
<?php $this->endBlock(); ?>

<?= $this->render('_search', ['model' => $searchModel]); ?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="fas fa-microscope me-2"></i><?= Html::encode($this->title) ?>
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4">พนักงาน</th>
                            <th>ปีที่ตรวจ</th>
                            <th>วันที่คัดกรอง</th>
                            <th class="text-center">สรุปผลสุขภาพ</th>
                            <th class="text-center">สถานะการตรวจ</th>
                            <th class="text-center" style="width: 350px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dataProvider->getModels())): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">ไม่พบข้อมูลการตรวจสุขภาพ</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($dataProvider->getModels() as $index => $item): ?>
                            <tr>
                                <td class="ps-4">
                                    <?= $item->employee->getAvatar(false) ?>
                                </td>
                                <td><?= $item->thai_year ?></td>
                                <td><?= Yii::$app->formatter->asDate($item->date_checkup, 'medium') ?></td>
                                <td class="text-center">
                                    <?php
                                    $sumKey   = $item->data_json['final_summary'] ?? 'healthy';
                                    $sumLabel = $item::getFinalSummaryDisplay($sumKey, 'label');
                                    $sumColor = $item::getFinalSummaryDisplay($sumKey, 'color');
                                    $sumIcon  = $item::getFinalSummaryDisplay($sumKey, 'icon');
                                    ?>
                                    <span class="badge rounded-pill bg-<?= $sumColor ?>-subtle text-<?= $sumColor ?> border border-<?= $sumColor ?>-subtle px-3 py-2">
                                        <i class="<?= $sumIcon ?> me-1"></i> <?= $sumLabel ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= $item->viewStatus() ?></td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
                                        <?= Html::a(
                                            '<i class="fas fa-vial"></i> Lab',
                                            ['lab-confirm', 'id' => $item->id],
                                            ['class' => 'btn btn-sm btn-outline-info border-0 rounded-pill px-3', 'title' => 'ลงผล LAB']
                                        )
                                        ?>

                                        <?= Html::a(
                                            '<i class="fas fa-stethoscope"></i> แพทย์',
                                            ['physical-exam', 'id' => $item->id],
                                            ['class' => 'btn btn-sm btn-outline-success border-0 rounded-pill px-3', 'title' => 'ลงความเห็นแพทย์']
                                        )
                                        ?>

                                        <?= Html::a(
                                            '<i class="fas fa-print"></i>',
                                            ['print-report', 'id' => $item->id],
                                            ['class' => 'btn btn-sm btn-outline-secondary border-0 rounded-pill px-2', 'target' => '_blank']
                                        )
                                        ?>
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
                <div>แสดงผลจาก <?= count($dataProvider->getModels()) ?> รายการ</div>
                <div>
                    <?= LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'options' => ['class' => 'pagination pagination-sm mb-0'],
                        'listOptions' => ['class' => 'pagination pagination-sm mb-0'],
                        'linkContainerOptions' => ['class' => 'page-item'],
                        'linkOptions' => ['class' => 'page-link'],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .bg-hover:hover {
        background-color: #f8f9fa;
    }

    .btn-group .btn:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
        border-radius: 6px;
    }
</style>