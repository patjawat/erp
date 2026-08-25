<?php
use app\modules\iacRisk\models\FiscalYear;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ตั้งค่า IAC&Risk';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>โรงพยาบาล ปีงบประมาณ และรอบรายงาน<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ IAC&Risk', ['/iac-risk/default/index'], ['class' => 'btn btn-outline-secondary']) ?><?php $this->endBlock(); ?>

<?php if (!$hospitals): ?>
<section class="card bg-body border shadow-sm mb-3">
    <div class="card-body text-center py-5">
        <h2 class="h5 fw-semibold">เชื่อมโรงพยาบาลปัจจุบัน</h2>
        <p class="text-body-secondary">ระบบจะใช้ชื่อและรหัสโรงพยาบาลจากการตั้งค่า ERP เดิม</p>
        <?= Html::beginForm(['initialize'], 'post') ?><?= Html::submitButton('เชื่อมข้อมูลโรงพยาบาล', ['class' => 'btn btn-primary']) ?><?= Html::endForm() ?>
    </div>
</section>
<?php else: ?>
<div class="d-flex justify-content-end mb-3"><?= Html::a('<i class="bi bi-plus-lg me-1"></i> สร้างปีงบประมาณ', ['create-year'], ['class' => 'btn btn-primary']) ?></div>
<section class="card bg-body border shadow-sm">
    <div class="card-body p-0">
        <div class="d-none d-lg-block">
            <table class="table align-middle mb-0">
                <thead class="table-secondary"><tr><th>โรงพยาบาล</th><th>ปีงบประมาณ</th><th>ช่วงเวลา</th><th>รอบรายงาน</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
                <tbody>
                <?php foreach ($years as $year): ?>
                    <tr>
                        <td><?= Html::encode($year->hospital->name ?? '-') ?></td>
                        <td class="fw-semibold"><?= (int) $year->fiscal_year ?></td>
                        <td><?= Yii::$app->formatter->asDate($year->start_date) ?> ถึง <?= Yii::$app->formatter->asDate($year->end_date) ?></td>
                        <td><?= count($year->periods) ?> รอบ</td>
                        <td><span class="badge bg-<?= $year->status === FiscalYear::STATUS_OPEN ? 'success' : 'secondary' ?>-subtle text-<?= $year->status === FiscalYear::STATUS_OPEN ? 'success' : 'secondary' ?>-emphasis"><?= Html::encode(FiscalYear::statusLabels()[$year->status] ?? $year->status) ?></span></td>
                        <td class="text-end">
                            <?php if ($year->status === FiscalYear::STATUS_DRAFT): ?><?= Html::beginForm(['open', 'id' => $year->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('เปิดใช้งาน', ['class' => 'btn btn-sm btn-outline-primary']) ?><?= Html::endForm() ?><?php endif; ?>
                            <?php if ($year->status === FiscalYear::STATUS_OPEN): ?><?= Html::beginForm(['close', 'id' => $year->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('ปิดปี', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'ยืนยันการปิดปีงบประมาณ?']) ?><?= Html::endForm() ?><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$years): ?><tr><td colspan="6" class="text-center py-5 text-body-secondary">ยังไม่มีปีงบประมาณ</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <ul class="list-group list-group-flush d-lg-none" role="list">
        <?php foreach ($years as $year): ?>
            <li class="list-group-item bg-body py-3">
                <div class="d-flex justify-content-between gap-2"><span class="fw-semibold">ปี <?= (int) $year->fiscal_year ?></span><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode(FiscalYear::statusLabels()[$year->status] ?? $year->status) ?></span></div>
                <div class="small text-body-secondary mt-1"><?= Html::encode($year->hospital->name ?? '-') ?> · <?= count($year->periods) ?> รอบ</div>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>
