<?php

use yii\helpers\Html;

/**
 * รายการจัดอันดับที่ใช้ร่วมกันทุกการ์ดบน dashboard (หน่วยงาน / รถ / พนักงานขับรถ)
 *
 * @var yii\web\View $this
 * @var string      $title
 * @var string      $icon      Bootstrap Icons class
 * @var string      $note
 * @var array       $items     [['label' => string, 'value' => int|float, 'url' => ?string, 'aria' => ?string], ...]
 * @var string      $unit
 * @var string      $emptyText
 * @var string      $id
 */

$items = $items ?? [];
$unit = $unit ?? '';
$max = 0;
foreach ($items as $item) {
    $max = max($max, (float) $item['value']);
}
$headingId = $id . '-heading';
?>

<section class="card border-0 shadow-sm vd-card" aria-labelledby="<?= $headingId ?>">
    <div class="card-header bg-primary-gradient text-white">
        <h4 id="<?= $headingId ?>" class="h6 text-white mb-0">
            <i class="bi <?= Html::encode($icon) ?> me-1" aria-hidden="true"></i><?= Html::encode($title) ?>
        </h4>
        <?php if (!empty($note)): ?>
            <p class="small text-white-50 mb-0"><?= Html::encode($note) ?></p>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <?php if (empty($items)): ?>
            <div class="text-center text-body-secondary py-4">
                <i class="bi bi-bar-chart-steps fs-3 d-block mb-1" aria-hidden="true"></i>
                <div class="small"><?= Html::encode($emptyText ?? 'ยังไม่มีข้อมูลในปีงบประมาณนี้') ?></div>
            </div>
        <?php else: ?>
            <ol class="vd-rank">
                <?php foreach ($items as $index => $item): ?>
                    <?php
                    $value = (float) $item['value'];
                    $pct = $max > 0 ? ($value / $max) * 100 : 0;
                    $label = (string) $item['label'];
                    $url = $item['url'] ?? null;
                    $tag = $url ? 'a' : 'div';
                    $attributes = ['class' => 'vd-rank__item'];
                    if ($url) {
                        $attributes['href'] = $url;
                        $attributes['aria-label'] = $item['aria'] ?? ($label . ' ' . number_format($value) . ' ' . $unit);
                        if (!empty($item['modal'])) {
                            $attributes['class'] .= ' open-modal';
                            $attributes['data-size'] = 'modal-xl';
                        }
                    }
                    ?>
                    <li>
                        <?= Html::beginTag($tag, $attributes) ?>
                        <span class="vd-rank__no text-body-tertiary"><?= $index + 1 ?></span>
                        <span class="vd-rank__body">
                            <span class="vd-rank__line">
                                <span class="vd-rank__name" title="<?= Html::encode($label) ?>"><?= Html::encode($label) ?></span>
                                <span class="vd-rank__value">
                                    <?= number_format($value) ?><?= $unit !== '' ? ' <span class="fw-normal text-body-secondary">' . Html::encode($unit) . '</span>' : '' ?>
                                </span>
                            </span>
                            <span class="vd-rank__track d-block">
                                <span class="vd-rank__bar d-block" style="width:<?= round($pct, 2) ?>%"></span>
                            </span>
                        </span>
                        <?php if ($url): ?>
                            <i class="bi bi-chevron-right text-body-tertiary vd-rank__chevron" aria-hidden="true"></i>
                        <?php endif; ?>
                        <?= Html::endTag($tag) ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</section>
