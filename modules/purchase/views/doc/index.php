<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\modules\purchase\models\DocTemplate;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\DocTemplate[] $templates */
/** @var array $refOptions  ref_type => [{id,label}, ...] */
/** @var array $refDefault  ref_type => id ของรายการล่าสุด */
/** @var int $year */

/**
 * หน้าสร้างเอกสาร — เลือกเรื่องอ้างอิงด้านบน แล้วกดการ์ดเอกสารที่ต้องการ
 *
 * ลำดับนี้ยึดตามโปรแกรมพัสดุภาครัฐที่ผู้ใช้ชี้ให้ดู คือ "เลือกข้อมูลอ้างอิงก่อน
 * แล้วจึงเลือกการ์ด" เหตุผลที่มันเร็วกว่าแบบมีฟอร์มขวาง: ค่าทุกค่าที่ฟอร์มจะถาม
 * ระบบเดาได้เองอยู่แล้ว (เรื่องล่าสุด วันที่วันนี้ ชื่อเอกสารจากแม่แบบ) การถามซ้ำ
 * จึงเป็นการให้ผู้ใช้พิมพ์สิ่งที่ระบบรู้แล้ว
 *
 * href ของการ์ดถูกเขียนใหม่ทุกครั้งที่เปลี่ยนช่องอ้างอิงด้านบน ไม่ได้ไปคำนวณตอนคลิก
 * เพราะตัวเปิด modal ของ ERP ผูกไว้ที่ body แบบ delegated ถ้าไปแก้ href ตอนคลิก
 * จะแข่งกับ handler นั้นว่าใครทำงานก่อน ซึ่งเดาไม่ได้และพังเป็นครั้งคราว
 */

