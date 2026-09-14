<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\km\models\KmActivity $activity */
/** @var bool $canManage */

$this->title = $activity->title;
$statusTone = ['draft' => 'secondary', 'published' => 'success'];
$time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '') . ($activity->end_time ? ' - ' . substr($activity->end_time, 0, 5) : ''));
?>
<?php $this->beginBlock('page-title'); ?>รายละเอียดกิจกรรม<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>คลังกิจกรรม KM<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/km/menu', ['active' => 'activity']) ?></div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับทะเบียน', ['index', 'fy' => $activity->fiscal_year], ['class' => 'btn btn-sm btn-outline-secondary mb-2']) ?>
            <h1 class="h4 fw-semibold mb-1"><?= Html::encode($activity->title) ?></h1>
            <div class="d-flex gap-1 flex-wrap">
                <?php if ($activity->category): ?><span class="badge text-bg-light border"><?= Html::encode($activity->category->name) ?></span><?php endif; ?>
                <span class="badge text-bg-<?= $statusTone[$activity->status] ?? 'secondary' ?>"><?= Html::encode($activity->statusLabel()) ?></span>
                <span class="badge text-bg-light border">ปีงบ <?= $activity->fiscal_year ?></span>
            </div>
        </div>
        <?php if ($canManage): ?>
            <div class="d-flex gap-2">
                <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $activity->id], ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::beginForm(['delete', 'id' => $activity->id], 'post', ['onsubmit' => "return confirm('ยืนยันลบกิจกรรมนี้?');", 'class' => 'd-inline']) ?>
                    <?= Html::submitButton('<i class="bi bi-trash me-1"></i> ลบ', ['class' => 'btn btn-sm btn-outline-danger']) ?>
                <?= Html::endForm() ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border shadow-sm mb-3">
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-3 text-body-secondary">หน่วยงานเจ้าภาพ</dt>
                        <dd class="col-sm-9"><?= $activity->ownerUnit ? Html::encode($activity->ownerUnit->name) : '—' ?></dd>
                        <dt class="col-sm-3 text-body-secondary">วันที่จัด</dt>
                        <dd class="col-sm-9"><?= $activity->activity_date ? AppHelper::convertToThai($activity->activity_date) : '—' ?><?= $time ? ' &nbsp; เวลา ' . Html::encode($time) . ' น.' : '' ?></dd>
                        <dt class="col-sm-3 text-body-secondary">สถานที่</dt>
                        <dd class="col-sm-9"><?= $activity->location ? Html::encode($activity->location) : '—' ?></dd>
                    </dl>
                </div>
            </div>

            <?php if ($activity->summary || $activity->objective || $activity->detail): ?>
            <div class="card border shadow-sm mb-3">
                <div class="card-body">
                    <?php if ($activity->summary): ?>
                        <h2 class="h6 fw-semibold">สรุปย่อ</h2>
                        <div class="km-richtext text-body-secondary"><?= \app\modules\km\components\RichText::render($activity->summary) ?></div>
                    <?php endif; ?>
                    <?php if ($activity->objective): ?>
                        <h2 class="h6 fw-semibold">วัตถุประสงค์</h2>
                        <div class="km-richtext text-body-secondary"><?= \app\modules\km\components\RichText::render($activity->objective) ?></div>
                    <?php endif; ?>
                    <?php if ($activity->detail): ?>
                        <h2 class="h6 fw-semibold">รายละเอียด / ถอดบทเรียน</h2>
                        <div class="km-richtext text-body-secondary mb-0"><?= \app\modules\km\components\RichText::render($activity->detail) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- คลังภาพ -->
            <?= $this->render('_photos', ['activity' => $activity, 'canManage' => $canManage]) ?>
        </div>

        <div class="col-lg-4">
            <!-- ลิงก์หลักฐาน -->
            <?= $this->render('_links', ['activity' => $activity, 'canManage' => $canManage]) ?>
        </div>
    </div>
</div>
