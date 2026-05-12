<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentsDetail;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$currentUserId = (int) Yii::$app->user->id;

$commentRows = DocumentsDetail::find()
    ->where(['document_id' => $model->id, 'name' => 'comment'])
    ->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC])
    ->all();

$commentIds = [];
foreach ($commentRows as $row) {
    $commentIds[] = (int) $row->id;
}

$commentChildrenMap = [];
if ($commentIds) {
    $children = DocumentsDetail::find()
        ->where(['from_id' => $commentIds])
        ->andWhere(['in', 'name', ['comment_emp', 'comment_dept']])
        ->all();

    $childEmpIds = [];
    $childDeptIds = [];
    foreach ($children as $child) {
        if ($child->name === 'comment_emp') {
            $childEmpIds[] = (int) $child->to_id;
        } elseif ($child->name === 'comment_dept') {
            $childDeptIds[] = (int) $child->to_id;
        }
    }

    $childEmpMap = $childEmpIds ? Employees::find()->where(['id' => $childEmpIds])->indexBy('id')->all() : [];
    $childDeptMap = $childDeptIds ? Organization::find()->where(['id' => $childDeptIds])->indexBy('id')->all() : [];

    foreach ($children as $child) {
        $parentId = (int) $child->from_id;
        if (!isset($commentChildrenMap[$parentId])) {
            $commentChildrenMap[$parentId] = ['emp' => [], 'dept' => []];
        }

        if ($child->name === 'comment_emp') {
            $emp = $childEmpMap[(int) $child->to_id] ?? null;
            $commentChildrenMap[$parentId]['emp'][] = [
                'id' => $child->id,
                'label' => $emp ? $emp->fullname : ('บุคคล #' . $child->to_id),
                'is_owner' => (int) $child->created_by === $currentUserId,
            ];
        } else {
            $org = $childDeptMap[(int) $child->to_id] ?? null;
            $commentChildrenMap[$parentId]['dept'][] = [
                'id' => $child->id,
                'label' => $org ? $org->name : ('หน่วยงาน #' . $child->to_id),
                'is_owner' => (int) $child->created_by === $currentUserId,
            ];
        }
    }
}

$creatorIds = [];
foreach ($commentRows as $row) {
    $creatorIds[(int) $row->created_by] = true;
}
$creatorIds = array_keys(array_filter($creatorIds));
$creatorMap = $creatorIds
    ? Employees::find()->where(['user_id' => $creatorIds])->indexBy('user_id')->all()
    : [];
?>

<?php if (empty($commentRows)): ?>
    <div class="px-4 py-5 text-center">
        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width:56px;height:56px;">
            <i class="fa-regular fa-comments text-muted fs-4"></i>
        </div>
        <div class="text-muted small">ยังไม่มีการลงความเห็น</div>
    </div>
<?php else: ?>
    <ul class="list-unstyled mb-0">
        <?php foreach ($commentRows as $index => $row): ?>
            <?php
            $creator = $creatorMap[$row->created_by] ?? null;
            $creatorName = $creator ? $creator->fullname : 'ผู้ใช้ #' . $row->created_by;
            $creatorDept = $creator ? $creator->departmentName() : '';
            $thaiDate = '';
            try {
                $thaiDate = ThaiDateHelper::formatThaiDate($row->created_at);
                $timePart = explode(' ', (string) $row->created_at);
                $thaiDate .= ' ' . ($timePart[1] ?? '');
            } catch (\Throwable $th) {
                $thaiDate = $row->created_at;
            }

            $commentText = is_array($row->data_json) && isset($row->data_json['comment'])
                ? (string) $row->data_json['comment']
                : '';
            $children = $commentChildrenMap[(int) $row->id] ?? ['emp' => [], 'dept' => []];
            $isLast = ($index === count($commentRows) - 1);
            ?>
            <li class="px-4 py-3 <?= $isLast ? '' : 'border-bottom border-light-subtle' ?>">
                <div class="d-flex gap-3 align-items-start">
                    <div class="flex-shrink-0">
                        <span class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-circle" style="width:40px;height:40px;">
                            <i class="fa-solid fa-comment-dots"></i>
                        </span>
                    </div>

                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge text-bg-light text-muted rounded-pill border">ลงความเห็น</span>
                        </div>

                        <?php if ($commentText !== ''): ?>
                            <div class="mt-2 px-3 py-2 rounded-3 bg-info bg-opacity-10 border border-info-subtle text-dark">
                                <i class="fa-solid fa-quote-left text-info-emphasis opacity-50 me-1 small"></i><?= nl2br(Html::encode($commentText)) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($children['emp']) || !empty($children['dept'])): ?>
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                <?php foreach ($children['emp'] as $chip): ?>
                                    <span class="badge text-bg-light border border-primary-subtle text-primary rounded-pill px-2 py-1 small d-inline-flex align-items-center gap-1">
                                        <i class="fa-solid fa-user opacity-75"></i>
                                        <?= Html::encode($chip['label']) ?>
                                        <?php if ($chip['is_owner']): ?>
                                            <a href="<?= Url::to(['/dms/documents/delete-forwarding', 'id' => $chip['id']]) ?>" class="text-muted text-decoration-none ms-1 delete-forwarding" title="ลบ">
                                                <i class="fa-solid fa-xmark small"></i>
                                            </a>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php foreach ($children['dept'] as $chip): ?>
                                    <span class="badge text-bg-light border border-success-subtle text-success rounded-pill px-2 py-1 small d-inline-flex align-items-center gap-1">
                                        <i class="fa-solid fa-building opacity-75"></i>
                                        <?= Html::encode($chip['label']) ?>
                                        <?php if ($chip['is_owner']): ?>
                                            <a href="<?= Url::to(['/dms/documents/delete-forwarding', 'id' => $chip['id']]) ?>" class="text-muted text-decoration-none ms-1 delete-forwarding" title="ลบ">
                                                <i class="fa-solid fa-xmark small"></i>
                                            </a>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
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

                    <?php if ((int) $row->created_by === $currentUserId): ?>
                        <div class="flex-shrink-0 d-flex gap-1">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>',
                                ['/me/documents/update-comment', 'id' => $row->id],
                                ['class' => 'btn btn-sm btn-light text-secondary rounded-circle update-comment', 'title' => 'แก้ไข']) ?>
                            <?= Html::a('<i class="fa-regular fa-trash-can"></i>',
                                ['/me/documents/delete-comment', 'id' => $row->id],
                                ['class' => 'btn btn-sm btn-light text-danger rounded-circle delete-comment', 'title' => 'ลบ']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
