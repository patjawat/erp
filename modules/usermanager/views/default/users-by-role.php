<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var string|null $role ชื่อบทบาทที่เลือก (ถ้ามี) */
/* @var array $roleDetails ชื่อบทบาท => ['description' => string] */
/* @var array $roleUsers ชื่อบทบาท => User[] */
/* @var array $roles */

$this->title = $role !== null && isset($roleDetails[$role])
    ? 'รายชื่อผู้ใช้ในบทบาท: ' . ($roleDetails[$role]['description'] ?? $role)
    : 'รายชื่อผู้ใช้แยกตามบทบาท';
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวม', 'url' => ['/usermanager/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-bold text-body mb-0 d-flex align-items-center gap-2">
    <span class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
        <i class="bi bi-person-badge fs-4"></i>
    </span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <?= Html::a('<i class="bi bi-grid-1x2 me-1"></i> ภาพรวม', ['/usermanager/default/dashboard'], ['class' => 'btn btn-outline-success rounded-3 link-loading']) ?>
    <?= Html::a('<i class="bi bi-person-badge me-1"></i> จัดการบทบาท', ['/usermanager/role'], ['class' => 'btn btn-outline-danger rounded-3 link-loading']) ?>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <?php foreach ($roleUsers as $roleName => $users): ?>
    <?php $detail = $roleDetails[$roleName] ?? ['description' => $roleName]; ?>
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold text-body mb-0">
                <?= Html::encode($detail['description']) ?> <span class="text-muted fw-normal">(<?= Html::encode($roleName) ?>)</span>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1 ms-2"><?= count($users) ?> คน</span>
            </h5>
            <?= Html::a('ดูในภาพรวม', ['/usermanager/default/dashboard'], ['class' => 'btn btn-sm btn-outline-secondary rounded-3 link-loading']) ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($users)): ?>
            <div class="p-4 text-center text-muted">ไม่มีผู้ใช้ในบทบาทนี้</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small fw-semibold text-center">#</th>
                            <th class="small fw-semibold text-start">ชื่อเข้าใช้งาน</th>
                            <th class="small fw-semibold text-start">ชื่อ-นามสกุล</th>
                            <th class="small fw-semibold text-center">สถานะ</th>
                            <th class="small fw-semibold text-center">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach ($users as $i => $model): ?>
                        <?php
                        $isActive = $model->status == \app\modules\usermanager\models\User::STATUS_ACTIVE;
                        $badgeClass = $isActive
                            ? 'badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1'
                            : 'badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1';
                        ?>
                        <tr>
                            <td class="text-center text-muted"><?= $i + 1 ?></td>
                            <td class="text-start"><?= Html::encode($model->username) ?></td>
                            <td class="text-start"><?= Html::encode($model->employee ? $model->employee->fullname : '-') ?></td>
                            <td class="text-center"><span class="<?= $badgeClass ?>"><?= Html::encode($model->statusName) ?></span></td>
                            <td class="text-center">
                                <?= Html::a('<i class="bi bi-eye"></i> ดู', ['/usermanager/user/view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary rounded-pill link-loading']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($roleUsers)): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body py-5 text-center text-muted">
            <i class="bi bi-person-badge display-4 opacity-50"></i>
            <p class="mb-0 mt-2">ไม่มีข้อมูลบทบาทหรือตาราง auth_assignment</p>
        </div>
    </div>
    <?php endif; ?>
</div>
