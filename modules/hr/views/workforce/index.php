<?php

use yii\helpers\Html;

$titles = [
    'overview' => ['ภาพรวมงาน HRD', 'ติดตามความพร้อมและรายการที่ HR ต้องดำเนินการจากจุดเดียว'],
    'jd' => ['JD', 'ภาพรวมคำบรรยายลักษณะงานและการลงนามรับทราบ'],
    'kpi' => ['KPI', 'ภาพรวมตัวชี้วัดผลการปฏิบัติงานรายบุคคลตามหน่วยงาน'],
    'appraisal' => ['ประเมินผล', 'การประเมินช่วงทดลองงานและผลการปฏิบัติงานประจำปี'],
    'exit' => ['Exit Interview', 'ติดตามการสัมภาษณ์ก่อนออกจากงานและประเด็นที่องค์กรควรนำไปปรับปรุง'],
];
[$heading, $description] = $titles[$section];

$this->title = $heading;
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => $section === 'overview' ? 'workforce' : $section]); $this->endBlock();
?>
<div class="workforce-shell">
    <?= $this->render('_menu', ['active' => $section]) ?>

    <header class="workforce-head">
        <div>
            <h1><?= Html::encode($heading) ?></h1>
            <p><?= Html::encode($description) ?></p>
        </div>
    </header>

    <?php if ($section === 'overview'): ?>
        <?= $this->render('@app/modules/hr/views/_kpi_cards', ['cards' => [
            ['label' => 'บุคลากรที่ปฏิบัติงาน', 'value' => $metrics['employees'], 'icon' => 'bi-people-fill', 'color' => 'primary', 'hint' => 'สถานะปฏิบัติราชการ'],
            ['label' => 'มี JD ปัจจุบัน', 'value' => $metrics['jd_active'], 'icon' => 'bi-file-earmark-text', 'color' => 'info', 'hint' => 'รอลงนาม ' . number_format($metrics['jd_pending']) . ' คน'],
            ['label' => 'IDP ในรอบปัจจุบัน', 'value' => $metrics['idp_total'], 'icon' => 'bi-bullseye', 'color' => 'warning', 'hint' => 'ต้องดำเนินการ ' . number_format($metrics['idp_action']) . ' แผน'],
            ['label' => 'TRM กำลังดำเนินการ', 'value' => $metrics['trm_in_progress'], 'icon' => 'bi-signpost-2', 'color' => 'success', 'hint' => 'Roadmap ใช้งาน ' . number_format($metrics['trm_active']) . ' แบบ'],
        ]]) ?>

        <div class="workforce-grid">
            <section class="workforce-panel">
                <h2>รายการที่ต้องดูแล</h2>
                <?= Html::a('<span><strong>JD รอลงนามรับทราบ</strong><small>' . number_format($metrics['jd_pending']) . ' คนยังไม่ลงนาม</small></span><i data-lucide="chevron-right"></i>', ['/hr/workforce/index', 'section' => 'jd'], ['class' => 'workforce-link']) ?>
                <?= Html::a('<span><strong>IDP ที่ต้องดำเนินการ</strong><small>' . number_format($metrics['idp_action']) . ' แผนในรอบปัจจุบัน</small></span><i data-lucide="chevron-right"></i>', ['/hr/idp/index'], ['class' => 'workforce-link']) ?>
                <?= Html::a('<span><strong>TRM ที่อยู่ระหว่างดำเนินการ</strong><small>' . number_format($metrics['trm_in_progress']) . ' แผนบุคลากร</small></span><i data-lucide="chevron-right"></i>', ['/hr/training-roadmap/index'], ['class' => 'workforce-link']) ?>
            </section>
            <aside class="workforce-panel">
                <h2>รอบที่กำลังใช้งาน</h2>
                <?php if ($activeCycle): ?>
                    <div class="fw-semibold"><?= Html::encode($activeCycle->title) ?></div>
                    <div class="text-muted small mt-1">IDP · <?= Html::encode($activeCycle->start_date) ?> – <?= Html::encode($activeCycle->end_date) ?></div>
                <?php else: ?>
                    <p class="text-muted mb-3">ยังไม่มีรอบ IDP ที่กำลังใช้งาน</p>
                    <?= Html::a('ตั้งค่ารอบ IDP', ['/hr/idp/cycle'], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?>
                <?php endif ?>
            </aside>
        </div>
    <?php elseif ($section === 'jd'): ?>
        <?= $this->render('@app/modules/hr/views/_kpi_cards', ['cards' => [
            ['label' => 'บุคลากรที่ปฏิบัติงาน', 'value' => $metrics['employees'], 'icon' => 'bi-people-fill', 'color' => 'primary'],
            ['label' => 'มี JD ปัจจุบัน', 'value' => $metrics['jd_active'], 'icon' => 'bi-file-earmark-check', 'color' => 'success'],
            ['label' => 'อยู่ระหว่างลงนาม', 'value' => $metrics['jd_approval_pending'], 'icon' => 'bi-pen', 'color' => 'warning'],
            ['label' => 'ยังไม่ได้กำหนด JD', 'value' => $metrics['jd_missing'], 'icon' => 'bi-file-earmark-x', 'color' => 'danger'],
        ]]) ?>
        <div class="mt-3">
            <?= $this->render('_jd_registry', compact('jdDataProvider', 'jdByEmployee', 'approvalByJd', 'acknowledgedJdIds', 'showAll')) ?>
        </div>
    <?php elseif ($section === 'kpi'): ?>
        <?= $this->render('@app/modules/hr/views/_kpi_cards', ['cards' => [
            ['label' => 'บุคลากรที่ปฏิบัติงาน', 'value' => $metrics['employees'], 'icon' => 'bi-people-fill', 'color' => 'primary'],
            ['label' => 'มีชุด KPI ปีนี้', 'value' => $metrics['kpi_current'] ?? 0, 'icon' => 'bi-bullseye', 'color' => 'success', 'hint' => 'ใช้งาน ' . number_format($metrics['kpi_active'] ?? 0) . ' ชุด'],
            ['label' => 'รออนุมัติ/ร่าง', 'value' => $metrics['kpi_pending'] ?? 0, 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
            ['label' => 'ยังไม่มีชุด KPI', 'value' => $metrics['kpi_missing'] ?? 0, 'icon' => 'bi-exclamation-triangle', 'color' => 'danger'],
        ]]) ?>
        <div class="mt-3">
            <?= $this->render('_kpi_registry', compact('kpiDataProvider', 'kpiByEmployee', 'kpiItemCounts', 'departments', 'currentFy', 'showAll')) ?>
        </div>
    <?php elseif ($section === 'appraisal'): ?>
        <section class="workforce-empty">
            <span class="workforce-empty__icon"><i data-lucide="chart-no-axes-combined"></i></span>
            <h2>การประเมินช่วงทดลองงาน</h2>
            <p>ติดตามการประเมินเดือนที่ 1, 2 และ 3 ตั้งแต่การมอบหมายจนถึง ผอ.รับทราบผล</p>
            <?= Html::a('เปิดระบบประเมินทดลองงาน', ['/hr/probation-appraisal/index'], ['class' => 'btn btn-primary']) ?>
        </section>
    <?php else: ?>
        <section class="workforce-empty">
            <span class="workforce-empty__icon"><i data-lucide="log-out"></i></span>
            <h2>วางตำแหน่งเมนูไว้แล้ว</h2>
            <p>ขั้นถัดไปจะออกแบบแบบสัมภาษณ์ การส่งมอบงาน เหตุผลการลาออก และรายงานวิเคราะห์สำหรับ HR</p>
            <?= Html::a('กลับไปภาพรวม', ['/hr/workforce/index'], ['class' => 'btn btn-outline-primary']) ?>
        </section>
    <?php endif ?>
</div>
