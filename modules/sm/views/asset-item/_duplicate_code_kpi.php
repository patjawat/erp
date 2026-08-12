<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $duplicateCodeSummary */
/** @var int $duplicateCodeGroupCount */
/** @var int $duplicateCodeTotalCount */
/** @var int $duplicateCodeExtraCount */

$duplicateCodeSummary = $duplicateCodeSummary ?? [];
$duplicateCodeGroupCount = (int) ($duplicateCodeGroupCount ?? 0);
$duplicateCodeTotalCount = (int) ($duplicateCodeTotalCount ?? 0);
$duplicateCodeExtraCount = (int) ($duplicateCodeExtraCount ?? 0);
?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 py-3">
        <div>
            <div class="small text-muted">
                ตรวจสอบจากเงื่อนไข `group_id = EQUIP` และ `category_id IS NOT NULL`
            </div>
            <h6 class="mb-0 fw-semibold text-body d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle text-danger"></i>
                KPI รหัส `code` ที่ซ้ำ
            </h6>
        </div>

        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-3 py-2">
            พบ <?= number_format($duplicateCodeGroupCount) ?> กลุ่ม
        </span>
    </div>

    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-4">
                <div class="rounded-4 border border-danger-subtle bg-danger bg-opacity-10 p-3 h-100">
                    <div class="small text-danger fw-semibold mb-1">กลุ่ม code ซ้ำ</div>
                    <div class="display-6 fw-bold text-danger mb-1"><?= number_format($duplicateCodeGroupCount) ?></div>
                    <div class="small text-muted">code + category_id ที่พบมากกว่า 1 รายการ</div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="rounded-4 border border-warning-subtle bg-warning bg-opacity-10 p-3 h-100">
                    <div class="small text-warning-emphasis fw-semibold mb-1">รายการทั้งหมดในกลุ่มซ้ำ</div>
                    <div class="display-6 fw-bold text-warning-emphasis mb-1"><?= number_format($duplicateCodeTotalCount) ?></div>
                    <div class="small text-muted">รวมทุกแถวที่อยู่ในกลุ่มซ้ำ</div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="rounded-4 border border-primary-subtle bg-primary bg-opacity-10 p-3 h-100">
                    <div class="small text-primary fw-semibold mb-1">รายการส่วนเกินที่ต้องแก้</div>
                    <div class="display-6 fw-bold text-primary mb-1"><?= number_format($duplicateCodeExtraCount) ?></div>
                    <div class="small text-muted">จำนวนแถวที่ซ้ำเกินจากตัวแรกในแต่ละกลุ่ม</div>
                </div>
            </div>
        </div>

        <?php if (!empty($duplicateCodeSummary)): ?>
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary sticky-top" style="z-index: 1;">
                        <tr>
                            <th class="text-center" style="width: 64px;">#</th>
                            <th style="width: 150px;">Code</th>
                            <th style="width: 160px;">Category ID</th>
                            <th>Title</th>
                            <th class="text-center" style="width: 110px;">Total</th>
                            <th class="text-center" style="width: 140px;">ตรวจสอบ</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($duplicateCodeSummary as $index => $row): ?>
                            <?php
                            $code = (string) ($row['code'] ?? '');
                            $categoryId = (string) ($row['category_id'] ?? '');
                            $title = (string) ($row['title'] ?? '-');
                            $total = (int) ($row['total'] ?? 0);
                            $inspectUrl = Url::to([
                                '/sm/asset-item/index',
                                'AssetItemSearch' => [
                                    'code' => $code,
                                    'category_id' => $categoryId,
                                ],
                            ]);
                            ?>
                            <tr>
                                <td class="text-center text-muted"><?= $index + 1 ?></td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                        <?= Html::encode($code) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                        <?= Html::encode($categoryId) ?>
                                    </span>
                                </td>
                                <td class="text-truncate" style="max-width: 420px;" title="<?= Html::encode($title) ?>">
                                    <?= Html::encode($title) ?>
                                </td>
                                <td class="text-center fw-bold text-danger">
                                    <?= number_format($total) ?>
                                </td>
                                <td class="text-center">
                                    <?= Html::a(
                                        '<i class="bi bi-search me-1"></i> ตรวจสอบ',
                                        $inspectUrl,
                                        ['class' => 'btn btn-sm btn-outline-danger']
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-success border-0 rounded-3 mb-0">
                ไม่พบรหัสซ้ำตามเงื่อนไขนี้
            </div>
        <?php endif; ?>
    </div>
</div>
