<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\jd\models\JdEmployee;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $jdDataProvider */
/** @var array<int, JdEmployee> $jdByEmployee */
/** @var array<int, array> $approvalByJd */
/** @var array<int, bool> $acknowledgedJdIds */

$models = $jdDataProvider->getModels();
$statusLabels = JdEmployee::statusLabels();
$statusClasses = [
    JdEmployee::STATUS_DRAFT => 'is-neutral',
    JdEmployee::STATUS_PENDING => 'is-warning',
    JdEmployee::STATUS_ACTIVE => 'is-success',
];
?>
<section class="jd-registry" aria-labelledby="jd-registry-title">
    <div class="jd-registry__head">
        <div>
            <h2 id="jd-registry-title">ภาพรวม JD รายบุคคล</h2>
            <p>ตรวจสอบหน่วยงาน ตำแหน่ง เอกสาร และการลงนามได้จากรายการนี้</p>
        </div>
        <?= Html::a('<i data-lucide="settings-2"></i><span>ตั้งค่า JD Template</span>', ['/jd/template/index'], ['class' => 'btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2']) ?>
    </div>

    <?= Html::beginForm(['/hr/workforce/index'], 'get', ['class' => 'jd-registry__search']) ?>
    <?= Html::hiddenInput('section', 'jd') ?>
    <label class="visually-hidden" for="jd-registry-search">ค้นหาบุคลากรหรือตำแหน่ง</label>
    <div class="jd-registry__search-control">
        <i data-lucide="search" aria-hidden="true"></i>
        <?= Html::textInput('jd_q', Yii::$app->request->get('jd_q'), [
            'id' => 'jd-registry-search',
            'class' => 'form-control',
            'placeholder' => 'ค้นหาชื่อบุคลากรหรือตำแหน่ง',
        ]) ?>
    </div>
    <label class="d-inline-flex align-items-center gap-1 small text-nowrap mb-0">
        <?= Html::checkbox('show_all', ($showAll ?? false), ['value' => 1, 'class' => 'form-check-input mt-0']) ?>
        แสดงทั้งหมด (รวมผู้ที่ไม่ได้ปฏิบัติงาน)
    </label>
    <?= Html::submitButton('ค้นหา', ['class' => 'btn btn-primary']) ?>
    <?php if (trim((string) Yii::$app->request->get('jd_q')) !== '' || ($showAll ?? false)): ?>
        <?= Html::a('ล้างการค้นหา', ['/hr/workforce/index', 'section' => 'jd'], ['class' => 'btn btn-outline-secondary']) ?>
    <?php endif; ?>
    <?= Html::endForm() ?>

    <?php if ($models === []): ?>
        <div class="jd-registry__empty">
            <strong>ไม่พบบุคลากรที่ตรงกับคำค้นหา</strong>
            <span>ลองค้นหาด้วยชื่อหรือตำแหน่งอีกครั้ง</span>
        </div>
    <?php else: ?>
        <div class="d-none d-lg-block">
            <table class="jd-registry__table">
                <thead>
                <tr>
                    <th>บุคลากร</th>
                    <th>หน่วยงาน</th>
                    <th>ตำแหน่ง</th>
                    <th>การกำหนด JD</th>
                    <th>การลงนาม</th>
                    <th><span class="visually-hidden">ดำเนินการ</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($models as $employee): ?>
                    <?php
                    $jd = $jdByEmployee[(int) $employee->id] ?? null;
                    $approvals = $jd ? ($approvalByJd[(int) $jd->id] ?? []) : [];
                    $signed = count(array_filter($approvals, static fn($row): bool => $row->status === 'Pass'));
                    $totalSigners = count($approvals);
                    $acknowledged = $jd && isset($acknowledgedJdIds[(int) $jd->id]);
                    ?>
                    <tr>
                        <td>
                            <strong><?= Html::encode($employee->fullname) ?></strong>
                            <small>รหัสบุคลากร <?= (int) $employee->id ?></small>
                        </td>
                        <td><?= Html::encode($employee->departmentName()) ?></td>
                        <td><?= Html::encode(strip_tags((string) $employee->positionName())) ?></td>
                        <td>
                            <?php if ($jd): ?>
                                <span class="jd-status <?= $statusClasses[$jd->status] ?? 'is-neutral' ?>"><?= Html::encode($statusLabels[$jd->status] ?? $jd->status) ?></span>
                                <small>Revision <?= (int) $jd->revision_no ?></small>
                            <?php else: ?>
                                <span class="jd-status is-danger">ยังไม่ได้กำหนด</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$jd): ?>
                                <span class="jd-sign-state is-muted">ยังไม่มีเอกสาร</span>
                            <?php elseif ($totalSigners > 0): ?>
                                <span class="jd-sign-state <?= $signed === $totalSigners ? 'is-done' : 'is-waiting' ?>">
                                    ลงนาม <?= $signed ?>/<?= $totalSigners ?>
                                </span>
                                <?php if ($jd->status === JdEmployee::STATUS_ACTIVE): ?>
                                    <small><?= $acknowledged ? 'เจ้าของ JD รับทราบแล้ว' : 'รอเจ้าของ JD รับทราบ' ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="jd-sign-state is-muted"><?= $jd->status === JdEmployee::STATUS_DRAFT ? 'ยังไม่ส่งลงนาม' : 'ไม่มีข้อมูลผู้ลงนาม' ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <?php if ($jd): ?><?= Html::a('PDF', ['/jd/employee-jd/pdf', 'id' => $jd->id], ['class' => 'btn btn-sm btn-outline-danger', 'target' => '_blank', 'rel' => 'noopener']) ?><?php endif; ?>
                                <?= Html::a($jd ? 'เปิด JD' : 'จัดทำ JD', ['/jd/employee-jd/view', 'emp_id' => $employee->id, 'id' => $jd?->id], ['class' => 'btn btn-sm btn-outline-primary text-nowrap']) ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <ul class="jd-registry__mobile d-lg-none" role="list">
            <?php foreach ($models as $employee): ?>
                <?php
                $jd = $jdByEmployee[(int) $employee->id] ?? null;
                $approvals = $jd ? ($approvalByJd[(int) $jd->id] ?? []) : [];
                $signed = count(array_filter($approvals, static fn($row): bool => $row->status === 'Pass'));
                ?>
                <li>
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div><strong><?= Html::encode($employee->fullname) ?></strong><small><?= Html::encode(strip_tags((string) $employee->positionName())) ?></small></div>
                        <span class="jd-status <?= $jd ? ($statusClasses[$jd->status] ?? 'is-neutral') : 'is-danger' ?>"><?= Html::encode($jd ? ($statusLabels[$jd->status] ?? $jd->status) : 'ยังไม่ได้กำหนด') ?></span>
                    </div>
                    <dl>
                        <div><dt>หน่วยงาน</dt><dd><?= Html::encode($employee->departmentName()) ?></dd></div>
                        <div><dt>การลงนาม</dt><dd><?= $approvals ? $signed . '/' . count($approvals) . ' คน' : ($jd ? 'ยังไม่ส่งลงนาม' : 'ยังไม่มีเอกสาร') ?></dd></div>
                    </dl>
                    <div class="d-flex gap-2">
                        <?php if ($jd): ?><?= Html::a('พิมพ์ PDF', ['/jd/employee-jd/pdf', 'id' => $jd->id], ['class' => 'btn btn-sm btn-outline-danger flex-fill', 'target' => '_blank', 'rel' => 'noopener']) ?><?php endif; ?>
                        <?= Html::a($jd ? 'เปิด JD' : 'จัดทำ JD', ['/jd/employee-jd/view', 'emp_id' => $employee->id, 'id' => $jd?->id], ['class' => 'btn btn-sm btn-outline-primary flex-fill']) ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="jd-registry__footer">
        <?= DataSummaryWidget::widget(['dataProvider' => $jdDataProvider]) ?>
    </div>
</section>
