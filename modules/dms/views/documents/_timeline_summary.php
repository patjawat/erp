<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentsDetail;

/** @var app\modules\dms\models\Documents $model */
/** @var bool $canManageDepartmentExtra */

$detailRows = DocumentsDetail::find()
    ->where(['document_id' => $model->id])
    ->andWhere(['in', 'name', ['department', 'employee_tag', 'tags', 'employee', 'comment', 'req_approve']])
    ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
    ->all();

$creatorIds = [];
foreach ($detailRows as $r) {
    $creatorIds[(int) $r->created_by] = true;
}
$creatorIds = array_keys(array_filter($creatorIds));
$creatorMap = $creatorIds
    ? Employees::find()->where(['user_id' => $creatorIds])->indexBy('user_id')->all()
    : [];

$events = [];
foreach ($detailRows as $r) {
    $kind = $r->name;
    $targetLabel = '';
    $icon = 'fa-share';
    $iconBg = 'bg-primary bg-opacity-10';
    $iconColor = 'text-primary';
    $kindLabel = 'Tag บุคคล';

    if ($kind === 'department') {
        $org = Organization::findOne((int) $r->to_id);
        $targetLabel = $org ? $org->name : ('หน่วยงาน #' . $r->to_id);
        $icon = 'fa-building';
        $iconBg = 'bg-success bg-opacity-10';
        $iconColor = 'text-success';
        $kindLabel = 'ส่งหน่วยงาน';
    } elseif ($kind === 'comment') {
        $icon = 'fa-comment-dots';
        $iconBg = 'bg-info bg-opacity-10';
        $iconColor = 'text-info';
        $kindLabel = 'ลงความเห็น';
    } elseif ($kind === 'req_approve') {
        $icon = 'fa-file-signature';
        $iconBg = 'bg-warning bg-opacity-10';
        $iconColor = 'text-warning';
        $kindLabel = 'เสนอผู้อำนวยการ';
        $director = $r->to_id ? Employees::findOne((int) $r->to_id) : null;
        $targetLabel = $director ? $director->fullname : ('บุคคล #' . $r->to_id);
    } else {
        $emp = Employees::findOne((int) $r->to_id);
        $targetLabel = $emp ? $emp->fullname : ('บุคคล #' . $r->to_id);
    }

    $comment = is_array($r->data_json) && isset($r->data_json['comment']) ? $r->data_json['comment'] : '';
$events[] = [
        'kind' => $kind,
        'kind_label' => $kindLabel,
        'icon' => $icon,
        'icon_bg' => $iconBg,
        'icon_color' => $iconColor,
        'target' => $targetLabel,
        'comment' => $comment,
        'created_at' => $r->created_at,
        'created_by' => (int) $r->created_by,
    ];
}

usort($events, function ($a, $b) {
    return strcmp((string) $b['created_at'], (string) $a['created_at']);
});

$timelineEvents = array_values(array_filter($events, function ($event) {
    return $event['kind'] !== 'comment';
}));

$count = count($timelineEvents);
$sendCount = 0;
$approvalCount = 0;
foreach ($timelineEvents as $event) {
    if ($event['kind'] === 'req_approve') {
        $approvalCount++;
    } else {
        $sendCount++;
    }
}
$shown = array_slice($timelineEvents, 0, 5);
$remaining = max(0, $count - count($shown));
?>
<div class="d-flex align-items-start gap-2">
    <span class="d-inline-flex align-items-center text-primary-emphasis small fw-semibold flex-shrink-0 flex-wrap" style="row-gap:.25rem;">
        <i class="fa-regular fa-clock me-1"></i>ไทม์ไลน์เอกสาร
        <span class="badge text-bg-light text-muted ms-1 small">(<?= $count ?>)</span>
        <span class="badge text-bg-light text-success border border-success-subtle ms-1 small">ส่ง <?= $sendCount ?></span>
        <span class="badge text-bg-light text-warning border border-warning-subtle ms-1 small">อนุมัติ <?= $approvalCount ?></span>
    </span>
    <div class="flex-grow-1 min-width-0">
        <?php if ($count === 0): ?>
            <span class="small text-muted fst-italic">ยังไม่มีไทม์ไลน์</span>
            <?php if ($canManageDepartmentExtra): ?>
                <span class="small text-muted ms-1">กดปุ่ม "ส่งหน่วยงาน" เพื่อเริ่ม</span>
            <?php endif; ?>
        <?php else: ?>
            <button type="button" class="btn btn-link p-0 text-decoration-none d-inline-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTimeline" aria-controls="offcanvasTimeline">
                <?php if ($remaining > 0): ?>
                    <span class="badge text-bg-light text-muted rounded-pill small ms-1">+<?= $remaining ?></span>
                <?php endif; ?>
                <span class="text-muted small ms-1">ดูทั้งหมด</span>
            </button>
        <?php endif; ?>
    </div>
</div>
