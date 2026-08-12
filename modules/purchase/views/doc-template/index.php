<?php

use yii\helpers\Html;
use app\modules\purchase\models\DocTemplate;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\DocTemplate[] $models */

$this->title = 'แม่แบบเอกสาร';
$this->params['breadcrumbs'][] = ['label' => 'งานพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = ['label' => 'พิมพ์เอกสาร', 'url' => ['/purchase/doc']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-sliders"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับทะเบียนเอกสาร', ['/purchase/doc'], [
    'class' => 'btn btn-sm btn-outline-secondary',
]) ?>
<?php $this->endBlock(); ?>

<div class="alert alert-info small">
    <i class="bi bi-info-circle me-1"></i>
    แม่แบบคือข้อความตั้งต้นของเอกสาร เมื่อมีหนังสือเวียนใหม่หรือเปลี่ยนถ้อยคำ
    แก้ที่นี่ได้เองโดยไม่ต้องรอแก้โปรแกรม
    การแก้แม่แบบมีผลกับเอกสารที่สร้างใหม่เท่านั้น เอกสารที่ออกไปแล้วยังใช้ข้อความเดิม
</div>

<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-collection"></i> รายการแม่แบบ
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">
                <?= number_format(count($models)) ?>
            </span>
        </h6>
        <?= Html::a('<i class="bi bi-plus-circle me-1"></i>เพิ่มแม่แบบ', ['create'], [
            'class' => 'btn btn-success btn-sm',
        ]) ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary">
                    <tr>
                        <th style="width:42px" class="text-center">#</th>
                        <th style="min-width:240px">ชื่อเอกสาร</th>
                        <th style="min-width:130px">หมวด</th>
                        <th style="min-width:160px">ดึงข้อมูลจาก</th>
                        <th style="min-width:110px">กระดาษ</th>
                        <th style="min-width:110px" class="text-center">ออกไปแล้ว</th>
                        <th style="min-width:90px">สถานะ</th>
                        <th style="width:130px" class="text-end">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($models as $i => $item): ?>
                        <?php $used = $item->docCount(); ?>
                        <tr class="<?= $item->active ? '' : 'opacity-50' ?>">
                            <td class="text-center text-muted"><?= $i + 1 ?></td>
                            <td>
                                <?= Html::a(Html::encode($item->name), ['update', 'id' => $item->id], [
                                    'class' => 'fw-semibold small text-decoration-none',
                                ]) ?>
                                <div class="small text-muted font-monospace"><?= Html::encode($item->code) ?></div>
                                <?php if ($item->law_ref): ?>
                                    <div class="small text-muted"><?= Html::encode($item->law_ref) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= Html::encode($item->categoryName()) ?></td>
                            <td class="small"><?= Html::encode($item->refTypeName()) ?></td>
                            <td class="small">
                                <?= $item->orientation === 'landscape' ? 'A4 นอน' : 'A4 ตั้ง' ?>
                                <div class="text-muted">
                                    <?= (int) $item->font_size ?>pt ·
                                    <?= $item->emblem === DocTemplate::EMBLEM_NONE
                                        ? 'ไม่มีครุฑ'
                                        : 'ครุฑ ' . Html::encode($item->emblem) . ' ซม.' ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($used): ?>
                                    <span class="badge bg-primary-subtle text-primary-emphasis"><?= $used ?> ฉบับ</span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $item->active ? 'success' : 'secondary' ?>">
                                    <?= $item->active ? 'เปิดใช้' : 'ปิดใช้' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'แก้ไข ' . $item->name,
                                        'aria-label' => 'แก้ไข ' . $item->name,
                                    ]) ?>
                                    <?= Html::a(
                                        '<i class="bi bi-' . ($item->active ? 'eye-slash' : 'eye') . '"></i>',
                                        ['toggle', 'id' => $item->id],
                                        [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'title' => ($item->active ? 'ปิดใช้ ' : 'เปิดใช้ ') . $item->name,
                                            'aria-label' => ($item->active ? 'ปิดใช้ ' : 'เปิดใช้ ') . $item->name,
                                            'data' => ['method' => 'post'],
                                        ]
                                    ) ?>
                                    <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'title' => 'ลบ ' . $item->name,
                                        'aria-label' => 'ลบ ' . $item->name,
                                        'data' => [
                                            'confirm' => $used
                                                ? 'แม่แบบนี้ออกเอกสารไปแล้ว ' . $used . ' ฉบับ'
                                                    . ' เอกสารเหล่านั้นยังพิมพ์ได้ แต่ปุ่มรีเซ็ตจะใช้ไม่ได้อีก'
                                                    . ' หากเพียงต้องการเลิกใช้ ให้กดปุ่มปิดใช้แทน — ยืนยันลบ ?'
                                                : 'ยืนยันการลบแม่แบบ "' . $item->name . '" ?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($models)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="text-center py-5">
                                    <div class="fw-semibold mb-1">ยังไม่มีแม่แบบเอกสาร</div>
                                    <div class="text-muted small mb-3">
                                        ถ้าเพิ่งติดตั้งระบบ ให้รัน migration เพื่อรับแม่แบบตั้งต้น
                                        หรือกดปุ่มด้านล่างเพื่อสร้างเอง
                                    </div>
                                    <?= Html::a('เพิ่มแม่แบบ', ['create'], ['class' => 'btn btn-success']) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
