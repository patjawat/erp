<?php

use app\components\AppHelper;
use app\modules\complaint\models\Complaint;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var Complaint[] $rows */
/** @var yii\data\Pagination $pages */
/** @var int $count */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var array<int,string> $types */
/** @var array<int,array{id:int,name:string}> $units */
/** @var array $filters */

$this->title = 'ทะเบียนเรื่องร้องเรียน';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ปีงบ <?= $fiscalYear ?> · พบ <?= number_format($count) ?> เรื่อง<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-semibold mb-0"><i class="bi bi-megaphone me-1"></i> ทะเบียนเรื่องร้องเรียน</h1>
        <?= Html::a('<i class="bi bi-plus-lg me-1"></i> รับเรื่องใหม่', ['create', 'fy' => $fiscalYear], ['class' => 'btn btn-primary btn-sm']) ?>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/complaint/menu', ['active' => 'registry']) ?></div>

    <div class="card border shadow-sm mb-3">
        <div class="card-body">
            <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">ปีงบ</label>
                    <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">สถานะ</label>
                    <?= Html::dropDownList('status', $filters['status'], ['' => '— ทั้งหมด —'] + Complaint::statusLabels(), ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">ประเภท</label>
                    <?= Html::dropDownList('type_id', $filters['type_id'], ['' => '— ทั้งหมด —'] + $types, ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">ระดับ</label>
                    <?= Html::dropDownList('level', $filters['level'], ['' => '— ทั้งหมด —'] + Complaint::severityLabels(), ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">หน่วยงาน</label>
                    <?= Html::dropDownList('unit_id', $filters['unit_id'], ['' => '— ทั้งหมด —'] + \yii\helpers\ArrayHelper::map($units, 'id', 'name'), ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small mb-1">ค้นหา</label>
                    <div class="input-group input-group-sm">
                        <?= Html::textInput('q', $filters['q'], ['class' => 'form-control', 'placeholder' => 'เลขที่/ชื่อเรื่อง/ผู้ร้อง']) ?>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="card border shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:130px">เลขที่</th>
                        <th>เรื่อง / ผู้ร้อง</th>
                        <th style="width:120px">ประเภท</th>
                        <th style="width:90px" class="text-center">ระดับ</th>
                        <th style="width:150px">หน่วยงาน</th>
                        <th style="width:110px">วันร้องเรียน</th>
                        <th style="width:120px" class="text-center">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="7" class="text-center text-body-secondary py-4">— ไม่พบข้อมูล —</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $c): ?>
                        <?php
                        $overdue = !$c->isFinal() && $c->close_due && $c->close_due < date('Y-m-d');
                        ?>
                        <tr>
                            <td>
                                <?= Html::a(Html::encode($c->complaint_no), ['view', 'id' => $c->id], ['class' => 'fw-semibold text-decoration-none']) ?>
                                <div class="small text-body-secondary"><code><?= Html::encode($c->tracking_code) ?></code></div>
                            </td>
                            <td>
                                <?= Html::a(Html::encode($c->title), ['view', 'id' => $c->id], ['class' => 'text-decoration-none']) ?>
                                <div class="small text-body-secondary">
                                    <?php if ($c->is_anonymous): ?><i class="bi bi-incognito"></i> ไม่ประสงค์ออกนาม<?php else: ?><?= Html::encode($c->reporter_name ?: '—') ?><?php endif; ?>
                                    <?php if ($c->channel): ?> · <i class="bi bi-signpost"></i> <?= Html::encode($c->channel->name) ?><?php endif; ?>
                                </div>
                            </td>
                            <td class="small"><?= $c->type ? Html::encode($c->type->name) : '—' ?></td>
                            <td class="text-center">
                                <?php if ($c->severity_level): ?>
                                    <span class="badge text-bg-<?= $c->severity_level >= 3 ? 'danger' : ($c->severity_level == 2 ? 'warning' : 'secondary') ?>">ระดับ <?= (int) $c->severity_level ?></span>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td class="small"><?= $c->assignedUnit ? Html::encode($c->assignedUnit->name) : '—' ?></td>
                            <td class="small">
                                <?= $c->complaint_date ? AppHelper::convertToThai($c->complaint_date) : '—' ?>
                                <?php if ($overdue): ?><span class="badge text-bg-danger ms-1" title="เกินกำหนดปิดเคส"><i class="bi bi-alarm"></i></span><?php endif; ?>
                            </td>
                            <td class="text-center"><span class="badge text-bg-<?= Complaint::statusColor($c->status) ?>"><?= Html::encode($c->statusLabel()) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pages->pageCount > 1): ?>
        <div class="mt-3 d-flex justify-content-center">
            <?= LinkPager::widget(['pagination' => $pages, 'options' => ['class' => 'pagination pagination-sm mb-0']]) ?>
        </div>
    <?php endif; ?>
</div>
