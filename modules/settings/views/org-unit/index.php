<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\widgets\Select2;
use app\modules\settings\models\OrgUnit;

/** @var yii\web\View $this */
/** @var int $year */
/** @var string $type */
/** @var string $source */
/** @var string $q */
/** @var OrgUnit[] $rows */
/** @var int[] $years */
/** @var array $types */
/** @var array $employees */
/** @var array $typeCounts */
/** @var array $srcCounts */
/** @var array $levels */

$this->title = 'ทะเบียนหน่วยงาน';
$totalYear = array_sum($srcCounts);
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/settings/views/menu', ['active' => 'org-unit']) ?>
<?php $this->endBlock(); ?>

<?php foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls): ?>
    <?php if (Yii::$app->session->hasFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Yii::$app->session->getFlash($key) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-3 align-items-end justify-content-between">
            <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex flex-wrap gap-2 align-items-end', 'id' => 'ou-filter']) ?>
                <div>
                    <label class="form-label small mb-1">ปีงบประมาณ</label>
                    <?= Select2::widget([
                        'name' => 'thai_year',
                        'value' => $year,
                        'data' => array_combine($years, $years),
                        'options' => ['id' => 'ou-year'],
                        'pluginOptions' => ['allowClear' => false],
                        'pluginEvents' => ['change' => 'function(){ document.getElementById("ou-filter").submit(); }'],
                    ]) ?>
                </div>
                <div>
                    <label class="form-label small mb-1">ประเภท</label>
                    <select name="unit_type" class="form-select form-select-sm" onchange="document.getElementById('ou-filter').submit()">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($types as $code => $title): ?>
                            <option value="<?= Html::encode($code) ?>" <?= $type === $code ? 'selected' : '' ?>><?= Html::encode($title) ?> (<?= (int) ($typeCounts[$code] ?? 0) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">แหล่งที่มา</label>
                    <select name="source" class="form-select form-select-sm" onchange="document.getElementById('ou-filter').submit()">
                        <option value="">ทั้งหมด</option>
                        <option value="structure" <?= $source === 'structure' ? 'selected' : '' ?>>โครงสร้าง (<?= (int) ($srcCounts['structure'] ?? 0) ?>)</option>
                        <option value="manual" <?= $source === 'manual' ? 'selected' : '' ?>>เพิ่มเอง (<?= (int) ($srcCounts['manual'] ?? 0) ?>)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">ค้นหา</label>
                    <input type="search" name="q" value="<?= Html::encode($q) ?>" class="form-control form-control-sm" placeholder="ชื่อ/อักษรย่อ" style="min-width:180px">
                </div>
                <button class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
                <?php if ($type || $source || $q): ?>
                    <?= Html::a('ล้าง', ['index', 'thai_year' => $year], ['class' => 'btn btn-sm btn-link']) ?>
                <?php endif; ?>
            <?= Html::endForm() ?>

            <div class="d-flex gap-2">
                <?= Html::beginForm(['sync'], 'post', ['onsubmit' => 'return confirm("ซิงก์หน่วยงานจากผังโครงสร้างเข้าปี ' . $year . '?\nจะดึงหน่วยใหม่ + อัปเดตชื่อ/หัวหน้าจากผัง (ไม่ลบข้อมูลเดิม)")']) ?>
                    <?= Html::hiddenInput('thai_year', $year) ?>
                    <?= Html::submitButton('<i class="fa-solid fa-arrows-rotate me-1"></i> ซิงก์จากผัง', ['class' => 'btn btn-sm btn-outline-primary']) ?>
                <?= Html::endForm() ?>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#type-modal"><i class="fa-solid fa-tags me-1"></i> ประเภท</button>
                <button class="btn btn-sm btn-success" type="button" data-bs-toggle="collapse" data-bs-target="#add-manual"><i class="fa-solid fa-plus me-1"></i> เพิ่มหน่วยงานภายใน</button>
            </div>
        </div>

        <!-- เพิ่มหน่วยงานภายใน -->
        <div class="collapse mt-3" id="add-manual">
            <?= Html::beginForm(['add'], 'post', ['class' => 'row g-2 align-items-end border rounded p-3 bg-body-tertiary']) ?>
                <?= Html::hiddenInput('thai_year', $year) ?>
                <div class="col-md-4">
                    <label class="form-label small mb-1">ชื่อหน่วยงาน <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-sm" required placeholder="เช่น สสอ.ด่านซ้าย / ทีม IC">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">ประเภท</label>
                    <select name="unit_type" class="form-select form-select-sm">
                        <?php foreach ($types as $code => $title): ?>
                            <option value="<?= Html::encode($code) ?>" <?= $code === OrgUnit::TYPE_TEAM ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">อักษรย่อ</label>
                    <input type="text" name="code" class="form-control form-control-sm text-uppercase" maxlength="20" placeholder="เช่น SSO">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">หัวหน้า/ผู้รับผิดชอบ</label>
                    <?= Select2::widget([
                        'name' => 'leader_emp_id',
                        'data' => $employees,
                        'options' => ['placeholder' => 'เลือกบุคลากรภายใน'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                </div>
                <div class="col-md-1">
                    <?= Html::submitButton('<i class="fa-solid fa-check"></i>', ['class' => 'btn btn-sm btn-success w-100']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<?= Html::beginForm(['save'], 'post') ?>
<?= Html::hiddenInput('thai_year', $year) ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">หน่วยงานปี <?= $year ?> <span class="text-muted fw-normal">(<?= count($rows) ?><?= ($type || $source || $q) ? ' จาก ' . $totalYear : '' ?> หน่วย)</span></span>
        <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึกทั้งหมด', ['class' => 'btn btn-sm btn-primary']) ?>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="min-width:220px">ชื่อหน่วยงาน</th>
                    <th style="width:90px">แหล่ง</th>
                    <th style="width:150px">ประเภท</th>
                    <th style="width:130px">อักษรย่อ</th>
                    <th style="min-width:200px">หัวหน้า/ผู้รับผิดชอบ</th>
                    <th style="width:80px" class="text-center">เปิดใช้</th>
                    <th style="width:50px"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">ไม่มีข้อมูล — กด <strong>ซิงก์จากผัง</strong> เพื่อดึงหน่วยงานปีนี้</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): $manual = $r->source === OrgUnit::SOURCE_MANUAL; ?>
                    <tr>
                        <td>
                            <?php if ($manual): ?>
                                <input type="text" name="rows[<?= $r->id ?>][name]" value="<?= Html::encode($r->name) ?>" class="form-control form-control-sm">
                            <?php else: $lvl = (int) ($levels[$r->ref_id] ?? 1); ?>
                                <span class="fw-medium" style="padding-inline-start:<?= max(0, $lvl - 1) * 1.4 ?>rem">
                                    <?php if ($lvl > 1): ?><i class="fa-solid fa-turn-up fa-rotate-90 text-muted small me-1"></i><?php endif; ?>
                                    <?= Html::encode($r->name) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($manual): ?>
                                <span class="badge text-bg-info">เพิ่มเอง</span>
                            <?php else: ?>
                                <span class="badge text-bg-light border">โครงสร้าง</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <select name="rows[<?= $r->id ?>][unit_type]" class="form-select form-select-sm">
                                <option value="">—</option>
                                <?php foreach ($types as $code => $title): ?>
                                    <option value="<?= Html::encode($code) ?>" <?= $r->unit_type === $code ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="rows[<?= $r->id ?>][code]" value="<?= Html::encode((string) $r->code) ?>" class="form-control form-control-sm text-uppercase" maxlength="20" placeholder="—">
                        </td>
                        <td>
                            <?php if ($manual): ?>
                                <?= Select2::widget([
                                    'name' => "rows[{$r->id}][leader_emp_id]",
                                    'value' => $r->leader_emp_id,
                                    'data' => $employees,
                                    'options' => ['placeholder' => 'เลือกบุคลากร'],
                                    'pluginOptions' => ['allowClear' => true],
                                ]) ?>
                            <?php else: ?>
                                <span class="text-body-secondary"><?= $r->leader_emp_id ? Html::encode($employees[$r->leader_emp_id] ?? ('#' . $r->leader_emp_id)) : '<span class="text-muted">— ตามผัง —</span>' ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?= Html::hiddenInput("rows[{$r->id}][active]", 0) ?>
                            <div class="form-check form-switch d-inline-block">
                                <?= Html::checkbox("rows[{$r->id}][active]", (bool) $r->active, ['value' => 1, 'class' => 'form-check-input']) ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($manual): ?>
                                <?= Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $r->id], [
                                    'class' => 'btn btn-sm btn-outline-danger border-0',
                                    'data-method' => 'post',
                                    'data-confirm' => 'ลบหน่วยงาน "' . Html::encode($r->name) . '"?',
                                ]) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($rows): ?>
        <div class="card-footer d-flex justify-content-end">
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึกทั้งหมด', ['class' => 'btn btn-sm btn-primary']) ?>
        </div>
    <?php endif; ?>
</div>
<?= Html::endForm() ?>

<!-- Modal: จัดการประเภทหน่วยงาน -->
<div class="modal fade" id="type-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-tags me-2"></i>จัดการประเภทหน่วยงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">ตั้งประเภทได้เองตามบริบทของโรงพยาบาล เช่น หน่วยงาน / ทีมประสาน / เครือข่าย / อำเภอ ฯลฯ</p>

                <?= Html::beginForm(['type-add'], 'post', ['class' => 'input-group input-group-sm mb-3']) ?>
                    <?= Html::hiddenInput('thai_year', $year) ?>
                    <input type="text" name="title" class="form-control" placeholder="ชื่อประเภทใหม่" required>
                    <button class="btn btn-success"><i class="fa-solid fa-plus me-1"></i> เพิ่ม</button>
                <?= Html::endForm() ?>

                <?= Html::beginForm(['type-save'], 'post') ?>
                    <?= Html::hiddenInput('thai_year', $year) ?>
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:70px">ลำดับ</th>
                                <th>ชื่อประเภท</th>
                                <th class="text-center" style="width:70px">ใช้งาน</th>
                                <th style="width:90px">ใช้อยู่</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($manageTypes as $t): $used = (int) ($typeCounts[$t->code] ?? 0); ?>
                                <tr>
                                    <td><input type="number" name="types[<?= $t->id ?>][sort]" value="<?= (int) $t->sort ?>" class="form-control form-control-sm" style="width:60px"></td>
                                    <td><input type="text" name="types[<?= $t->id ?>][title]" value="<?= Html::encode($t->title) ?>" class="form-control form-control-sm"></td>
                                    <td class="text-center">
                                        <?= Html::hiddenInput("types[{$t->id}][active]", 0) ?>
                                        <input type="checkbox" name="types[<?= $t->id ?>][active]" value="1" class="form-check-input" <?= $t->active ? 'checked' : '' ?>>
                                    </td>
                                    <td><span class="badge text-bg-light border"><?= $used ?> หน่วย</span></td>
                                    <td class="text-center">
                                        <?php if (!$used): ?>
                                            <?= Html::a('<i class="fa-solid fa-trash"></i>', ['type-delete', 'id' => $t->id], [
                                                'class' => 'btn btn-sm btn-outline-danger border-0',
                                                'data-method' => 'post',
                                                'data-confirm' => 'ลบประเภท "' . Html::encode($t->title) . '"?',
                                            ]) ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="text-end">
                        <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึกประเภท', ['class' => 'btn btn-sm btn-primary']) ?>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>
