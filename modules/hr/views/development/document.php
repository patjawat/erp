<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\modules\hr\components\DevelopmentDocumentCatalog;

/** @var yii\web\View $this */
/** @var array $documentTypes */
/** @var array $developmentOptions */
/** @var int|null $defaultDevelopmentId */

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
        <div class="row g-3 align-items-center">
            <div class="col-12 col-xl-7">
                <h5 class="mb-1">ชุดเอกสารเบิกค่าใช้จ่ายในการเดินทาง</h5>
                <p class="text-body-secondary mb-0">เลือกทะเบียนการเดินทาง แล้วสร้างเอกสารจากแม่แบบของงานการเงิน</p>
            </div>
            <div class="col-12 col-xl-5">
                <label class="form-label fw-medium" for="development-document-source">ทะเบียนการเดินทาง</label>
                <?= Select2::widget([
                    'name' => 'development_id',
                    'value' => $defaultDevelopmentId,
                    'data' => $developmentOptions,
                    'options' => [
                        'id' => 'development-document-source',
                        'placeholder' => 'ค้นหาเลขทะเบียน เรื่อง บุคลากร หรือวันที่',
                    ],
                    'pluginOptions' => [
                        'allowClear' => false,
                        'width' => '100%',
                    ],
                ]) ?>
            </div>
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
                        <?php
                        $sourceReady = $documentType['status'] === DevelopmentDocumentCatalog::STATUS_SOURCE_READY;
                        $openUrl = $sourceReady && $defaultDevelopmentId !== null
                            ? Url::to(['/hr/development/document-open', 'code' => $documentType['code'], 'development_id' => $defaultDevelopmentId])
                            : '#';
                        ?>
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
                                <?php if ($sourceReady): ?>
                                    <?= Html::a(
                                        '<i class="bi bi-pencil-square me-1" aria-hidden="true"></i>เปิดเอกสาร',
                                        $openUrl,
                                        [
                                            'class' => 'btn btn-sm btn-outline-primary ms-2 js-open-development-document'
                                                . ($defaultDevelopmentId !== null ? ' open-modal' : ' disabled'),
                                            'data' => [
                                                'base-url' => Url::to(['/hr/development/document-open', 'code' => $documentType['code']]),
                                                'size' => 'modal-xl',
                                            ],
                                            'aria-disabled' => $defaultDevelopmentId !== null ? 'false' : 'true',
                                        ]
                                    ) ?>
                                <?php endif; ?>
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

<?php
$this->registerJs(<<<'JS'
(function () {
    var source = document.getElementById('development-document-source');
    var buttons = document.querySelectorAll('.js-open-development-document');
    if (!source) return;

    function syncButtons() {
        var id = source.value;
        buttons.forEach(function (button) {
            if (!id) {
                button.href = '#';
                button.classList.add('disabled');
                button.classList.remove('open-modal');
                button.setAttribute('aria-disabled', 'true');
                return;
            }

            var separator = button.dataset.baseUrl.indexOf('?') === -1 ? '?' : '&';
            button.href = button.dataset.baseUrl + separator + 'development_id=' + encodeURIComponent(id);
            button.classList.remove('disabled');
            button.classList.add('open-modal');
            button.setAttribute('aria-disabled', 'false');
        });
    }

    source.addEventListener('change', syncButtons);
    if (window.jQuery) {
        window.jQuery(source).on('change', syncButtons);
    }
    syncButtons();
})();
JS);
?>
