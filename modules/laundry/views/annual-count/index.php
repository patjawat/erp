<?php

use yii\helpers\Html;

$this->title = 'สอบยอดผ้ารายหน่วยงาน';
$canManage = Yii::$app->user->can('laundry.manage');
?>
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div><h4 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h4><div class="text-muted">ตรวจนับจำนวนชิ้นจริง เทียบยอดบัญชี ณ วันเริ่มสอบ</div></div>
        <?= Html::a('กลับบัญชีผ้า', ['/laundry/inventory/index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
    </div>
    <?php if (Yii::$app->session->hasFlash('error')): ?><div class="alert alert-danger"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div><?php endif; ?>
    <?php if ($canManage): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body">
            <h5 class="fw-semibold">เปิดรอบสอบยอด</h5>
            <?= Html::beginForm(['open'], 'post', ['class' => 'row g-3 align-items-end']) ?>
                <div class="col-12 col-sm-7"><label class="form-label">หน่วยงาน</label><?= Html::dropDownList('department_id', null, $departments, ['prompt' => 'เลือกหน่วยงาน', 'class' => 'form-select', 'required' => true]) ?></div>
                <div class="col-12 col-sm-2"><label class="form-label">ปี พ.ศ.</label><?= Html::input('number', 'count_year', (int) date('Y') + 543, ['class' => 'form-control', 'min' => 2543, 'max' => 2743, 'required' => true]) ?></div>
                <div class="col-12 col-sm-3"><?= Html::submitButton('เปิดรอบ', ['class' => 'btn btn-primary rounded-3 w-100']) ?></div>
            <?= Html::endForm() ?>
        </div></div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden"><div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">รายการสอบยอด</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="ps-4">ปี</th><th>หน่วยงาน</th><th>ตัดยอด</th><th>สถานะ</th><th class="text-end pe-4">รายละเอียด</th></tr></thead><tbody>
            <?php foreach ($counts as $count): ?><tr><td class="ps-4"><?= Html::encode((int) $count['count_year'] + 543) ?></td><td><?= Html::encode($count['department_name'] ?: '#' . $count['department_id']) ?></td><td><?= Html::encode($count['cutoff_at']) ?></td><td><span class="badge <?= $count['status'] === 'APPROVED' ? 'bg-success' : ($count['status'] === 'CANCELLED' ? 'bg-secondary' : 'bg-warning text-dark') ?>"><?= $count['status'] === 'APPROVED' ? 'อนุมัติแล้ว' : ($count['status'] === 'CANCELLED' ? 'ยกเลิก' : 'รอตรวจนับ') ?></span></td><td class="text-end pe-4"><?= Html::a('เปิด', ['view', 'id' => $count['id']], ['class' => 'btn btn-sm btn-outline-primary rounded-3']) ?></td></tr><?php endforeach; ?>
            <?php if (!$counts): ?><tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีรอบสอบยอด</td></tr><?php endif; ?>
        </tbody></table></div></div>
    </div>
</div>
