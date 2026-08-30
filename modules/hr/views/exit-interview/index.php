<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\hr\models\ExitInterview;
$this->title = 'วิเคราะห์ Exit Interview';
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'exit']); $this->endBlock();
$metrics = $dashboard['metrics'];
$suppressed = (bool)($dashboard['suppressed'] ?? false);
$labels = ['compensation' => 'ค่าตอบแทนและสวัสดิการ', 'workload' => 'ภาระงานและสมดุลชีวิต', 'management' => 'การบริหารและมอบหมายงาน', 'supervisor' => 'หัวหน้างาน', 'colleagues' => 'เพื่อนร่วมงาน', 'communication' => 'การสื่อสาร', 'career' => 'โอกาสเติบโต', 'development' => 'การเรียนรู้และพัฒนา', 'mentoring' => 'ระบบพี่เลี้ยง', 'safety' => 'สภาพแวดล้อมและความปลอดภัย', 'recommend' => 'การแนะนำองค์กร'];
?>
<div class="container-fluid px-0">
<?= $this->render('_nav', ['active' => 'dashboard']) ?>
<?php if ($suppressed): ?><div class="alert alert-info" role="status">ข้อมูลในช่วงที่เลือกมีน้อยกว่า <?= (int)\app\modules\hr\services\ExitInterviewService::MINIMUM_ANALYTICS_GROUP ?> คน ระบบจึงซ่อนคะแนนและเหตุผลเพื่อคุ้มครองข้อมูลส่วนบุคคล</div><?php endif ?>
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-3">
        <div><h1 class="h3 mb-1">Exit Interview Dashboard</h1><p class="text-body-secondary mb-0">วิเคราะห์เหตุผลการออก ความพึงพอใจ และประเด็นที่ควรปรับปรุง โดยไม่แสดงข้อมูลระบุตัวตน</p></div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (Yii::$app->user->can('exitInterviewImport') || Yii::$app->user->can('admin')): ?>
            <?= Html::a('<i data-lucide="download"></i> ดาวน์โหลด Excel Template', ['download-template'], ['class' => 'btn btn-outline-secondary d-inline-flex align-items-center gap-2']) ?>
            <?= Html::a('<i data-lucide="upload"></i> นำเข้า Excel', ['import'], ['class' => 'btn btn-outline-primary d-inline-flex align-items-center gap-2']) ?>
            <?php endif ?>
            <?php if (Yii::$app->user->can('exitInterviewExportIdentified') || Yii::$app->user->can('admin')): ?>
            <?= Html::a('<i data-lucide="file-down"></i> ส่งออก CSV', ['export-csv'], ['class' => 'btn btn-outline-secondary d-inline-flex align-items-center gap-2']) ?>
            <?php endif ?>
        </div>
    </div>
    <?= Html::beginForm(['index'], 'get', ['class' => 'card bg-body border shadow-sm mb-3']) ?>
    <div class="card-body"><div class="row g-2 align-items-end">
        <div class="col-12 col-md-3"><label class="form-label" for="date_from">ตั้งแต่วันที่</label><?= Html::input('date', 'date_from', $filters['date_from'], ['class' => 'form-control', 'id' => 'date_from']) ?></div>
        <div class="col-12 col-md-3"><label class="form-label" for="date_to">ถึงวันที่</label><?= Html::input('date', 'date_to', $filters['date_to'], ['class' => 'form-control', 'id' => 'date_to']) ?></div>
        <div class="col-12 col-md-2"><label class="form-label" for="department">หน่วยงาน</label><?= Html::dropDownList('department', $filters['department'], ['' => 'ทุกหน่วยงาน'] + $departmentItems, ['class' => 'form-select', 'id' => 'department']) ?></div>
        <div class="col-12 col-md-2"><label class="form-label" for="exit_type">ประเภทการออก</label><?= Html::dropDownList('exit_type', $filters['exit_type'], ['' => 'ทุกประเภท'] + ExitInterview::exitTypeOptions(), ['class' => 'form-select', 'id' => 'exit_type']) ?></div>
        <div class="col-12 col-md-2 d-flex gap-2"><?= Html::submitButton('ใช้ตัวกรอง', ['class' => 'btn btn-primary']) ?><?= Html::a('ล้าง', ['index'], ['class' => 'btn btn-outline-secondary']) ?></div>
    </div></div><?= Html::endForm() ?>
    <div class="row g-3 mb-3">
        <?php foreach ([['ผู้ให้ข้อมูล', $suppressed ? '< ' . \app\modules\hr\services\ExitInterviewService::MINIMUM_ANALYTICS_GROUP : number_format($metrics['total']), 'คน'], ['Exit Satisfaction', $metrics['satisfaction_t2b'] === null ? '—' : $metrics['satisfaction_t2b'] . '%', 'คะแนน 4–5'], ['พิจารณากลับมาทำงาน', $metrics['rehire_percent'] === null ? '—' : $metrics['rehire_percent'] . '%', 'ตอบ ใช่/อาจพิจารณา'], ['ปัจจัยต้องเร่งแก้', number_format($metrics['at_risk_categories']), 'T2B ต่ำกว่า 40%']] as [$label, $value, $hint]): ?>
        <div class="col-12 col-sm-6 col-xl-3"><section class="card bg-body border shadow-sm h-100"><div class="card-body"><div class="text-body-secondary small mb-2"><?= Html::encode($label) ?></div><div class="fs-2 fw-bold font-monospace"><?= Html::encode($value) ?></div><div class="text-body-secondary small mt-1"><?= Html::encode($hint) ?></div></div></section></div>
        <?php endforeach ?>
    </div>
    <div class="row g-3">
        <div class="col-12 col-xl-7"><section class="card bg-body border shadow-sm h-100"><div class="card-header bg-body-tertiary"><h2 class="h5 mb-1">T2B ตามหมวดปัจจัย</h2><p class="text-body-secondary small mb-0">สัดส่วนคำตอบระดับ 4–5 จากผู้ตอบคำถามนั้น</p></div><div class="card-body">
            <?php if (!$dashboard['categories']): ?><p class="text-body-secondary mb-0">ยังไม่มีข้อมูลคะแนนสำหรับตัวกรองนี้</p><?php endif ?>
            <?php foreach ($dashboard['categories'] as $key => $row): ?><div class="mb-3"><div class="d-flex justify-content-between gap-3 mb-1"><span><?= Html::encode($labels[$key] ?? $key) ?></span><strong class="font-monospace"><?= Html::encode($row['t2b'] . '%') ?></strong></div><progress class="w-100" aria-label="<?= Html::encode($labels[$key] ?? $key) ?>" value="<?= (float)$row['t2b'] ?>" max="100"></progress></div><?php endforeach ?>
        </div></section></div>
        <div class="col-12 col-xl-5"><section class="card bg-body border shadow-sm mb-3"><div class="card-header bg-body-tertiary"><h2 class="h5 mb-0">เหตุผลลาออกที่สำคัญ</h2></div><ol class="list-group list-group-numbered list-group-flush"><?php foreach (array_slice($dashboard['reasons'], 0, 8, true) as $reason => $score): ?><li class="list-group-item d-flex justify-content-between align-items-center gap-3"><span><?= Html::encode($reason) ?></span><span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill"><?= number_format($score) ?></span></li><?php endforeach ?><?php if (!$dashboard['reasons']): ?><li class="list-group-item text-body-secondary">ยังไม่มีข้อมูล</li><?php endif ?></ol></section>
        <section class="card bg-body border shadow-sm"><div class="card-header bg-body-tertiary"><h2 class="h5 mb-1">หน่วยงานที่แสดงผลได้</h2><p class="text-body-secondary small mb-0">แสดงเฉพาะกลุ่มที่มีผู้ตอบอย่างน้อย 5 คน</p></div><ul class="list-group list-group-flush"><?php foreach ($dashboard['departments'] as $row): ?><li class="list-group-item d-flex justify-content-between"><span><?= Html::encode($row['name']) ?></span><strong><?= number_format($row['count']) ?></strong></li><?php endforeach ?><?php if (!$dashboard['departments']): ?><li class="list-group-item text-body-secondary">ยังไม่มีกลุ่มที่ผ่านเกณฑ์</li><?php endif ?></ul></section></div>
    </div>
</div>
