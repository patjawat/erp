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

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-center d-none d-md-table-cell">ลำดับ</th>
                <th class="text-center">เลขรับ/หนังสือ</th>
                <th class="">วันที่หนังสือ</th>
                <th>เรื่อง/รายละเอียด</th>
                <th>ไฟล์แนบ</th>
                <th class="d-none d-lg-table-cell">ผู้บันทึก</th>
                <th class="text-center">สถานะ</th>
                <th class="text-center d-none d-lg-table-cell">การอ่าน</th>
                <th class="text-center text-end">จัดการ</th>
            </tr>
        </thead>
        <tbody class="align-middle table-group-divider">
            <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                <?php
                if ($unreadOpenDetailIdByDocument !== [] && isset($unreadOpenDetailIdByDocument[$item->id])) {
                    $id = $unreadOpenDetailIdByDocument[$item->id];
                    $doc = $unreadOpenDocumentsDetailById[$id] ?? ($item->documentTags ?? $item->documentDepartment ?? null);
                } else {
                    $doc = $item->documentTags ?? $item->documentDepartment ?? null;
                    $id = $doc->id ?? null;
                }
                $readRaw = ($id && isset($readAtByRoutingId[(int) $id])) ? $readAtByRoutingId[(int) $id] : null;
                ?>
                <tr>
                    <td class="text-center d-none d-md-table-cell text-muted">
                        <?php
                        $offset = ($dataProvider->pagination !== false) ? (int) $dataProvider->pagination->offset : 0;
                        echo $offset + 1 + (int) $key;
                        ?>
                    </td>

                    <td class="text-center">
                        <div class="fw-bold text-dark small"><?= Html::encode($item->doc_regis_number) ?></div>
                        <div class="text-danger small"><?= Html::encode($item->doc_number) ?></div>
                    </td>
                    <td>
                        <div class="small">
                            <?= Html::encode(ThaiDateHelper::formatThaiDate($item->doc_date, 'short')) ?>
                        </div>
                    </td>

                    <td>
                        <div class="d-flex flex-column gap-1">
                            <div class="topic-container">
                                <?php if ($item->doc_speed == 'ด่วนที่สุด'): ?>
                                    <span class="badge text-bg-danger small mb-1">ด่วนที่สุด</span>
                                <?php endif; ?>

                                <?php if ($item->secret == 'ลับที่สุด'): ?>
                                    <span class="badge text-bg-dark small mb-1">ลับที่สุด</span>
                                <?php endif; ?>

                                <?php if ($id): ?>
                                    <a href="<?= Url::to(['/me/documents/view', 'id' => $id]) ?>"
                                       class="open-modal fw-medium d-block text-primary text-decoration-none"
                                       data-size="modal-fullscreen">
                                        <?= Html::encode($item->topic) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="fw-medium"><?= Html::encode($item->topic) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="text-muted small d-none d-sm-block">
                                <?= Html::encode($item->data_json['des'] ?? '') ?>
                            </div>

                            <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                                <span class="text-secondary small">
                                    <i class="fa-solid fa-inbox me-1" aria-hidden="true"></i><?= Html::encode($item->documentOrg->title ?? '-') ?>
                                </span>
                                <span class="badge rounded-pill bg-light text-primary border fw-light">
                                    <i class="fa-regular fa-eye" aria-hidden="true"></i> <?= (int) $item->viewCount() ?>
                                </span>

                                <?= $item->StackDocumentTags('comment') ?>
                            </div>
                            <?php if ($id): ?>
                                <div class="d-lg-none mt-2 pt-2 border-top border-light">
                                    <?php if ($readRaw !== null && $readRaw !== ''): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">อ่านแล้ว</span>
                                        <span class="small text-muted ms-1"><?= Html::encode(ThaiDateHelper::formatThaiDate($readRaw, 'short')) ?> <?= Html::encode(date('H:i', strtotime($readRaw))) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ยังไม่ได้อ่าน</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>

                    <td>
                        <?= $item->isFile() ?>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <div class="small">
                            <?= $item->viewCreate()['avatar'] ?>
                        </div>
                    </td>

                    <td class="text-center">
                        <?php if ($doc): ?>
                            <div class="d-flex flex-column align-items-center gap-1">
                                <?= Html::a($doc->docRead('fs-5')['view'], ['/me/documents/bookmark', 'id' => $doc->id], [
                                    'class' => 'bookmark bookmark-star-' . (int) $doc->id,
                                    'id' => (string) $doc->id,
                                ]) ?>
                                <span class="small text-nowrap"><?= Html::encode($item->documentStatus->title ?? '-') ?></span>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-center d-none d-lg-table-cell">
                        <?php if ($id): ?>
                            <?php if ($readRaw !== null && $readRaw !== ''): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">อ่านแล้ว</span>
                                <div class="small text-muted text-nowrap mt-1">
                                    <?= Html::encode(ThaiDateHelper::formatThaiDate($readRaw, 'short')) ?>
                                    <span class="text-secondary"><?= Html::encode(date('H:i', strtotime($readRaw))) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ยังไม่ได้อ่าน</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-end">
                        <?php if ($id): ?>
                            <div class="d-flex justify-content-end">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>', ['view', 'id' => $id], [
                                    'class' => 'btn btn-outline-primary btn-sm open-modal rounded-pill',
                                    'data' => ['size' => 'modal-fullscreen'],
                                ]) ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card-footer bg-body border-top py-3 px-4">
    <?= DataSummaryWidget::widget([
        'dataProvider' => $dataProvider,
        'pagerOptions' => [],
    ]) ?>
</div>
