<?php

use app\modules\laundry\models\LaundryExternalSource;
use yii\helpers\Html;

/** @var LaundryExternalSource[] $sources */
/** @var array $usage  external_source_id => [times, pieces] */
$this->title = 'ตั้งค่า — หน่วยงานภายนอก';
$canManage = Yii::$app->user->can('laundry.manage');
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'setting']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-truck me-2"></i>หน่วยงานภายนอก</h1>
        <div class="text-body-secondary small">หน่วยงานที่ส่งผ้ามาให้ (เช่น รพ.เลย ส่งผ้ากลับหลัง Refer) — ใช้ในหน้า นับ–รีด–QC · พิมพ์ชื่อใหม่ในฟอร์มก็เพิ่มให้อัตโนมัติ</div>
    </div>

    <?php foreach (['error' => 'danger', 'success' => 'success'] as $k => $c): ?>
        <?php if (Yii::$app->session->hasFlash($k)): ?><div class="alert alert-<?= $c ?> d-flex align-items-center"><i class="bi bi-<?= $c === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($k)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>เพิ่มหน่วยงานภายนอก</h2></div>
                <div class="card-body">
                    <?php if ($canManage): ?>
                        <?= Html::beginForm(['external-save'], 'post', ['class' => 'row g-3']) ?>
                            <div class="col-12">
                                <label class="form-label">ชื่อหน่วยงาน</label>
                                <?= Html::textInput('name', '', ['class' => 'form-control', 'maxlength' => 150, 'placeholder' => 'เช่น โรงพยาบาลเลย', 'required' => true]) ?>
                            </div>
                            <div class="col-6">
                                <label class="form-label">ลำดับ</label>
                                <?= Html::input('number', 'sort_order', count($sources) + 1, ['class' => 'form-control', 'min' => 0]) ?>
                            </div>
                            <div class="col-12">
                                <?= Html::submitButton('<i class="bi bi-save me-1"></i>เพิ่ม', ['class' => 'btn btn-primary']) ?>
                            </div>
                        <?= Html::endForm() ?>
                    <?php else: ?>
                        <div class="text-body-secondary small">ดูอย่างเดียว</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>หน่วยงานภายนอก (<?= count($sources) ?>)</h2></div>
                <div class="card-body p-0"><div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">ลำดับ / ชื่อหน่วยงาน</th><th class="text-end">รับผ้า</th><th>สถานะ</th><th class="pe-4 text-end">จัดการ</th></tr></thead>
                        <tbody>
                        <?php foreach ($sources as $s): $u = $usage[$s->id] ?? null; ?>
                            <tr>
                                <td class="ps-4">
                                    <?php if ($canManage): ?>
                                        <?= Html::beginForm(['external-save'], 'post', ['class' => 'd-flex align-items-center gap-2']) ?>
                                            <?= Html::hiddenInput('id', $s->id) ?>
                                            <?= Html::input('number', 'sort_order', (int) $s->sort_order, ['class' => 'form-control form-control-sm', 'style' => 'width:64px', 'min' => 0, 'title' => 'ลำดับ']) ?>
                                            <?= Html::textInput('name', $s->name, ['class' => 'form-control form-control-sm', 'style' => 'max-width:280px', 'required' => true, 'maxlength' => 150]) ?>
                                            <?= Html::submitButton('<i class="bi bi-save"></i>', ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'บันทึก']) ?>
                                        <?= Html::endForm() ?>
                                    <?php else: ?>
                                        <?= Html::encode($s->name) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end small text-nowrap"><?= $u ? (int) $u['times'] . ' ครั้ง · ' . number_format((int) $u['pieces']) . ' ชิ้น' : '<span class="text-body-secondary">—</span>' ?></td>
                                <td><?= $s->is_active ? '<span class="badge text-bg-success">ใช้งาน</span>' : '<span class="badge text-bg-secondary">ปิด</span>' ?></td>
                                <td class="pe-4 text-end text-nowrap">
                                    <?php if ($canManage): ?>
                                        <?php if ($s->is_active): ?>
                                            <?= Html::beginForm(['external-delete'], 'post', ['class' => 'd-inline']) ?>
                                                <?= Html::hiddenInput('id', $s->id) ?>
                                                <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger', 'title' => 'ลบ/ปิดใช้', 'data-confirm' => 'ลบหน่วยงานนี้? (ถ้ามีประวัติรับผ้าจะปิดใช้แทน)']) ?>
                                            <?= Html::endForm() ?>
                                        <?php else: ?>
                                            <?= Html::beginForm(['external-save'], 'post', ['class' => 'd-inline']) ?>
                                                <?= Html::hiddenInput('id', $s->id) ?>
                                                <?= Html::hiddenInput('name', $s->name) ?>
                                                <?= Html::hiddenInput('sort_order', $s->sort_order) ?>
                                                <?= Html::hiddenInput('is_active', 1) ?>
                                                <?= Html::submitButton('<i class="bi bi-play"></i>', ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'เปิดใช้งาน']) ?>
                                            <?= Html::endForm() ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$sources): ?><tr><td colspan="4" class="text-center text-body-secondary py-4"><i class="bi bi-inbox me-1"></i>ยังไม่มีหน่วยงานภายนอก</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div></div>
            </div>
        </div>
    </div>
</div>
