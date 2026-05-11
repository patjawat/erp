<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\dms\models\DocumentsDetail;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$currentUserId = (int) Yii::$app->user->id;

$detailTags = DocumentsDetail::find()
    ->where(['document_id' => $model->id])
    ->andWhere(['in', 'name', ['employee_tag', 'tags', 'employee']])
    ->orderBy(['id' => SORT_DESC])
    ->all();

// แสดงรายการ tag บุคคลจาก documents_detail เท่านั้น
$rows = [];
foreach ($detailTags as $r) {
    $rows[] = [
        'source' => 'detail',
        'id' => $r->id,
        'to_id' => (int) $r->to_id,
        'created_by' => (int) $r->created_by,
        'created_at' => $r->created_at,
        'comment' => is_array($r->data_json) && isset($r->data_json['comment']) ? $r->data_json['comment'] : '',
    ];
}

// preload employee + creator
$empIds = array_filter(array_map(function ($r) { return $r['to_id']; }, $rows));
$creatorIds = array_filter(array_map(function ($r) { return $r['created_by']; }, $rows));
$empMap = $empIds ? Employees::find()->where(['id' => $empIds])->indexBy('id')->all() : [];
$creatorMap = $creatorIds ? Employees::find()->where(['user_id' => $creatorIds])->indexBy('user_id')->all() : [];
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="fa-solid fa-user-tag me-1 text-primary"></i> Tag บุคคล
            <small class="text-muted fw-normal">เพื่อให้รับทราบ / ดำเนินการ</small>
        </h6>
        <span class="badge text-bg-light rounded-pill"><?= count($rows) ?></span>
    </div>

    <?php Pjax::begin(['id' => 'document-tag', 'timeout' => false]); ?>
    <div class="card-body p-3">
        <?php if (empty($rows)): ?>
            <div class="text-center text-muted small py-3">
                <i class="fa-regular fa-user fs-4 d-block mb-2 opacity-50"></i>
                ยังไม่มีบุคคลที่ถูก tag
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($rows as $row): ?>
                    <?php
                    $emp = $empMap[$row['to_id']] ?? null;
                    $creator = $creatorMap[$row['created_by']] ?? null;
                    $isOwner = $row['created_by'] === $currentUserId;
                    $thaiDate = '';
                    try {
                        $thaiDate = ThaiDateHelper::formatThaiDate($row['created_at']);
                    } catch (\Throwable $th) {
                        $thaiDate = $row['created_at'];
                    }
                    ?>
                    <div class="d-flex align-items-start gap-2 p-2 rounded bg-light bg-opacity-50">
                        <div class="flex-shrink-0">
                            <?php if ($emp): ?>
                                <img src="<?= $emp->showAvatar() ?>" class="rounded-circle border" style="width:36px;height:36px;object-fit:cover;" alt="">
                            <?php else: ?>
                                <span class="d-inline-flex align-items-center justify-content-center bg-secondary text-white rounded-circle" style="width:36px;height:36px;">
                                    <i class="fa-solid fa-user"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="fw-semibold"><?= Html::encode($emp ? $emp->fullname : ('บุคคล #' . $row['to_id'])) ?></span>
                                <?php if ($emp): ?>
                                    <span class="badge text-bg-light text-muted small"><?= Html::encode($emp->departmentName()) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted">
                                <i class="fa-regular fa-circle-user me-1"></i>
                                tag โดย <span class="fw-medium text-dark"><?= Html::encode($creator ? $creator->fullname : 'ผู้ใช้ #' . $row['created_by']) ?></span>
                                · <i class="fa-regular fa-clock"></i> <?= Html::encode($thaiDate) ?>
                            </div>
                            <?php if (!empty($row['comment'])): ?>
                                <div class="small mt-1 text-dark"><i class="fa-regular fa-comment text-muted me-1"></i><?= Html::encode($row['comment']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($isOwner): ?>
                            <div class="flex-shrink-0 d-flex gap-1">
                                <?php if ($row['source'] === 'tag'): ?>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>',
                                        ['/dms/document-tags/update', 'id' => $row['id'], 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข tag บุคคล'],
                                        [
                                            'class' => 'btn btn-sm btn-outline-secondary rounded-circle open-modal',
                                            'data' => ['size' => 'modal-md'],
                                            'title' => 'แก้ไข',
                                        ]) ?>
                                    <?= Html::a('<i class="fa-regular fa-trash-can"></i>',
                                        ['/dms/document-tags/delete', 'id' => $row['id']],
                                        [
                                            'class' => 'btn btn-sm btn-outline-danger rounded-circle delete-tag',
                                            'title' => 'ลบ',
                                        ]) ?>
                                <?php else: ?>
                                    <?= Html::a('<i class="fa-regular fa-trash-can"></i>',
                                        ['/dms/documents/delete-forwarding', 'id' => $row['id']],
                                        [
                                            'class' => 'btn btn-sm btn-outline-danger rounded-circle delete-forwarding',
                                            'title' => 'ลบ',
                                        ]) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php Pjax::end(); ?>

    <div class="card-footer bg-white border-top">
        <?= Html::a(
            '<i class="fa-solid fa-circle-plus me-1"></i> Tag บุคคลเพิ่มเติม',
            ['/dms/document-tags/create', 'document_id' => $model->id, 'ref' => $model->ref, 'name' => 'tags', 'title' => '<i class="fa-solid fa-user-tag"></i> Tag บุคคล'],
            ['class' => 'btn btn-sm btn-primary rounded-pill open-modal w-100 w-md-auto', 'data' => ['size' => 'modal-md']]
        ) ?>
    </div>
</div>
