<?php

use app\components\AppHelper;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'จัดการข้อมูลตรวจสุขภาพเจ้าหน้าที่';
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

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนตรวจสุขภาพเจ้าหน้าที่
                <span class="badge bg-light bg-opacity-10 text-white border border-light-subtle rounded-pill fw-medium px-2 py-1">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?> รายการ</span>
            </h6>
            <div class="d-flex justify-content-center gap-2">
                <?php \yii\helpers\Html::a(
                    '<i class="fas fa-plus me-1"></i> บันทึกข้อมูลสุขภาพ',
                    ['create'],
                    ['class' => 'btn btn-light btn-sm rounded-pill px-3 open-modal','data' => ['size' => 'modal-xl']]
                ) ?>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 600px;max-height: 600px;min-height:300px; overflow: auto;">
            <table class="table table-striped table-hover mb-0">
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th>ปีที่ตรวจ</th>
                        <th>วันที่คัดกรอง</th>
                        <th class="ps-4">เจ้าหน้าที่</th>
                        <th class="text-start" scope="col">หน่วยงาน</th>
                        <th class="text-center">สรุปผลสุขภาพ</th>
                        <th class="text-center">สถานะการตรวจ</th>
                        <th class="text-center" style="width: 350px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider" id="pjax-loading" style="background-color: #f0f8ff;">
                    <?php if (empty($dataProvider->getModels())): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">ไม่พบข้อมูลการตรวจสุขภาพ</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($dataProvider->getModels() as $index => $item): ?>
                        <tr>
                            <td><?= $item->thai_year ?></td>
                            <td> <?= AppHelper::convertToThai($item->date_checkup) ??  ''; ?></td>
                            <td class="ps-4"><?= $item->employee->getAvatar(false) ?></td>
                            <td class="text-start text-truncate" style="max-width:150px;"><?php echo $item->employee->departmentName() ?></td>
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
                                        ['print', 'id' => $item->id],
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