<?php

use app\components\AppHelper;
use app\modules\flowchart\models\Flowchart;
use app\modules\settings\models\OrgUnit;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\flowchart\models\FlowchartSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'คลังผังกระบวนการ';
$items = $dataProvider->getModels();
$unitGroups = OrgUnit::groupedForSelect((int) AppHelper::YearBudget());
?>
<?php $this->beginBlock('page-title'); ?>ผังกระบวนการ<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ป้อนขั้นตอนการทำงาน แล้วสร้างผังและเอกสารให้อัตโนมัติ<?php $this->endBlock(); ?>

<div class="fc-index">

    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex flex-wrap gap-2 align-items-center']) ?>
            <div class="input-group input-group-sm" style="width: 260px;">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <?= Html::textInput('q', $searchModel->q, ['class' => 'form-control', 'placeholder' => 'ค้นหาชื่อ / รหัส / คำอธิบาย']) ?>
            </div>
            <?= Html::dropDownList('category', $searchModel->category, array_merge(
                ['' => 'ทุกประเภท'],
                Flowchart::CATEGORY_LABELS
            ), ['class' => 'form-select form-select-sm', 'style' => 'width:auto;', 'onchange' => 'this.form.submit()']) ?>
            <?= Html::dropDownList('status', $searchModel->status, [
                '' => 'ทุกสถานะ',
                Flowchart::STATUS_DRAFT => 'ฉบับร่าง',
                Flowchart::STATUS_PUBLISHED => 'เผยแพร่แล้ว',
            ], ['class' => 'form-select form-select-sm', 'style' => 'width:auto;', 'onchange' => 'this.form.submit()']) ?>
            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">กรอง</button>
        <?= Html::endForm() ?>

        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#fcCreateModal">
            <i class="bi bi-plus-lg me-1"></i> สร้างผังใหม่
        </button>
    </div>

    <?php if (empty($items)): ?>
        <div class="text-center text-muted py-5 border rounded-3 bg-body-tertiary">
            <i class="bi bi-diagram-3 d-block mb-2" style="font-size:2.5rem;"></i>
            <p class="mb-1">ยังไม่มีผังกระบวนการ</p>
            <p class="small mb-3">เริ่มด้วยการสร้างผังแรก แล้วป้อนขั้นตอนทีละขั้น ระบบจะวาดผังและตารางเอกสารให้เอง</p>
            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#fcCreateModal">
                <i class="bi bi-plus-lg me-1"></i> สร้างผังแรก
            </button>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($items as $fc): /** @var Flowchart $fc */
                $count = $fc->stepCount();
                $statusTone = $fc->isPublished() ? 'success' : 'secondary';
            ?>
            <div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
                <div class="card h-100 shadow-sm border-0 fc-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill bg-<?= $statusTone ?>-subtle text-<?= $statusTone ?>-emphasis"><?= Html::encode($fc->statusLabel()) ?></span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><?= Html::a('<i class="bi bi-eye me-2"></i>ดูผัง', ['view', 'id' => $fc->id], ['class' => 'dropdown-item']) ?></li>
                                    <li><?= Html::a('<i class="bi bi-pencil-square me-2"></i>แก้ไขขั้นตอน', ['update', 'id' => $fc->id], ['class' => 'dropdown-item']) ?></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <?= Html::a('<i class="bi bi-trash me-2"></i>ลบ', ['delete', 'id' => $fc->id], [
                                            'class' => 'dropdown-item text-danger',
                                            'data' => ['method' => 'post', 'confirm' => 'ยืนยันการลบผัง "' . $fc->title . '" ? ขั้นตอนทั้งหมดจะถูกลบ'],
                                        ]) ?>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <a href="<?= Url::to(['view', 'id' => $fc->id]) ?>" class="text-decoration-none text-body">
                            <h6 class="fw-semibold mb-1"><?= Html::encode($fc->title) ?></h6>
                        </a>
                        <div class="small text-muted mb-2"><?= Html::encode($fc->code ?: '—') ?><?= $fc->categoryLabel() ? ' · ' . Html::encode($fc->categoryLabel()) : '' ?></div>
                        <?php if ($fc->unitName() !== ''): ?>
                            <div class="small text-muted mb-2"><i class="bi bi-diagram-2 me-1"></i><?= Html::encode($fc->unitName()) ?></div>
                        <?php endif; ?>
                        <p class="small text-muted flex-grow-1 mb-2" style="min-height:2.4em;">
                            <?= Html::encode(mb_substr((string) $fc->description, 0, 90)) ?><?= mb_strlen((string) $fc->description) > 90 ? '…' : '' ?>
                        </p>

                        <div class="d-flex align-items-center justify-content-between small text-muted border-top pt-2">
                            <span><i class="bi bi-list-ol me-1"></i><?= $count ?> ขั้นตอน</span>
                            <span><i class="bi bi-calendar3 me-1"></i>ปีงบ <?= $fc->budget_year ?: '-' ?></span>
                        </div>
                    </div>
                    <a href="<?= Url::to(['view', 'id' => $fc->id]) ?>" class="card-footer bg-body border-0 text-primary small fw-semibold text-decoration-none">
                        เปิดดูผัง <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'pagination pagination-sm mb-0'],
                'linkContainerOptions' => ['class' => 'page-item'],
                'linkOptions' => ['class' => 'page-link'],
                'disabledListItemSubTagOptions' => ['tag' => 'span', 'class' => 'page-link'],
            ]) ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: สร้างผังใหม่ -->
<div class="modal fade" id="fcCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <?= Html::beginForm(['create'], 'post', ['class' => 'modal-content']) ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-diagram-3 me-2"></i>สร้างผังกระบวนการใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">ชื่อกระบวนการ <span class="text-danger">*</span></label>
                    <?= Html::textInput('Flowchart[title]', '', ['class' => 'form-control', 'required' => true, 'placeholder' => 'เช่น ขั้นตอนการรับคำขอและอนุมัติ', 'maxlength' => 255]) ?>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">หน่วยงาน / กลุ่มงาน / ทีมประสาน</label>
                    <?= Html::dropDownList('Flowchart[org_unit_id]', null, $unitGroups, [
                        'class' => 'form-select',
                        'prompt' => '— เลือกหน่วยงาน (เว้นว่างได้) —',
                    ]) ?>
                    <div class="form-text">อักษรย่อของหน่วยจะเป็นรหัสนำ เช่น <span class="fw-semibold">EMR-<?= (int) AppHelper::YearBudget() ?>-0001</span></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">ประเภท</label>
                    <?= Html::dropDownList('Flowchart[category]', 'sop', Flowchart::CATEGORY_LABELS, ['class' => 'form-select']) ?>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold small">คำอธิบาย/วัตถุประสงค์</label>
                    <?= Html::textarea('Flowchart[description]', '', ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'อธิบายสั้น ๆ ว่ากระบวนการนี้ทำเพื่ออะไร (ไม่บังคับ)']) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>สร้างและป้อนขั้นตอน</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>

<style>
.fc-card { transition: transform .12s ease, box-shadow .12s ease; }
.fc-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
</style>
