<?php

use yii\helpers\Html;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\usermanager\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$totalCount = $dataProvider->getTotalCount();
$pagination = $dataProvider->pagination;
$pageSize = $pagination ? (int) $pagination->pageSize : 20;
$currentPage = $pagination ? (int) $pagination->page : 0;
$from = $totalCount > 0 ? ($currentPage * $pageSize + 1) : 0;
$to = min($currentPage * $pageSize + $pageSize, $totalCount);
?>

<?= $this->render('_search', ['model' => $searchModel]) ?>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="small fw-semibold text-center">#</th>
                <th class="small fw-semibold text-start"><i class="bi bi-person me-1"></i> ชื่อเข้าใช้งาน</th>
                <th class="small fw-semibold text-start"><i class="bi bi-person-badge me-1"></i> ชื่อ-นามสกุล</th>
                <th class="small fw-semibold text-start"><i class="bi bi-building me-1"></i> แผนก/หน่วยงาน</th>
                <th class="small fw-semibold text-start"><i class="bi bi-shield-check me-1"></i> บทบาท/สิทธิ</th>
                <th class="small fw-semibold text-center">สถานะ</th>
                <th class="small fw-semibold text-end">สร้างเมื่อ</th>
                <th class="small fw-semibold text-center">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="align-middle table-group-divider">
            <?php
            $index = $from;
            $authManager = Yii::$app->authManager;
            foreach ($dataProvider->getModels() as $model):
                $fullname = $model->employee ? $model->employee->fullname : '-';
                $isActive = $model->status == \app\modules\usermanager\models\User::STATUS_ACTIVE;
                $badgeClass = $isActive
                    ? 'badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1'
                    : 'badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1';
                $userRoles = $authManager ? $authManager->getRolesByUser($model->id) : [];
            ?>
            <tr>
                <td class="text-center text-muted small"><?= $index ?></td>
                <td class="text-start"><?= Html::encode($model->username) ?></td>
                <td class="text-start"><?= Html::encode($fullname) ?></td>
                <td class="text-start"><?= $model->employee && method_exists($model->employee, 'departmentName') ? Html::encode($model->employee->departmentName()) : '—' ?></td>
                <td class="text-start">
                    <?php if (!empty($userRoles)): ?>
                        <?php foreach ($userRoles as $r): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1 me-1 mb-1"><?= Html::encode($r->description ?: $r->name) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted small">—</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <span class="<?= $badgeClass ?>"><?= Html::encode($model->statusName) ?></span>
                </td>
                <td class="text-end small text-muted"><?= $model->created_at ? ThaiDateHelper::formatThaiDate($model->created_at, 'short') : '-' ?></td>
                <td class="text-center">
                    <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary rounded-pill', 'title' => 'ดู']) ?>
                    <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill link-loading', 'title' => 'แก้ไข']) ?>
                </td>
            </tr>
            <?php
            $index++;
            endforeach;
            ?>
            <?php if ($dataProvider->getCount() === 0): ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-5">ไม่พบข้อมูลผู้ใช้งาน</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
    <div class="small text-muted">
        แสดง <?= $from ?> ถึง <?= $to ?> จากทั้งหมด <?= number_format($totalCount) ?> รายการ
    </div>
    <?= \yii\bootstrap5\LinkPager::widget([
    'pagination' => $dataProvider->pagination,
    'linkOptions' => ['data-pjax' => '0'],
]) ?>
</div>