$this->title = 'สร้างเอกสาร';
$this->params['breadcrumbs'][] = ['label' => 'งานพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;

$refLabels = [
    DocTemplate::REF_ORDER => ['icon' => 'bi-cart-check', 'label' => 'รายการจัดซื้อจัดจ้าง'],
    DocTemplate::REF_CONTRACT => ['icon' => 'bi-file-earmark-check', 'label' => 'สัญญา/ข้อตกลง'],
    DocTemplate::REF_BOND => ['icon' => 'bi-shield-check', 'label' => 'หลักประกัน'],
];

/** เอกสารใบไหนใช้ช่องอ้างอิงช่องใด — เอาไปขึ้นบรรทัด "ใช้กับ:" ใต้แต่ละช่อง */
$usedBy = [];
foreach ($templates as $template) {
    if ($template->ref_type !== DocTemplate::REF_NONE) {
        $usedBy[$template->ref_type][] = $template->name;
    }
}
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-printer"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'doc']) ?>
<?php $this->endBlock(); ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="alert alert-success mb-0 py-2 flex-grow-1">
        <i class="bi bi-check2-square me-1"></i>
        เอกสารทุกใบแก้ไขได้บนจอก่อนพริ้นท์ — กด <span class="fw-medium">ตราครุฑ</span>
        ในแถบเครื่องมือเพื่อเพิ่มหรือเปลี่ยนขนาดตรา
    </div>
    <?= Html::a('<i class="bi bi-journal-text me-1"></i>ทะเบียนเอกสาร', ['register'], [
        'class' => 'btn btn-outline-secondary',
    ]) ?>
    <?= Html::a('<i class="bi bi-sliders me-1"></i>จัดการแม่แบบ', ['/purchase/doc-template'], [
        'class' => 'btn btn-outline-primary',
    ]) ?>
</div>

<?php if (empty($templates)): ?>
    <div class="alert alert-warning">
        <div class="fw-medium">
            <i class="bi bi-exclamation-triangle me-1"></i>ยังไม่มีแม่แบบเอกสารที่เปิดใช้อยู่
        </div>
        <div class="small">
            หน้านี้แสดงเอกสารจากแม่แบบที่เปิดใช้
            <?= Html::a('ไปหน้าจัดการแม่แบบ', ['/purchase/doc-template'], ['class' => 'alert-link']) ?>
            เพื่อเพิ่มหรือเปิดใช้แม่แบบก่อน
        </div>
    </div>
<?php else: ?>

    <?php if (!empty($refOptions)): ?>
        <div class="card border-primary-subtle mb-3">
            <div class="card-header bg-primary-subtle py-2">
                <h6 class="mb-0 text-primary-emphasis">
                    <i class="bi bi-pin-angle me-1"></i>เลือกข้อมูลอ้างอิง
                    <span class="fw-normal small">(ระบบดึงค่าจากเรื่องที่เลือกมาเติมในเอกสารให้)</span>
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($refOptions as $refType => $options): ?>
                        <?php $meta = $refLabels[$refType] ?? ['icon' => 'bi-link-45deg', 'label' => $refType]; ?>
                        <div class="col-lg-4">
                            <label class="form-label fw-medium" for="doc-ref-<?= Html::encode($refType) ?>">
                                <i class="bi <?= $meta['icon'] ?> me-1"></i><?= Html::encode($meta['label']) ?>
                            </label>
                            <?php if (empty($options)): ?>
                                <select class="form-select js-doc-ref" id="doc-ref-<?= Html::encode($refType) ?>"
                                    data-ref-type="<?= Html::encode($refType) ?>" disabled>
                                    <option value="">ยังไม่มีข้อมูลในปีงบ <?= $year ?></option>
                                </select>
                            <?php else: ?>
                                <select class="form-select js-doc-ref" id="doc-ref-<?= Html::encode($refType) ?>"
                                    data-ref-type="<?= Html::encode($refType) ?>">
                                    <?php foreach ($options as $i => $row): ?>
                                        <option value="<?= (int) $row['id'] ?>" <?= $i === 0 ? 'selected' : '' ?>>
                                            <?= $i === 0 ? '— ล่าสุด (ดึงอัตโนมัติ) — ' : '' ?><?= Html::encode($row['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                            <?php if (!empty($usedBy[$refType])): ?>
                                <div class="form-text">
                                    ใช้กับ: <?= Html::encode(implode(', ', $usedBy[$refType])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($templates as $template): ?>
            <?php
            $refType = $template->ref_type;
            $hasRef = $refType !== DocTemplate::REF_NONE;
            $ready = !$hasRef || !empty($refDefault[$refType]);

            $url = Url::to([
                'quick',
                'template_id' => $template->id,
                'ref_id' => $refDefault[$refType] ?? null,
            ]);
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body d-flex flex-column text-center">
                        <div class="mb-2">
                            <i class="bi <?= $ready ? 'bi-file-earmark-text text-success' : 'bi-file-earmark-x text-secondary' ?> fs-1"></i>
                        </div>

                        <div class="fw-semibold mb-1"><?= Html::encode($template->name) ?></div>

                        <div class="small text-muted mb-2">
                            <?php if ($template->law_ref): ?>
                                <div><i class="bi bi-journal-check me-1"></i><?= Html::encode($template->law_ref) ?></div>
                            <?php endif; ?>
                            <div>
                                <i class="bi bi-link-45deg me-1"></i>
                                <?= Html::encode($template->refTypeName()) ?>
                                <?php if ($template->emblem !== DocTemplate::EMBLEM_NONE): ?>
                                    · มีตราครุฑ
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <?php if ($ready): ?>
                                <?= Html::a('สร้างเอกสาร <i class="bi bi-arrow-right ms-1"></i>', $url, [
                                    'class' => 'btn btn-outline-primary js-doc-card',
                                    'data' => [
                                        'size' => 'modal-xl',
                                        'template-id' => $template->id,
                                        'ref-type' => $hasRef ? $refType : '',
                                        'base' => Url::to(['quick', 'template_id' => $template->id]),
                                    ],
                                ]) ?>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary" disabled
                                    title="ยังไม่มี<?= Html::encode($template->refTypeName()) ?>ในปีงบ <?= $year ?>">
                                    ยังไม่มีข้อมูลอ้างอิง
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($legacy as $doc): ?>
            <?php
            // การ์ดของเอกสารที่ยังไม่ได้แปลง — ชี้ไปทางเดิม /ms-word/<key> ซึ่งต้องมี
            // id ของใบขอซื้อ จึงใช้ค่าจากช่อง "รายการจัดซื้อจัดจ้าง" ด้านบนเหมือนกัน
            $orderRef = $refDefault[DocTemplate::REF_ORDER] ?? null;
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body d-flex flex-column text-center">
                        <div class="mb-2">
                            <i class="bi bi-file-earmark-word text-secondary fs-1"></i>
                        </div>

                        <div class="fw-semibold mb-1"><?= Html::encode($doc['label']) ?></div>

                        <div class="small text-muted mb-2">
                            <div>
                                <i class="bi bi-download me-1"></i>ยังเป็นไฟล์ Word แก้บนจอไม่ได้
                            </div>
                            <div class="font-monospace"><?= Html::encode($doc['key']) ?>.docx</div>
                        </div>

                        <div class="mt-auto">
                            <?php if ($orderRef): ?>
                                <?= Html::a('ดาวน์โหลด Word <i class="bi bi-box-arrow-down ms-1"></i>', [
                                    '/ms-word/' . $doc['key'],
                                    'id' => $orderRef,
                                ], [
                                    'class' => 'btn btn-outline-secondary js-doc-legacy',
                                    'data' => [
                                        'size' => $doc['size'],
                                        'base' => Url::to(['/ms-word/' . $doc['key']]),
                                    ],
                                ]) ?>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary" disabled>
                                    ยังไม่มีข้อมูลอ้างอิง
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($legacy)): ?>
        <div class="alert alert-secondary small mt-3 mb-0">
            <i class="bi bi-info-circle me-1"></i>
            แปลงเป็นเอกสารแก้ไขบนจอแล้ว <?= $legacyProgress['done'] ?> จาก
            <?= $legacyProgress['total'] ?> ใบ — ใบที่เหลือยังใช้ทางเดิมคือดาวน์โหลดไฟล์ Word
            ไปเปิดใน Word เอง (การพรีวิวของทางเดิมใช้บริการภายนอกซึ่งเข้าถึงไม่ได้จากอินทราเน็ต
            จึงขึ้นว่า "ไม่มีตัวอย่างที่ใช้ได้" เสมอ)
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php
/**
 * ผูก class open-modal ให้การ์ดหลังจากเขียน href เสร็จ
 *
 * ไม่ใส่ open-modal มาจาก PHP ตั้งแต่แรก เพราะถ้าใส่แล้วผู้ใช้กดการ์ดก่อนที่ JS
 * ชุดนี้จะทำงาน (หน้ายังโหลดไม่เสร็จ) จะได้ href ที่ยังไม่ผูกกับช่องอ้างอิงที่เลือกไว้
 * เติม class ทีหลังจึงเท่ากับบอกว่า "การ์ดพร้อมใช้แล้ว" ในตัว
 */
$js = <<<'JS'
function docSyncCards() {
    var picked = {};
    jQuery('.js-doc-ref').each(function () {
        picked[jQuery(this).data('ref-type')] = jQuery(this).val() || '';
    });

    jQuery('.js-doc-card').each(function () {
        var $card = jQuery(this);
        var refType = String($card.data('ref-type') || '');
        var base = $card.data('base');

        if (refType === '') {
            $card.attr('href', base);
        } else {
            $card.attr('href', base + '&ref_id=' + encodeURIComponent(picked[refType] || ''));
        }

        $card.addClass('open-modal');
    });

    // การ์ดของเอกสารที่ยังไม่ได้แปลง ใช้ id ของใบขอซื้อจากช่องเดียวกัน
    jQuery('.js-doc-legacy').each(function () {
        var $card = jQuery(this);
        $card.attr('href', $card.data('base') + '?id=' + encodeURIComponent(picked['order'] || ''));
        $card.addClass('open-modal');
    });
}

jQuery('.js-doc-ref').on('change', docSyncCards);
docSyncCards();

// เก็บ backdrop ที่ค้างหลังปิด modal — แผ่นที่เกินมาโปร่งใสจนมองไม่เห็น แต่คลุมทั้งจอ
// และกินคลิกทุกจุด ทำให้ดูเหมือนโปรแกรมค้างทั้งที่ JS ไม่ได้ค้าง
jQuery('#main-modal').off('hidden.bs.modal.docCleanup')
    .on('hidden.bs.modal.docCleanup', function () {
        if (document.querySelector('.modal.show')) { return; }
        document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
JS;

$this->registerJs($js, View::POS_READY);
?>
