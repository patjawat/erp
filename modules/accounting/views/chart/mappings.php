<?php

use app\modules\accounting\models\AccountingChartMapping;
use yii\helpers\Html;

$this->title = 'ตรวจการจับคู่ผังบัญชี';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบัญชี', 'url' => ['/accounting/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ผังบัญชี', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-link-45deg" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>รหัสตรงกันยืนยันอัตโนมัติ ส่วนรหัสย่อยต้องให้ฝ่ายบัญชีตรวจรับ<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= Html::a('กลับผังโรงพยาบาล', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?><?php $this->endBlock(); ?>

<?php
$confirmed = count(array_filter($mappings, static fn($m) => $m->status === AccountingChartMapping::STATUS_CONFIRMED));
$suggested = count(array_filter($mappings, static fn($m) => $m->status === AccountingChartMapping::STATUS_SUGGESTED));
$rejected = count(array_filter($mappings, static fn($m) => $m->status === AccountingChartMapping::STATUS_REJECTED));
?>
<div class="d-flex flex-wrap gap-2 mb-3" aria-label="สรุปผลการจับคู่">
    <span class="badge rounded-pill bg-success-subtle text-success-emphasis">ยืนยันแล้ว <?= number_format($confirmed) ?></span>
    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">รอตรวจ <?= number_format($suggested) ?></span>
    <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis">ปฏิเสธ <?= number_format($rejected) ?></span>
    <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">ยังไม่จับคู่ <?= number_format(count($unmapped)) ?></span>
</div>

<section class="card border mb-3" aria-labelledby="mapping-list-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="mapping-list-heading">รายการที่ระบบจับคู่ได้</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>รหัสโรงพยาบาล</th><th>ชื่อบัญชีโรงพยาบาล</th><th>รหัสมาตรฐาน</th><th>วิธีจับคู่</th><th>สถานะ</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($mappings as $mapping): ?>
                <tr>
                    <td class="font-monospace text-nowrap"><?= Html::encode($mapping->hospitalAccount->code) ?></td>
                    <td><?= Html::encode($mapping->hospitalAccount->name) ?></td>
                    <td><div class="font-monospace text-nowrap"><?= Html::encode($mapping->standardAccount->code) ?></div><div class="small text-body-secondary"><?= Html::encode($mapping->standardAccount->name) ?></div></td>
                    <td><?= $mapping->match_type === AccountingChartMapping::TYPE_EXACT ? 'รหัสตรงกัน' : 'รหัสย่อยของบัญชีมาตรฐาน' ?></td>
                    <td>
                        <?php if ($mapping->status === AccountingChartMapping::STATUS_CONFIRMED): ?><span class="badge bg-success-subtle text-success-emphasis">ยืนยันแล้ว</span>
                        <?php elseif ($mapping->status === AccountingChartMapping::STATUS_REJECTED): ?><span class="badge bg-danger-subtle text-danger-emphasis">ปฏิเสธ</span>
                        <?php else: ?><span class="badge bg-warning-subtle text-warning-emphasis">รอตรวจ</span><?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <?php if (Yii::$app->user->can('accountingChartManage') && $mapping->status === AccountingChartMapping::STATUS_SUGGESTED): ?>
                            <?= Html::beginForm(['confirm-mapping', 'id' => $mapping->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('ยืนยัน', ['class' => 'btn btn-sm btn-success']) ?><?= Html::endForm() ?>
                            <?= Html::beginForm(['reject-mapping', 'id' => $mapping->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('ปฏิเสธ', ['class' => 'btn btn-sm btn-outline-danger']) ?><?= Html::endForm() ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$mappings): ?><tr><td colspan="6" class="text-center text-body-secondary py-4">ยังไม่มีรหัสที่จับคู่กับผังมาตรฐานได้</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card border" aria-labelledby="unmapped-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="unmapped-heading">รหัสรายได้และค่าใช้จ่ายที่ยังไม่จับคู่</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>รหัสโรงพยาบาล</th><th>ชื่อบัญชี</th><th>สิ่งที่ต้องทำ</th></tr></thead>
            <tbody>
            <?php foreach ($unmapped as $account): ?><tr><td class="font-monospace text-nowrap"><?= Html::encode($account->code) ?></td><td><?= Html::encode($account->name) ?></td><td class="text-body-secondary">ตรวจว่าควรเพิ่มในผังมาตรฐานหรือจับคู่ด้วยตนเอง</td></tr><?php endforeach; ?>
            <?php if (!$unmapped): ?><tr><td colspan="3" class="text-center text-body-secondary py-4">บัญชีหมวดรายได้และค่าใช้จ่ายถูกจับคู่ครบแล้ว</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
