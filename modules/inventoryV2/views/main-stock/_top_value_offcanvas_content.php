<?php
use yii\helpers\Html;

/** @var array $items */
/** @var int $totalCount */
/** @var int $shownCount */
/** @var float $shownValueSum */
/** @var string $q */
/** @var string $currentWarehouseName */
/** @var string $fullPageUrl */

$fmtQty = fn($v) => number_format((float) $v, 2);
$fmtMoney = fn($v) => number_format((float) $v, 2);
?>

<header class="kpi-oc__bar">
    <div class="search-input-wrap kpi-oc__search">
        <i class="bi bi-search search-input__icon" aria-hidden="true"></i>
        <input type="text" class="form-control form-control-sm" data-oc-search
               value="<?= Html::encode($q) ?>" placeholder="ค้นหารหัส/ชื่อพัสดุ">
    </div>
</header>

<?php if (empty($items)): ?>
    <div class="empty-block">
        <i class="bi bi-cash-coin"></i>
        <p class="empty-block__title">ยังไม่มีพัสดุในคลังนี้</p>
        <p class="empty-block__caption"><?= $q !== '' ? 'ลองล้างคำค้นเพื่อดูทั้งหมด' : 'รับพัสดุเข้าคลังเพื่อเริ่มดูมูลค่ารวม' ?></p>
    </div>
<?php else: ?>
    <ul class="kpi-oc__list" role="list">
        <?php foreach ($items as $i => $r):
            $rank = $i + 1;
        ?>
            <li class="kpi-oc__row">
                <div class="kpi-oc__row-link">
                    <span class="kpi-oc__row-rank" aria-hidden="true"><?= $rank ?></span>
                    <span class="kpi-oc__row-thumb">
                        <?php if (!empty($r['img'])): ?>
                            <img src="<?= Html::encode($r['img']) ?>" alt="" loading="lazy"
                                 onerror="this.parentNode.classList.add('is-empty');this.remove();">
                        <?php else: ?>
                            <i class="bi bi-box-seam" aria-hidden="true"></i>
                        <?php endif; ?>
                    </span>
                    <div class="kpi-oc__row-main">
                        <div class="kpi-oc__row-title">
                            <span class="kpi-oc__row-name"><?= Html::encode($r['item_name']) ?></span>
                        </div>
                        <div class="kpi-oc__row-meta">
                            <span class="kpi-oc__row-code"><?= Html::encode($r['item_code']) ?></span>
                            <span class="kpi-oc__row-sep" aria-hidden="true">·</span>
                            คงเหลือ <?= $fmtQty($r['total_qty']) ?>
                            <?php if (!empty($r['unit_name'])): ?>
                                <?= Html::encode($r['unit_name']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="kpi-oc__row-fig">
                        <span class="kpi-oc__row-value"><?= $fmtMoney($r['total_value']) ?></span>
                        <span class="kpi-oc__row-unit">บาท <?= $r['percent'] ?>%</span>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<footer class="kpi-oc__foot">
    <span class="kpi-oc__foot-meta">
        <?php if ($totalCount > 0): ?>
            แสดง <strong><?= (int) $shownCount ?></strong> จาก <strong><?= (int) $totalCount ?></strong> รายการ
        <?php else: ?>
            ไม่มีรายการ
        <?php endif; ?>
    </span>
    <a class="kpi-oc__foot-link" href="<?= Html::encode($fullPageUrl) ?>">
        ดูหน้าเต็ม <i class="bi bi-arrow-right" aria-hidden="true"></i>
    </a>
</footer>
