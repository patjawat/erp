<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentTags;
use app\modules\dms\models\DocumentsDetail;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var bool $canManageDepartmentExtra */
/** @var array $currentDeptIdsStr */

$currentUserId = (int) Yii::$app->user->id;

$detailRows = DocumentsDetail::find()
    ->where(['document_id' => $model->id])
    ->andWhere(['in', 'name', ['department', 'employee_tag', 'tags', 'employee', 'comment']])
    ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
    ->all();

// children ของแต่ละ comment (link ผ่าน from_id)
$commentIds = [];
foreach ($detailRows as $r) {
    if ($r->name === 'comment') { $commentIds[] = $r->id; }
}
$commentChildrenMap = [];
if ($commentIds) {
    $children = DocumentsDetail::find()
        ->where(['from_id' => $commentIds])
        ->andWhere(['in', 'name', ['comment_emp', 'comment_dept']])
        ->all();
    $childEmpIds = [];
    $childDeptIds = [];
    foreach ($children as $c) {
        if ($c->name === 'comment_emp') { $childEmpIds[] = (int) $c->to_id; }
        elseif ($c->name === 'comment_dept') { $childDeptIds[] = (int) $c->to_id; }
    }
    $childEmpMap = $childEmpIds ? Employees::find()->where(['id' => $childEmpIds])->indexBy('id')->all() : [];
    $childDeptMap = $childDeptIds ? Organization::find()->where(['id' => $childDeptIds])->indexBy('id')->all() : [];
    foreach ($children as $c) {
        $parentId = (int) $c->from_id;
        if (!isset($commentChildrenMap[$parentId])) {
            $commentChildrenMap[$parentId] = ['emp' => [], 'dept' => []];
        }
        if ($c->name === 'comment_emp') {
            $emp = $childEmpMap[(int) $c->to_id] ?? null;
            $commentChildrenMap[$parentId]['emp'][] = ['id' => $c->id, 'label' => $emp ? $emp->fullname : ('บุคคล #' . $c->to_id), 'is_owner' => (int) $c->created_by === $currentUserId];
        } else {
            $org = $childDeptMap[(int) $c->to_id] ?? null;
            $commentChildrenMap[$parentId]['dept'][] = ['id' => $c->id, 'label' => $org ? $org->name : ('หน่วยงาน #' . $c->to_id), 'is_owner' => (int) $c->created_by === $currentUserId];
        }
    }
}

$tagRows = DocumentTags::find()
    ->where(['document_id' => $model->id])
    ->andWhere(['in', 'name', ['employee_tag', 'employee', 'req_approve']])
    ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
    ->all();

$creatorIds = [];
foreach ($detailRows as $r) { $creatorIds[(int) $r->created_by] = true; }
foreach ($tagRows as $r) { $creatorIds[(int) $r->created_by] = true; }
$creatorIds = array_keys(array_filter($creatorIds));
$creatorMap = $creatorIds
    ? Employees::find()->where(['user_id' => $creatorIds])->indexBy('user_id')->all()
    : [];

