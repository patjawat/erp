<?php
use yii\helpers\Html;

/** @var app\modules\helpdesk2\models\Helpdesk $model */
/** @var string $titleText */
/** @var string $statusBadge */
/** @var string $priorityBadge */
/** @var string $slaBadgeHtml */
/** @var string|null $returnUrl */
/** @var array $requester */
/** @var string $descriptionSummary */
/** @var string $descriptionExtra */
/** @var string $locationLabel */

$returnUrl = $returnUrl ?? null;

?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-start gap-3">
            <div>
                <h1 class="fw-bold mb-2 fs-4 text-break"><?= Html::encode($titleText) ?></h1>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <?= $statusBadge ?>
                    <?= $priorityBadge ?>
                    <?= $slaBadgeHtml ?>
                </div>
            </div>

            <div class="d-flex flex-column align-items-end gap-2">
                <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-md-end">
                    <?php if ($returnUrl !== null && $returnUrl !== ''): ?>
                        <?= Html::a(
                            '<i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>กลับรายการ',
                            $returnUrl,
                            ['class' => 'btn btn-sm btn-outline-secondary']
                        ) ?>
                    <?php endif; ?>
                    <?= Html::a(
                        '<i class="fa-solid fa-print me-1" aria-hidden="true"></i>พิมพ์ใบส่งซ่อม',
                        ['/helpdesk/service/print-send-repair-pdf', 'id' => $model->id],
                        ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']
                    ) ?>
                </div>
            </div>
        </div>

        <div class="row g-4 border-top mt-4 pt-3 align-items-start">
            <div class="col-12 col-lg-8">
                <dl class="mb-0 d-flex flex-column gap-3" aria-label="สรุปบริบทงานซ่อม">
                    <div>
                        <dt class="text-body-secondary fw-medium">
                            <i class="fa-solid fa-user me-2" aria-hidden="true"></i>ผู้แจ้ง
                        </dt>
                        <dd class="mb-0 mt-1 ps-sm-4">
                            <span class="fw-semibold"><?= Html::encode($requester['fullname'] ?? '-') ?></span>
                            <?php if (!empty($requester['department']) && $requester['department'] !== '-'): ?>
                                <span class="text-body-secondary small ms-sm-2"><?= Html::encode($requester['department']) ?></span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-body-secondary fw-medium">
                            <i class="fa-solid fa-screwdriver-wrench me-2" aria-hidden="true"></i>ปัญหา
                        </dt>
                        <dd class="mb-0 mt-1 ps-sm-4">
                            <div class="fw-semibold"><?= $descriptionSummary ?></div>
                            <?php if ($descriptionExtra !== ''): ?>
                                <div class="text-body-secondary mt-1"><?= nl2br($descriptionExtra) ?></div>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-body-secondary fw-medium">
                            <i class="fa-solid fa-location-dot me-2" aria-hidden="true"></i>สถานที่
                        </dt>
                        <dd class="fw-semibold mb-0 mt-1 ps-sm-4"><?= $locationLabel ?></dd>
                    </div>
                </dl>
            </div>

            <aside class="col-12 col-lg-4" aria-labelledby="request-images-heading">
                <div class="d-flex flex-column align-items-start align-items-lg-end gap-2">
                    <h2 id="request-images-heading" class="h6 text-body-secondary fw-medium mb-0 text-lg-end">
                        <i class="fa-regular fa-images me-2" aria-hidden="true"></i>รูปภาพที่ผู้แจ้งแนบมา
                    </h2>
                    <div class="d-flex flex-wrap align-items-start justify-content-start justify-content-lg-end gap-2">
                        <?= $model->imageRequest ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
