<?php

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\modules\purchase\models\Doc;
use app\components\widgets\DataSummaryWidget;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\DocSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $counters */

/**
 * ทะเบียนเอกสารที่สร้างแล้ว
 *
 * หน้านี้มีไว้ตามที่ผู้ใช้เลือกให้เก็บเอกสารลงฐานข้อมูล เพื่อพิมพ์ซ้ำได้เหมือนฉบับที่
 * ลงนามไปแล้ว และตรวจย้อนหลังได้ว่าออกหนังสือฉบับใดไปเมื่อไร — ไม่ใช่หน้าเริ่มงาน
 * หน้าเริ่มงานคือหน้าการ์ดที่ /purchase/doc
 */

$this->title = 'ทะเบียนเอกสาร';
$this->params['breadcrumbs'][] = ['label' => 'งานพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = ['label' => 'สร้างเอกสาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$year = $searchModel->thai_year ?: (int) AppHelper::YearBudget();
$open = (int) Yii::$app->request->get('open');

$kpi = function (string $icon, string $color, $value, string $label) {
    return '<div class="col-6 col-lg-3">'
        . '<div class="card h-100"><div class="card-body d-flex align-items-center gap-3 py-3">'
        . '<div class="rounded-3 d-flex align-items-center justify-content-center text-bg-' . $color . '"'
        . ' style="width:42px;height:42px"><i class="bi ' . $icon . ' fs-5"></i></div>'
        . '<div><div class="fs-4 fw-semibold lh-1">' . $value . '</div>'
        . '<div class="small text-muted">' . $label . '</div></div>'
        . '</div></div></div>';
};
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-journal-text"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'doc']) ?>
<?php $this->endBlock(); ?>

<div class="d-flex flex-wrap gap-2 mb-3">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับหน้าสร้างเอกสาร', ['index'], [
        'class' => 'btn btn-outline-secondary',
    ]) ?>
    <?= Html::a('<i class="bi bi-sliders me-1"></i>จัดการแม่แบบ', ['/purchase/doc-template'], [
        'class' => 'btn btn-outline-primary',
    ]) ?>
</div>

<div class="row g-3 mb-3">
    <?= $kpi('bi-files', 'primary', number_format($counters['total']), 'เอกสารในทะเบียน (ปี ' . $year . ')') ?>
    <?= $kpi('bi-pencil-square', 'secondary', number_format($counters['draft']), 'ยังเป็นร่าง') ?>
    <?= $kpi('bi-patch-check', 'success', number_format($counters['final']), 'ออกเลขแล้ว') ?>
    <?= $kpi('bi-printer', 'info', number_format($counters['printed']), 'เคยพิมพ์แล้ว') ?>
</div>

<div class="card mb-3">
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>
</div>