$events = [];
foreach ($detailRows as $r) {
    $kind = $r->name;
    $targetLabel = '';
    if ($kind === 'department') {
        $org = Organization::findOne((int) $r->to_id);
        $targetLabel = $org ? $org->name : ('หน่วยงาน #' . $r->to_id);
        $iconBg = 'bg-success bg-opacity-10';
        $iconColor = 'text-success';
        $icon = 'fa-building';
        $kindLabel = 'ส่งหน่วยงาน';
    } elseif ($kind === 'comment') {
        $iconBg = 'bg-info bg-opacity-10';
        $iconColor = 'text-info';
        $icon = 'fa-comment-dots';
        $kindLabel = 'ลงความเห็น';
        $children = $commentChildrenMap[(int) $r->id] ?? ['emp' => [], 'dept' => []];
    } else {
        $emp = Employees::findOne((int) $r->to_id);
        $targetLabel = $emp ? $emp->fullname : ('บุคคล #' . $r->to_id);
        $iconBg = 'bg-primary bg-opacity-10';
        $iconColor = 'text-primary';
        $icon = 'fa-user';
        $kindLabel = 'Tag บุคคล';
    }
    $comment = is_array($r->data_json) && isset($r->data_json['comment']) ? $r->data_json['comment'] : '';
    $events[] = [
        'source' => 'detail',
        'kind' => $kind,
        'id' => $r->id,
        'created_at' => $r->created_at,
        'created_by' => (int) $r->created_by,
        'kind_label' => $kindLabel,
        'icon' => $icon,
        'icon_bg' => $iconBg,
        'icon_color' => $iconColor,
        'target' => $targetLabel,
        'comment' => $comment,
        'children' => $children ?? null,
        'is_owner' => (int) $r->created_by === $currentUserId,
    ];
    unset($children);
}
foreach ($tagRows as $r) {
    if ($r->name === 'req_approve') {
        $kindLabel = 'เสนอผู้อำนวยการ';
        $iconBg = 'bg-warning bg-opacity-10';
        $iconColor = 'text-warning';
        $icon = 'fa-file-signature';
    } else {
        $kindLabel = 'Tag บุคคล';
        $iconBg = 'bg-primary bg-opacity-10';
        $iconColor = 'text-primary';
        $icon = 'fa-user';
    }
    $emp = $r->tag_id ? Employees::findOne((int) $r->tag_id) : null;
    $targetLabel = $emp ? $emp->fullname : ($r->tag_id ? ('บุคคล #' . $r->tag_id) : '-');
    $comment = is_array($r->data_json) && isset($r->data_json['comment']) ? $r->data_json['comment'] : '';
    $events[] = [
        'source' => 'tag',
        'id' => $r->id,
        'created_at' => $r->created_at,
        'created_by' => (int) $r->created_by,
        'kind' => $r->name,
        'kind_label' => $kindLabel,
        'icon' => $icon,
        'icon_bg' => $iconBg,
        'icon_color' => $iconColor,
        'target' => $targetLabel,
        'comment' => $comment,
        'is_owner' => (int) $r->created_by === $currentUserId,
    ];
}
usort($events, function ($a, $b) {
    return strcmp((string) $a['created_at'], (string) $b['created_at']);
});
?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="px-4 py-3 border-bottom border-light-subtle">
        <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width:32px;height:32px;">
                <i class="fa-regular fa-paper-plane"></i>
            </span>
            <div>
                <div class="text-uppercase small text-primary fw-semibold opacity-75" style="letter-spacing:.05em;">Activity</div>
                <div class="fw-bold text-dark">ไทม์ไลน์เอกสาร <span class="text-muted small fw-normal">(<?= count($events) ?>)</span></div>
            </div>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'document-tag', 'timeout' => false]); ?>
    <div class="p-0">
        <?php if (empty($events)): ?>
            <div class="px-4 py-5 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width:56px;height:56px;">
                    <i class="fa-regular fa-paper-plane text-muted fs-4"></i>
                </div>
                <div class="text-muted small">ยังไม่มีการส่งต่อเอกสาร</div>
                <?php if ($canManageDepartmentExtra): ?>
                    <div class="text-muted small mt-1">กดปุ่ม "ส่งหน่วยงาน" ด้านบนเพื่อเริ่ม</div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <ul class="list-unstyled mb-0">
                <?php foreach ($events as $i => $event): ?>
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
                    $isLast = ($i === count($events) - 1);
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

                                <?php if ($event['kind'] === 'comment' && !empty($event['comment'])): ?>
                                    <div class="mt-2 px-3 py-2 rounded-3 bg-info bg-opacity-10 border border-info-subtle text-dark">
                                        <i class="fa-solid fa-quote-left text-info-emphasis opacity-50 me-1 small"></i><?= nl2br(Html::encode($event['comment'])) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($event['kind'] === 'comment' && !empty($event['children'])): ?>
                                    <?php $kids = $event['children']; ?>
                                    <?php if (!empty($kids['emp']) || !empty($kids['dept'])): ?>
                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            <?php foreach ($kids['emp'] as $chip): ?>
                                                <span class="badge text-bg-light border border-primary-subtle text-primary rounded-pill px-2 py-1 small d-inline-flex align-items-center gap-1">
                                                    <i class="fa-solid fa-user opacity-75"></i>
                                                    <?= Html::encode($chip['label']) ?>
                                                    <?php if ($chip['is_owner']): ?>
                                                        <a href="<?= Url::to(['/dms/documents/delete-forwarding', 'id' => $chip['id']]) ?>" class="text-muted text-decoration-none ms-1 delete-forwarding" title="ลบ"><i class="fa-solid fa-xmark small"></i></a>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php foreach ($kids['dept'] as $chip): ?>
                                                <span class="badge text-bg-light border border-success-subtle text-success rounded-pill px-2 py-1 small d-inline-flex align-items-center gap-1">
                                                    <i class="fa-solid fa-building opacity-75"></i>
                                                    <?= Html::encode($chip['label']) ?>
                                                    <?php if ($chip['is_owner']): ?>
                                                        <a href="<?= Url::to(['/dms/documents/delete-forwarding', 'id' => $chip['id']]) ?>" class="text-muted text-decoration-none ms-1 delete-forwarding" title="ลบ"><i class="fa-solid fa-xmark small"></i></a>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="small text-muted mt-2 d-flex flex-wrap gap-3">
                                    <span><i class="fa-regular fa-circle-user me-1"></i>โดย <span class="text-dark fw-medium"><?= Html::encode($creatorName) ?></span></span>
                                    <?php if ($creatorDept): ?>
                                        <span><i class="fa-regular fa-building me-1"></i><?= Html::encode($creatorDept) ?></span>
                                    <?php endif; ?>
                                    <span><i class="fa-regular fa-clock me-1"></i><?= Html::encode($thaiDate) ?></span>
                                </div>

                                <?php if ($event['kind'] !== 'comment' && !empty($event['comment'])): ?>
                                    <div class="mt-2 px-3 py-2 rounded-3 bg-light small text-dark">
                                        <i class="fa-regular fa-comment text-muted me-1"></i><?= Html::encode($event['comment']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($event['is_owner']): ?>
                                <div class="flex-shrink-0 d-flex gap-1">
                                    <?php if ($event['source'] === 'detail' && $event['kind'] === 'comment'): ?>
                                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>',
                                            ['/me/documents/update-comment', 'id' => $event['id']],
                                            ['class' => 'btn btn-sm btn-light text-secondary rounded-circle update-comment', 'title' => 'แก้ไข']) ?>
                                        <?= Html::a('<i class="fa-regular fa-trash-can"></i>',
                                            ['/me/documents/delete-comment', 'id' => $event['id']],
                                            ['class' => 'btn btn-sm btn-light text-danger rounded-circle delete-comment', 'title' => 'ลบ']) ?>
                                    <?php elseif ($event['source'] === 'tag' && $event['kind'] !== 'req_approve'): ?>
                                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>',
                                            ['/dms/document-tags/update', 'id' => $event['id'], 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข tag'],
                                            ['class' => 'btn btn-sm btn-light text-secondary rounded-circle open-modal', 'data' => ['size' => 'modal-md'], 'title' => 'แก้ไข']) ?>
                                        <?= Html::a('<i class="fa-regular fa-trash-can"></i>',
                                            ['/dms/document-tags/delete', 'id' => $event['id']],
                                            ['class' => 'btn btn-sm btn-light text-danger rounded-circle delete-tag', 'title' => 'ลบ']) ?>
                                    <?php elseif ($event['source'] === 'detail'): ?>
                                        <?= Html::a('<i class="fa-regular fa-trash-can"></i>',
                                            ['/dms/documents/delete-forwarding', 'id' => $event['id']],
                                            ['class' => 'btn btn-sm btn-light text-danger rounded-circle delete-forwarding', 'title' => 'ลบ']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php Pjax::end(); ?>

    <div class="border-top border-light-subtle bg-white px-4 py-3">
        <div class="viewFormComment">
            <div class="text-center text-muted small py-2">
                <div class="spinner-border spinner-border-sm me-2"></div> กำลังโหลดช่องเขียนความเห็น...
            </div>
        </div>
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
                    if ($('#document-tag').length) { $.pjax.reload({ container: '#document-tag', history: false, timeout: false }); }
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
