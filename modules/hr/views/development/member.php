<?php
use yii\helpers\Html;
use app\components\UserHelper;
$me = UserHelper::GetEmployee();
$memberInfo = $model->memberText();
?>
<div class="card mt-4">
    <div class="card-header p-2">
        <div class="d-flex align-items-center justify-content-between">
            <strong><i class="bi bi-people me-2"></i>คณะเดินทาง</strong>
            <?= Html::a('<i class="bi bi-plus-circle me-1"></i> เพิ่ม', ['/me/development-detail/create', 'name' => 'member', 'development_id' => $model->id, 'title' => 'คณะเดินทาง'], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($memberInfo['count'] > 0): ?>
            <div class="mb-2"><?= $model->StackMember() ?></div>
            <div class="small text-body">
                <span class="text-muted">รายชื่อ:</span>
                <?= Html::encode($memberInfo['text']) ?>
            </div>
        <?php else: ?>
            <p class="text-muted small mb-0">ยังไม่มีรายชื่อคณะเดินทาง — กดปุ่ม «เพิ่ม» เพื่อเพิ่มสมาชิก</p>
        <?php endif; ?>
    </div>
</div>