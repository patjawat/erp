<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $items */
/** @var int $totalCount */
/** @var int $shownCount */
/** @var string $q */
/** @var string $currentWarehouseName */
/** @var string $fullPageUrl */
?>

<header class="kpi-oc__bar">
    <div class="search-input-wrap kpi-oc__search">
        <i class="bi bi-search search-input__icon" aria-hidden="true"></i>
        <input type="text" class="form-control form-control-sm" data-oc-search
               value="<?= Html::encode($q) ?>" placeholder="ค้นหาเลขที่ใบเบิก">
    </div>
</header>

<?php if (empty($items)): ?>
    <div class="empty-block">
        <i class="bi bi-inbox"></i>
        <p class="empty-block__title">ไม่มีใบเบิกรอจัดของ</p>
        <p class="empty-block__caption"><?= $q !== '' ? 'ลองล้างคำค้นเพื่อดูทั้งหมด' : 'คลังนี้ยังไม่มีใบเบิกที่อนุมัติแล้วรอจ่าย' ?></p>
    </div>
<?php else: ?>
    <ul class="kpi-oc__list" role="list">
        <?php foreach ($items as $req):
            $subName = $req->subWarehouse ? $req->subWarehouse->warehouse_name : 'ไม่ระบุ';
            $detailCount = is_array($req->stockDetails) ? count($req->stockDetails) : (method_exists($req, 'getStockDetails') ? $req->getStockDetails()->count() : 0);
            $timeAgo = $req->order_date ? Yii::$app->formatter->asRelativeTime(strtotime($req->order_date)) : '';
            $statusInfo = \app\modules\inventoryV2\models\StockOrder::getStatusBadgeConfigFor($req->status);
            $href = Url::to(['/inventory-v2/issue/process', 'id' => $req->id]);
        ?>
            <li class="kpi-oc__row">
                <a class="kpi-oc__row-link" href="<?= $href ?>">
                    <span class="kpi-oc__row-icon" aria-hidden="true">
                        <i class="bi bi-receipt"></i>
                    </span>
                    <div class="kpi-oc__row-main">
                        <div class="kpi-oc__row-title">
                            <span class="kpi-oc__row-code"><?= Html::encode($req->order_no) ?></span>
                            <span class="kpi-oc__row-sep" aria-hidden="true">·</span>
                            <span class="kpi-oc__row-name"><?= Html::encode($subName) ?></span>
                        </div>
                        <div class="kpi-oc__row-meta">
                            <?= (int) $detailCount ?> รายการ
                            <?php if ($timeAgo): ?>
                                <span class="kpi-oc__row-sep" aria-hidden="true">·</span>
                                <?= Html::encode($timeAgo) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="kpi-oc__row-action">
                        <span class="kpi-oc__row-cta">จัดของ</span>
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<footer class="kpi-oc__foot">
    <span class="kpi-oc__foot-meta">
        <?php if ($totalCount > 0): ?>
            แสดง <strong><?= (int) $shownCount ?></strong> จาก <strong><?= (int) $totalCount ?></strong> ใบ
        <?php else: ?>
            ไม่มีรายการ
        <?php endif; ?>
    </span>
    <a class="kpi-oc__foot-link" href="<?= Html::encode($fullPageUrl) ?>">
        ดูหน้าเต็ม <i class="bi bi-arrow-right" aria-hidden="true"></i>
    </a>
</footer>
