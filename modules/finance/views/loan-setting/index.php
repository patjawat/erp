<?php

use app\modules\finance\models\FinanceLoanAccount;
use app\modules\finance\models\FinanceLoanExpenseType;
use app\modules\finance\models\FinanceLoanItemKind;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $tab */
/** @var array $tabs */
/** @var yii\db\ActiveRecord[] $models */
/** @var array<int,int> $usage */

$this->title = 'ตั้งค่าเงินยืม';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเงินยืม', 'url' => ['/finance/loan/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2"><i class="bi bi-gear fs-4" aria-hidden="true"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ประเภทค่าใช้จ่าย รายการในใบประมาณการ และบัญชีที่ใช้ในใบยืมเงิน<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'loan']); $this->endBlock();

$usageLabel = $tab === 'kind' ? 'บรรทัด' : 'ใบ';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <nav class="d-flex flex-wrap gap-2" aria-label="หมวดการตั้งค่าเงินยืม">
        <?php foreach ($tabs as $key => [, $label, $icon]): ?>
            <a href="<?= Url::to(['index', 'tab' => $key]) ?>"
               class="btn rounded-pill <?= $tab === $key ? 'btn-primary' : 'btn-outline-secondary' ?>"
               <?= $tab === $key ? 'aria-current="page"' : '' ?>>
                <i class="bi <?= Html::encode($icon) ?> me-1" aria-hidden="true"></i><?= Html::encode($label) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="d-flex flex-wrap gap-2">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับทะเบียนเงินยืม', ['/finance/loan/index'], ['class' => 'btn rounded-pill btn-outline-secondary']) ?>
        <?= Html::a('<i class="bi bi-plus-circle me-1"></i> เพิ่ม' . Html::encode($tabs[$tab][1]), ['create', 'tab' => $tab], ['class' => 'btn rounded-pill btn-primary']) ?>
    </div>
</div>

<section class="card border" aria-labelledby="loan-setting-heading">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
        <h5 class="mb-0" id="loan-setting-heading"><?= Html::encode($tabs[$tab][1]) ?></h5>
        <span class="text-body-secondary small"><?= count($models) ?> รายการ</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th class="text-end" style="width:4rem">ลำดับ</th>
                <?php if ($tab === 'type'): ?>
                    <th>ประเภทค่าใช้จ่าย</th>
                    <th>ส่งใช้ภายใน</th>
                    <th>แบบใบประมาณการ</th>
                <?php elseif ($tab === 'kind'): ?>
                    <th>ชื่อรายการ</th>
                    <th>ช่องในทะเบียนคุม</th>
                    <th>ช่องกรอก</th>
                <?php else: ?>
                    <th>เลขที่บัญชี</th>
                    <th>ชื่อบัญชี</th>
                    <th>ธนาคาร</th>
                <?php endif; ?>
                <th class="text-end">ใช้อยู่</th>
                <th>สถานะ</th>
                <th class="text-end" style="width:1%">จัดการ</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$models): ?>
                <tr><td colspan="7" class="text-center text-body-secondary py-4">ยังไม่มีข้อมูล</td></tr>
            <?php endif; ?>
            <?php foreach ($models as $m): ?>
                <tr class="<?= $m->is_active ? '' : 'text-body-secondary' ?>">
                    <td class="text-end font-monospace"><?= (int) $m->sort_order ?></td>
                    <?php if ($m instanceof FinanceLoanExpenseType): ?>
                        <td>
                            <div class="fw-semibold"><?= Html::encode($m->name) ?></div>
                            <div class="small text-body-secondary font-monospace"><?= Html::encode($m->code) ?></div>
                        </td>
                        <td><?= (int) $m->due_days ?> วัน <span class="small text-body-secondary">นับจาก<?= Html::encode($m->basisLabel()) ?></span></td>
                        <td><?= Html::encode(FinanceLoanExpenseType::formOptions()[$m->estimate_form] ?? $m->estimate_form) ?></td>
                    <?php elseif ($m instanceof FinanceLoanItemKind): ?>
                        <td>
                            <div class="fw-semibold"><?= Html::encode($m->name) ?></div>
                            <div class="small text-body-secondary font-monospace"><?= Html::encode($m->code) ?></div>
                        </td>
                        <td><?= Html::encode($m->registerColumnLabel()) ?></td>
                        <td class="small">
                            <?php
                            $parts = [];
                            if ($m->has_persons) { $parts[] = 'จำนวน' . ($m->person_unit_name ?: 'คน'); }
                            if ($m->has_units) { $parts[] = 'จำนวน' . ($m->unit_name ?: 'หน่วย'); }
                            $parts[] = 'อัตรา';
                            echo Html::encode($m->has_persons || $m->has_units ? implode(' × ', $parts) : 'กรอกยอดเงินอย่างเดียว');
                            ?>
                        </td>
                    <?php elseif ($m instanceof FinanceLoanAccount): ?>
                        <td class="font-monospace fw-semibold"><?= Html::encode($m->account_no) ?></td>
                        <td><?= Html::encode($m->name) ?></td>
                        <td><?= Html::encode($m->bank_name ?: '—') ?></td>
                    <?php endif; ?>
                    <td class="text-end text-nowrap"><?= number_format($usage[$m->id] ?? 0) ?> <span class="small text-body-secondary"><?= $usageLabel ?></span></td>
                    <td>
                        <?php if ($m->is_active): ?>
                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis">ใช้งาน</span>
                        <?php else: ?>
                            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">ปิดใช้งาน</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'tab' => $tab, 'id' => $m->id], [
                            'class' => 'btn btn-sm btn-outline-primary', 'title' => 'แก้ไข', 'aria-label' => 'แก้ไข',
                        ]) ?>
                        <?= Html::a($m->is_active ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>', ['toggle', 'tab' => $tab, 'id' => $m->id], [
                            'class' => 'btn btn-sm ' . ($m->is_active ? 'btn-outline-secondary' : 'btn-outline-success'),
                            'title' => $m->is_active ? 'ปิดการใช้งาน' : 'เปิดใช้งาน',
                            'aria-label' => $m->is_active ? 'ปิดการใช้งาน' : 'เปิดใช้งาน',
                            'data-method' => 'post',
                            'data-confirm' => $m->is_active
                                ? 'ปิดการใช้งานรายการนี้? ใบยืมเดิมยังแสดงชื่อเดิม แต่จะเลือกในใบยืมใหม่ไม่ได้'
                                : null,
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-body small text-body-secondary">
        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
        ลบรายการไม่ได้ เพื่อให้ใบยืมเดิมยังอ้างถึงได้ — รายการที่เลิกใช้ให้กดปิดการใช้งาน
        <?php if ($tab === 'type'): ?>
            · แก้กติกาวันส่งใช้แล้ว ใบยืมที่บันทึกไว้ก่อนหน้ายังใช้กติกาเดิม
        <?php endif; ?>
    </div>
</section>
