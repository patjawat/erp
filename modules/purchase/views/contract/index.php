<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\modules\purchase\models\Contract;
use app\modules\purchase\components\ContractCalculator;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\ContractSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $counters */

$this->title = 'บริหารสัญญา';
$this->params['breadcrumbs'][] = ['label' => 'งานพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;

$year = $searchModel->thai_year ?: (int) AppHelper::YearBudget();
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-file-earmark-check"></i> <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'contract']) ?>
<?php $this->endBlock(); ?>

<?php if ($counters['overdue'] || $counters['duesoon']): ?>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php if ($counters['overdue']): ?>
            <?= Html::a(
                '<i class="bi bi-exclamation-octagon me-1"></i>เลยกำหนดส่งมอบ '
                    . $counters['overdue'] . ' ฉบับ',
                ['index', 'ContractSearch[thai_year]' => $year, 'ContractSearch[flag]' => 'overdue'],
                ['class' => 'btn btn-sm btn-danger rounded-pill px-3']
            ) ?>
        <?php endif; ?>
        <?php if ($counters['duesoon']): ?>
            <?= Html::a(
                '<i class="bi bi-clock-history me-1"></i>ครบกำหนดใน 7 วัน '
                    . $counters['duesoon'] . ' ฉบับ',
                ['index', 'ContractSearch[thai_year]' => $year, 'ContractSearch[flag]' => 'duesoon'],
                ['class' => 'btn btn-sm btn-warning rounded-pill px-3']
            ) ?>
        <?php endif; ?>
        <?php if ($searchModel->flag): ?>
            <?= Html::a('<i class="bi bi-x-lg me-1"></i>ล้างตัวกรอง', ['index'], [
                'class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3',
            ]) ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>
</div>

<?php Pjax::begin(['id' => 'contract-container', 'enablePushState' => false]); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0">
            <i class="bi bi-journal-text"></i> ทะเบียนสัญญา
            <span class="badge text-bg-secondary"><?= number_format($dataProvider->getTotalCount()) ?></span> ฉบับ
        </h6>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-file-earmark-word me-1"></i>ทะเบียนคุม', ['register', 'year' => $year], [
                'class' => 'btn btn-outline-secondary btn-sm rounded-pill px-3',
                'title' => 'ส่งออกทะเบียนคุมสัญญาทั้งปีเป็นไฟล์ Word',
                'target' => '_blank',
                'data' => ['pjax' => 0],
            ]) ?>
            <?= Html::a('<i class="bi bi-plus-circle me-1"></i>บันทึกสัญญาใหม่', ['create'], [
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
                        <th style="min-width:130px">เลขที่</th>
                        <th style="min-width:240px">ชื่อสัญญา/รายการ</th>
                        <th style="min-width:110px">ประเภท</th>
                        <th style="min-width:170px">คู่สัญญา</th>
                        <th style="min-width:120px" class="text-end">วงเงิน</th>
                        <th style="min-width:120px">ครบกำหนด</th>
                        <th style="min-width:110px" class="text-end">ค่าปรับ</th>
                        <th style="min-width:110px">สถานะ</th>
                        <th style="width:150px" class="text-end">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php $offset = $dataProvider->pagination ? $dataProvider->pagination->offset : 0; ?>
                    <?php foreach ($dataProvider->getModels() as $i => $item): ?>
                        <?php
                        $badge = Contract::statusBadge($item->status);
                        $overdue = $item->isOverdue();
                        $daysLeft = $item->daysToDue();
                        $ratio = $item->fineRatio();
                        ?>
                        <tr>
                            <td class="text-center text-muted"><?= $offset + $i + 1 ?></td>
                            <td>
                                <span class="badge text-bg-light border"><?= Html::encode($item->contract_no ?: ($item->doc_no ?: '—')) ?></span>
                                <?php if ($item->order_id): ?>
                                    <div class="small text-muted" title="ผูกกับใบสั่งซื้อในระบบ">
                                        <i class="bi bi-link-45deg"></i> ใบสั่งซื้อ
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= Html::a(Html::encode($item->title), ['view', 'id' => $item->id], [
                                    'class' => 'fw-semibold text-decoration-none',
                                    'data' => ['pjax' => 0],
                                ]) ?>
                                <?php if ($item->egp_no): ?>
                                    <div class="small text-muted">e-GP <?= Html::encode($item->egp_no) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= Html::encode($item->typeName()) ?></td>
                            <td class="small"><?= Html::encode($item->partyName()) ?></td>
                            <td class="text-end"><?= number_format((float) $item->budget, 2) ?></td>
                            <td class="small">
                                <?= $item->end_date ? AppHelper::convertToThai($item->end_date) : '—' ?>
                                <?php if ($overdue): ?>
                                    <div class="text-danger fw-semibold">เลยกำหนด <?= abs((int) $daysLeft) ?> วัน</div>
                                <?php elseif ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7 && !$item->closingDate()): ?>
                                    <div class="text-warning-emphasis">เหลือ <?= $daysLeft ?> วัน</div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ((float) $item->fine_amount > 0): ?>
                                    <span class="fw-semibold text-danger"><?= number_format((float) $item->fine_amount, 2) ?></span>
                                    <?php if ($ratio >= ContractCalculator::WARN_RATIO): ?>
                                        <div class="small text-danger" title="ค่าปรับถึงเกณฑ์ที่ต้องพิจารณาบอกเลิกสัญญา">
                                            <?= number_format($ratio, 1) ?>% ของวงเงิน
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge text-bg-<?= $badge['color'] ?>"><?= $badge['label'] ?></span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?= Html::a('<i class="bi bi-search"></i>', ['view', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'title' => 'ดูรายละเอียด',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                    <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'แก้ไข',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                    <?= Html::a('<i class="bi bi-file-earmark-word"></i>', ['word', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'ร่างสัญญา (Word)',
                                        'target' => '_blank',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$dataProvider->getTotalCount()): ?>
                        <tr>
                            <td colspan="10">
                                <div class="text-center py-5">
                                    <div class="fw-semibold mb-1">ยังไม่มีสัญญาที่ตรงกับเงื่อนไข</div>
                                    <div class="text-muted small mb-3">
                                        บันทึกสัญญาใหม่ได้เลย หรือเลือกจากใบสั่งซื้อที่ออกไว้แล้วเพื่อให้ระบบเติมข้อมูลให้
                                    </div>
                                    <?= Html::a('บันทึกสัญญาใหม่', ['create'], [
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
