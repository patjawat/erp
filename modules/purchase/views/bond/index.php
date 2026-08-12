<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\modules\purchase\models\Bond;
use app\modules\purchase\models\BondPolicy;
use app\modules\purchase\components\BondCalculator;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\BondSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $counters */
/** @var array $missing */

$this->title = 'ทะเบียนหลักประกัน';
$this->params['breadcrumbs'][] = ['label' => 'งานพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;

$year = $searchModel->thai_year ?: (int) AppHelper::YearBudget();

$kpi = function (string $icon, string $color, $value, string $label) {
    return '<div class="col-6 col-lg-3">'
        . '<div class="card h-100"><div class="card-body d-flex align-items-center gap-3 py-3">'
        . '<div class="rounded-3 d-flex align-items-center justify-content-center text-bg-' . $color . '"'
        . ' style="width:42px;height:42px"><i class="bi ' . $icon . ' fs-5"></i></div>'
        . '<div><div class="fs-4 fw-semibold lh-1">' . $value . '</div>'
        . '<div class="small text-muted">' . $label . '</div></div>'
        . '</div></div></div>';
};
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-shield-check"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'guarantee']) ?>
<?php $this->endBlock(); ?>

<?php if (BondPolicy::needsReview()): ?>
    <div class="alert alert-warning d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <div class="fw-medium">
                <i class="bi bi-exclamation-triangle me-1"></i>
                เกณฑ์หลักประกันที่ระบบใช้อยู่ยังเป็นค่าตั้งต้น ยังไม่ผ่านการยืนยันจากงานพัสดุ
            </div>
            <div class="small">
                ตัวเลขวงเงินและอัตราที่ใช้แนะนำในหน้าบันทึกมาจากตารางเกณฑ์
                โปรดเทียบกับระเบียบและหนังสือเวียนฉบับที่ใช้อยู่ก่อนนำไปอ้างอิงในเอกสารจริง
            </div>
        </div>
        <?= Html::a('ไปหน้าตั้งค่าเกณฑ์', ['/purchase/bond-policy'], [
            'class' => 'btn btn-sm btn-warning rounded-pill px-3',
        ]) ?>
    </div>
<?php endif; ?>

<div class="row g-3 mb-3">
    <?= $kpi('bi-shield-check', 'primary', number_format($counters['total']), 'หลักประกันในทะเบียน (ปี ' . $year . ')') ?>
    <?= $kpi('bi-clock-history', 'warning', number_format($counters['near']), 'ใกล้สิ้นอายุใน ' . BondCalculator::NEAR_DAYS . ' วัน') ?>
    <?= $kpi('bi-exclamation-octagon', 'danger', number_format($counters['expired']), 'สิ้นอายุแล้วแต่ยังไม่ปิดเรื่อง') ?>
    <?= $kpi('bi-cash-stack', 'success', number_format((float) $counters['amount'], 2), 'ยอดที่ยังอยู่ในความดูแล (บาท)') ?>
</div>

<?php if ($counters['expired'] || $counters['near'] || $counters['pending']): ?>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php if ($counters['expired']): ?>
            <?= Html::a(
                '<i class="bi bi-exclamation-octagon me-1"></i>สิ้นอายุแล้ว ' . $counters['expired'] . ' ฉบับ',
                ['index', 'BondSearch[thai_year]' => $year, 'BondSearch[flag]' => 'expired'],
                ['class' => 'btn btn-sm btn-danger rounded-pill px-3']
            ) ?>
        <?php endif; ?>
        <?php if ($counters['near']): ?>
            <?= Html::a(
                '<i class="bi bi-clock-history me-1"></i>ใกล้สิ้นอายุ ' . $counters['near'] . ' ฉบับ',
                ['index', 'BondSearch[thai_year]' => $year, 'BondSearch[flag]' => 'near'],
                ['class' => 'btn btn-sm btn-warning rounded-pill px-3']
            ) ?>
        <?php endif; ?>
        <?php if ($counters['pending']): ?>
            <?= Html::a(
                '<i class="bi bi-hourglass-split me-1"></i>ยังไม่วาง ' . $counters['pending'] . ' ฉบับ',
                ['index', 'BondSearch[thai_year]' => $year, 'BondSearch[flag]' => 'pending'],
                ['class' => 'btn btn-sm btn-outline-warning rounded-pill px-3']
            ) ?>
        <?php endif; ?>
        <?php if ($searchModel->flag): ?>
            <?= Html::a('<i class="bi bi-x-lg me-1"></i>ล้างตัวกรอง', ['index'], [
                'class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3',
            ]) ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($missing): ?>
    <div class="card border-warning mb-3">
        <div class="card-header bg-warning-subtle">
            <h6 class="mb-0 text-warning-emphasis">
                <i class="bi bi-exclamation-triangle me-1"></i>
                สัญญาที่เข้าเกณฑ์ต้องวางหลักประกัน แต่ยังไม่มีหลักประกันในทะเบียน
                <span class="badge text-bg-warning"><?= count($missing) ?></span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:42px" class="text-center">#</th>
                            <th style="min-width:120px">เลขที่สัญญา</th>
                            <th style="min-width:220px">ชื่อสัญญา</th>
                            <th style="min-width:120px" class="text-end">วงเงิน</th>
                            <th style="min-width:150px" class="text-end">ต้องวางตามเกณฑ์</th>
                            <th style="width:120px" class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($missing as $i => $row): ?>
                            <?php $contract = $row['contract']; ?>
                            <tr>
                                <td class="text-center text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <span class="badge text-bg-light border">
                                        <?= Html::encode($contract->contract_no ?: ($contract->doc_no ?: '—')) ?>
                                    </span>
                                </td>
                                <td class="small">
                                    <?= Html::a(Html::encode($contract->title), ['/purchase/contract/view', 'id' => $contract->id], [
                                        'class' => 'text-decoration-none',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                </td>
                                <td class="text-end"><?= number_format((float) $contract->budget, 2) ?></td>
                                <td class="text-end">
                                    <span class="fw-semibold text-danger">
                                        <?= number_format((float) $row['policy']['amount'], 2) ?>
                                    </span>
                                    <div class="small text-muted">
                                        <?= rtrim(rtrim(number_format((float) $row['policy']['rate'], 2), '0'), '.') ?>% ของวงเงิน
                                    </div>
                                </td>
                                <td class="text-end">
                                    <?= Html::a('<i class="bi bi-plus-circle me-1"></i>บันทึก', [
                                        'create',
                                        'contract_id' => $contract->id,
                                    ], [
                                        'class' => 'btn btn-sm btn-warning rounded-pill px-3',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>
</div>

<?php Pjax::begin(['id' => 'bond-container', 'enablePushState' => false]); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0">
            <i class="bi bi-journal-text"></i> รายการหลักประกัน
            <span class="badge text-bg-secondary"><?= number_format($dataProvider->getTotalCount()) ?></span> ฉบับ
        </h6>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-file-earmark-word me-1"></i>ทะเบียนคุม', ['register', 'year' => $year], [
                'class' => 'btn btn-outline-secondary btn-sm rounded-pill px-3',
                'title' => 'ส่งออกทะเบียนคุมหลักประกันทั้งปีเป็นไฟล์ Word',
                'target' => '_blank',
                'data' => ['pjax' => 0],
            ]) ?>
            <?= Html::a('<i class="bi bi-plus-circle me-1"></i>บันทึกหลักประกัน', ['create'], [
                'class' => 'btn btn-success btn-sm rounded-pill px-3',
                'data' => ['pjax' => 0],
            ]) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:42px" class="text-center">#</th>
                        <th style="min-width:130px">เลขที่ / แหล่ง</th>
                        <th style="min-width:230px">รายการ / ผู้วางหลักประกัน</th>
                        <th style="min-width:150px">ประเภท / รูปแบบ</th>
                        <th style="min-width:120px" class="text-end">วงเงิน</th>
                        <th style="min-width:110px">วางเมื่อ</th>
                        <th style="min-width:130px">สิ้นอายุ</th>
                        <th style="min-width:110px">สถานะ</th>
                        <th style="width:150px" class="text-end">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php $offset = $dataProvider->pagination ? $dataProvider->pagination->offset : 0; ?>
                    <?php foreach ($dataProvider->getModels() as $i => $item): ?>
                        <?php
                        $badge = Bond::statusBadge($item->status);
                        $state = $item->expiryState();
                        $days = $item->daysToExpiry();
                        $rowClass = $state === BondCalculator::STATE_EXPIRED
                            ? 'table-danger'
                            : ($state === BondCalculator::STATE_NEAR ? 'table-warning' : '');
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="text-center text-muted"><?= $offset + $i + 1 ?></td>
                            <td>
                                <span class="badge text-bg-light border"><?= Html::encode($item->doc_no ?: '—') ?></span>
                                <?php if ($item->source_type !== Bond::SOURCE_NONE): ?>
                                    <div class="small text-muted">
                                        <i class="bi bi-link-45deg"></i>
                                        <?php if ($url = $item->sourceUrl()): ?>
                                            <?= Html::a(Html::encode($item->sourceLabel()), $url, [
                                                'class' => 'text-decoration-none',
                                                'data' => ['pjax' => 0],
                                            ]) ?>
                                        <?php else: ?>
                                            <?= Html::encode($item->sourceLabel()) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold small"><?= Html::encode($item->title) ?></div>
                                <div class="small text-muted"><?= Html::encode($item->partyName()) ?></div>
                            </td>
                            <td class="small">
                                <?= Html::encode($item->typeName()) ?>
                                <div class="text-muted">
                                    <?= Html::encode($item->bondFormName()) ?>
                                    <?php if ($item->doc_ref): ?>
                                        · <?= Html::encode($item->doc_ref) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <?php if ($item->status === Bond::STATUS_EXEMPT): ?>
                                    <span class="text-muted">ยกเว้น</span>
                                <?php else: ?>
                                    <span class="fw-semibold"><?= number_format((float) $item->amount, 2) ?></span>
                                    <?php if ($item->rate && $item->base_amount): ?>
                                        <div class="small text-muted">
                                            <?= rtrim(rtrim(number_format((float) $item->rate, 2), '0'), '.') ?>%
                                            ของ <?= number_format((float) $item->base_amount, 2) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= $item->place_date ? AppHelper::convertToThai($item->place_date) : '—' ?></td>
                            <td class="small">
                                <?= $item->expiry_date ? AppHelper::convertToThai($item->expiry_date) : '—' ?>
                                <?php if ($state === BondCalculator::STATE_EXPIRED): ?>
                                    <div class="text-danger fw-semibold">สิ้นอายุแล้ว <?= abs((int) $days) ?> วัน</div>
                                <?php elseif ($state === BondCalculator::STATE_NEAR): ?>
                                    <div class="text-warning-emphasis">เหลือ <?= (int) $days ?> วัน</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $badge['color'] ?>"><?= $badge['label'] ?></span>
                                <?php if ($item->return_date): ?>
                                    <div class="small text-muted"><?= AppHelper::convertToThai($item->return_date) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'แก้ไข',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                    <?php if (!in_array($item->status, [Bond::STATUS_RETURNED, Bond::STATUS_SEIZED, Bond::STATUS_EXEMPT], true)): ?>
                                        <?= Html::a('<i class="bi bi-box-arrow-up"></i>', ['return', 'id' => $item->id], [
                                            'class' => 'btn btn-sm btn-outline-success',
                                            'title' => 'บันทึกการคืน/การยึด',
                                            'data' => ['pjax' => 0],
                                        ]) ?>
                                    <?php endif; ?>
                                    <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'title' => 'ลบ',
                                        'data' => [
                                            'confirm' => 'ยืนยันการลบหลักประกัน "' . $item->title . '" ?',
                                            'method' => 'post',
                                            'pjax' => 0,
                                        ],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$dataProvider->getTotalCount()): ?>
                        <tr>
                            <td colspan="9">
                                <div class="text-center py-5">
                                    <div class="fw-semibold mb-1">ยังไม่มีหลักประกันที่ตรงกับเงื่อนไข</div>
                                    <div class="text-muted small mb-3">
                                        บันทึกได้จากหน้ารายละเอียดสัญญา หรือกดปุ่มด้านล่างเพื่อบันทึกใบที่ไม่ได้ผูกกับสัญญาในระบบ
                                        เช่น หลักประกันซองที่วางไว้ตั้งแต่ยื่นข้อเสนอ
                                    </div>
                                    <?= Html::a('บันทึกหลักประกัน', ['create'], [
                                        'class' => 'btn btn-success rounded-pill px-4',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
            <div class="d-flex justify-content-center py-3">
                <?= yii\bootstrap5\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination pagination-sm mb-0'],
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php Pjax::end(); ?>
