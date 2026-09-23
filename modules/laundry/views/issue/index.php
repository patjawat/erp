<?php

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $date */
/** @var array $rows */
/** @var array $staffNames  user_id => name */
/** @var \app\modules\laundry\models\LaundryUnit[] $units */
/** @var array $names        tree_id => name */
/** @var array $issuedToday  tree_id => [times, total] */
/** @var array $lastCount    tree_id => counted_at */
/** @var array $parUnits     tree_id => (มียอดตั้งต้นแล้ว) */
$this->title = 'ส่งผ้า / เบิกจ่าย';
$canManage = Yii::$app->user->can('laundry.manage');
$tones = ['info', 'primary', 'success', 'warning', 'danger', 'secondary'];
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'issue']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-box-arrow-right me-2"></i>ส่งผ้า / เบิกจ่าย <span class="text-body-secondary fs-6 fw-normal">ประจำวันที่ <?= Html::encode(ThaiDateHelper::formatThaiDate($date)) ?></span></h1>
        <div class="d-flex align-items-center gap-2">
            <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-1']) ?>
                <?= DatepickerThai::widget(['name' => 'date', 'value' => AppHelper::convertToThai($date), 'options' => ['class' => 'form-control form-control-sm', 'style' => 'max-width:140px', 'autocomplete' => 'off', 'onchange' => 'this.form.submit()']]) ?>
            <?= Html::endForm() ?>
            <?php if ($canManage): ?>
                <?= Html::a('<i class="bi bi-plus-lg me-1"></i>อ้างอิงผลตรวจนับ', ['create', 'date' => AppHelper::convertToThai($date)], ['class' => 'btn btn-outline-primary']) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php foreach (['error' => 'danger', 'success' => 'success'] as $k => $c): ?>
        <?php if (Yii::$app->session->hasFlash($k)): ?><div class="alert alert-<?= $c ?> d-flex align-items-center"><i class="bi bi-<?= $c === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($k)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <?php if (!$units): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body text-center text-body-secondary py-5">
            <i class="bi bi-diagram-3 fs-1 d-block mb-3"></i>ยังไม่มีหน่วยงาน — เพิ่มที่ <?= Html::a('ตั้งค่า → หน่วยงาน', ['/laundry/setting/unit'], ['class' => 'fw-semibold']) ?>
        </div></div>
    <?php else: ?>
        <div class="text-body-secondary small mb-2"><i class="bi bi-info-circle me-1"></i>แตะการ์ดหน่วยงานเพื่อจัดผ้าจ่าย (ระบบดึงผลตรวจนับล่าสุดมาคิดส่วนขาดให้)</div>
        <div class="row g-4 g-xl-5 mb-4">
            <?php foreach ($units as $i => $u): $s = $issuedToday[$u->tree_id] ?? null; $tone = $tones[$i % count($tones)]; $lc = $lastCount[$u->tree_id] ?? null; ?>
                <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                    <?php
                    $attrs = [
                        'class' => 'card border-0 shadow-sm rounded-4 h-100 w-100 text-decoration-none text-body',
                        'style' => 'background:var(--bs-' . $tone . '-bg-subtle,#f8f9fa);border-top:4px solid var(--bs-' . $tone . ',#0d6efd) !important',
                    ];
                    $tag = $canManage ? 'a' : 'div';
                    if ($canManage) {
                        $attrs['href'] = Url::to(['create', 'tree_id' => $u->tree_id, 'date' => AppHelper::convertToThai($date)]);
                    }
                    ?>
                    <<?= $tag ?> <?= Html::renderTagAttributes($attrs) ?>>
                        <div class="card-body py-3 text-center">
                            <div class="fw-bold lh-1 mb-1" style="font-size:1.9rem;color:var(--bs-<?= $tone ?>)"><?= Html::encode($u->abbr ?: mb_substr($names[$u->tree_id] ?? '?', 0, 4)) ?></div>
                            <div class="small text-body-secondary text-truncate mb-2" title="<?= Html::encode($names[$u->tree_id] ?? '') ?>"><?= Html::encode($names[$u->tree_id] ?? ('#' . $u->tree_id)) ?></div>
                            <div class="small text-start">
                                <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                    <span>ส่งผ้า</span><span class="fw-semibold"><?= (int) ($s['times'] ?? 0) ?> ครั้ง</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-body-secondary">รวมจ่าย</span><span><span class="fw-semibold"><?= number_format((int) ($s['total'] ?? 0)) ?></span> ชิ้น</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-body-secondary">นับล่าสุด</span><span><?= $lc ? Html::encode(ThaiDateHelper::formatThaiDate($lc)) : '—' ?></span>
                                </div>
                            </div>
                            <?php if (!isset($parUnits[$u->tree_id])): ?>
                                <div class="small text-warning-emphasis mt-2"><i class="bi bi-exclamation-circle me-1"></i>ยังไม่ตั้งยอดตั้งต้น</div>
                            <?php endif; ?>
                        </div>
                    </<?= $tag ?>>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h2 class="h6 fw-semibold mb-2"><i class="bi bi-list-ul me-2"></i>รายการส่งผ้าวันนี้ (<?= count($rows) ?>)</h2>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr>
                    <th class="ps-4">เลขที่</th><th>เวลา</th><th>หน่วยงาน</th><th>ผู้จ่าย</th>
                    <th class="text-end">ประเภท</th><th class="text-end pe-4">รวมจ่าย (ชิ้น)</th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= Html::encode($r['issue_no'] ?: '#' . $r['id']) ?></td>
                        <td><?= date('H:i', strtotime($r['issued_at'])) ?></td>
                        <td><?= Html::encode($r['unit_name'] ?: '#' . $r['tree_id']) ?></td>
                        <td class="text-body-secondary small"><?= Html::encode($staffNames[$r['created_by']] ?? '') ?></td>
                        <td class="text-end"><?= (int) $r['types'] ?></td>
                        <td class="text-end pe-4 fw-semibold"><?= number_format((int) $r['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="text-center text-body-secondary py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>ยังไม่มีการส่งผ้าในวันนี้</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>
