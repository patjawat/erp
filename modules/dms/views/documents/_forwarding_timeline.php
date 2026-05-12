<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentsDetail;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$currentUserId = (int) Yii::$app->user->id;

// ดึงประวัติการ tag ที่เกี่ยวข้องจาก documents_detail เท่านั้น
$detailRows = DocumentsDetail::find()
    ->where(['document_id' => $model->id])
    ->andWhere(['in', 'name', ['department', 'employee_tag', 'tags', 'employee', 'comment', 'req_approve']])
    ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
    ->all();

// แปลงเป็น flat events
$events = [];

$creatorIds = [];
foreach ($detailRows as $r) {
    $creatorIds[(int) $r->created_by] = true;
}
$creatorIds = array_keys(array_filter($creatorIds));
$creatorMap = [];
if ($creatorIds) {
    $creatorMap = Employees::find()
        ->where(['user_id' => $creatorIds])
        ->indexBy('user_id')
        ->all();
}

foreach ($detailRows as $r) {
    $kind = $r->name;
    $targetLabel = '';
    $icon = 'fa-share';
    $badgeClass = 'text-bg-secondary';
    if ($kind === 'department') {
        $org = Organization::findOne((int) $r->to_id);
        $targetLabel = $org ? $org->name : ('หน่วยงาน #' . $r->to_id);
        $icon = 'fa-building';
        $badgeClass = 'text-bg-success';
        $kindLabel = 'ส่งหน่วยงาน';
    } elseif ($kind === 'comment') {
        $icon = 'fa-comment-dots';
        $badgeClass = 'text-bg-info';
        $kindLabel = 'ลงความเห็น';
    } elseif ($kind === 'req_approve') {
        $icon = 'fa-file-signature';
        $kindLabel = 'เสนอผู้อำนวยการ';
        $director = $r->to_id ? Employees::findOne((int) $r->to_id) : null;
        $targetLabel = $director ? $director->fullname : ('บุคคล #' . $r->to_id);
        $badgeClass = 'text-bg-warning';
    } else {
        $emp = Employees::findOne((int) $r->to_id);
        $targetLabel = $emp ? $emp->fullname : ('บุคคล #' . $r->to_id);
        $icon = 'fa-user';
        $badgeClass = 'text-bg-primary';
        $kindLabel = 'Tag บุคคล';
    }
    $comment = is_array($r->data_json) && isset($r->data_json['comment']) ? $r->data_json['comment'] : '';
    $events[] = [
        'source' => 'detail',
        'id' => $r->id,
        'created_at' => $r->created_at,
        'created_by' => (int) $r->created_by,
        'kind' => $kind,
        'kind_label' => $kindLabel,
        'icon' => $icon,
        'badge_class' => $badgeClass,
        'target' => $targetLabel,
        'comment' => $comment,
        'is_owner' => (int) $r->created_by === $currentUserId,
    ];
}

usort($events, function ($a, $b) {
    return strcmp((string) $b['created_at'], (string) $a['created_at']);
});
?>

<?php Pjax::begin(['id' => 'forwarding-timeline-pjax', 'timeout' => false]); ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">
                <i class="fa-regular fa-paper-plane me-1 text-primary"></i> ประวัติการส่งต่อเอกสาร
            </h6>
            <span class="badge text-bg-light rounded-pill"><?= count($events) ?> รายการ</span>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($events)): ?>
            <div class="p-4 text-center text-muted small">
                <i class="fa-regular fa-folder-open fs-3 d-block mb-2 opacity-50"></i>
                ยังไม่มีการส่งต่อเอกสาร
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($events as $event): ?>
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
                    ?>
                    <div class="list-group-item px-3 py-3">
                        <div class="d-flex flex-column flex-md-row gap-2 gap-md-3 align-items-start">
                            <div class="flex-shrink-0">
                                <span class="badge <?= $event['badge_class'] ?> rounded-pill px-3 py-2">
                                    <i class="fa-solid <?= $event['icon'] ?> me-1"></i><?= Html::encode($event['kind_label']) ?>
                                </span>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="fw-semibold text-dark"><?= Html::encode($creatorName) ?></span>
                                    <i class="fa-solid fa-arrow-right text-muted small"></i>
                                    <?php if (!empty($event['target'])): ?>
                                        <span class="text-primary fw-medium"><?= Html::encode($event['target']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted d-flex flex-wrap gap-3">
                                    <?php if ($creatorDept): ?>
                                        <span><i class="fa-regular fa-building me-1"></i><?= Html::encode($creatorDept) ?></span>
                                    <?php endif; ?>
                                    <span><i class="fa-regular fa-clock me-1"></i><?= Html::encode($thaiDate) ?></span>
                                </div>
                                <?php if (!empty($event['comment'])): ?>
                                    <div class="mt-2 p-2 rounded bg-light small text-dark">
                                        <i class="fa-regular fa-comment text-muted me-1"></i>
                                        <?= Html::encode($event['comment']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($event['is_owner']): ?>
                                <div class="flex-shrink-0 d-flex gap-1">
                                    <?php if ($event['kind'] === 'comment'): ?>
                                        <?= Html::a(
                                            '<i class="fa-regular fa-pen-to-square"></i>',
                                            ['/me/documents/update-comment', 'id' => $event['id']],
                                            [
                                                'class' => 'btn btn-sm btn-outline-secondary rounded-circle update-comment',
                                                'title' => 'แก้ไข',
                                            ]
                                        ) ?>
                                        <?= Html::a(
                                            '<i class="fa-regular fa-trash-can"></i>',
                                            ['/me/documents/delete-comment', 'id' => $event['id']],
                                            [
                                                'class' => 'btn btn-sm btn-outline-danger rounded-circle delete-comment',
                                                'title' => 'ลบ',
                                            ]
                                        ) ?>
                                    <?php elseif ($event['kind'] !== 'req_approve'): ?>
                                        <?= Html::a(
                                            '<i class="fa-regular fa-trash-can"></i>',
                                            ['/dms/documents/delete-forwarding', 'id' => $event['id']],
                                            [
                                                'class' => 'btn btn-sm btn-outline-danger rounded-circle delete-forwarding',
                                                'title' => 'ลบ',
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php Pjax::end(); ?>

<?php
$js = <<< 'JS'
$(document).off('click.forwardingTimeline');
$(document).on('click.forwardingTimeline', '.delete-forwarding', function (e) {
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
                    $.pjax.reload({ container: '#forwarding-timeline-pjax', history: false, timeout: false });
                    if ($('#document-tag').length) {
                        $.pjax.reload({ container: '#document-tag', history: false, timeout: false });
                    }
                    Swal.fire({ icon: 'success', title: 'ลบแล้ว', timer: 1200, showConfirmButton: false });
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
