<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\modules\hr\models\TrainingRoadmap;
use app\modules\hr\models\TrainingRoadmapActivity;

$this->title = $model->title;
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($model->code . ' · ' . $model->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'training-roadmap']); $this->endBlock();
$activityCount = 0; foreach ($model->phases as $phase) $activityCount += count($phase->activities);
?>
<div class="trm-shell">
    <div class="trm-page-head">
        <div>
            <div class="d-flex gap-2 align-items-center mb-2"><span class="trm-status trm-status--<?= Html::encode($model->status) ?>"><?= Html::encode($model->statusLabel) ?></span><span class="trm-meta">เวอร์ชัน <?= (int) $model->version_no ?></span></div>
            <h1><?= Html::encode($model->title) ?></h1><p><?= nl2br(Html::encode($model->description ?: 'กำหนดระยะพัฒนา กิจกรรม และเกณฑ์ประเมินสำหรับกลุ่มเป้าหมาย')) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?= Html::a('แก้ไขข้อมูล', ['update', 'id' => $model->id, 'title' => 'แก้ไข Training Roadmap'], ['class' => 'btn btn-outline-secondary open-modal', 'data-size' => 'modal-xl']) ?>
            <?= Html::a('<i class="bi bi-person-plus me-1"></i> มอบหมาย', ['assign', 'roadmap_id' => $model->id, 'title' => 'มอบหมาย Training Roadmap'], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-lg']) ?>
        </div>
    </div>
    <div class="trm-builder">
        <div class="trm-builder__main">
            <?php Pjax::begin(['id' => 'roadmap-builder', 'enablePushState' => false]); ?>
            <section class="trm-card mb-3">
                <div class="trm-section-head"><h2>เส้นทางการพัฒนา</h2><?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มระยะ', ['phase', 'roadmap_id' => $model->id, 'title' => 'เพิ่มระยะพัฒนา'], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?></div>
                <?php if ($model->phases): foreach ($model->phases as $index => $phase): ?>
                <article class="trm-phase">
                    <div class="trm-phase__rail"><div class="trm-phase__num"><?= $index + 1 ?></div><div class="trm-phase__line"></div></div>
                    <div class="trm-phase__head">
                        <div><div class="trm-period"><?= Html::encode($phase->period_label ?: 'ระยะที่ ' . ($index + 1)) ?></div><h3 class="trm-phase__title"><?= Html::encode($phase->title) ?></h3><?php if ($phase->description): ?><div class="trm-meta mt-1"><?= Html::encode($phase->description) ?></div><?php endif ?></div>
                        <div class="trm-phase__actions">
                            <?= Html::a('<i class="bi bi-pencil me-1" aria-hidden="true"></i>แก้ไข', ['phase', 'roadmap_id' => $model->id, 'id' => $phase->id, 'title' => 'แก้ไขระยะพัฒนา'], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data-size' => 'modal-lg']) ?>
                            <?= Html::a('<i class="bi bi-plus-lg me-1" aria-hidden="true"></i>เพิ่มกิจกรรม', ['activity', 'phase_id' => $phase->id, 'title' => 'เพิ่มกิจกรรม'], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?>
                        </div>
                    </div>
                    <?php if ($phase->activities): foreach ($phase->activities as $activity): ?>
                    <div class="trm-activity">
                        <div><div class="trm-activity__title"><?= Html::encode($activity->title) ?><?= $activity->is_required ? ' <span class="text-danger" title="กิจกรรมบังคับ">*</span>' : '' ?></div><?php if ($activity->description): ?><div class="trm-activity__desc"><?= Html::encode($activity->description) ?></div><?php endif ?><div class="trm-tags"><span class="trm-tag"><?= Html::encode(TrainingRoadmapActivity::typeOptions()[$activity->activity_type] ?? $activity->activity_type) ?></span><span class="trm-tag"><?= Html::encode(TrainingRoadmapActivity::requirementOptions()[$activity->requirement_type] ?? $activity->requirement_type) ?></span><?php if ($activity->competency_code): ?><span class="trm-tag"><?= Html::encode($activity->competency_code) ?> · ระดับ <?= (int) $activity->competency_level ?></span><?php endif ?></div></div>
                        <div class="trm-activity__actions">
                            <?= Html::a('<i class="bi bi-pencil me-1" aria-hidden="true"></i>แก้ไข', ['activity', 'phase_id' => $phase->id, 'id' => $activity->id, 'title' => 'แก้ไขกิจกรรม'], ['class' => 'btn btn-sm btn-outline-secondary open-modal trm-action-btn', 'data-size' => 'modal-lg']) ?>
                        </div>
                    </div>
                    <?php endforeach; else: ?><div class="trm-meta py-2">ยังไม่มีกิจกรรมในระยะนี้</div><?php endif ?>
                </article>
                <?php endforeach; else: ?><div class="trm-empty"><h3>เริ่มออกแบบเส้นทางการพัฒนา</h3><p>เพิ่มระยะแรก เช่น ปฐมนิเทศ สัปดาห์ที่ 1–2 หรือเดือนที่ 1</p></div><?php endif ?>
            </section>
            <section class="trm-card">
                <div class="trm-section-head"><h2>จุดประเมินตามระยะ</h2><?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มจุดประเมิน', ['milestone', 'roadmap_id' => $model->id, 'title' => 'เพิ่มจุดประเมิน'], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?></div>
                <div class="px-3">
                <?php if ($model->milestones): foreach ($model->milestones as $milestone): ?><div class="trm-milestone"><div class="trm-milestone__mark"><i class="bi bi-flag"></i></div><div class="flex-grow-1"><div class="d-flex justify-content-between gap-2"><div class="fw-semibold"><?= Html::encode($milestone->title) ?></div><?= Html::a('แก้ไข', ['milestone', 'roadmap_id' => $model->id, 'id' => $milestone->id, 'title' => 'แก้ไขจุดประเมิน'], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data-size' => 'modal-lg']) ?></div><div class="trm-meta">ครบกำหนดหลังเริ่ม <?= (int) $milestone->due_offset ?> <?= Html::encode(TrainingRoadmap::durationUnitOptions()[$milestone->offset_unit] ?? $milestone->offset_unit) ?></div><?php if ($milestone->criteria_text): ?><div class="small mt-1"><?= Html::encode($milestone->criteria_text) ?></div><?php endif ?></div></div><?php endforeach; else: ?><div class="trm-empty py-4"><h3>ยังไม่มีจุดประเมิน</h3><p>กำหนดเกณฑ์ตรวจความพร้อมในเดือนหรือระยะสำคัญ</p></div><?php endif ?>
                </div>
            </section>
            <?php Pjax::end(); ?>
        </div>
        <aside class="trm-builder__side">
            <div class="trm-card mb-3"><div class="trm-section-head"><h2>ภาพรวมแม่แบบ</h2></div><div class="trm-summary"><dl><dt>ประเภท</dt><dd><?= Html::encode(TrainingRoadmap::typeOptions()[$model->roadmap_type] ?? $model->roadmap_type) ?></dd><dt>ระยะเวลา</dt><dd><?= (int) $model->duration_value ?> <?= Html::encode(TrainingRoadmap::durationUnitOptions()[$model->duration_unit] ?? $model->duration_unit) ?></dd><dt>ระยะพัฒนา</dt><dd><?= count($model->phases) ?></dd><dt>กิจกรรม</dt><dd><?= $activityCount ?></dd><dt>จุดประเมิน</dt><dd><?= count($model->milestones) ?></dd></dl></div></div>
            <div class="trm-card" id="roadmap-assignments"><div class="trm-section-head"><h2>การนำไปใช้</h2></div><div class="trm-summary"><dl><dt>บุคลากรที่ได้รับ</dt><dd><?= count($model->assignments) ?> คน</dd></dl><p class="trm-meta mt-3 mb-0">เมื่อมอบหมาย ระบบจะเก็บ snapshot ของเวอร์ชันนี้เพื่อรักษาเกณฑ์เดิมตลอดแผน</p></div></div>
        </aside>
    </div>
</div>
