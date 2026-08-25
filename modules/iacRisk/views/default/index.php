<?php
use yii\helpers\Html;

$this->title = 'IAC&Risk';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>การควบคุมภายในและการบริหารความเสี่ยง<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<div class="d-flex flex-wrap gap-2">
    <?php if ($access->canManageSettings()): ?>
        <?= Html::a('<i class="bi bi-gear me-1" aria-hidden="true"></i> ตั้งค่ารอบปี', ['/iac-risk/setting/index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?php endif; ?>
</div>
<?php $this->endBlock(); ?>

<?= $this->render('_context', ['context' => $context]) ?>
<div class="mb-3"><?= $this->render('@app/modules/iacRisk/menu', ['active' => 'overview', 'context' => $context]) ?></div>

<?php if (!$context['hospitalId']): ?>
    <section class="card bg-body border shadow-sm">
        <div class="card-body text-center py-5">
            <h2 class="h5 fw-semibold">ยังไม่ได้ตั้งค่าโรงพยาบาล</h2>
            <p class="text-body-secondary mb-3">ผู้ดูแลระบบต้องเชื่อมข้อมูลโรงพยาบาลก่อนเปิดปีงบประมาณ</p>
            <?php if ($access->canManageSettings()): ?><?= Html::a('ไปหน้าตั้งค่า', ['/iac-risk/setting/index'], ['class' => 'btn btn-primary']) ?><?php endif; ?>
        </div>
    </section>
<?php elseif (!$context['fiscalYear']): ?>
    <section class="card bg-body border shadow-sm">
        <div class="card-body text-center py-5">
            <h2 class="h5 fw-semibold">ยังไม่ได้เปิดปีงบประมาณ</h2>
            <p class="text-body-secondary mb-3">เมื่อเปิดปีงบประมาณ ระบบจะแสดงรอบ 6 เดือน 9 เดือน และสิ้นปี</p>
            <?php if ($access->canManageSettings()): ?><?= Html::a('สร้างปีงบประมาณ', ['/iac-risk/setting/create-year'], ['class' => 'btn btn-primary']) ?><?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <section class="card bg-body border shadow-sm h-100">
                <div class="card-header bg-body-tertiary border-bottom">
                    <h2 class="h6 fw-semibold mb-1">ขอบเขตที่กำลังใช้งาน</h2>
                    <p class="small text-body-secondary mb-0">ทุกเมนูจะคงโรงพยาบาล ปี รอบ และหน่วยงานชุดนี้</p>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-body-secondary">ปีงบประมาณ</dt><dd class="col-sm-8 fw-semibold"><?= (int) $context['fiscalYear']->fiscal_year ?></dd>
                        <dt class="col-sm-4 text-body-secondary">สถานะ</dt><dd class="col-sm-8"><span class="badge bg-<?= $context['fiscalYear']->status === 'open' ? 'success' : 'secondary' ?>-subtle text-<?= $context['fiscalYear']->status === 'open' ? 'success' : 'secondary' ?>-emphasis"><?= Html::encode(\app\modules\iacRisk\models\FiscalYear::statusLabels()[$context['fiscalYear']->status] ?? $context['fiscalYear']->status) ?></span></dd>
                        <dt class="col-sm-4 text-body-secondary">ช่วงเวลา</dt><dd class="col-sm-8 mb-0"><?= Yii::$app->formatter->asDate($context['fiscalYear']->start_date) ?> ถึง <?= Yii::$app->formatter->asDate($context['fiscalYear']->end_date) ?></dd>
                    </dl>
                </div>
            </section>
        </div>
        <div class="col-12 col-lg-5">
            <section class="card bg-body border shadow-sm h-100">
                <div class="card-header bg-body-tertiary border-bottom"><h2 class="h6 fw-semibold mb-0">รอบรายงาน</h2></div>
                <div class="list-group list-group-flush">
                    <?php foreach ($context['periods'] as $period): ?>
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center gap-3">
                            <div><div class="fw-semibold"><?= Html::encode($period->name) ?></div><div class="small text-body-secondary">สิ้นสุด <?= Yii::$app->formatter->asDate($period->end_date) ?></div></div>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode($period->status === 'pending' ? 'รอเปิดรอบ' : ($period->status === 'open' ? 'เปิดอยู่' : 'ปิดแล้ว')) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>

    <section class="card bg-body border shadow-sm mt-3">
        <div class="card-header bg-body-tertiary border-bottom"><h2 class="h6 fw-semibold mb-0">กิจกรรมล่าสุด</h2></div>
        <div class="list-group list-group-flush">
            <?php foreach ($activities as $activity): ?>
                <div class="list-group-item bg-body">
                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-1"><span class="fw-semibold"><?= Html::encode($activity->message ?: $activity->action) ?></span><time class="small text-body-secondary"><?= Yii::$app->formatter->asDatetime($activity->created_at) ?></time></div>
                </div>
            <?php endforeach; ?>
            <?php if (!$activities): ?><div class="list-group-item bg-body text-center py-4 text-body-secondary">ยังไม่มีกิจกรรมในขอบเขตนี้</div><?php endif; ?>
        </div>
    </section>
<?php endif; ?>
