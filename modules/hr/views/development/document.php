<?php

use yii\helpers\Html;
use app\modules\hr\components\DevelopmentDocumentCatalog;

/** @var yii\web\View $this */
/** @var array $documentTypes */

$this->title = 'พิมพ์เอกสารเดินทางไปราชการ';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-printer text-primary" aria-hidden="true"></i>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('menu', ['active' => 'document']) ?>
<?php $this->endBlock(); ?>

<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h5 class="mb-1">ชุดเอกสารเบิกค่าใช้จ่ายในการเดินทาง</h5>
                <p class="text-body-secondary mb-0">เลือกทะเบียนการเดินทาง แล้วสร้างเอกสารจากแม่แบบของงานการเงิน</p>
            </div>
            <button type="button" class="btn btn-primary" disabled aria-disabled="true">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>สร้างเอกสาร
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-3">แม่แบบเอกสาร</th>
                        <th scope="col">การใช้งาน</th>
                        <th scope="col" class="text-end pe-3">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentTypes as $documentType): ?>
                        <?php $sourceReady = $documentType['status'] === DevelopmentDocumentCatalog::STATUS_SOURCE_READY; ?>
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium"><?= Html::encode($documentType['name']) ?></div>
                                <?php if (!empty($documentType['source_format'])): ?>
                                    <div class="small text-body-secondary">
                                        <?= Html::encode($documentType['source_format']) ?>
                                        <?php if (!empty($documentType['orientation']) && !empty($documentType['pages'])): ?>
                                            · <?= Html::encode($documentType['orientation']) ?>
                                            · <?= number_format((int) $documentType['pages']) ?> หน้า
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-body-secondary"><?= Html::encode($documentType['description']) ?></td>
                            <td class="text-end pe-3">
                                <span class="badge <?= $sourceReady ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>">
                                    <?= Html::encode(DevelopmentDocumentCatalog::statusLabel($documentType['status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-body py-3 text-body-secondary">
        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
        ลำดับถัดไปคือจับคู่ข้อมูลจากทะเบียนและเครื่องคำนวณกับช่องในเอกสาร แล้วเปิดให้ตรวจแก้ก่อนพิมพ์
    </div>
</div>
