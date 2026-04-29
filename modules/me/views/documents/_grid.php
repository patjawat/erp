<?php

use yii\helpers\Url;
use yii\bootstrap5\Html;
use app\components\ThaiDateHelper;
use app\components\widgets\DataSummaryWidget;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int,int> $unreadOpenDetailIdByDocument */
/** @var array<int,\app\modules\dms\models\DocumentsDetail> $unreadOpenDocumentsDetailById */
/** @var array<int,string> $readAtByRoutingId */

$unreadOpenDetailIdByDocument = $unreadOpenDetailIdByDocument ?? [];
$unreadOpenDocumentsDetailById = $unreadOpenDocumentsDetailById ?? [];
$readAtByRoutingId = $readAtByRoutingId ?? [];
?>

<div class="p-3">
    <div class="row g-3">
        <?php foreach ($dataProvider->getModels() as $item): ?>
            <?php
            if ($unreadOpenDetailIdByDocument !== [] && isset($unreadOpenDetailIdByDocument[$item->id])) {
                $id = $unreadOpenDetailIdByDocument[$item->id];
                $doc = $unreadOpenDocumentsDetailById[$id] ?? ($item->documentTags ?? $item->documentDepartment ?? null);
            } else {
                $doc = $item->documentTags ?? $item->documentDepartment ?? null;
                $id = $doc->id ?? null;
            }
            ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="min-w-0">
                                <?php if ($item->doc_speed == 'ด่วนที่สุด'): ?>
                                    <span class="badge text-bg-danger small me-1">ด่วนที่สุด</span>
                                <?php endif; ?>
                                <?php if ($item->secret == 'ลับที่สุด'): ?>
                                    <span class="badge text-bg-dark small">ลับที่สุด</span>
                                <?php endif; ?>
                                <div class="fw-bold text-primary small mt-1"><?= Html::encode($item->doc_regis_number) ?></div>
                                <div class="text-danger small"><?= Html::encode($item->doc_number) ?></div>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                <?php if ($doc): ?>
                                    <?= Html::a($doc->docRead('fs-5')['view'], ['/me/documents/bookmark', 'id' => $doc->id], [
                                        'class' => 'bookmark bookmark-star-' . (int) $doc->id,
                                        'id' => (string) $doc->id,
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($id): ?>
                            <a href="<?= Url::to(['/me/documents/view', 'id' => $id]) ?>"
                               class="open-modal fw-semibold text-body text-decoration-none"
                               data-size="modal-fullscreen">
                                <?= Html::encode($item->topic) ?>
                            </a>
                        <?php else: ?>
                            <div class="fw-semibold text-body"><?= Html::encode($item->topic) ?></div>
                        <?php endif; ?>

                        <p class="text-muted small mb-2 mt-1 mb-auto">
                            <?= Html::encode($item->data_json['des'] ?? '') ?>
                        </p>

                        <div class="d-flex flex-wrap gap-2 align-items-center small text-secondary mt-2">
                            <span><i class="fa-solid fa-inbox me-1" aria-hidden="true"></i><?= Html::encode($item->documentOrg->title ?? '-') ?></span>
                            <span class="badge rounded-pill bg-light text-primary border">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i> <?= (int) $item->viewCount() ?>
                            </span>
                        </div>

                        <?php
                        $readRawCard = ($id && isset($readAtByRoutingId[(int) $id])) ? $readAtByRoutingId[(int) $id] : null;
                        ?>
                        <?php if ($id): ?>
                            <div class="mt-2 pt-2 border-top border-light">
                                <?php if ($readRawCard !== null && $readRawCard !== ''): ?>
                                    <div class="d-flex flex-wrap align-items-center gap-2 small">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">อ่านแล้ว</span>
                                        <span class="text-muted"><?= Html::encode(ThaiDateHelper::formatThaiDate($readRawCard, 'short')) ?> <span class="text-secondary"><?= Html::encode(date('H:i', strtotime($readRawCard))) ?></span></span>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ยังไม่ได้อ่าน</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="small text-muted"><?= Html::encode($item->documentStatus->title ?? '-') ?></span>
                            <div class="d-flex justify-content-end gap-1 position-relative">
                                <?= $item->isFile() ?>
                                <?php if ($id): ?>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>', ['view', 'id' => $id], [
                                        'class' => 'btn btn-outline-primary btn-sm open-modal rounded-pill',
                                        'data' => ['size' => 'modal-fullscreen'],
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card-footer bg-body border-top py-3 px-4">
    <?= DataSummaryWidget::widget([
        'dataProvider' => $dataProvider,
        'pagerOptions' => [],
    ]) ?>
</div>
