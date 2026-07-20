<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\components\CategoriseHelper;
use app\components\widgets\DataSummaryWidget;

/** @var yii\web\View $this */
/** @var app\modules\jd\models\JdTemplateSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'คลัง Template JD';
$this->params['breadcrumbs'][] = $this->title;
$models = $dataProvider->getModels();
$pagination = $dataProvider->pagination;
$statusLabels = ['draft' => 'ฉบับร่าง', 'review' => 'รอตรวจสอบ', 'active' => 'พร้อมใช้งาน', 'retired' => 'ยกเลิกใช้งาน'];
$statusClasses = ['draft' => 'jd-status--draft', 'review' => 'jd-status--review', 'active' => 'jd-status--active', 'retired' => 'jd-status--retired'];
?>
<?php $this->beginBlock('page-title'); ?>
<div><h4 class="fw-semibold mb-1">คลัง Template JD</h4><div class="text-muted small">จัดทำ Template มาตรฐานและ Template เฉพาะลักษณะงานของแต่ละตำแหน่ง</div></div>
<?php $this->endBlock(); ?>
<?php
$pageAction = Html::a('<i class="bi bi-plus-lg me-1"></i>สร้าง Template', ['create'], ['class' => 'btn btn-primary']);
foreach (['action', 'page-action'] as $actionBlock) {
    $this->beginBlock($actionBlock);
    echo $pageAction;
    $this->endBlock();
}
?>

<style>
.jd-page{--jd-ink:#1a202c;--jd-muted:#718096;--jd-surface:#fff;--jd-surface-2:#f7f9fc;--jd-line:rgba(15,23,42,.08);--jd-line-strong:rgba(15,23,42,.14);--jd-radius:10px;--jd-radius-sm:8px;padding:1rem 1rem 2rem}.jd-library{background:var(--jd-surface);border:1px solid var(--jd-line);border-radius:var(--jd-radius);box-shadow:0 1px 2px rgba(15,23,42,.04)}.jd-library__filter{padding:1rem 1.1rem;border-bottom:1px solid var(--jd-line);background:var(--jd-surface-2);border-radius:var(--jd-radius) var(--jd-radius) 0 0}.jd-library__filter .form-label{font-size:.8rem;font-weight:600;color:#4a5568}.jd-library__filter .form-control,.jd-library__filter .form-select{min-height:42px;border-color:var(--jd-line-strong);border-radius:var(--jd-radius-sm)}.jd-library table{margin:0}.jd-library th{background:var(--jd-surface-2);color:#4a5568;font-size:.78rem;font-weight:600;padding:.65rem .9rem;border-bottom-color:var(--jd-line-strong)}.jd-library td{padding:.72rem .9rem;font-size:.88rem;border-color:var(--jd-line)}.jd-template-name{font-weight:600;color:var(--jd-ink);text-decoration:none}.jd-template-name:hover{color:#0a58ca}.jd-template-meta{font-size:.74rem;color:var(--jd-muted);margin-top:.15rem}.jd-library__footer{padding:.75rem 1rem;border-top:1px solid var(--jd-line);background:var(--jd-surface-2);border-radius:0 0 var(--jd-radius) var(--jd-radius)}.jd-status--draft{color:#4a5568;background:#eef2f7}.jd-status--review{color:#92400e;background:rgba(180,83,9,.10)}.jd-status--active{color:#166534;background:rgba(21,128,61,.10)}.jd-status--retired{color:#64748b;background:#e2e8f0}@media(max-width:991.98px){.jd-page{padding:.75rem}.jd-library__desktop{display:none}.jd-mobile-item{display:block;padding:.8rem;border-bottom:1px solid var(--jd-line);text-decoration:none;color:inherit}.jd-mobile-item:last-child{border-bottom:0}}@media(min-width:992px){.jd-library__mobile{display:none}}
</style>

<div class="jd-page">
<div class="jd-library">
    <div class="jd-library__filter">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
        <div class="row g-2 align-items-end">
            <div class="col-md-5"><?= $form->field($searchModel, 'name')->textInput(['placeholder' => 'ค้นหาชื่อ Template'])->label('ค้นหา') ?></div>
            <div class="col-md-4"><?= $form->field($searchModel, 'position_code')->dropDownList(['' => 'ทุกตำแหน่ง'] + CategoriseHelper::PositionName())->label('ตำแหน่ง') ?></div>
            <div class="col-md-3 d-flex gap-2 pb-3"><?= Html::submitButton('<i class="bi bi-search me-1"></i>ค้นหา', ['class' => 'btn btn-primary']) ?><?= Html::a('ล้างตัวกรอง', ['index'], ['class' => 'btn btn-outline-secondary']) ?></div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <div class="jd-library__desktop">
        <table class="table table-hover align-middle">
            <thead><tr><th>Template</th><th>ตำแหน่ง</th><th>ประเภท</th><th>Revision</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
            <tbody>
            <?php foreach ($models as $item): $status = $item->lifecycle_status ?: 'draft'; ?>
                <tr>
                    <td><a class="jd-template-name" href="<?= Html::encode(\yii\helpers\Url::to(['structure', 'id' => $item->id])) ?>"><?= Html::encode($item->name) ?></a><div class="jd-template-meta"><?= Html::encode($item->template_code ?: 'ยังไม่กำหนดรหัส') ?></div></td>
                    <td><?= Html::encode($item->getPositionTitle()) ?></td>
                    <td><?= $item->template_type === 'variant' ? 'เฉพาะลักษณะงาน' : 'มาตรฐาน' ?></td>
                    <td><?= (int) ($item->revision_no ?: 1) ?></td>
                    <td><span class="badge rounded-pill <?= $statusClasses[$status] ?? 'jd-status--draft' ?>"><?= Html::encode($statusLabels[$status] ?? $status) ?></span></td>
                    <td class="text-end">
                        <?= Html::a('จัดทำเนื้อหา', ['structure', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'แก้ไขข้อมูล Template']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="jd-library__mobile">
        <?php foreach ($models as $item): ?>
            <a class="jd-mobile-item" href="<?= Html::encode(\yii\helpers\Url::to(['structure', 'id' => $item->id])) ?>"><div class="d-flex justify-content-between gap-2"><strong><?= Html::encode($item->name) ?></strong><span>Rev. <?= (int) ($item->revision_no ?: 1) ?></span></div><div class="jd-template-meta"><?= Html::encode($item->getPositionTitle()) ?> · <?= $item->template_type === 'variant' ? 'เฉพาะลักษณะงาน' : 'มาตรฐาน' ?></div></a>
        <?php endforeach; ?>
    </div>
    <?php if (!$models): ?><div class="text-center p-5"><h5 class="fw-semibold">ยังไม่มี Template</h5><p class="text-muted">สร้าง Template แรกเพื่อเริ่มจัดทำข้อมูลตำแหน่ง</p><?= Html::a('สร้าง Template', ['create'], ['class' => 'btn btn-primary']) ?></div><?php endif; ?>
    <?php if ($models): ?><div class="jd-library__footer"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div><?php endif; ?>
</div>
</div>
