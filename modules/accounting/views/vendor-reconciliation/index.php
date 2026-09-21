<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\accounting\services\PurchaseVendorReconciliationService as Reconciliation;

$this->title = 'ตรวจผู้ขายจากพัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบัญชี', 'url' => ['/accounting/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-person-check" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ตรวจรหัสผู้ขายในใบสั่งซื้อปีงบประมาณ <?= Html::encode($year) ?> ก่อนนำเข้าทะเบียนเจ้าหนี้<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('@app/modules/accounting/menu', ['active' => 'vendor-reconciliation']) ?><?php $this->endBlock(); ?>

<div class="alert alert-info" role="status">
    <strong>รายงานตรวจสอบเท่านั้น</strong> — ไม่เปลี่ยนผู้ขาย ใบสั่งซื้อ หรือยอดเจ้าหนี้ รายการที่รหัสไม่ตรงต้องให้เจ้าหน้าที่ตรวจหลักฐานก่อนจับคู่
</div>

<form method="get" class="d-flex flex-wrap align-items-end gap-2 mb-3" aria-label="เลือกปีงบประมาณ">
    <div><label for="reconciliation-year" class="form-label">ปีงบประมาณ</label>
        <input id="reconciliation-year" type="number" name="year" min="2500" max="2700" value="<?= Html::encode($year) ?>" class="form-control" required></div>
    <button type="submit" class="btn btn-outline-primary">แสดงรายงาน</button>
</form>

<nav class="d-flex flex-wrap gap-2 mb-3" aria-label="กรองผลการจับคู่">
    <?php foreach (['' => ['ทั้งหมด', $report['total']], Reconciliation::MISSING => ['ไม่พบรหัส', $report['counts'][Reconciliation::MISSING]], Reconciliation::REVIEW => ['ต้องตรวจเพิ่ม', $report['counts'][Reconciliation::REVIEW]], Reconciliation::MATCHED => ['รหัสตรง', $report['counts'][Reconciliation::MATCHED]]] as $key => [$label, $count]): ?>
        <?= Html::a(Html::encode($label) . ' ' . number_format($count), ['index', 'year' => $year, 'status' => $key], ['class' => 'btn ' . ($status === $key ? 'btn-primary' : 'btn-outline-primary'), 'aria-current' => $status === $key ? 'page' : null]) ?>
    <?php endforeach; ?>
</nav>

<section class="card border" aria-labelledby="vendor-report-heading">
    <div class="card-header bg-body d-flex flex-wrap justify-content-between gap-2"><h5 class="mb-0" id="vendor-report-heading">รหัสผู้ขายที่ใช้ในใบสั่งซื้อ</h5><span class="small text-body-secondary">นับเป็นจำนวนใบสั่งซื้อ ไม่ใช่จำนวนผู้ขาย</span></div>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class=\"card-footer bg-body d-flex flex-wrap justify-content-between gap-2\">{summary}{pager}</div>",
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'columns' => [
                ['label' => 'รหัสในพัสดุ', 'format' => 'raw', 'value' => static fn(array $row) => Html::tag('span', Html::encode($row['source_code'] ?: 'ไม่ระบุ'), ['class' => 'font-monospace'])],
                ['label' => 'จำนวนใบสั่งซื้อ', 'value' => static fn(array $row) => number_format($row['order_count']), 'contentOptions' => ['class' => 'text-end text-nowrap'], 'headerOptions' => ['class' => 'text-end']],
                ['label' => 'ผลตรวจ', 'format' => 'raw', 'value' => static function (array $row) {
                    $labels = [Reconciliation::MATCHED => ['รหัสตรง', 'bg-success-subtle text-success-emphasis'], Reconciliation::REVIEW => ['ต้องตรวจเพิ่ม', 'bg-warning-subtle text-warning-emphasis'], Reconciliation::MISSING => ['ไม่พบรหัส', 'bg-danger-subtle text-danger-emphasis']];
                    [$label, $class] = $labels[$row['status']];
                    return Html::tag('span', $label, ['class' => 'badge ' . $class]) . Html::tag('div', Html::encode($row['reason']), ['class' => 'small text-body-secondary mt-1']);
                }],
                ['label' => 'ผู้ขายในทะเบียน', 'format' => 'raw', 'value' => static function (array $row) {
                    if (!$row['candidates']) return Html::tag('span', '—', ['class' => 'text-body-secondary']);
                    return implode('<br>', array_map(static fn($vendor) => Html::encode($vendor->code . ' · ' . $vendor->title) . (!$vendor->active ? ' <span class="small text-body-secondary">(ปิดใช้งาน)</span>' : ''), $row['candidates']));
                }],
                ['label' => 'ตรวจเอกสาร', 'format' => 'raw', 'value' => static fn(array $row) => Html::a('ดูใบสั่งซื้อล่าสุด', ['/purchase/order/view', 'id' => $row['last_order_id']], ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank', 'rel' => 'noopener'])],
            ],
            'emptyText' => 'ไม่พบใบสั่งซื้อในปีงบประมาณนี้ หรือไม่มีรายการตามตัวกรอง',
            'emptyTextOptions' => ['class' => 'text-center text-body-secondary py-5'],
        ]) ?>
    </div>
</section>
