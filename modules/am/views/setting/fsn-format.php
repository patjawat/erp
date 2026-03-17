<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AmAssetNumberFormat[] $formats */
/** @var app\modules\am\models\AmAssetSequence[] $sequences */
/** @var bool $tableFormatsExists */
/** @var bool $tableSeqsExists */

$this->title = 'การกำหนดรูปแบบ ลำดับ FSN ครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าทรัพย์สิน', 'url' => ['/am/setting']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <span class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary p-2">
        <i class="fa-solid fa-hashtag fs-5"></i>
    </span>
    <div>
        <h5 class="mb-0 fw-semibold text-body"><?= Html::encode($this->title) ?></h5>
        <small class="text-muted">เลือกรูปแบบการสร้างหมายเลขครุภัณฑ์และดูลำดับต่อปี</small>
    </div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> กลับตั้งค่าทรัพย์สิน', ['/am/setting'], ['class' => 'btn btn-outline-secondary']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!$tableFormatsExists): ?>
        <div class="alert alert-warning mb-0">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            ยังไม่ได้ติดตั้งตารางรูปแบบหมายเลขครุภัณฑ์ กรุณารัน migration ก่อน (เช่น <code>php yii migrate</code>)
        </div>
        <?php return; ?>
    <?php endif; ?>

    <!-- รูปแบบหมายเลข FSN -->
    <div class="card border-0 shadow-sm rounded-2 mb-4">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                <i data-lucide="list-ordered"></i>
            </div>
            <h6 class="text-uppercase text-secondary m-0">รูปแบบหมายเลขครุภัณฑ์ (FSN)</h6>
            <div class="ms-auto">
                <?= Html::a('<i class="fa-solid fa-plus me-1"></i> เพิ่มรูปแบบ', ['fsn-format-create'], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                ระบบจะใช้ <strong>รูปแบบที่เลือก</strong> เท่านั้นในการสร้างหมายเลขอัตโนมัติ
                ตัวแปร: <code>{category}</code> = รหัส FSN หมวด, <code>{year}</code> = ปี พ.ศ. 2 หลัก, <code>{seq}</code> = ลำดับในปี
            </p>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">ชื่อรูปแบบ</th>
                            <th class="border-0">รูปแบบ (Pattern)</th>
                            <th class="border-0 text-center" style="width: 120px;">สถานะ</th>
                            <th class="border-0 text-end" style="width: 200px;">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php
                        $activeFormatId = null;
                        foreach ($formats as $f) {
                            if ($f->is_active) {
                                $activeFormatId = $f->id;
                                break;
                            }
                        }
                        foreach ($formats as $fmt):
                            $isEffectiveActive = ($activeFormatId !== null && $fmt->id == $activeFormatId);
                        ?>
                            <tr>
                                <td class="fw-medium"><?= Html::encode($fmt->name) ?></td>
                                <td><code class="text-body"><?= Html::encode($fmt->pattern) ?></code></td>
                                <td class="text-center">
                                    <?php if ($isEffectiveActive): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">ใช้อยู่</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ไม่ใช้</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                                        <?php if (!$isEffectiveActive): ?>
                                            <?= Html::a('<i class="fa-solid fa-check me-1"></i> ใช้', ['set-active-format', 'id' => $fmt->id], ['class' => 'btn btn-primary btn-sm']) ?>
                                        <?php endif; ?>
                                        <?= Html::a('<i class="fa-solid fa-pen me-1"></i> แก้ไข', ['fsn-format-update', 'id' => $fmt->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                                        <?= Html::a('<i class="fa-solid fa-trash me-1"></i> ลบ', ['fsn-format-delete', 'id' => $fmt->id], [
                                            'class' => 'btn btn-outline-danger btn-sm',
                                            'data' => ['method' => 'post', 'confirm' => 'ยืนยันลบรูปแบบ «' . Html::encode($fmt->name) . '»?']
                                        ]) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
            $activePattern = \app\modules\am\services\AssetNumberGenerator::getActivePattern();
            $example = str_replace(['{category}', '{year}', '{seq}'], ['7910-003-0003', '66', '01'], $activePattern);
            ?>
            <div class="mt-3 p-3 rounded-2 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                <small class="text-muted d-block mb-1">ตัวอย่างหมายเลขที่ได้จากรูปแบบที่ใช้อยู่</small>
                <code class="fs-6"><?= Html::encode($example) ?></code>
            </div>
        </div>
    </div>

    <!-- ลำดับปัจจุบัน (ต่อปีต่อหมวด) -->
    <div class="card border-0 shadow-sm rounded-2">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                <i data-lucide="hash"></i>
            </div>
            <h6 class="text-uppercase text-secondary m-0">ลำดับปัจจุบัน (ต่อปีต่อหมวด)</h6>
            <?php if ($tableSeqsExists): ?>
                <div class="ms-auto">
                    <?= Html::a('<i class="fa-solid fa-plus me-1"></i> เพิ่มลำดับ', ['fsn-sequence-create'], ['class' => 'btn btn-primary']) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!$tableSeqsExists): ?>
                <p class="text-muted mb-0">ยังไม่มีตารางเก็บลำดับ (รัน migration แล้วลำดับจะรีเซ็ตต่อปีต่อหมวดอัตโนมัติ)</p>
            <?php elseif (empty($sequences)): ?>
                <p class="text-muted mb-0">ยังไม่มีข้อมูลลำดับ เมื่อมีการสร้างครุภัณฑ์ใหม่ระบบจะเริ่มนับให้เอง หรือกด «เพิ่มลำดับ» เพื่อกำหนดเอง</p>
                <p class="mb-0"><?= Html::a('<i class="fa-solid fa-plus me-1"></i> เพิ่มลำดับ', ['fsn-sequence-create'], ['class' => 'btn btn-outline-primary']) ?></p>
            <?php else: ?>
                <p class="text-muted small mb-3">ลำดับจะรีเซ็ตทุกปี (ปี พ.ศ.) ต่อหมวด FSN สามารถเพิ่ม แก้ไข ลบ ได้</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">รหัสหมวด (FSN)</th>
                                <th class="border-0 text-center">ปี พ.ศ.</th>
                                <th class="border-0 text-center">ลำดับล่าสุด</th>
                                <th class="border-0">อัปเดตเมื่อ</th>
                                <th class="border-0 text-end" style="width: 140px;">ดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php foreach ($sequences as $row): ?>
                                <tr>
                                    <td><code><?= Html::encode($row->category_id) ?></code></td>
                                    <td class="text-center"><?= Html::encode($row->year) ?></td>
                                    <td class="text-center fw-medium"><?= Html::encode($row->current_sequence) ?></td>
                                    <td class="text-muted small"><?= $row->updated_at ? date('d/m/Y H:i', strtotime($row->updated_at)) : '-' ?></td>
                                    <td class="text-end">
                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                            <?= Html::a('<i class="fa-solid fa-pen me-1"></i> แก้ไข', ['fsn-sequence-update', 'id' => $row->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                                            <?= Html::a('<i class="fa-solid fa-trash me-1"></i> ลบ', ['fsn-sequence-delete', 'id' => $row->id], [
                                                'class' => 'btn btn-outline-danger btn-sm',
                                                'data' => ['method' => 'post', 'confirm' => 'ยืนยันลบลำดับหมวด «' . Html::encode($row->category_id) . '» ปี ' . $row->year . '?']
                                            ]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
