<?php

use yii\helpers\Url;
use yii\bootstrap5\Html;
use app\components\ThaiDateHelper;
use app\components\widgets\DataSummaryWidget;
use app\components\UserHelper;
use app\modules\dms\models\DocumentsDetail;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int,int> $unreadOpenDetailIdByDocument */
/** @var array<int,\app\modules\dms\models\DocumentsDetail> $unreadOpenDocumentsDetailById */
/** @var array<int,string> $readAtByRoutingId */

$viewQueryParams = Yii::$app->request->queryParams;
$unreadOpenDetailIdByDocument = $unreadOpenDetailIdByDocument ?? [];
$unreadOpenDocumentsDetailById = $unreadOpenDocumentsDetailById ?? [];
$readAtByRoutingId = $readAtByRoutingId ?? [];

$elapsedLabel = function ($date) {
    if (empty($date)) {
        return '';
    }
    try {
        $start = new \DateTime(date('Y-m-d', strtotime($date)));
        $today = new \DateTime(date('Y-m-d'));
        if ($start > $today) {
            return '';
        }
        $diff = $today->diff($start);
        $days = (int) $diff->days;
        if ($days === 0) {
            return 'วันนี้';
        }
        if ($days === 1) {
            return 'เมื่อวาน';
        }
        $years = (int) $diff->y;
        $months = (int) $diff->m;
        $d = (int) $diff->d;
        if ($years === 0 && $months === 0) {
            return 'ผ่านมาแล้ว ' . $days . ' วัน';
        }
        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' ปี';
        }
        if ($months > 0) {
            $parts[] = $months . ' เดือน';
        }
        if ($d > 0) {
            $parts[] = $d . ' วัน';
        }
        return 'ผ่านมาแล้ว ' . implode(' ', $parts);
    } catch (\Throwable $e) {
        return '';
    }
};
$models = $dataProvider->getModels();
$hasSelectableRows = false;
foreach ($models as $model) {
    if (empty($model->doc_read)) {
        $hasSelectableRows = true;
        break;
    }
}

$bookmarkedDocumentIds = [];
$currentEmp = UserHelper::GetEmployee();
$currentEmpId = $currentEmp ? (int) $currentEmp->id : 0;
if ($currentEmpId > 0 && !empty($models)) {
    $itemIds = [];
    foreach ($models as $m) {
        $mid = (int) ($m->id ?? 0);
        if ($mid > 0) {
            $itemIds[] = $mid;
        }
    }
    if (!empty($itemIds)) {
        $rows = DocumentsDetail::find()
            ->select('document_id')
            ->where([
                'name' => 'read',
                'to_id' => $currentEmpId,
                'bookmark' => 'Y',
                'document_id' => $itemIds,
            ])
            ->column();
        foreach ($rows as $rid) {
            $bookmarkedDocumentIds[(int) $rid] = true;
        }
    }
}
?>

