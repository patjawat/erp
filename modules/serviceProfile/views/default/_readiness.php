<?php use yii\helpers\Html; ?>
<section class="card bg-body border shadow-sm mb-3" aria-labelledby="sp-readiness-title">
    <div class="card-header bg-body-tertiary d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-3">
        <div><h2 id="sp-readiness-title" class="h6 fw-semibold mb-1">ความพร้อมก่อนส่งพิจารณา</h2><p class="small text-body-secondary mb-0">ระบบตรวจข้อมูลและผู้รับผิดชอบในทุกขั้นตอน</p></div>
        <span class="badge <?= $readiness['ready']?'bg-success-subtle text-success-emphasis':'bg-warning-subtle text-warning-emphasis' ?>"><?= $readiness['ready']?'พร้อมส่ง':'ยังไม่พร้อม' ?></span>
    </div>
    <div class="card-body p-3 p-md-4">
        <div class="row g-3">
            <?php foreach($readiness['checks'] as $check): ?>
            <div class="col-12 col-md-6 col-xl"><div class="d-flex align-items-start gap-2"><i class="bi <?= $check['ready']?'bi-check-circle-fill text-success':'bi-exclamation-circle-fill text-warning' ?> mt-1" aria-hidden="true"></i><div><div class="fw-semibold"><?= Html::encode($check['label']) ?></div><div class="small text-body-secondary"><?= Html::encode($check['detail']) ?></div></div></div></div>
            <?php endforeach; ?>
        </div>
        <?php if($readiness['missing_sections']): ?><details class="mt-3"><summary class="small fw-semibold text-primary">ดูหัวข้อที่ยังไม่ครบ</summary><ul class="small mb-0 mt-2"><?php foreach($readiness['missing_sections'] as $title): ?><li><?= Html::encode($title) ?></li><?php endforeach; ?></ul></details><?php endif; ?>
    </div>
</section>
