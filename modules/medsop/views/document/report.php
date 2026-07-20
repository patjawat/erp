<?php

use app\modules\medsop\assets\MedSopAsset;
use app\modules\medsop\models\Document;
use yii\helpers\Html;
use yii\helpers\Url;

MedSopAsset::register($this);
$this->title = 'รายงานคลัง SOP/WI';

$total = array_sum(array_map(static fn($row) => (int) $row['total'], $statusCounts));
$published = (int) ($statusCounts[Document::STATUS_PUBLISHED]['total'] ?? 0);
$pending = (int) ($statusCounts[Document::STATUS_PENDING]['total'] ?? 0);
$publishedPercent = $total > 0 ? round($published * 100 / $total) : 0;
$maxMonthly = max(1, ...array_map(static fn($row) => (int) $row['total'], $monthlyCounts));
$maxOrganization = max(1, ...array_map(static fn($row) => (int) $row['total'], $organizationCounts ?: [['total' => 1]]));
$statusLabels = Document::statusOptions();

$csvRows = [['ตัวชี้วัด', 'จำนวน']];
$csvRows[] = ['เอกสารทั้งหมด', $total];
$csvRows[] = ['เผยแพร่แล้ว', $published];
$csvRows[] = ['รออนุมัติ', $pending];
$csvRows[] = ['ถึงกำหนดทบทวนภายใน 90 วัน', $reviewDueCount];
foreach ($typeCounts as $type => $row) {
    $csvRows[] = ['ประเภท ' . $type, (int) $row['total']];
}
?>

<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ติดตามสถานะเอกสาร แนวโน้มการจัดทำ และรายการที่ต้องดำเนินการ<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= $this->render('_nav', ['access' => $access, 'active' => 'report']) ?><?php $this->endBlock(); ?>

