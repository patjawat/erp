<?php
/**
 * การ์ด KPI มาตรฐาน (ไอคอนวงกลม tint + ป้าย + ตัวเลข tabular) — ใช้ร่วมกันทุกหน้าใน HRD
 *
 * @var \yii\web\View $this
 * @var array  $cards   แต่ละใบ: ['label'=>string, 'value'=>int|string, 'icon'=>'bi-...', 'color'=>'primary|info|success|warning|danger|secondary', 'hint'=>?string]
 * @var string|null $title    หัวข้อ section (ไม่ส่ง = ไม่แสดง สำหรับหน้าที่มี header อยู่แล้ว)
 * @var string|null $subtitle คำบรรยายใต้หัวข้อ
 */

use yii\helpers\Html;

$title = $title ?? null;
$subtitle = $subtitle ?? null;
$cards = $cards ?? [];

$this->registerCss(<<<CSS
.kpi-summary__title{font-size:1.15rem;font-weight:700;line-height:1.2;margin:0}
.kpi-summary__sub{font-size:.8rem;color:#64748b;margin:.15rem 0 0}
.kpi-card{border:0;border-radius:12px}
.kpi-card .card-body{padding:1.1rem 1.15rem}
.kpi-card__top{display:flex;align-items:center;gap:.85rem}
.kpi-card__icon{flex:none;width:52px;height:52px;border-radius:50%;display:grid;place-items:center;font-size:1.5rem}
.kpi-card__label{font-size:.8rem;font-weight:600;color:#64748b;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.kpi-card__value{font-size:1.9rem;font-weight:700;line-height:1.1;font-variant-numeric:tabular-nums;margin:.15rem 0 0}
.kpi-card__hint{font-size:.72rem;color:#94a3b8;line-height:1.3;margin:.25rem 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
[data-bs-theme="dark"] .kpi-summary__sub,[data-bs-theme="dark"] .kpi-card__label{color:var(--bs-secondary-color)}
@media(max-width:575.98px){.kpi-card__value{font-size:1.55rem}.kpi-card__icon{width:46px;height:46px;font-size:1.3rem}}
CSS, [], 'hr-kpi-cards');

$colByCount = [1 => 'col-12', 2 => 'col-sm-6', 3 => 'col-6 col-lg-4', 4 => 'col-6 col-md-3'];
$colClass = $colByCount[count($cards)] ?? 'col-6 col-md-3';
?>
<div class="kpi-summary mb-3">
    <?php if ($title !== null): ?>
        <div class="mb-2">
            <h2 class="kpi-summary__title"><?= Html::encode($title) ?></h2>
            <?php if ($subtitle !== null): ?><p class="kpi-summary__sub"><?= Html::encode($subtitle) ?></p><?php endif ?>
        </div>
    <?php endif ?>
    <div class="row g-3" aria-label="สรุปตัวชี้วัด">
        <?php foreach ($cards as $card): $color = $card['color'] ?? 'primary'; ?>
            <div class="<?= $colClass ?>">
                <div class="card kpi-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="kpi-card__top">
                            <span class="kpi-card__icon bg-<?= $color ?>-subtle text-<?= $color ?>-emphasis"><i class="bi <?= Html::encode($card['icon']) ?>" aria-hidden="true"></i></span>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="kpi-card__label" title="<?= Html::encode($card['label']) ?>"><?= Html::encode($card['label']) ?></div>
                                <div class="kpi-card__value"><?= is_numeric($card['value']) ? number_format((float)$card['value']) : Html::encode($card['value']) ?></div>
                                <?php if (!empty($card['hint'])): ?><div class="kpi-card__hint" title="<?= Html::encode($card['hint']) ?>"><?= Html::encode($card['hint']) ?></div><?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
