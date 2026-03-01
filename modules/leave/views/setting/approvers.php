<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'ผู้อนุมัติใบลา';
$this->params['breadcrumbs'][] = ['label' => 'การลา', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$levelsUrl = Url::to(['/approve-v2/setting/levels', 'system' => 'leave']);
$diagramUrl = Url::to(['/hr/organization/diagram']);
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="p-3 bg-primary bg-opacity-10 rounded-3 text-primary flex-shrink-0">
                        <i class="bi bi-person-gear fs-2"></i>
                    </div>
                    <div>
                        <h5 class="fw-semibold text-body mb-2">ตั้งค่าระดับการอนุมัติใบลา</h5>
                        <p class="text-muted small mb-0">
                            กำหนดว่าขั้นตอนการอนุมัติใบลามีกี่ระดับ และแต่ละระดับให้ใครเป็นผู้อนุมัติ (ตามผังโครงสร้างองค์กร หรือบทบาท)
                        </p>
                    </div>
                </div>

                <ul class="text-muted small mb-4 ps-3">
                    <li class="mb-1">ลำดับการอนุมัติ: ระดับ 1 → ระดับ 2 → … ตามที่กำหนด</li>
                    <li class="mb-1">ผู้อนุมัติมาจาก <strong>ผังโครงสร้างองค์กร</strong> หรือบทบาท (Role)</li>
                    <li>ตัวอย่าง: หัวหน้างาน → หัวหน้ากลุ่มงาน → ตรวจสอบ → ผอ.อนุมัติ</li>
                </ul>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <?php if (Yii::$app->user->can('admin')): ?>
                    <?= Html::a(
                        '<i class="bi bi-diagram-3 me-2"></i> จัดการระดับการอนุมัติ',
                        $levelsUrl,
                        ['class' => 'btn btn-primary rounded-3 px-4']
                    ) ?>
                    <?php endif; ?>
                    <?= Html::a(
                        'ดูผังองค์กร',
                        $diagramUrl,
                        ['class' => 'btn btn-outline-secondary rounded-3', 'target' => '_blank']
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>