<main class="medsop-report" aria-labelledby="report-heading">
    <header class="medsop-report__head">
        <div>
            <h1 id="report-heading" class="h4 fw-semibold mb-1"><i class="bi bi-bar-chart-line me-2 text-primary" aria-hidden="true"></i>ภาพรวมและตัวชี้วัดคลังเอกสาร</h1>
            <p class="mb-0">ข้อมูลตามสิทธิ์การเข้าถึงของคุณ อัปเดตจากเอกสารในระบบปัจจุบัน</p>
        </div>
        <div class="medsop-report__actions">
            <button type="button" class="btn btn-success" id="medsop-export-report"><i class="bi bi-file-earmark-excel me-1" aria-hidden="true"></i>Export Excel (CSV)</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1" aria-hidden="true"></i>พิมพ์รายงาน</button>
        </div>
    </header>

    <section class="medsop-report-kpis" aria-label="ตัวชี้วัดสำคัญ">
        <article class="medsop-report-kpi">
            <span class="medsop-report-kpi__icon is-primary"><i class="bi bi-journals" aria-hidden="true"></i></span>
            <div><span>เอกสารทั้งหมด</span><strong><?= number_format($total) ?></strong><small>SOP และ WI ที่มองเห็นได้</small></div>
        </article>
        <article class="medsop-report-kpi">
            <span class="medsop-report-kpi__icon is-success"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
            <div><span>เผยแพร่แล้ว</span><strong><?= number_format($published) ?></strong><small><?= number_format($publishedPercent) ?>% ของเอกสารทั้งหมด</small></div>
        </article>
        <article class="medsop-report-kpi">
            <span class="medsop-report-kpi__icon is-warning"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
            <div><span>รออนุมัติ</span><strong><?= number_format($pending) ?></strong><small><?= $pending > 0 ? 'ควรตรวจสอบและดำเนินการ' : 'ไม่มีรายการค้าง' ?></small></div>
        </article>
        <article class="medsop-report-kpi">
            <span class="medsop-report-kpi__icon <?= $reviewDueCount > 0 ? 'is-danger' : 'is-neutral' ?>"><i class="bi bi-calendar2-check" aria-hidden="true"></i></span>
            <div><span>กำหนดทบทวน</span><strong><?= number_format($reviewDueCount) ?></strong><small>ครบกำหนดภายใน 90 วัน</small></div>
        </article>
    </section>

    <div class="medsop-report-grid">
        <section class="medsop-report-panel medsop-report-panel--wide" aria-labelledby="monthly-title">
            <header><div><h2 id="monthly-title">แนวโน้มการสร้างเอกสาร</h2><p>จำนวนเอกสารใหม่ย้อนหลัง 12 เดือน</p></div></header>
            <div class="medsop-column-chart" role="img" aria-label="กราฟจำนวนเอกสารใหม่ย้อนหลัง 12 เดือน">
                <?php foreach ($monthlyCounts as $row): $height = (int) $row['total'] > 0 ? max(7, round((int) $row['total'] * 100 / $maxMonthly)) : 2; ?>
                    <div class="medsop-column-chart__item" title="<?= Html::encode(Yii::$app->formatter->asDate($row['month'] . '-01', 'MMMM yyyy')) ?>: <?= number_format($row['total']) ?> รายการ">
                        <strong><?= number_format($row['total']) ?></strong>
                        <span class="medsop-column-chart__track"><span style="height: <?= $height ?>%"></span></span>
                        <small><?= Html::encode(Yii::$app->formatter->asDate($row['month'] . '-01', 'MMM')) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="medsop-report-panel" aria-labelledby="type-title">
            <header><div><h2 id="type-title">สัดส่วนประเภทเอกสาร</h2><p>จำแนกตาม SOP และ WI</p></div></header>
            <div class="medsop-type-list">
                <?php foreach (Document::typeOptions() as $type => $label): $count = (int) ($typeCounts[$type]['total'] ?? 0); $percent = $total > 0 ? round($count * 100 / $total) : 0; ?>
                    <div class="medsop-type-list__item">
                        <div class="medsop-type-list__label"><span><?= Html::encode($label) ?></span><strong><?= number_format($count) ?></strong></div>
                        <div class="medsop-progress" role="progressbar" aria-label="<?= Html::encode($label) ?>" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"><span style="width: <?= $percent ?>%"></span></div>
                        <small><?= number_format($percent) ?>% ของเอกสารทั้งหมด</small>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="medsop-report-panel" aria-labelledby="org-title">
            <header><div><h2 id="org-title">หน่วยงานที่มีเอกสารมากที่สุด</h2><p>6 อันดับแรกตามสิทธิ์ที่มองเห็น</p></div></header>
            <?php if (!$organizationCounts): ?>
                <p class="medsop-report-empty">ยังไม่มีข้อมูลหน่วยงาน เมื่อสร้างเอกสารแล้วจะแสดงสรุปที่นี่</p>
            <?php else: ?>
                <ol class="medsop-org-ranking">
                    <?php foreach ($organizationCounts as $index => $row): $organization = $organizations[$row['organization_id']] ?? null; ?>
                        <li><span class="medsop-org-ranking__number"><?= $index + 1 ?></span><span class="medsop-org-ranking__name" title="<?= Html::encode($organization ? $organization->name : 'ไม่ระบุหน่วยงาน') ?>"><?= Html::encode($organization ? $organization->name : 'ไม่ระบุหน่วยงาน') ?></span><span class="medsop-org-ranking__bar"><span style="width: <?= round((int) $row['total'] * 100 / $maxOrganization) ?>%"></span></span><strong><?= number_format($row['total']) ?></strong></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>

        <section class="medsop-report-panel medsop-report-panel--wide" aria-labelledby="recent-title">
            <header><div><h2 id="recent-title">เอกสารที่ปรับปรุงล่าสุด</h2><p>ใช้ตรวจสอบความเคลื่อนไหวและสถานะล่าสุด</p></div><a href="<?= Url::to(['/medsop/document/index']) ?>">ดูคลังเอกสารทั้งหมด</a></header>
            <?php if (!$recentDocuments): ?>
                <p class="medsop-report-empty">ยังไม่มีเอกสาร เมื่อเริ่มจัดทำเอกสารแล้ว รายการล่าสุดจะแสดงที่นี่</p>
            <?php else: ?>
                <div class="table-responsive medsop-report-table-wrap">
                    <table class="table medsop-report-table mb-0">
                        <caption class="visually-hidden">รายการเอกสารที่ปรับปรุงล่าสุด</caption>
                        <thead><tr><th>รหัสเอกสาร</th><th>ชื่อเอกสาร</th><th>หน่วยงาน</th><th>แก้ไขล่าสุด</th><th>สถานะ</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentDocuments as $document): $badge = Document::getStatusBadgeConfigFor($document->status); ?>
                                <tr>
                                    <td><a class="medsop-report-code" href="<?= Url::to(['/medsop/document/view', 'id' => $document->id]) ?>"><?= Html::encode($document->document_no) ?></a></td>
                                    <td><?= Html::encode($document->title) ?></td>
                                    <td><?= Html::encode($document->organization ? $document->organization->name : 'ไม่ระบุ') ?></td>
                                    <td class="medsop-tabular text-nowrap"><?= Yii::$app->formatter->asDate($document->updated_at, 'php:d/m/Y') ?></td>
                                    <td><span class="<?= Html::encode($badge['class']) ?>"><?= Html::encode($statusLabels[$document->status] ?? $document->status) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php
$csvJson = json_encode($csvRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$this->registerJs(<<<JS
document.getElementById('medsop-export-report')?.addEventListener('click', function () {
    const rows = $csvJson;
    const csv = '\uFEFF' + rows.map(row => row.map(value => '"' + String(value).replaceAll('"', '""') + '"').join(',')).join('\r\n');
    const url = URL.createObjectURL(new Blob([csv], {type: 'text/csv;charset=utf-8'}));
    const link = document.createElement('a');
    link.href = url;
    link.download = 'รายงานคลัง-SOP-WI.csv';
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
});
JS);
?>
