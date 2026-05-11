<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentsDetail;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var bool $canManageDepartmentExtra */
/** @var array $currentDeptIdsStr */

$currentUserId = (int) Yii::$app->user->id;

$detailRows = DocumentsDetail::find()
    ->where(['document_id' => $model->id])
    ->andWhere(['in', 'name', ['department', 'employee_tag', 'tags', 'employee', 'req_approve']])
    ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
    ->all();

$creatorIds = [];
foreach ($detailRows as $row) {
    $creatorIds[(int) $row->created_by] = true;
}
$creatorIds = array_keys(array_filter($creatorIds));
$creatorMap = $creatorIds
    ? Employees::find()->where(['user_id' => $creatorIds])->indexBy('user_id')->all()
    : [];

$events = [];
foreach ($detailRows as $row) {
    $kind = $row->name;
    $targetLabel = '';
    $icon = 'fa-share';
    $iconBg = 'bg-primary bg-opacity-10';
    $iconColor = 'text-primary';
    $kindLabel = 'Tag บุคคล';

    if ($kind === 'department') {
        $org = Organization::findOne((int) $row->to_id);
        $targetLabel = $org ? $org->name : ('หน่วยงาน #' . $row->to_id);
        $icon = 'fa-building';
        $iconBg = 'bg-success bg-opacity-10';
        $iconColor = 'text-success';
        $kindLabel = 'ส่งหน่วยงาน';
    } elseif ($kind === 'req_approve') {
        $icon = 'fa-file-signature';
        $iconBg = 'bg-warning bg-opacity-10';
        $iconColor = 'text-warning';
        $kindLabel = 'เสนอผู้อำนวยการ';
        $director = $row->to_id ? Employees::findOne((int) $row->to_id) : null;
        $targetLabel = $director ? $director->fullname : ('บุคคล #' . $row->to_id);
    } else {
        $emp = Employees::findOne((int) $row->to_id);
        $targetLabel = $emp ? $emp->fullname : ('บุคคล #' . $row->to_id);
        if ($kind === 'employee' || $kind === 'tags' || $kind === 'employee_tag') {
            $kindLabel = 'ส่งต่อบุคคล';
        }
    }

    $comment = is_array($row->data_json) && isset($row->data_json['comment']) ? $row->data_json['comment'] : '';
    $events[] = [
        'id' => $row->id,
        'kind' => $kind,
        'kind_label' => $kindLabel,
        'icon' => $icon,
        'icon_bg' => $iconBg,
        'icon_color' => $iconColor,
        'target' => $targetLabel,
        'comment' => $comment,
        'created_at' => $row->created_at,
        'created_by' => (int) $row->created_by,
        'is_owner' => (int) $row->created_by === $currentUserId,
    ];
}

usort($events, function ($a, $b) {
    return strcmp((string) $a['created_at'], (string) $b['created_at']);
});
?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="px-4 py-3 border-bottom border-light-subtle">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width:32px;height:32px;">
                    <i class="fa-regular fa-paper-plane"></i>
                </span>
                <div>
                    <div class="text-uppercase small text-primary fw-semibold opacity-75" style="letter-spacing:.05em;">Activity</div>
                    <div class="fw-bold text-dark">ประวัติการส่งต่อเอกสาร</div>
                </div>
            </div>
            <span class="badge text-bg-light text-muted rounded-pill"><?= count($events) ?> รายการ</span>
        </div>
    </div>

    <div class="p-0">
        <?php if (empty($events)): ?>
            <div class="px-4 py-5 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width:56px;height:56px;">
                    <i class="fa-regular fa-paper-plane text-muted fs-4"></i>
                </div>
                <div class="text-muted small">ยังไม่มีประวัติการส่งต่อเอกสาร</div>
                <?php if ($canManageDepartmentExtra): ?>
                    <div class="text-muted small mt-1">กดปุ่ม "ส่งหน่วยงาน" ด้านบนเพื่อเริ่ม</div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <ul class="list-unstyled mb-0">
                <?php foreach ($events as $index => $event): ?>
                    <?php
                    $creator = $creatorMap[$event['created_by']] ?? null;
                    $creatorName = $creator ? $creator->fullname : 'ผู้ใช้ #' . $event['created_by'];
                    $creatorDept = $creator ? $creator->departmentName() : '';
                    $thaiDate = '';
                    try {
                        $thaiDate = ThaiDateHelper::formatThaiDate($event['created_at']);
                        $timePart = explode(' ', (string) $event['created_at']);
                        $thaiDate .= ' ' . ($timePart[1] ?? '');
                    } catch (\Throwable $th) {
                        $thaiDate = $event['created_at'];
                    }
                    $isLast = ($index === count($events) - 1);
                    ?>
                    <li class="px-4 py-3 <?= $isLast ? '' : 'border-bottom border-light-subtle' ?>">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="flex-shrink-0">
                                <span class="d-inline-flex align-items-center justify-content-center <?= $event['icon_bg'] ?> <?= $event['icon_color'] ?> rounded-circle" style="width:40px;height:40px;">
                                    <i class="fa-solid <?= $event['icon'] ?>"></i>
                                </span>
                            </div>

                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="badge text-bg-light text-muted rounded-pill border"><?= Html::encode($event['kind_label']) ?></span>
                                    <?php if (!empty($event['target'])): ?>
                                        <span class="text-primary fw-semibold"><?= Html::encode($event['target']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($event['comment'])): ?>
                                    <div class="mt-2 px-3 py-2 rounded-3 bg-light small text-dark">
                                        <i class="fa-regular fa-comment text-muted me-1"></i><?= Html::encode($event['comment']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="small text-muted mt-2 d-flex flex-wrap gap-3">
                                    <span><i class="fa-regular fa-circle-user me-1"></i>โดย <span class="text-dark fw-medium"><?= Html::encode($creatorName) ?></span></span>
                                    <?php if ($creatorDept): ?>
                                        <span><i class="fa-regular fa-building me-1"></i><?= Html::encode($creatorDept) ?></span>
                                    <?php endif; ?>
                                    <span><i class="fa-regular fa-clock me-1"></i><?= Html::encode($thaiDate) ?></span>
                                </div>
                            </div>

                            <?php if ($event['is_owner'] && $event['kind'] !== 'req_approve'): ?>
                                <div class="flex-shrink-0 d-flex gap-1">
                                    <?= Html::a('<i class="fa-regular fa-trash-can"></i>',
                                        ['/dms/documents/delete-forwarding', 'id' => $event['id']],
                                        ['class' => 'btn btn-sm btn-light text-danger rounded-circle delete-forwarding', 'title' => 'ลบ']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php
$js = <<< 'JS'
$(document).off('click.fwd');
$(document).on('click.fwd', '.delete-forwarding, .delete-tag', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: 'รายการที่ลบจะหายถาวร',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#dc3545',
    }).then(function (result) {
        if (!result.isConfirmed) { return; }
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    var p = reloadTimeline();
                    if (p && p.then) {
                        p.then(function () {
                            if (typeof listComment === 'function') { listComment(); }
                            Swal.fire({ icon: 'success', title: 'ลบแล้ว', timer: 1200, showConfirmButton: false });
                        });
                    } else {
                        if (typeof listComment === 'function') { listComment(); }
                        Swal.fire({ icon: 'success', title: 'ลบแล้ว', timer: 1200, showConfirmButton: false });
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'ลบไม่สำเร็จ', text: res.message || '' });
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด';
                Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: msg });
            }
        });
    });
});
JS;
$this->registerJs($js);
?>