<style>
    tr.js-document-row.is-bookmarked > td {
        background-color: rgba(245, 158, 11, 0.10) !important;
        border-top: 1px solid rgba(245, 158, 11, 0.35);
        border-bottom: 1px solid rgba(245, 158, 11, 0.35);
    }
    tr.js-document-row.is-bookmarked > td:first-child {
        border-left: 4px solid #f59e0b !important;
    }
    .bookmark-indicator-badge {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff !important;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
    }
    .bookmark-indicator-badge i { color: #fff; }
</style>
<div class="table-responsive">
    <table class="table table-hover align-middle table-striped mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-center" style="width:42px;">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        id="chk-select-all-documents"
                        title="เลือกทั้งหมดเฉพาะรายการที่ยังไม่ได้อ่าน"
                        <?= $hasSelectableRows ? '' : 'disabled' ?>
                    >
                </th>
                <th class="text-center d-none d-md-table-cell">ลำดับ</th>
                <th class="text-center" style="min-width:120px">เลขรับ/วันที่</th>
                <th class="">วันที่หนังสือ</th>
                <th>เรื่อง/รายละเอียด</th>
                <th style="min-width: 100px;">ไฟล์แนบ</th>
                <th class="d-none d-lg-table-cell">ผู้บันทึก</th>
                <th class="text-center">สถานะ</th>
                <th class="text-center text-end">จัดการ</th>
            </tr>
        </thead>
        <tbody class="align-middle table-group-divider">
            <?php foreach ($models as $key => $item): ?>
                <?php
                $detailId = (int) ($item->detail_id ?? 0);
                $isUnread = empty($item->doc_read);
                if ($unreadOpenDetailIdByDocument !== [] && isset($unreadOpenDetailIdByDocument[$item->id])) {
                    $selectedDetailId = (int) $unreadOpenDetailIdByDocument[$item->id];
                    $doc = $unreadOpenDocumentsDetailById[$selectedDetailId] ?? ($item->documentTags ?? $item->documentDepartment ?? null);
                } else {
                    $doc = $item->documentTags ?? $item->documentDepartment ?? null;
                }
                $isBookmarked = isset($bookmarkedDocumentIds[(int) $item->id]);
                $bookmarkInfo = [
                    'status' => $isBookmarked ? 'Y' : 'N',
                    'view' => $isBookmarked
                        ? '<i class="fa-solid fa-bookmark text-warning fs-5"></i>'
                        : '<i class="fa-regular fa-bookmark"></i>',
                ];
                ?>
                <tr class="js-document-row<?= $isBookmarked ? ' is-bookmarked' : '' ?>" data-detail-id="<?= $detailId ?>" data-is-unread="<?= $isUnread ? 1 : 0 ?>" data-is-bookmarked="<?= $isBookmarked ? 1 : 0 ?>">
                    <td class="text-center">
                        <?php if ($isUnread): ?>
                            <input
                                type="checkbox"
                                class="form-check-input js-document-select-row"
                                value="<?= $detailId ?>"
                                <?= $detailId > 0 ? '' : 'disabled' ?>
                                title="เลือกเอกสารนี้"
                            >
                        <?php else: ?>
                            <span class="text-muted small" title="เอกสารนี้อ่านแล้ว">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center d-none d-md-table-cell text-muted">
     
                        <?php
                        $offset = ($dataProvider->pagination !== false) ? (int) $dataProvider->pagination->offset : 0;
                         echo $offset + 1 + (int) $key;
                        ?>
                    </td>

                    <td class="text-center">
                        <div class="fw-bold text-dark small"><?= Html::encode($item->doc_regis_number) ?></div>
                        <div class="small"><?= Html::encode(ThaiDateHelper::formatThaiDate($item->doc_transactions_date, 'short')) ?></div>
                    </td>
                    <td>
                        <div class="text-danger small"><?= Html::encode($item->doc_number) ?></div>
                    </td>

                    <td>
                        <div class="d-flex flex-column gap-1">
                            <div class="topic-container">
                                <div class="d-flex flex column gap-2 my-2">
                                    <div>
                                        <?php if ($item->doc_speed == 'ด่วนที่สุด'): ?>
                                            <span class="badge text-bg-danger small mb-1"><i class="bi bi-exclamation-circle-fill"></i> ด่วนที่สุด</span>
                                        <?php endif; ?>

                                        <?php if ($item->secret == 'ลับที่สุด'): ?>
                                            <span class="badge text-bg-dark small mb-1">ลับที่สุด</span>
                                        <?php endif; ?>

                                        <?php if ($isBookmarked): ?>
                                            <span class="badge bookmark-indicator-badge rounded-pill px-2 py-1 small mb-1">
                                                <i class="fa-solid fa-thumbtack"></i> ปักหมุดแล้ว
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <?php if ($item->doc_read): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-rounded-top-pill px-2 py-1 small">
                                                <i class="bi bi-envelope-open"></i> อ่านแล้ว  <?= Html::encode(ThaiDateHelper::formatThaiDate($item->doc_read, 'short')) ?>
                                <span class="text-secondary"><?= Html::encode(date('H:i', strtotime($item->doc_read))) ?></span>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-2 py-1 small">
                                                <i class="bi bi-envelope"></i> ยังไม่อ่าน
                                            </span>
                                            <?php $elapsed = $elapsedLabel($item->doc_transactions_date); ?>
                                            <?php if ($elapsed !== ''): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-2 py-1 small ms-1">
                                                    <i class="bi bi-clock-history"></i> <?= Html::encode($elapsed) ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                </div>

                                <a href="<?= Url::to(array_merge(['/me/documents/view', 'id' => $item->detail_id], $viewQueryParams)) ?>"
                                    class="open-modal js-document-view-link fw-medium d-block text-primary text-decoration-none"
                                    data-size="modal-fullscreen">
                                    <?php if ($isBookmarked): ?>
                                        <i class="fa-solid fa-thumbtack text-warning me-1" title="ปักหมุดแล้ว"></i>
                                    <?php endif; ?>
                                    <?= Html::encode($item->topic) ?>
                                </a>

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
                                <?= Html::a($bookmarkInfo['view'], ['/me/documents/bookmark', 'id' => $doc->id], [
                                    'class' => 'bookmark bookmark-star-' . (int) $doc->id . ($isBookmarked ? ' fs-5' : ''),
                                    'id' => (string) $doc->id,
                                    'title' => $isBookmarked ? 'ยกเลิกปักหมุด' : 'ปักหมุด',
                                ]) ?>
                                <span class="small text-nowrap"><?= Html::encode($item->documentStatus->title ?? '-') ?></span>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>', array_merge(['/me/documents/view', 'id' => $item->detail_id], $viewQueryParams), [
                                'class' => 'btn btn-outline-primary btn-sm open-modal js-document-view-link rounded-pill',
                                'data' => ['size' => 'modal-fullscreen'],
                            ]) ?>
                        </div>
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
