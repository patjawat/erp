<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\TorTemplate[] $items */
/** @var array $categories */
/** @var string $category */
/** @var string $q */

$pickerUrl = Url::to(['template-picker', 'title' => 'คลังแม่แบบคุณลักษณะ']);
?>

<div id="tor-template-picker">
    <div class="d-flex gap-2 mb-3">
        <input type="text" class="form-control" id="tor-tpl-q" value="<?= Html::encode($q) ?>"
            placeholder="ค้นหาชื่อรายการหรือข้อความในคุณลักษณะ…">
        <button type="button" class="btn btn-primary" id="tor-tpl-search">
            <i class="bi bi-search"></i>
        </button>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-sm rounded-pill px-3 tor-tpl-cat <?= $category === '' ? 'btn-primary' : 'btn-outline-secondary' ?>"
            data-cat="">ทั้งหมด</button>
        <?php foreach ($categories as $code => $info): ?>
            <button type="button"
                class="btn btn-sm rounded-pill px-3 tor-tpl-cat <?= $category === $code ? 'btn-primary' : 'btn-outline-secondary' ?>"
                data-cat="<?= Html::encode($code) ?>">
                <?= Html::encode($info['label']) ?>
                <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1"><?= $info['count'] ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="alert alert-secondary py-2 small">
        <i class="bi bi-info-circle me-1"></i>
        แม่แบบเป็นเพียงจุดตั้งต้น — ต้องตรวจสอบและปรับคุณลักษณะให้ตรงกับความต้องการใช้งานจริงก่อนบันทึกทุกครั้ง
        ราคาอ้างอิงที่แสดงใช้ดูประกอบเท่านั้น ไม่ใช่ราคาที่สืบได้
    </div>

    <div class="row g-2" style="max-height:52vh;overflow-y:auto">
        <?php foreach ($items as $t): ?>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 tor-tpl-card" role="button" data-id="<?= $t->id ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="fw-semibold"><?= Html::encode($t->title) ?></div>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis flex-shrink-0"><?= Html::encode($t->categoryName()) ?></span>
                    </div>
                    <div class="small text-muted mt-1" style="max-height:3.6em;overflow:hidden">
                        <?= Html::encode(mb_substr(trim(strip_tags((string) $t->spec)), 0, 120)) ?>…
                    </div>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <?php if ($t->unit_name): ?>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">หน่วย: <?= Html::encode($t->unit_name) ?></span>
                        <?php endif; ?>
                        <?php if ($t->delivery_days): ?>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">ส่งมอบ <?= $t->delivery_days ?> วัน</span>
                        <?php endif; ?>
                        <?php if ($t->warranty): ?>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">ประกัน <?= Html::encode($t->warranty) ?></span>
                        <?php endif; ?>
                        <?php if ($t->ref_price): ?>
                            <span class="badge text-bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                ราคาอ้างอิง <?= number_format((float) $t->ref_price, 2) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (!$items): ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    ไม่พบแม่แบบที่ตรงกับเงื่อนไข — ลองเปลี่ยนคำค้นหรือเลือกหมวดอื่น
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$js = <<<JS
(function () {
    var wrap = \$('#tor-template-picker');

    function reload(cat, q) {
        \$.get('{$pickerUrl}', { category: cat, q: q }, function (r) {
            if (r && r.content) \$('#main-modal .modal-body').html(r.content);
        }, 'json');
    }

    wrap.on('click', '.tor-tpl-cat', function () {
        reload(\$(this).data('cat') || '', \$('#tor-tpl-q').val());
    });

    wrap.on('click', '#tor-tpl-search', function () {
        reload(\$('.tor-tpl-cat.btn-primary').data('cat') || '', \$('#tor-tpl-q').val());
    });

    wrap.on('keypress', '#tor-tpl-q', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            \$('#tor-tpl-search').click();
        }
    });

    wrap.on('click', '.tor-tpl-card', function () {
        if (typeof window.torApplyTemplate === 'function') {
            window.torApplyTemplate(\$(this).data('id'));
        }
    });
})();
JS;
$this->registerJs($js, View::POS_READY);
?>
