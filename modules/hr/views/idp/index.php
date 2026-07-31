<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\hr\models\IdpPlan;

$this->title = 'IDP Management';
echo $this->render('_styles');
echo $this->render('@app/modules/hr/views/workforce/_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'idp']); $this->endBlock();
$models = $dataProvider->getModels();
?>
<div class="idp-shell" id="idp-management">
    <?= $this->render('@app/modules/hr/views/workforce/_menu', ['active' => 'idp']) ?>
    <div class="idp-head">
        <div><h1>IDP Management</h1><p>กำหนดรอบและติดตามแผนพัฒนารายบุคคล โดยเริ่มจากรายการที่ต้องดำเนินการ</p></div>
        <?= Html::a('<i class="bi bi-calendar-plus me-1"></i> สร้างรอบ IDP', ['cycle'], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-lg']) ?>
    </div>
    <div class="idp-surface">
        <div class="idp-actions" aria-label="รายการที่ต้องดำเนินการ">
            <div class="idp-action"><span class="idp-action__icon"><i data-lucide="user-round-check"></i></span><span><span class="idp-action__label">รอหัวหน้าเห็นชอบ</span><span class="idp-action__value d-block"><?= (int)($counts['submitted'] ?? 0) ?> รายการ</span></span></div>
            <div class="idp-action"><span class="idp-action__icon is-warning"><i data-lucide="folder-open"></i></span><span><span class="idp-action__label">รอ HR เปิดบันทึก</span><span class="idp-action__value d-block"><?= (int)($counts['approved'] ?? 0) ?> รายการ</span></span></div>
            <div class="idp-action"><span class="idp-action__icon"><i data-lucide="flag-triangle-right"></i></span><span><span class="idp-action__label">รอปิดรอบ</span><span class="idp-action__value d-block"><?= (int)($counts['assessment'] ?? 0) ?> รายการ</span></span></div>
        </div>
        <form class="idp-toolbar" method="get">
            <div><label for="idp-cycle">รอบ IDP</label><select id="idp-cycle" name="cycle_id" class="form-select"><?php foreach($cycles as $item): ?><option value="<?= $item->id ?>" <?= $cycle && $cycle->id == $item->id ? 'selected' : '' ?>><?= Html::encode($item->title) ?></option><?php endforeach ?></select></div>
            <div class="flex-grow-1"><label for="idp-q">ค้นหาบุคลากร</label><input id="idp-q" name="q" value="<?= Html::encode($q) ?>" class="form-control" placeholder="ชื่อ นามสกุล หรือรหัส"></div>
            <div><label for="idp-status">สถานะ</label><select id="idp-status" name="status" class="form-select"><option value="">ทุกสถานะ</option><?php foreach(IdpPlan::statusOptions() as $key=>$label): ?><option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>><?= Html::encode($label) ?></option><?php endforeach ?></select></div>
            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-funnel me-1"></i> กรองข้อมูล</button>
            <?php if($cycle): ?><?= Html::a('แก้ไขรอบ', ['cycle','id'=>$cycle->id], ['class'=>'btn btn-light open-modal','data-size'=>'modal-lg']) ?><?php endif ?>
        </form>
        <?php if($models): ?>
        <div class="idp-table-wrap"><table class="table idp-table">
            <thead><tr><th>บุคลากร</th><th>รอบ IDP</th><th>เป้าหมาย</th><th>ความก้าวหน้า</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
            <tbody><?php foreach($models as $plan): ?><tr>
                <td><div class="fw-semibold"><?= Html::encode($plan->employee?->fullname ?? '-') ?></div><div class="text-muted small"><?= Html::encode($plan->employee?->departmentName() ?? '-') ?></div></td>
                <td><?= Html::encode($plan->cycle?->title ?? '-') ?></td><td><?= count($plan->goals) ?> เป้าหมาย</td>
                <td style="min-width:130px"><div class="d-flex align-items-center gap-2"><div class="idp-progress flex-grow-1"><span style="width:<?= min(100,(float)$plan->progress_percent) ?>%"></span></div><span class="small"><?= (int)$plan->progress_percent ?>%</span></div></td>
                <td><span class="idp-status idp-status--<?= Html::encode($plan->status) ?>"><?= Html::encode($plan->statusLabel) ?></span></td>
                <td class="text-end"><?= Html::a('เปิดแผน', ['employee','emp_id'=>$plan->emp_id], ['class'=>'btn btn-sm btn-outline-primary','data-pjax'=>'0']) ?></td>
            </tr><?php endforeach ?></tbody>
        </table></div>
        <div class="p-3"><?= \app\components\widgets\DataSummaryWidget::widget(['dataProvider'=>$dataProvider]) ?></div>
        <?php else: ?><div class="idp-empty"><h2>ยังไม่มีแผนในรอบนี้</h2><p>เมื่อพนักงานเริ่มจัดทำ IDP รายการจะปรากฏที่นี่โดยอัตโนมัติ</p></div><?php endif ?>
    </div>
</div>
