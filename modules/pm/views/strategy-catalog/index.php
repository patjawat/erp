<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\components\RichText;
use app\components\widgets\DataSummaryWidget;

/** @var string $type @var int|null $planId @var app\modules\pm\models\StrategyPlan|null $plan */
/** @var int|null $year @var string $q @var array $plans */

// ทะเบียนตัวชี้วัดใช้หน้า indicator-index แยกต่างหาก ที่นี่เหลือปัจจัย/RCA มาตรการ และแผนงานหลัก
$labels = ['factor' => 'ปัจจัยความสำเร็จ/RCA', 'measure' => 'มาตรการ', 'program' => 'แผนงานหลัก'];
$this->title = 'ทะเบียน' . $labels[$type];
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => $type === 'program' ? 'program' : 'indicator']) ?><?php $this->endBlock();

app\assets\RichTextAsset::register($this);
$canEdit = $plan && $plan->isEditable() && Yii::$app->user->can('pmStrategyManage');
$planItems = ArrayHelper::map($plans, 'id', fn($p) => $p->name . ' · รุ่น ' . $p->version);
?>
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3 mb-3">
    <div>
        <h2 class="h5 mb-1"><?= Html::encode($labels[$type]) ?></h2>
        <p class="text-muted mb-0">เลือกชุดแผนเพื่อดูและจัดการข้อมูลที่อ้างอิงร่วมกัน</p>
    </div>
    <?php if ($canEdit && $type === 'program'): ?>
        <?= Html::a('<i data-lucide="plus" class="me-1"></i> เพิ่ม' . $labels[$type], ['create', 'type' => $type, 'planId' => $planId], ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <?= Html::beginForm(['index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
    <?= Html::hiddenInput('type', $type) ?>
    <div class="col-12 col-md-5">
        <label class="form-label fw-semibold">ชุดแผนยุทธศาสตร์</label>
        <?= Html::dropDownList('planId', $planId, $planItems, ['class' => 'form-select', 'prompt' => 'เลือกชุดแผน']) ?>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label fw-semibold">ค้นหา</label>
        <?= Html::textInput('q', $q, ['class' => 'form-control', 'placeholder' => 'รหัสหรือชื่อรายการ']) ?>
    </div>
    <div class="col-12 col-md-auto"><?= Html::submitButton('แสดงข้อมูล', ['class' => 'btn btn-primary']) ?></div>
    <?= Html::endForm() ?>
</div></div>

<div class="card border-0 shadow-sm overflow-hidden"><div class="card-body p-0">
    <div class="table-responsive d-none d-md-block"><table class="table align-middle mb-0">
        <thead class="table-light"><tr><th class="ps-4">รายการ</th><th>รายละเอียด</th><th class="text-end pe-4">จัดการ</th></tr></thead>
        <tbody>
        <?php foreach ($dataProvider->models as $item): ?>
            <tr>
                <td class="ps-4"><div class="fw-semibold"><?= Html::encode($item->code ?: '-') ?></div></td>
                <td class="erp-richtext"><?= RichText::render($item->name) ?: '-' ?></td>
                <td class="text-end pe-4"><?php if ($canEdit): ?>
                    <?= Html::a('แก้ไข', ['update', 'type' => $type, 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?= Html::a('ลบ', ['delete', 'type' => $type, 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบ?']) ?>
                <?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$dataProvider->count): ?>
            <tr><td colspan="3" class="text-center text-muted py-5"><?= $planId ? 'ยังไม่มีข้อมูลในชุดแผนนี้' : 'กรุณาเลือกชุดแผนยุทธศาสตร์' ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>

    <div class="d-md-none p-3 d-grid gap-3">
        <?php foreach ($dataProvider->models as $item): ?>
            <article class="border rounded-3 p-3">
                <div class="fw-semibold"><?= Html::encode($item->code ?: '-') ?></div>
                <div class="small text-muted mt-2"><?= Html::encode(RichText::plain($item->name, 160) ?: '-') ?></div>
                <?php if ($canEdit): ?><div class="d-flex flex-wrap gap-2 mt-3">
                    <?= Html::a('แก้ไข', ['update', 'type' => $type, 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?= Html::a('ลบ', ['delete', 'type' => $type, 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบ?']) ?>
                </div><?php endif; ?>
            </article>
        <?php endforeach; ?>
        <?php if (!$dataProvider->count): ?>
            <div class="text-center text-muted py-4"><?= $planId ? 'ยังไม่มีข้อมูล' : 'กรุณาเลือกชุดแผนยุทธศาสตร์' ?></div>
        <?php endif; ?>
    </div>
</div>
<div class="card-footer bg-body px-4 py-3"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
</div>
