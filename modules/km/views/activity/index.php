<?php

use app\components\AppHelper;
use app\modules\km\models\KmCategory;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\km\models\KmActivity[] $activities */
/** @var yii\data\Pagination $pages */
/** @var int $count */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var app\modules\km\models\KmCategory[] $categories */
/** @var array<int,array{id:int,name:string}> $units */
/** @var array $filters */

$this->title = 'ทะเบียนกิจกรรม KM';

$statusTone = ['draft' => 'secondary', 'published' => 'success'];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>คลังกิจกรรมและหลักฐานการดำเนินงาน ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<style>
.km-card{height:100%;transition:box-shadow .15s}
.km-card:hover{box-shadow:0 .5rem 1rem rgba(0,0,0,.1)!important}
.km-card__cover{aspect-ratio:16/9;background:#eef1f6;display:grid;place-items:center;border-radius:.5rem .5rem 0 0;color:#9aa4b2;overflow:hidden}
.km-card__cover img{width:100%;height:100%;object-fit:cover}
.km-card__title{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
</style>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-semibold mb-0"><i class="bi bi-collection me-1"></i> ทะเบียนกิจกรรม</h1>
            <div class="text-body-secondary small">พบ <?= number_format($count) ?> กิจกรรม ในปีงบ <?= $fiscalYear ?></div>
        </div>
        <?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มกิจกรรม', ['create', 'fy' => $fiscalYear], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/km/menu', ['active' => 'activity']) ?></div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- ตัวกรอง -->
    <?= Html::beginForm(['index'], 'get', ['class' => 'card border shadow-sm mb-3']) ?>
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">ปีงบ</label>
                    <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">หมวดหมู่</label>
                    <?= Select2::widget([
                        'name' => 'category_id',
                        'value' => $filters['category_id'],
                        'data' => KmCategory::dropdownMap(),
                        'options' => ['placeholder' => 'ทั้งหมด'],
                        'size' => Select2::SMALL,
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">หน่วยงานเจ้าภาพ</label>
                    <?= Select2::widget([
                        'name' => 'unit_id',
                        'value' => $filters['unit_id'],
                        'data' => ArrayHelper::map($units, 'id', 'name'),
                        'options' => ['placeholder' => 'ทั้งหมด'],
                        'size' => Select2::SMALL,
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">สถานะ</label>
                    <?= Html::dropDownList('status', $filters['status'], ['' => 'ทั้งหมด', 'draft' => 'ฉบับร่าง', 'published' => 'เผยแพร่แล้ว'], ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">ค้นหา</label>
                    <?= Html::textInput('q', $filters['q'], ['class' => 'form-control form-control-sm', 'placeholder' => 'ชื่อ/สถานที่']) ?>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>กรอง</button>
                <?= Html::a('ล้าง', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            </div>
        </div>
    <?= Html::endForm() ?>

    <?php if (!$activities): ?>
        <div class="text-center text-body-secondary py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            ยังไม่มีกิจกรรมตามเงื่อนไขที่เลือก
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($activities as $a): ?>
                <div class="col-6 col-lg-4 col-xxl-3">
                    <div class="card border shadow-sm km-card">
                        <a href="<?= Url::to(['view', 'id' => $a->id]) ?>" class="text-decoration-none">
                            <div class="km-card__cover">
                                <?php if ($a->coverPhoto): ?>
                                    <img src="<?= Url::to(['photo', 'id' => $a->coverPhoto->id, 'thumb' => 1]) ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <i class="bi bi-image fs-1"></i>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="card-body">
                            <div class="d-flex gap-1 mb-1 flex-wrap">
                                <?php if ($a->category): ?>
                                    <span class="badge text-bg-light border"><?= Html::encode($a->category->name) ?></span>
                                <?php endif; ?>
                                <span class="badge text-bg-<?= $statusTone[$a->status] ?? 'secondary' ?>"><?= Html::encode($a->statusLabel()) ?></span>
                            </div>
                            <a href="<?= Url::to(['view', 'id' => $a->id]) ?>" class="text-decoration-none text-body">
                                <div class="fw-semibold km-card__title mb-1"><?= Html::encode($a->title) ?></div>
                            </a>
                            <div class="small text-body-secondary">
                                <?php if ($a->activity_date): ?>
                                    <i class="bi bi-calendar3 me-1"></i><?= AppHelper::convertToThai($a->activity_date) ?><br>
                                <?php endif; ?>
                                <?php if ($a->ownerUnit): ?>
                                    <i class="bi bi-building me-1"></i><?= Html::encode($a->ownerUnit->name) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            <?= LinkPager::widget(['pagination' => $pages, 'options' => ['class' => 'pagination pagination-sm']]) ?>
        </div>
    <?php endif; ?>
</div>
