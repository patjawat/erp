<?php

use yii\helpers\Html;

$titles = [
    'overview' => ['ภาพรวมงาน HRD', 'ติดตามความพร้อมและรายการที่ HR ต้องดำเนินการจากจุดเดียว'],
    'jd' => ['JD', 'ภาพรวมคำบรรยายลักษณะงานและการลงนามรับทราบ'],
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
        <section class="workforce-metrics" aria-label="ตัวชี้วัดงานบุคลากร">
            <div class="workforce-metric"><span class="workforce-metric__label">บุคลากรทั้งหมด</span><strong class="workforce-metric__value"><?= number_format($metrics['employees']) ?></strong><span class="workforce-metric__hint">ทะเบียนบุคลากร</span></div>
            <div class="workforce-metric"><span class="workforce-metric__label">มี JD ปัจจุบัน</span><strong class="workforce-metric__value"><?= number_format($metrics['jd_active']) ?></strong><span class="workforce-metric__hint">รอลงนาม <?= number_format($metrics['jd_pending']) ?> คน</span></div>
            <div class="workforce-metric"><span class="workforce-metric__label">IDP ในรอบปัจจุบัน</span><strong class="workforce-metric__value"><?= number_format($metrics['idp_total']) ?></strong><span class="workforce-metric__hint">ต้องดำเนินการ <?= number_format($metrics['idp_action']) ?> แผน</span></div>
            <div class="workforce-metric"><span class="workforce-metric__label">TRM กำลังดำเนินการ</span><strong class="workforce-metric__value"><?= number_format($metrics['trm_in_progress']) ?></strong><span class="workforce-metric__hint">Roadmap ใช้งาน <?= number_format($metrics['trm_active']) ?> แบบ</span></div>
        </section>

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
        <section class="workforce-metrics" aria-label="ตัวชี้วัด JD">
            <div class="workforce-metric"><span class="workforce-metric__label">บุคลากรทั้งหมด</span><strong class="workforce-metric__value"><?= number_format($metrics['employees']) ?></strong></div>
            <div class="workforce-metric"><span class="workforce-metric__label">มี JD ปัจจุบัน</span><strong class="workforce-metric__value"><?= number_format($metrics['jd_active']) ?></strong></div>
            <div class="workforce-metric"><span class="workforce-metric__label">อยู่ระหว่างลงนาม</span><strong class="workforce-metric__value"><?= number_format($metrics['jd_approval_pending']) ?></strong></div>
            <div class="workforce-metric"><span class="workforce-metric__label">ยังไม่ได้กำหนด JD</span><strong class="workforce-metric__value"><?= number_format($metrics['jd_missing']) ?></strong></div>
        </section>
        <div class="mt-3">
            <?= $this->render('_jd_registry', compact('jdDataProvider', 'jdByEmployee', 'approvalByJd', 'acknowledgedJdIds')) ?>
        </div>
    <?php else: ?>
        <section class="workforce-empty">
            <span class="workforce-empty__icon"><i data-lucide="<?= $section === 'exit' ? 'log-out' : 'chart-no-axes-combined' ?>"></i></span>
            <h2>วางตำแหน่งเมนูไว้แล้ว</h2>
            <p><?= $section === 'exit'
                    ? 'ขั้นถัดไปจะออกแบบแบบสัมภาษณ์ การส่งมอบงาน เหตุผลการลาออก และรายงานวิเคราะห์สำหรับ HR'
                    : 'ขั้นถัดไปจะออกแบบรอบทดลองงานและรอบประเมินประจำปี โดยเชื่อมผลประเมินไปยัง IDP' ?></p>
            <?= Html::a('กลับไปภาพรวม', ['/hr/workforce/index'], ['class' => 'btn btn-outline-primary']) ?>
        </section>
    <?php endif ?>
</div>
