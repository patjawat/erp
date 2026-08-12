<?php

use yii\helpers\Html;
use app\modules\purchase\models\BondPolicy;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\BondPolicy[] $models */

$this->title = 'ตั้งค่าเกณฑ์หลักประกัน';
$this->params['breadcrumbs'][] = ['label' => 'งานพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-shield-exclamation"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php if (BondPolicy::needsReview()): ?>
    <div class="alert alert-warning d-flex gap-2 align-items-start">
        <i class="bi bi-exclamation-triangle mt-1"></i>
        <div>
            <div class="fw-medium">เกณฑ์ด้านล่างยังเป็นค่าตั้งต้นที่ระบบใส่ให้ ยังไม่ผ่านการยืนยันจากงานพัสดุ</div>
            <div class="small">
                ตัวเลขชุดนี้เป็นตัวบอกเจ้าหน้าที่ว่าต้องเรียกหลักประกันจากคู่สัญญาหรือไม่และเท่าไร
                โปรดเทียบกับระเบียบและหนังสือเวียนฉบับที่หน่วยงานใช้อยู่
                เมื่อตรวจแล้วให้ลบข้อความในช่องหมายเหตุออก ป้ายเตือนนี้จะหายไปเอง
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="alert alert-info small">
    <i class="bi bi-info-circle me-1"></i>
    ระบบจับคู่วงเงินกับแถวแรกที่เข้าเกณฑ์ โดยไล่จากลำดับน้อยไปมาก และ<strong>นับรวมปลายช่วงทั้งสองข้าง</strong>
    แถวที่เจาะจงประเภทสัญญาจึงต้องมีลำดับน้อยกว่าแถว "ทุกประเภท" ที่ครอบช่วงเดียวกัน ไม่งั้นจะไม่มีวันถูกใช้
    ถ้าไม่มีแถวไหนครอบวงเงินที่กรอก ระบบจะบอกว่ายังไม่ได้ตั้งเกณฑ์ ไม่เดาให้เอง
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0"><i class="bi bi-table"></i> เกณฑ์ที่ใช้อยู่</h6>
        <?= Html::a('<i class="bi bi-plus-circle me-1"></i>เพิ่มเกณฑ์', ['create'], [
            'class' => 'btn btn-success btn-sm rounded-pill px-3',
        ]) ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:70px" class="text-center">ลำดับ</th>
                        <th style="min-width:260px">คำอธิบาย</th>
                        <th style="min-width:110px">ใช้กับ</th>
                        <th style="min-width:190px" class="text-end">ช่วงวงเงิน</th>
                        <th style="min-width:150px">ผล</th>
                        <th style="min-width:200px">อ้างอิง</th>
                        <th style="width:100px">สถานะ</th>
                        <th style="width:110px" class="text-end"></th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($models as $item): ?>
                        <tr>
                            <td class="text-center text-muted"><?= (int) $item->sort_order ?></td>
                            <td>
                                <div class="fw-medium"><?= Html::encode($item->title) ?></div>
                                <?php if ($item->note): ?>
                                    <div class="small text-warning-emphasis"><?= Html::encode($item->note) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= Html::encode($item->kindName()) ?></td>
                            <td class="text-end small"><?= Html::encode($item->rangeText()) ?></td>
                            <td>
                                <?php if ((int) $item->required === 1): ?>
                                    <span class="badge text-bg-warning">ต้องวาง</span>
                                    <div class="small fw-semibold">
                                        <?= rtrim(rtrim(number_format((float) $item->rate, 2), '0'), '.') ?>% ของวงเงิน
                                    </div>
                                <?php else: ?>
                                    <span class="badge text-bg-success">ไม่ต้องวาง</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= Html::encode($item->law_ref ?: '—') ?></td>
                            <td>
                                <?php if ((int) $item->active === 1): ?>
                                    <span class="badge text-bg-success">ใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">ปิดใช้งาน</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'แก้ไข',
                                    ]) ?>
                                    <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'title' => 'ลบ',
                                        'data' => [
                                            'confirm' => 'ลบเกณฑ์ "' . $item->title . '" ? '
                                                . 'วงเงินที่เคยเข้าเกณฑ์นี้จะถูกจับคู่กับแถวอื่นแทน',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$models): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                ยังไม่มีเกณฑ์ในระบบ — หน้าบันทึกหลักประกันจะไม่แนะนำวงเงินให้จนกว่าจะตั้งเกณฑ์
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
