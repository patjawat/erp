<?php
use app\components\widgets\DataSummaryWidget;
use app\modules\hr\models\Employees;
use app\modules\serviceProfile\models\ServiceProfileReview;
use yii\helpers\Html;

$this->title = 'ประวัติการแก้ไข Service Profile';
$activities = $dataProvider->getModels();
$creatorIds = array_values(array_unique(array_filter(array_map(static fn($row) => (int) $row->created_by, $activities))));
$creatorMap = $creatorIds ? Employees::find()->where(['user_id' => $creatorIds])->indexBy('user_id')->all() : [];
$actionLabels = ['created'=>'สร้างฉบับร่าง','section_updated'=>'แก้ไขหัวข้อ','section_commented'=>'เพิ่มความคิดเห็นรายหัวข้อ','section_comment_resolved'=>'แก้ไขความคิดเห็นรายหัวข้อแล้ว','authors_updated'=>'ปรับคณะผู้จัดทำ','submitted'=>'ส่งพิจารณา','quality_commented'=>'ผู้แทนคุณภาพให้ความคิดเห็น','quality_endorsed'=>'ผู้แทนคุณภาพเห็นชอบ','quality_returned'=>'ผู้แทนคุณภาพส่งกลับ','director_approved'=>'ผู้อำนวยการอนุมัติ','returned'=>'ส่งกลับแก้ไข','published'=>'รับทราบและประกาศใช้'];
$decisionLabels = [ServiceProfileReview::DECISION_COMMENTED=>'ให้ความคิดเห็น',ServiceProfileReview::DECISION_ENDORSED=>'เห็นชอบ',ServiceProfileReview::DECISION_RETURNED=>'ส่งกลับแก้ไข'];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($model->owner_name_snapshot . ' · ปีงบประมาณ ' . $model->fiscal_year . ' · Revision ' . $model->revision_no) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับไปเอกสาร', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?><?php $this->endBlock(); ?>

<?php if ($model->reviews): ?>
<section class="card bg-body border shadow-sm mb-3">
    <div class="card-header bg-body-tertiary py-3"><h2 class="h6 fw-semibold mb-0">ความคิดเห็นผู้แทนคุณภาพ</h2></div>
    <div class="list-group list-group-flush"><?php foreach ($model->reviews as $review): ?><article class="list-group-item p-3"><div class="d-flex flex-wrap justify-content-between gap-2"><strong><?= Html::encode($review->reviewer?->fullname() ?? ('บุคลากร #' . $review->reviewer_employee_id)) ?></strong><span class="badge bg-body-secondary text-body-secondary"><?= Html::encode($decisionLabels[$review->decision] ?? $review->decision) ?></span></div><?php if ($review->comment): ?><div class="mt-2"><?= nl2br(Html::encode($review->comment)) ?></div><?php endif; ?><?php if ($review->decided_at): ?><div class="small text-body-secondary mt-1"><?= Yii::$app->formatter->asDatetime($review->decided_at) ?></div><?php endif; ?></article><?php endforeach; ?></div>
</section>
<?php endif; ?>

<section class="card bg-body border shadow-sm">
    <div class="card-header bg-body-tertiary py-3"><h2 class="h6 fw-semibold mb-0">รายการดำเนินการ</h2></div>
    <div class="list-group list-group-flush">
    <?php foreach ($activities as $activity): $creator = $creatorMap[(int) $activity->created_by] ?? null; ?>
        <article class="list-group-item p-3"><div class="d-flex flex-column flex-md-row justify-content-between gap-2"><div><div class="fw-semibold"><?= Html::encode($actionLabels[$activity->action] ?? $activity->action) ?></div><?php if ($activity->message): ?><div class="small text-break mt-1"><?= nl2br(Html::encode($activity->message)) ?></div><?php endif; ?></div><div class="small text-body-secondary text-md-end flex-shrink-0"><?= Html::encode($creator?->fullname() ?? 'ระบบ') ?><br><?= Yii::$app->formatter->asDatetime($activity->created_at) ?></div></div></article>
    <?php endforeach; ?>
    <?php if (!$activities): ?><div class="p-5 text-center"><div class="fw-semibold">ยังไม่มีประวัติการดำเนินการ</div></div><?php endif; ?>
    </div>
    <?php if ($dataProvider->getTotalCount()): ?><div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div><?php endif; ?>
</section>
