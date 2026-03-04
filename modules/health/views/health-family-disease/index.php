<?php

use app\modules\health\models\HealthOption;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var HealthOption[] $models */

$this->title = 'ตั้งค่าประวัติการเจ็บป่วยในครอบครัว';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลสุขภาพ', 'url' => ['/health']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="dna"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/health/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php foreach (['success' => 'success', 'warning' => 'warning'] as $type => $bs): ?>
    <?php if (Yii::$app->session->hasFlash($type)): ?>
        <div class="alert alert-<?= $bs ?> alert-dismissible fade show rounded-3 mb-3" role="alert">
            <?= Yii::$app->session->getFlash($type) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary-gradient text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-white small fw-normal">
            <i class="fas fa-dna me-1"></i> รายการโรคประวัติครอบครัว
            <span class="badge bg-light bg-opacity-10 text-white border border-light-subtle rounded-pill fw-medium px-2 py-1">
                <?= count($models) ?> รายการ
            </span>
        </h6>
        <?= Html::a(
            '<i class="fas fa-plus me-1"></i> เพิ่มรายการโรค',
            ['create'],
            [
                'class'      => 'btn btn-light btn-sm rounded-pill px-3 open-modal',
                'data-size'  => 'modal-md',
                'data-title' => 'เพิ่มรายการโรคในครอบครัว',
            ]
        ) ?>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive" id="pjax-container">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 text-muted small" style="width:60px;">#</th>
                        <th class="text-muted small" style="width:160px;">รหัสโรค</th>
                        <th class="text-muted small">ชื่อโรค</th>
                        <th class="text-center text-muted small" style="width:100px;">สถานะ</th>
                        <th class="text-center text-muted small" style="width:160px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php if (empty($models)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i data-lucide="inbox" class="d-block mx-auto mb-3 text-muted" style="width:48px;height:48px;opacity:.3;"></i>
                                <p class="text-muted mb-3">ยังไม่มีข้อมูลในระบบ</p>
                                <?= Html::beginForm(['seed'], 'post') ?>
                                    <?= Html::submitButton(
                                        '<i class="fas fa-download me-1"></i> นำเข้าข้อมูลพื้นฐาน',
                                        ['class' => 'btn btn-outline-warning rounded-pill px-4']
                                    ) ?>
                                <?= Html::endForm() ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($models as $i => $model): ?>
                        <tr>
                            <td class="ps-3 text-center text-muted small"><?= $i + 1 ?></td>
                            <td>
                                <code class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                                    <?= Html::encode($model->code) ?>
                                </code>
                            </td>
                            <td class="fw-medium"><?= Html::encode($model->title) ?></td>
                            <td class="text-center">
                                <?php if ($model->active): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">ใช้งาน</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ปิด</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
                                    <?= Html::a(
                                        '<i class="fas fa-pencil-alt"></i>',
                                        ['update', 'id' => $model->id],
                                        [
                                            'class'      => 'btn btn-sm btn-light border-0 rounded-circle text-primary open-modal',
                                            'style'      => 'width:32px;height:32px;display:flex;align-items:center;justify-content:center;',
                                            'title'      => 'แก้ไข',
                                            'data-size'  => 'modal-md',
                                            'data-title' => 'แก้ไขรายการโรค',
                                        ]
                                    ) ?>
                                    <?= Html::a(
                                        '<i class="fas fa-trash-alt"></i>',
                                        ['delete', 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-sm btn-light border-0 rounded-circle text-danger ms-1',
                                            'style' => 'width:32px;height:32px;display:flex;align-items:center;justify-content:center;',
                                            'title' => 'ลบ',
                                            'data'  => [
                                                'confirm' => 'ลบ "' . $model->title . '" ออกจากรายการ?',
                                                'method'  => 'post',
                                            ],
                                        ]
                                    ) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer py-3 border-top-0">
        <div class="d-flex align-items-start gap-2 small text-muted">
            <i class="fas fa-info-circle text-info mt-1"></i>
            <span>รายการที่ <strong>ใช้งาน</strong> จะแสดงในแบบฟอร์มคัดกรองสุขภาพ (ส่วนที่ 3: ประวัติการเจ็บป่วยในครอบครัว) — รายการที่ <strong>ปิด</strong> จะไม่แสดงในฟอร์ม — การเปลี่ยน <strong>รหัสโรค</strong> หลังมีข้อมูลแล้วจะกระทบข้อมูลเดิม</span>
        </div>
    </div>
</div>
