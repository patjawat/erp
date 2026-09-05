<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentSearch $searchModel */
/** @var array $report ผลจาก DevelopmentReport::orgSummary() */
/** @var array $activityType ['series'=>[], 'labels'=>[]] */
/** @var int $year */

$this->title = 'รายงานภาพรวมการพัฒนาบุคลากร';
$this->params['breadcrumbs'][] = $this->title;

$fmt = static fn($n) => number_format((float) $n, 2);
$used = (float) $report['budget_used_percent'];
$usedColor = $used > 100 ? 'danger' : ($used >= 80 ? 'warning' : 'success');
$summary = $report['summary'];
$sumColor = $summary['percent'] >= 80 ? 'success' : ($summary['percent'] >= 40 ? 'warning' : 'danger');
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-bar-chart-line text-primary" aria-hidden="true"></i>
    <?= Html::encode($this->title) ?>
    <span class="badge bg-primary-subtle text-primary-emphasis">ปีงบ <?= $year ?></span>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('menu', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<!-- ตัวกรองปีงบประมาณ + ค้นหาสมุดพกรายบุคคล -->
<?php $empSearchResults = new JsExpression("function (data, params) { params.page = params.page || 1; return { results: data.results, pagination: { more: (params.page * 30) < data.total_count } }; }"); ?>
<div class="card border shadow-sm mb-3">
    <div class="card-body py-2 d-flex flex-wrap align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="text-body-secondary small"><i class="bi bi-funnel me-1"></i>ปีงบประมาณ</span>
            <?php $form = ActiveForm::begin(['action' => ['report'], 'method' => 'get', 'options' => ['class' => 'mb-0']]); ?>
            <?= $form->field($searchModel, 'thai_year', ['options' => ['class' => 'mb-0']])->widget(Select2::class, [
                'data' => $searchModel->groupYear(),
                'options' => ['placeholder' => 'ปีงบประมาณ'],
                'pluginOptions' => ['allowClear' => false, 'width' => '180px'],
                'pluginEvents' => ['select2:select' => 'function(){ $(this).closest("form").submit(); }'],
            ])->label(false); ?>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width:260px;max-width:420px;">
            <span class="text-body-secondary small text-nowrap"><i class="bi bi-person-vcard me-1"></i>สมุดพกรายคน</span>
            <?= Select2::widget([
                'name' => 'passport_emp',
                'options' => ['placeholder' => 'พิมพ์ชื่อบุคลากรเพื่อดูประวัติการพัฒนา...', 'id' => 'passport-search'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 1,
                    'width' => '100%',
                    'ajax' => [
                        'url' => Url::to(['/depdrop/employee-by-id']),
                        'dataType' => 'json',
                        'delay' => 250,
                        'data' => new JsExpression("function(params) { return { q: params.term || '', page: params.page }; }"),
                        'processResults' => $empSearchResults,
                        'cache' => true,
                    ],
                    'escapeMarkup' => new JsExpression('function (m) { return m; }'),
                    'templateSelection' => new JsExpression('function (item) { return item.fullname || item.text; }'),
                ],
            ]) ?>
            <?= Html::a('', '#', ['id' => 'passport-open', 'class' => 'open-modal d-none', 'data' => ['size' => 'modal-lg']]) ?>
        </div>
    </div>
</div>

<!-- KPI เชิงปริมาณ + คุณภาพ -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-primary">
            <div class="card-body">
                <p class="text-body-secondary small mb-1">กิจกรรมการพัฒนา</p>
                <h3 class="fw-bold mb-1"><?= number_format($report['activities']) ?> <small class="fs-6 fw-normal text-body-secondary">ครั้ง</small></h3>
                <span class="small <?= $report['activities_change_percent'] >= 0 ? 'text-success' : 'text-danger' ?>">
                    <i class="bi bi-caret-<?= $report['activities_change_percent'] >= 0 ? 'up' : 'down' ?>-fill"></i>
                    <?= abs($report['activities_change_percent']) ?>% จากปีก่อน
                </span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-success">
            <div class="card-body">
                <p class="text-body-secondary small mb-1">บุคลากรที่ได้รับการพัฒนา</p>
                <h3 class="fw-bold mb-1"><?= number_format($report['persons_developed']) ?> <small class="fs-6 fw-normal text-body-secondary">/ <?= number_format($report['active_staff']) ?> คน</small></h3>
                <span class="small text-success"><i class="bi bi-people-fill"></i> ครอบคลุม <?= $report['coverage_percent'] ?>%</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-info">
            <div class="card-body">
                <p class="text-body-secondary small mb-1">คน-ครั้ง (โอกาสพัฒนา)</p>
                <h3 class="fw-bold mb-1"><?= number_format($report['person_times']) ?></h3>
                <span class="small text-body-secondary"><i class="bi bi-person-plus"></i> ผู้ขอ + คณะเดินทาง</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-start border-4 border-<?= $sumColor ?>">
            <div class="card-body">
                <p class="text-body-secondary small mb-1">อัตราส่งสรุปผล (คุณภาพ)</p>
                <h3 class="fw-bold mb-1"><?= $summary['percent'] ?>%</h3>
                <span class="small text-body-secondary"><i class="bi bi-journal-check"></i> <?= number_format($summary['submitted'] + $summary['acknowledged']) ?> / <?= number_format($summary['total']) ?> ใบ</span>
            </div>
        </div>
    </div>
</div>

<!-- งบประมาณ แผน vs ผล -->
<div class="card border shadow-sm mb-3">
    <div class="card-header bg-body-tertiary fw-semibold">
        <i class="bi bi-cash-stack me-1 text-primary"></i>งบประมาณพัฒนาบุคลากร — แผน vs ผล
        <span class="text-body-secondary fw-normal small">(งบตั้งไว้จากแผนการเงิน · ใช้จริงจากใบไปราชการ)</span>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="text-body-secondary small">งบที่ตั้งไว้ (แผน)</div>
                <div class="fs-4 fw-bold text-primary"><?= $fmt($report['planned_budget']) ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-body-secondary small">ใช้จริง</div>
                <div class="fs-4 fw-bold"><?= $fmt($report['actual_spend']) ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-body-secondary small">คงเหลือ</div>
                <div class="fs-4 fw-bold text-<?= $report['budget_remaining'] < 0 ? 'danger' : 'success' ?>"><?= $fmt($report['budget_remaining']) ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-body-secondary small">ใช้ไปคิดเป็น</div>
                <div class="fs-4 fw-bold text-<?= $usedColor ?>"><?= $used ?>%</div>
            </div>
        </div>
        <div class="progress mb-1" style="height:10px;" role="progressbar">
            <div class="progress-bar bg-<?= $usedColor ?>" style="width: <?= min(100, $used) ?>%"></div>
        </div>
        <?php if ($used > 100): ?>
            <p class="small text-danger mb-0"><i class="bi bi-exclamation-triangle-fill"></i> ใช้จริงเกินงบที่ตั้งไว้ — ส่วนใหญ่เป็นค่าเดินทาง/ที่พัก/เบี้ยเลี้ยง ที่ผังงบไม่ได้ตั้งแยก (ดูตารางแยกหมวดด้านล่าง)</p>
        <?php endif; ?>

        <div class="row g-3 mt-1">
            <!-- แผน แยกตามรายการงบ -->
            <div class="col-12 col-lg-6">
                <h6 class="fw-semibold"><i class="bi bi-list-check me-1"></i>งบที่ตั้งไว้ — แยกตามรายการงบ</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light"><tr><th>รหัส</th><th>รายการงบ (แผนการเงิน)</th><th class="text-end">จำนวนเงิน</th></tr></thead>
                        <tbody>
                        <?php foreach ($report['planned_by_item'] as $r): ?>
                            <tr>
                                <td class="text-nowrap"><span class="badge bg-primary-subtle text-primary-emphasis"><?= Html::encode($r['code']) ?></span></td>
                                <td><?= Html::encode($r['title']) ?></td>
                                <td class="text-end"><?= $fmt($r['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot><tr class="fw-semibold table-light"><td colspan="2">รวม</td><td class="text-end"><?= $fmt($report['planned_budget']) ?></td></tr></tfoot>
                    </table>
                </div>
            </div>
            <!-- ใช้จริง แยกตามหมวด -->
            <div class="col-12 col-lg-6">
                <h6 class="fw-semibold"><i class="bi bi-pie-chart me-1"></i>ใช้จริง — แยกตามหมวดค่าใช้จ่าย</h6>
                <div class="row g-2 align-items-center">
                    <div class="col-7">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light"><tr><th>หมวด</th><th class="text-end">จำนวนเงิน</th></tr></thead>
                                <tbody>
                                <?php foreach ($report['actual_by_component'] as $r): ?>
                                    <tr><td><?= Html::encode($r['label']) ?></td><td class="text-end"><?= $fmt($r['amount']) ?></td></tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot><tr class="fw-semibold table-light"><td>รวม</td><td class="text-end"><?= $fmt($report['actual_spend']) ?></td></tr></tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="col-5"><div id="componentChart" style="height:220px;"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ รายหน่วยงาน — ความครอบคลุม & งบ (เฟส 2) ═══ -->
<?php
$covColor = static fn($c) => $c >= 60 ? 'success' : ($c >= 30 ? 'warning' : 'danger');
$zeroDepts = count(array_filter($byDepartment, static fn($r) => $r['developed'] === 0));
$deptSpendTotal = array_sum(array_map(static fn($r) => $r['actual_spend'], $byDepartment));
?>
<div class="card border shadow-sm mb-3">
    <div class="card-header bg-body-tertiary fw-semibold d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="bi bi-diagram-3 me-1 text-primary"></i>รายหน่วยงาน — ความครอบคลุมการพัฒนา &amp; งบ</span>
        <?php if ($zeroDepts > 0): ?>
            <span class="badge text-bg-danger-subtle text-danger-emphasis"><i class="bi bi-exclamation-triangle me-1"></i><?= $zeroDepts ?> หน่วยยังไม่มีการพัฒนาเลย</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <p class="small text-body-secondary mb-2">
            ความครอบคลุม = บุคลากรที่ได้รับการพัฒนา ÷ บุคลากรในหน่วย · งบใช้จริงผูกกับหน่วยของผู้ขอ (คลิก <i class="bi bi-people"></i> เพื่อดูรายคนและผู้ที่ยังไม่ได้รับการพัฒนา)
        </p>
        <div class="table-responsive" style="max-height:520px;overflow:auto;">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>หน่วยงาน</th>
                        <th class="text-center">บุคลากร</th>
                        <th class="text-center">พัฒนาแล้ว</th>
                        <th style="min-width:140px;">ครอบคลุม</th>
                        <th class="text-center">คน-ครั้ง</th>
                        <th class="text-end">งบใช้จริง</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($byDepartment as $r): $c = (float) $r['coverage_percent']; $cc = $covColor($c); ?>
                    <tr>
                        <td><?= Html::encode($r['name']) ?></td>
                        <td class="text-center"><?= number_format($r['staff']) ?></td>
                        <td class="text-center"><?= number_format($r['developed']) ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:7px;"><div class="progress-bar bg-<?= $cc ?>" style="width:<?= min(100, $c) ?>%"></div></div>
                                <span class="small text-<?= $cc ?> fw-semibold" style="width:3rem;text-align:right;"><?= $c ?>%</span>
                            </div>
                        </td>
                        <td class="text-center"><?= number_format($r['person_times']) ?></td>
                        <td class="text-end"><?= $fmt($r['actual_spend']) ?></td>
                        <td class="text-end">
                            <?= Html::a('<i class="bi bi-people"></i>', ['/hr/development/report-department', 'thai_year' => $year, 'department' => $r['dept_id'], 'title' => '<i class="bi bi-people me-1"></i> ' . Html::encode($r['name'])], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-semibold table-light">
                        <td colspan="5">รวมงบที่ผูกหน่วยงานได้</td>
                        <td class="text-end"><?= $fmt($deptSpendTotal) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php if ($deptSpendTotal < (float) $report['actual_spend']): ?>
            <p class="small text-body-secondary mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>ต่างจากงบใช้จริงรวม <?= $fmt((float) $report['actual_spend'] - $deptSpendTotal) ?> บาท เป็นรายการที่ผู้ขอไม่อยู่ในผังหน่วยงานปัจจุบัน</p>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ เชิงคุณภาพตามมาตรฐาน HA — การติดตามผล & การนำไปใช้ประโยชน์ ═══ -->
<?php
$thaiDate = static fn($d) => $d ? \app\components\ThaiDateHelper::formatThaiDate($d, 'long', 'short') : '-';
$openSummary = static function (array $r, string $btnLabel, string $btnClass) {
    return Html::a(
        $btnLabel,
        ['/hr/development/summary', 'id' => $r['id'], 'title' => '<i class="bi bi-journal-check"></i> สรุปผล: ' . Html::encode($r['topic'])],
        ['class' => $btnClass . ' open-modal', 'data' => ['size' => 'modal-lg']]
    );
};
$suggestions = array_values(array_filter($benefitRegister, static fn($r) => trim((string) $r['suggestion']) !== ''));
$fp = $followup;
$closureColor = $fp['percent'] >= 80 ? 'success' : ($fp['percent'] >= 40 ? 'warning' : 'danger');
?>
<div class="card border shadow-sm mb-3">
    <div class="card-header bg-body-tertiary fw-semibold">
        <i class="bi bi-patch-check me-1 text-primary"></i>เชิงคุณภาพ (HA) — การติดตามผล &amp; การนำไปใช้ประโยชน์
        <span class="text-body-secondary fw-normal small">หลักฐานว่าการพัฒนาถูกนำไปใช้จริงและปิด loop</span>
    </div>
    <div class="card-body">
        <!-- 1) การปิด loop สรุปผล -->
        <div class="row g-2 mb-2">
            <div class="col-6 col-md-3"><div class="border rounded p-2 text-center"><div class="fs-4 fw-bold text-success"><?= number_format($fp['acknowledged']) ?></div><div class="small text-body-secondary">รับทราบแล้ว</div></div></div>
            <div class="col-6 col-md-3"><div class="border rounded p-2 text-center"><div class="fs-4 fw-bold text-warning"><?= number_format($fp['submitted']) ?></div><div class="small text-body-secondary">ส่งแล้ว รอรับทราบ</div></div></div>
            <div class="col-6 col-md-3"><div class="border rounded p-2 text-center"><div class="fs-4 fw-bold text-secondary"><?= number_format($fp['draft']) ?></div><div class="small text-body-secondary">ฉบับร่าง</div></div></div>
            <div class="col-6 col-md-3"><div class="border rounded p-2 text-center"><div class="fs-4 fw-bold text-danger"><?= number_format($fp['none']) ?></div><div class="small text-body-secondary">ยังไม่สรุปผล</div></div></div>
        </div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <div class="progress flex-grow-1" style="height:10px;" role="progressbar">
                <div class="progress-bar bg-<?= $closureColor ?>" style="width: <?= min(100, $fp['percent']) ?>%"></div>
            </div>
            <span class="small fw-semibold text-<?= $closureColor ?>">ปิด loop <?= $fp['percent'] ?>%</span>
        </div>
        <?php if ($fp['percent'] < 80): ?>
            <p class="small text-body-secondary mb-0"><i class="bi bi-info-circle me-1"></i>การพัฒนาจะเกิดประโยชน์ตาม HA ต่อเมื่อมีการสรุปผลและนำไปใช้ — ยังมี <?= number_format($fp['none'] + $fp['draft']) ?> รายการที่ยังไม่ปิด loop (ดูรายการด้านล่างเพื่อติดตาม)</p>
        <?php endif; ?>

        <hr class="my-3">

        <!-- 2) คลังการนำไปใช้ประโยชน์ -->
        <h6 class="fw-semibold mb-2"><i class="bi bi-lightbulb me-1 text-warning"></i>คลังการนำไปใช้ประโยชน์ (<?= count($benefitRegister) ?> รายการที่รายงานผลแล้ว)</h6>
        <?php if (empty($benefitRegister)): ?>
            <div class="alert alert-light border small mb-0">ยังไม่มีการรายงานผลการนำไปใช้ประโยชน์ในปีงบนี้</div>
        <?php else: ?>
            <div class="table-responsive mb-2">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th>หัวข้อ / ผู้รายงาน</th><th style="min-width:220px;">การนำไปใช้ประโยชน์</th><th style="min-width:200px;">ข้อเสนอแนะ</th><th class="text-center">สถานะ</th><th></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($benefitRegister as $r): ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= Html::encode($r['topic']) ?></div>
                                <div class="small text-body-secondary"><i class="bi bi-person"></i> <?= Html::encode($r['requester'] ?: '-') ?><?php if ($r['dept']): ?> · <?= Html::encode($r['dept']) ?><?php endif; ?></div>
                                <div class="small text-body-secondary"><i class="bi bi-calendar3"></i> <?= $thaiDate($r['date_start']) ?></div>
                            </td>
                            <td class="small"><?= $r['benefit'] ? nl2br(Html::encode($r['benefit'])) : '<span class="text-body-secondary">—</span>' ?></td>
                            <td class="small"><?= $r['suggestion'] ? nl2br(Html::encode($r['suggestion'])) : '<span class="text-body-secondary">—</span>' ?></td>
                            <td class="text-center">
                                <?php if ($r['status'] === 'acknowledged'): ?>
                                    <span class="badge rounded-pill text-bg-success">รับทราบแล้ว</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill text-bg-warning">รอรับทราบ</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= $openSummary($r, '<i class="bi bi-eye"></i>', 'btn btn-sm btn-outline-secondary') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- 3) ข้อเสนอแนะเพื่อพัฒนา (digest) -->
        <?php if (!empty($suggestions)): ?>
            <h6 class="fw-semibold mt-3 mb-2"><i class="bi bi-chat-left-quote me-1 text-primary"></i>ข้อเสนอแนะเพื่อการพัฒนา (ป้อน CQI)</h6>
            <ul class="small mb-0">
                <?php foreach ($suggestions as $r): ?>
                    <li><?= Html::encode($r['suggestion']) ?> <span class="text-body-secondary">— <?= Html::encode($r['topic']) ?></span></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- 4) รายการที่ยังไม่ปิด loop (chase list) -->
        <?php if (!empty($pendingSummary)): ?>
            <hr class="my-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-semibold mb-0"><i class="bi bi-exclamation-circle me-1 text-danger"></i>รายการที่ยังไม่ปิด loop (<?= count($pendingSummary) ?>)</h6>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#pendingList">แสดง/ซ่อน</button>
            </div>
            <div class="collapse" id="pendingList">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>หัวข้อ</th><th>ผู้ขอ / หน่วยงาน</th><th>วันที่</th><th class="text-center">สรุปผล</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($pendingSummary as $r): ?>
                            <tr>
                                <td class="small fw-medium"><?= Html::encode($r['topic']) ?></td>
                                <td class="small"><?= Html::encode($r['requester'] ?: '-') ?><?php if ($r['dept']): ?><span class="text-body-secondary"> · <?= Html::encode($r['dept']) ?></span><?php endif; ?></td>
                                <td class="small text-nowrap"><?= $thaiDate($r['date_start']) ?></td>
                                <td class="text-center">
                                    <?php if ($r['summary_status'] === 'draft'): ?>
                                        <span class="badge rounded-pill text-bg-secondary">ฉบับร่าง</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill text-bg-danger">ยังไม่สรุป</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= $openSummary($r, '<i class="bi bi-pencil-square"></i> สรุปผล', 'btn btn-sm btn-outline-primary') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ประเภทกิจกรรม -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-5">
        <div class="card h-100 border shadow-sm">
            <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-diagram-3 me-1 text-primary"></i>สัดส่วนประเภทการพัฒนา</div>
            <div class="card-body"><div id="activityTypeChart" style="height:300px;"></div></div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="card h-100 border shadow-sm">
            <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-table me-1 text-primary"></i>จำนวนกิจกรรมแยกตามประเภท</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>ประเภทการพัฒนา</th><th class="text-end">จำนวน (ครั้ง)</th></tr></thead>
                        <tbody>
                        <?php $tt = array_sum($activityType['series'] ?: [0]); ?>
                        <?php foreach ($activityType['labels'] as $i => $label): $c = (int) ($activityType['series'][$i] ?? 0); ?>
                            <tr>
                                <td><?= Html::encode($label) ?></td>
                                <td class="text-end"><?= number_format($c) ?> <span class="text-body-secondary small">(<?= $tt > 0 ? round($c / $tt * 100, 1) : 0 ?>%)</span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$componentLabels = Json::encode(array_map(fn($r) => $r['label'], $report['actual_by_component']));
$componentSeries = Json::encode(array_map(fn($r) => (float) $r['amount'], $report['actual_by_component']));
$typeLabels = Json::encode($activityType['labels']);
$typeSeries = Json::encode(array_map('intval', $activityType['series']));
$palette = "['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ec4899','#6b7280','#14b8a6']";
$passportUrlBase = Url::to(['/hr/development/report-person', 'thai_year' => $year]);
$js = <<<JS
// เปิดสมุดพกรายบุคคลเมื่อเลือกคนจากช่องค้นหา
\$('#passport-search').on('select2:select', function (e) {
    var id = e.params && e.params.data ? e.params.data.id : null;
    if (!id) { return; }
    \$('#passport-open').attr('href', '$passportUrlBase' + '&emp_id=' + encodeURIComponent(id)).trigger('click');
    \$(this).val(null).trigger('change');
});

document.addEventListener('DOMContentLoaded', function () {
    if (window.ApexCharts) {
        new ApexCharts(document.querySelector('#componentChart'), {
            series: $componentSeries, labels: $componentLabels,
            chart: { type: 'donut', height: 220 }, colors: $palette,
            legend: { show: false }, dataLabels: { enabled: false },
            tooltip: { y: { formatter: function (v) { return Number(v).toLocaleString() + ' บาท'; } } }
        }).render();

        new ApexCharts(document.querySelector('#activityTypeChart'), {
            series: $typeSeries, labels: $typeLabels,
            chart: { type: 'donut', height: 300 }, colors: $palette,
            legend: { position: 'bottom', fontFamily: 'Sarabun, sans-serif' },
            dataLabels: { enabled: true, formatter: function (v) { return v.toFixed(1) + '%'; } }
        }).render();
    }
});
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
