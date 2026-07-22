<?php
use app\components\widgets\DataSummaryWidget;
use app\modules\medsop\assets\MedSopAsset;
use app\modules\medsop\models\Document;
use yii\helpers\Html;

MedSopAsset::register($this);
$this->title = 'คลังขั้นตอนปฏิบัติงานมาตรฐาน';
$models = $dataProvider->getModels();
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ค้นหา SOP และ WI ที่ใช้งานในโรงพยาบาลตามสิทธิ์ของคุณ<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= $this->render('_nav', ['access' => $access, 'active' => 'index']) ?><?php $this->endBlock(); ?>

<div>
    <?= $this->render('_search', ['searchModel' => $searchModel, 'filterOrganizations' => $filterOrganizations, 'filterCreators' => $filterCreators, 'categoryOptions' => $categoryOptions]) ?>

    <section aria-labelledby="medsop-list-title">
        <div class="d-flex align-items-end justify-content-between gap-3 mb-3">
            <div>
                <h2 id="medsop-list-title" class="h6 fw-semibold mb-1">รายการเอกสาร</h2>
                <p class="small text-body-secondary mb-0">พบ <?= number_format($dataProvider->getTotalCount()) ?> เอกสารที่คุณเปิดอ่านได้</p>
            </div>
            <?php if ($access->canCreate()): ?>
                <?= Html::a('<i class="bi bi-plus-circle me-sm-2" aria-hidden="true"></i><span class="d-none d-sm-inline">สร้างเอกสาร</span><span class="visually-hidden d-sm-none">สร้างเอกสาร</span>', ['/medsop/document/create'], [
                    'class' => 'btn btn-primary flex-shrink-0 medsop-primary-action',
                    'aria-label' => 'สร้างเอกสารใหม่',
                ]) ?>
            <?php endif; ?>
        </div>
        <?php if (!$models): ?>
            <div class="card-body text-center py-5" role="status">
                <h3 class="h5 fw-semibold">ไม่พบเอกสารตามเงื่อนไข</h3>
                <p class="text-body-secondary">ลองเปลี่ยนคำค้นหา ประเภท หรือสถานะ แล้วค้นหาอีกครั้ง</p>
                <?= Html::a('ล้างตัวกรอง', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        <?php else: ?>
            <ul class="medsop-catalog" role="list">
                <?php foreach ($models as $model):
                    $badge = Document::getStatusBadgeConfigFor($model->status);
                    $isReviewDue = $model->review_date && $model->review_date <= date('Y-m-d');
                    $creator = $creators[$model->created_emp_id] ?? null;
                    $cover = $model->getCoverUrl();
                    foreach ($model->steps as $documentStep) {
                        foreach ($documentStep->media as $stepMedia) {
                            if (!$cover && $stepMedia->media_type === 'image') { $cover = $stepMedia->file_path; break 2; }
                        }
                    }
                ?>
                    <li class="medsop-catalog-card position-relative">
                        <div class="medsop-catalog-card__cover<?= $cover ? ' has-image' : '' ?>"<?php if ($cover): ?> style="background-image:url('<?= Html::encode($cover) ?>')"<?php endif; ?>>
                            <div class="medsop-catalog-card__cover-overlay">
                                <span class="medsop-catalog-card__department"><?= Html::encode(isset($organizations[$model->organization_id]) ? $organizations[$model->organization_id]->name : 'ไม่ระบุแผนก') ?></span>
                                <h3 class="medsop-catalog-card__cover-title"><?= Html::encode($model->title) ?></h3>
                            </div>
                        </div>
                        <div class="medsop-catalog-card__body">
                            <div class="d-flex justify-content-between align-items-start gap-2"><div><span class="medsop-catalog-card__type me-2"><?= Html::encode($model->document_type) ?></span><strong class="medsop-code"><?= Html::encode($model->document_no) ?></strong></div><span class="<?= Html::encode($badge['class']) ?>"><?= Html::encode($badge['label']) ?></span></div>
                            <p class="medsop-catalog-card__meta mb-2"><?= Html::encode($model->category) ?><?php if ($creator): ?> · <?= Html::encode($creator->fullname()) ?><?php endif; ?></p>
                            <p class="medsop-catalog-card__objective"><?= Html::encode(mb_strimwidth($model->objective, 0, 120, '…', 'UTF-8')) ?></p>
                        </div>
                        <div class="medsop-catalog-card__footer"><div><span class="d-block">ปรับปรุง <?= Yii::$app->formatter->asDate($model->updated_at, 'medium') ?></span><?php if ($model->review_date): ?><strong class="d-block<?= $isReviewDue ? ' medsop-review-due' : '' ?>"><?= $isReviewDue ? 'ถึงกำหนดทบทวน ' : 'ทบทวน ' ?><?= Yii::$app->formatter->asDate($model->review_date, 'medium') ?></strong><?php endif; ?></div><?= Html::a('ศึกษาขั้นตอน <i class="bi bi-arrow-right" aria-hidden="true"></i>', ['view', 'id' => $model->id], ['class' => 'stretched-link medsop-card-link', 'aria-label' => 'ศึกษาขั้นตอน ' . $model->title]) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <footer class="mt-3 py-3 border-top"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></footer>
        <?php endif; ?>
    </section>
</div>
