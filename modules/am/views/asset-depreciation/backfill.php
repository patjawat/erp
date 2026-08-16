<?php

use yii\helpers\Html;
use app\modules\am\services\DepreciationSnapshotService;

/** @var yii\web\View $this */
/** @var array $result ผลทดลอง (dry-run) จาก DepreciationSnapshotService::backfill() */
/** @var bool $force */
/** @var array<int,string> $profileNames */

$this->title = 'ตรึงเกณฑ์ค่าเสื่อมให้ทะเบียนเดิม';
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$applied = (int) $result['applied'];
$total = (int) $result['total'];
$reasons = $result['reasons'];
$noBinding = (int) ($reasons['no_binding'] ?? 0);

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="anchor"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3 dep-backfill">

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $cls): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $cls ?>"><?= Yii::$app->session->getFlash($flash) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="alert alert-info small mb-3">
        <i data-lucide="info"></i>
        ทรัพย์สินที่ขึ้นทะเบียน<b>ก่อน</b>มีการผูกเกณฑ์ ยังไม่มีเกณฑ์ติดตัว จึงคำนวณค่าเสื่อมไม่ได้
        หน้านี้จะคัดลอกเกณฑ์จากประเภท/หมวด/รายการที่ผูกไว้ มาตรึงไว้กับทรัพย์สินแต่ละชิ้น
        <br>
        <i data-lucide="shield-check"></i>
        ผลด้านล่างเป็น<b>การทดลอง ยังไม่บันทึก</b> — ทรัพย์สินที่มีเกณฑ์อยู่แล้วจะไม่ถูกแตะ
        และหลังจากนี้ทรัพย์สินที่บันทึกใหม่จะรับเกณฑ์อัตโนมัติ
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">ตรวจทั้งหมด</div>
                <div class="fs-4 fw-semibold"><?= number_format($total) ?></div>
                <div class="text-muted small">รายการที่เข้าเกณฑ์คิดค่าเสื่อม</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-primary"><div class="card-body">
                <div class="text-muted small">จะตรึงเกณฑ์ให้</div>
                <div class="fs-4 fw-semibold text-primary"><?= number_format($applied) ?></div>
                <div class="text-muted small">รายการ</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">ยังไม่ได้ผูกเกณฑ์</div>
                <div class="fs-4 fw-semibold <?= $noBinding > 0 ? 'text-warning' : '' ?>"><?= number_format($noBinding) ?></div>
                <div class="text-muted small">ต้องไปผูกเกณฑ์ก่อน</div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">มีเกณฑ์อยู่แล้ว</div>
                <div class="fs-4 fw-semibold"><?= number_format((int) ($reasons['already_snapshotted'] ?? 0)) ?></div>
                <div class="text-muted small">ไม่ถูกแตะ</div>
            </div></div>
        </div>
    </div>

    <?php if ($result['error'] !== null): ?>
        <div class="alert alert-danger">ทดลองคำนวณไม่สำเร็จ: <?= Html::encode($result['error']) ?></div>
    <?php endif; ?>

    <div class="card mb-3"><div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <?php if ($applied > 0): ?>
            <?= Html::beginForm(['backfill-apply'], 'post', [
                'data-confirm' => 'ยืนยันตรึงเกณฑ์ค่าเสื่อมให้ทรัพย์สิน ' . number_format($applied) . ' รายการ?',
                'class' => 'd-inline',
            ]) ?>
                <?= Html::hiddenInput('force', $force ? 1 : 0) ?>
                <?= Html::submitButton('<i data-lucide="check-check"></i> บันทึกจริง (' . number_format($applied) . ' รายการ)', [
                    'class' => 'btn btn-primary',
                ]) ?>
            <?= Html::endForm() ?>
        <?php else: ?>
            <span class="text-muted small"><i data-lucide="info"></i> ไม่มีรายการที่ต้องตรึงเกณฑ์</span>
        <?php endif; ?>

        <?= Html::a('<i data-lucide="link"></i> ไปผูกเกณฑ์เข้าลำดับชั้น', ['/am/depreciation-binding/index'], ['class' => 'btn btn-outline-primary']) ?>
        <?= Html::a('<i data-lucide="rotate-cw"></i> ทดลองใหม่', ['backfill', 'force' => $force ? 1 : 0], ['class' => 'btn btn-outline-secondary']) ?>

        <div class="ms-auto form-check">
            <?= Html::a(
                ($force ? '<i data-lucide="toggle-right"></i>' : '<i data-lucide="toggle-left"></i>') . ' โหมดทับเกณฑ์เดิม',
                ['backfill', 'force' => $force ? 0 : 1],
                ['class' => 'btn btn-sm ' . ($force ? 'btn-warning' : 'btn-outline-secondary')]
            ) ?>
        </div>
    </div></div>

    <?php if ($force): ?>
        <div class="alert alert-warning small">
            <i data-lucide="alert-triangle"></i>
            <b>โหมดทับเกณฑ์เดิม</b> — จะเขียนทับเกณฑ์ที่ทรัพย์สินตรึงไว้แล้วด้วยเกณฑ์ปัจจุบัน
            ใช้เมื่อผูกเกณฑ์ใหม่แล้วต้องการให้ทะเบียนทั้งหมดตามเกณฑ์ล่าสุด
            (ไม่กระทบงวดที่บันทึกบัญชี/ล็อกไปแล้ว แต่จะไม่มีประวัติการเปลี่ยนเกณฑ์รายชิ้น)
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100"><div class="card-body">
                <h6 class="fw-semibold mb-3">สรุปตามผลลัพธ์</h6>
                <table class="table table-sm mb-0">
                    <tbody>
                        <?php foreach ($reasons as $reason => $n): ?>
                            <tr>
                                <td><?= Html::encode(DepreciationSnapshotService::reasonLabel((string) $reason)) ?></td>
                                <td class="text-end fw-semibold"><?= number_format((int) $n) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reasons)): ?>
                            <tr><td class="text-muted text-center py-3">ไม่มีข้อมูล</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div></div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100"><div class="card-body">
                <h6 class="fw-semibold mb-3">จะตรึงด้วยเกณฑ์ใดบ้าง</h6>
                <?php if (empty($result['by_profile'])): ?>
                    <div class="text-muted small">ยังไม่มีรายการ — ผูกเกณฑ์ให้ประเภททรัพย์สินก่อน</div>
                <?php else: ?>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th scope="col">เกณฑ์</th><th scope="col" class="text-end">จำนวน (รายการ)</th></tr>
                        </thead>
                        <tbody>
                            <?php arsort($result['by_profile']); ?>
                            <?php foreach ($result['by_profile'] as $pid => $n): ?>
                                <tr>
                                    <td><?= Html::encode($profileNames[(int) $pid] ?? ('#' . $pid)) ?></td>
                                    <td class="text-end fw-semibold"><?= number_format((int) $n) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div></div>
        </div>
    </div>

    <?php if (!empty($result['samples'])): ?>
        <div class="card mt-3"><div class="card-body">
            <h6 class="fw-semibold mb-3">ตัวอย่างผลลัพธ์ (<?= count($result['samples']) ?> รายการแรก)</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <caption class="visually-hidden">ตัวอย่างทรัพย์สินที่จะถูกตรึงเกณฑ์ค่าเสื่อม</caption>
                    <thead class="table-light">
                        <tr>
                            <th scope="col">รหัส</th>
                            <th scope="col">ชื่อ</th>
                            <th scope="col" class="text-end">ราคาทุน</th>
                            <th scope="col">เกณฑ์ที่ได้</th>
                            <th scope="col">มาจากระดับ</th>
                            <th scope="col" class="text-end">อายุ (เดือน)</th>
                            <th scope="col" class="text-end">มูลค่าซาก</th>
                            <th scope="col">เริ่มคิด</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $levelLabels = [
                            'asset' => 'รายชิ้น',
                            'asset_item' => 'รายการ',
                            'asset_category' => 'หมวด',
                            'asset_type' => 'ประเภทหลัก',
                        ];
                        ?>
                        <?php foreach ($result['samples'] as $s): ?>
                            <tr>
                                <td><?= Html::encode((string) $s['code']) ?></td>
                                <td><?= Html::encode((string) $s['name']) ?></td>
                                <td class="text-end"><?= number_format((float) $s['price'], 2) ?></td>
                                <td><span class="badge bg-success"><?= Html::encode($profileNames[(int) $s['profile_id']] ?? ('#' . $s['profile_id'])) ?></span></td>
                                <td class="small text-muted"><?= Html::encode($levelLabels[(string) $s['source_type']] ?? (string) $s['source_type']) ?></td>
                                <td class="text-end"><?= $s['useful_life_months'] !== null ? number_format((int) $s['useful_life_months']) : '—' ?></td>
                                <td class="text-end"><?= $s['residual_value'] !== null ? number_format((float) $s['residual_value'], 2) : '—' ?></td>
                                <td class="small"><?= Html::encode((string) $s['start_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div></div>
    <?php endif; ?>
</div>

<?php $this->registerJs('if (window.lucide) { lucide.createIcons(); }'); ?>