<?php Pjax::begin(['id' => 'doc-container', 'enablePushState' => false]); ?>
<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-journal-text"></i> เอกสารที่สร้างแล้ว
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">
                <?= number_format($dataProvider->getTotalCount()) ?>
            </span>
        </h6>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-sliders2 me-1"></i>ระบุเรื่องอ้างอิงเอง', ['create'], [
                'class' => 'btn btn-outline-secondary btn-sm',
                'title' => 'สร้างเอกสารโดยเลือกเรื่องอ้างอิงและกรอกเลขที่เอง',
                'data' => ['pjax' => 0],
            ]) ?>
            <?= Html::a('<i class="bi bi-plus-circle me-1"></i>สร้างเอกสาร', ['index'], [
                'class' => 'btn btn-success btn-sm',
                'data' => ['pjax' => 0],
            ]) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary">
                    <tr>
                        <th style="width:42px" class="text-center">#</th>
                        <th style="min-width:120px">เลขที่ / วันที่</th>
                        <th style="min-width:260px">ชื่อเอกสาร</th>
                        <th style="min-width:190px">เรื่องต้นทาง</th>
                        <th style="min-width:110px">สถานะ</th>
                        <th style="min-width:120px">พิมพ์ล่าสุด</th>
                        <th style="width:170px" class="text-end">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php $offset = $dataProvider->pagination ? $dataProvider->pagination->offset : 0; ?>
                    <?php foreach ($dataProvider->getModels() as $i => $item): ?>
                        <tr>
                            <td class="text-center text-muted"><?= $offset + $i + 1 ?></td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                    <?= Html::encode($item->doc_no ?: '—') ?>
                                </span>
                                <div class="small text-muted">
                                    <?= $item->doc_date ? AppHelper::convertToThai($item->doc_date) : '—' ?>
                                </div>
                            </td>
                            <td>
                                <?= Html::a(
                                    '<i class="bi bi-pencil-square me-1"></i>' . Html::encode($item->title),
                                    ['editor', 'id' => $item->id],
                                    [
                                        'class' => 'open-modal fw-semibold small text-decoration-none js-doc-open',
                                        'data' => ['size' => 'modal-xl', 'pjax' => 0, 'doc-id' => $item->id],
                                        'title' => 'เปิดแก้ไข ' . $item->title,
                                    ]
                                ) ?>
                                <?php if ($item->template): ?>
                                    <div class="small text-muted">
                                        แม่แบบ: <?= Html::encode($item->template->name) ?>
                                    </div>
                                <?php elseif ($item->template_code): ?>
                                    <div class="small text-muted">
                                        แม่แบบ <?= Html::encode($item->template_code) ?> (ถูกลบแล้ว)
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= Html::encode($item->refLabel()) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $item->statusColor() ?>">
                                    <?= Html::encode($item->statusName()) ?>
                                </span>
                            </td>
                            <td class="small">
                                <?php if ($item->printed_at): ?>
                                    <?= AppHelper::convertToThai(substr($item->printed_at, 0, 10)) ?>
                                    <div class="text-muted"><?= (int) $item->print_count ?> ครั้ง</div>
                                <?php else: ?>
                                    <span class="text-muted">ยังไม่พิมพ์</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?= Html::a('<i class="bi bi-printer"></i>', ['print', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'title' => 'พริ้นท์ ' . $item->title,
                                        'aria-label' => 'พริ้นท์ ' . $item->title,
                                        'target' => '_blank',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                    <?= Html::a('<i class="bi bi-file-earmark-word"></i>', ['word', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'ส่งออก Word ' . $item->title,
                                        'aria-label' => 'ส่งออก Word ' . $item->title,
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                    <?= Html::a(
                                        '<i class="bi bi-' . ($item->status === Doc::STATUS_FINAL ? 'unlock' : 'lock') . '"></i>',
                                        ['toggle-status', 'id' => $item->id],
                                        [
                                            'class' => 'btn btn-sm btn-outline-success',
                                            'title' => $item->status === Doc::STATUS_FINAL
                                                ? 'ปลดล็อกกลับเป็นร่าง'
                                                : 'ทำเครื่องหมายว่าออกเลขแล้ว',
                                            'aria-label' => 'สลับสถานะ ' . $item->title,
                                            'data' => ['pjax' => 0],
                                        ]
                                    ) ?>
                                    <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'title' => 'ลบ ' . $item->title,
                                        'aria-label' => 'ลบ ' . $item->title,
                                        'data' => [
                                            'confirm' => 'ยืนยันการลบเอกสาร "' . $item->title . '" ออกจากทะเบียน ?',
                                            'method' => 'post',
                                            'pjax' => 0,
                                        ],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$dataProvider->getTotalCount()): ?>
                        <tr>
                            <td colspan="7">
                                <div class="text-center py-5">
                                    <div class="fw-semibold mb-1">ยังไม่มีเอกสารที่ตรงกับเงื่อนไข</div>
                                    <div class="text-muted small mb-3">
                                        เอกสารจะมาอยู่ในทะเบียนนี้เมื่อสร้างจากหน้าการ์ด
                                        หรือจากเมนูพิมพ์เอกสารในหน้ารายการจัดซื้อจัดจ้าง
                                    </div>
                                    <?= Html::a('ไปหน้าสร้างเอกสาร', ['index'], [
                                        'class' => 'btn btn-success',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-body-tertiary">
        <?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
    </div>
</div>
<?php Pjax::end(); ?>

<?php
// เก็บ backdrop ที่ค้างหลังปิด modal — ดูคำอธิบายเต็มที่ views/doc/index.php
$cleanupJs = <<<'JS'
jQuery('#main-modal').off('hidden.bs.modal.docCleanup')
    .on('hidden.bs.modal.docCleanup', function () {
        if (document.querySelector('.modal.show')) { return; }
        document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
JS;
$this->registerJs($cleanupJs, View::POS_READY);

if ($open) {
    // รอ window load จริงก่อนสั่งเปิด ไม่ใช่แค่ DOM ready — การสั่งเปิดตอนหน้ายัง init
    // ไม่เสร็จทำให้ Bootstrap สร้าง backdrop ซ้อนสองแผ่น แล้วเหลือค้างหนึ่งแผ่นตอนปิด
    $js = <<<JS
    (function () {
        var done = false;

        function openDocEditor() {
            if (done) { return; }
            var \$link = jQuery('.js-doc-open[data-doc-id="{$open}"]').first();
            if (!\$link.length) { return; }
            if (document.querySelector('.modal.show')) { return; }
            done = true;
            \$link.trigger('click');
        }

        if (document.readyState === 'complete') {
            window.setTimeout(openDocEditor, 0);
        } else {
            jQuery(window).one('load', function () { window.setTimeout(openDocEditor, 0); });
        }
    })();
    JS;
    $this->registerJs($js, View::POS_READY);
}
?>
