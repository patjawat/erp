<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var string $level */
/** @var array $levels */
/** @var string|null $q */
/** @var array $rows */
/** @var yii\data\Pagination $pages */
/** @var int $count */
/** @var app\modules\am\models\DepreciationProfile[] $profiles */

$this->title = 'ผูกเกณฑ์ค่าเสื่อมกับลำดับชั้นทรัพย์สิน';
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="link"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $cls): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $cls ?>"><?= Yii::$app->session->getFlash($flash) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="alert alert-info small">
        <i data-lucide="info"></i> ทรัพย์สินจะใช้เกณฑ์จากระดับที่เฉพาะเจาะจงที่สุด: <b>รายชิ้น → รายการ → หมวด → ประเภทหลัก</b> (ตั้งค่ารายชิ้นได้ที่เมนู "เปลี่ยนเกณฑ์ทรัพย์สิน")
    </div>

    <ul class="nav nav-pills mb-3">
        <?php foreach ($levels as $lv => $label): ?>
            <li class="nav-item">
                <?= Html::a($label, ['index', 'level' => $lv], ['class' => 'nav-link ' . ($level === $lv ? 'active' : '')]) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="card mb-3"><div class="card-body">
        <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <?= Html::hiddenInput('level', $level) ?>
            <?php if ($level === 'asset_category' && !empty($typeOptions)): ?>
            <div class="col-md-3">
                <label class="form-label">ประเภทหลัก</label>
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">— ทุกประเภท —</option>
                    <?php foreach ($typeOptions as $tcode => $ttitle): ?>
                        <option value="<?= Html::encode($tcode) ?>" <?= (string) ($type ?? '') === (string) $tcode ? 'selected' : '' ?>><?= Html::encode($ttitle) ?> (<?= Html::encode($tcode) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-4">
                <label class="form-label">ค้นหา (ชื่อ/รหัส)</label>
                <input type="text" name="q" class="form-control" value="<?= Html::encode($q) ?>">
            </div>
            <div class="col-md-2"><?= Html::submitButton('<i data-lucide="search"></i> ค้นหา', ['class' => 'btn btn-outline-primary']) ?></div>
            <div class="col text-end text-muted small">พบ <?= $count ?> รายการ (<?= $levels[$level] ?>)</div>
        <?= Html::endForm() ?>
    </div></div>

    <div class="card"><div class="card-body">
        <?php $showParentType = ($level === 'asset_category'); ?>
        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr><th>รหัส</th><th>ชื่อ</th><?php if ($showParentType): ?><th>ประเภทหลัก</th><?php endif; ?><th>เกณฑ์ที่ผูก</th><th style="width:360px">กำหนดเกณฑ์</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="<?= $showParentType ? 5 : 4 ?>" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?= Html::encode($r['code']) ?></td>
                        <td><?= Html::encode($r['title']) ?></td>
                        <?php if ($showParentType): ?>
                            <td>
                                <?php if (!empty($r['parent_type_name'])): ?>
                                    <span class="badge bg-light text-dark border"><i data-lucide="corner-down-right" style="width:.8rem;height:.8rem;"></i> <?= Html::encode($r['parent_type_name']) ?></span>
                                <?php elseif (!empty($r['parent_type_code'])): ?>
                                    <span class="text-muted small"><?= Html::encode($r['parent_type_code']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            <?php if ($r['bound_profile_id']): ?>
                                <span class="badge bg-success"><?= Html::encode($r['bound_profile_name']) ?></span>
                            <?php else: ?>
                                <span class="text-muted small">— ไม่ได้ผูก —</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= Html::beginForm(['set'], 'post', ['class' => 'd-flex gap-1']) ?>
                                <?= Html::hiddenInput('id', $r['id']) ?>
                                <?= Html::hiddenInput('level', $level) ?>
                                <?= Html::hiddenInput('q', $q) ?>
                                <?= Html::hiddenInput('type', $type ?? '') ?>
                                <select name="profile_id" class="form-select form-select-sm">
                                    <option value="">— ไม่ผูก —</option>
                                    <?php foreach ($profiles as $p): ?>
                                        <option value="<?= $p->id ?>" <?= $r['bound_profile_id'] == $p->id ? 'selected' : '' ?>>
                                            <?= Html::encode($p->code . ' — ' . $p->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?= Html::submitButton('<i data-lucide="save"></i>', ['class' => 'btn btn-sm btn-primary']) ?>
                            <?= Html::endForm() ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        <?= LinkPager::widget(['pagination' => $pages]) ?>
    </div></div>
</div>
