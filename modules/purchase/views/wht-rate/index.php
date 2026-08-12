<?php

use yii\helpers\Html;
use app\modules\purchase\models\WhtRate;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\WhtRate[] $models */

$this->title = 'ตั้งค่าอัตราภาษีหัก ณ ที่จ่าย';
$this->params['breadcrumbs'][] = ['label' => 'งานพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-percent"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php if (WhtRate::needsReview()): ?>
    <div class="alert alert-warning d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle mt-1"></i>
        <div>
            <div class="fw-medium">อัตราด้านล่างยังเป็นค่าตั้งต้นที่ระบบใส่ให้ ยังไม่ผ่านการยืนยันจากงานการเงิน</div>
            <div class="small">
                ตัวเลขชุดนี้ถูกนำไปพิมพ์ลงร่างสัญญา โปรดให้งานการเงินตรวจสอบและแก้ไขให้ตรงกับที่หน่วยงานใช้จริง
                เมื่อแก้แล้วให้ลบข้อความในช่องหมายเหตุออก ป้ายเตือนนี้จะหายไปเอง
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="alert alert-info small">
    <i class="bi bi-info-circle me-1"></i>
    ระบบใช้อัตราจากตารางนี้เท่านั้น ไม่มีอัตราสำรองในโปรแกรม
    ถ้าปิดใช้งานแถวไหน สัญญาประเภทนั้นจะไม่มีการคำนวณภาษีหัก ณ ที่จ่ายให้
    ฐานคำนวณถอดภาษีมูลค่าเพิ่มออกก่อนเมื่อสัญญาระบุว่าวงเงินรวม VAT แล้ว
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-table"></i> อัตราที่ใช้อยู่</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="min-width:240px">คำอธิบาย</th>
                        <th style="min-width:110px">ประเภทสัญญา</th>
                        <th style="min-width:110px">ผู้รับเงิน</th>
                        <th style="width:100px" class="text-end">อัตรา</th>
                        <th style="width:160px" class="text-end">เริ่มหักเมื่อยอดถึง</th>
                        <th style="min-width:200px">อ้างอิง</th>
                        <th style="width:100px">สถานะ</th>
                        <th style="width:80px" class="text-end"></th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($models as $item): ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= Html::encode($item->title) ?></div>
                                <?php if ($item->note): ?>
                                    <div class="small text-warning-emphasis"><?= Html::encode($item->note) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= Html::encode($item->contractTypeName()) ?></td>
                            <td class="small"><?= Html::encode($item->partyTypeName()) ?></td>
                            <td class="text-end fw-semibold"><?= rtrim(rtrim(number_format((float) $item->rate, 2), '0'), '.') ?>%</td>
                            <td class="text-end"><?= number_format((float) $item->threshold, 2) ?></td>
                            <td class="small text-muted"><?= Html::encode($item->law_ref ?: '—') ?></td>
                            <td>
                                <?php if ((int) $item->active === 1): ?>
                                    <span class="badge text-bg-success">ใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">ปิดใช้งาน</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-outline-secondary',
                                    'title' => 'แก้ไข',
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$models): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                ยังไม่มีอัตราภาษีในระบบ — ต้องรัน migration ก่อนใช้งาน
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
