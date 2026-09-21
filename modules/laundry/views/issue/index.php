<?php

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;

/** @var string $date */
/** @var array $rows */
/** @var array $staffNames  user_id => name */
$this->title = 'ส่งผ้า / เบิกจ่าย';
$canManage = Yii::$app->user->can('laundry.manage');
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'issue']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-box-arrow-right me-2"></i>ส่งผ้า / เบิกจ่าย <span class="text-body-secondary fs-6 fw-normal">ประจำวันที่ <?= Html::encode(ThaiDateHelper::formatThaiDate($date)) ?></span></h1>
        <div class="d-flex align-items-center gap-2">
            <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-1']) ?>
                <?= DatepickerThai::widget(['name' => 'date', 'value' => AppHelper::convertToThai($date), 'options' => ['class' => 'form-control form-control-sm', 'style' => 'max-width:140px', 'autocomplete' => 'off', 'onchange' => 'this.form.submit()']]) ?>
            <?= Html::endForm() ?>
            <?php if ($canManage): ?>
                <?= Html::a('<i class="bi bi-plus-lg me-1"></i>เพิ่มข้อมูลส่งผ้า', ['create'], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php foreach (['error' => 'danger', 'success' => 'success'] as $k => $c): ?>
        <?php if (Yii::$app->session->hasFlash($k)): ?><div class="alert alert-<?= $c ?> d-flex align-items-center"><i class="bi bi-<?= $c === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($k)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr>
                    <th class="ps-4">เลขที่</th><th>เวลา</th><th>หน่วยงาน</th><th>ผู้จ่าย</th>
                    <th class="text-end">ประเภท</th><th class="text-end pe-4">รวมจ่าย (ชิ้น)</th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= Html::encode($r['issue_no'] ?: '#' . $r['id']) ?></td>
                        <td><?= date('H:i', strtotime($r['issued_at'])) ?></td>
                        <td><?= Html::encode($r['unit_name'] ?: '#' . $r['tree_id']) ?></td>
                        <td class="text-body-secondary small"><?= Html::encode($staffNames[$r['created_by']] ?? '') ?></td>
                        <td class="text-end"><?= (int) $r['types'] ?></td>
                        <td class="text-end pe-4 fw-semibold"><?= number_format((int) $r['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="text-center text-body-secondary py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>ยังไม่มีการส่งผ้าในวันนี้</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>
