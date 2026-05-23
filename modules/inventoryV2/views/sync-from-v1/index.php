<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $dateFrom */
/** @var string $dateTo */
/** @var int|null $whId */
/** @var array $warehouseOptions */
/** @var array $stats */

$this->title = 'Sync ข้อมูลจาก V1 → V2';
$this->params['breadcrumbs'][] = ['label' => 'Inventory V2', 'url' => ['/inventoryV2']];
$this->params['breadcrumbs'][] = $this->title;

$total  = (int) ($stats['total_orders'] ?? 0);
$synced = (int) ($stats['synced_count'] ?? 0);
$pending = max(0, $total - $synced);
$pct = $total > 0 ? round(($synced / $total) * 100, 1) : 0;
?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= Yii::$app->session->getFlash('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('warning')): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <?= Yii::$app->session->getFlash('warning') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= Yii::$app->session->getFlash('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h5 class="text-white mb-0">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Sync ข้อมูลจาก Inventory V1 → V2
        </h5>
    </div>
    <div class="card-body">
        <p class="text-muted small">
            <i class="fa-solid fa-circle-info"></i>
            ระบบจะคัดลอกข้อมูล <code>stock_events</code> ของ V1 (header + items)
            ไปสร้างเป็น <code>stock_order</code> + <code>stock_detail</code> ใน V2
            พร้อมคำนวณ <code>stock_balance</code> ใหม่ —
            <strong>รัน sync ซ้ำได้ปลอดภัย</strong> (idempotent ผ่าน <code>ref = V1-EVENT-{id}</code>)
        </p>

        <?= $this->render('_filter', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'whId' => $whId,
            'warehouseOptions' => $warehouseOptions,
            'action' => 'index',
        ]) ?>

        <!-- Stats cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <div class="text-muted small">ใบรวม (V1)</div>
                        <div class="h3 fw-bold text-primary mb-0"><?= number_format($total) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <div class="text-muted small">รับเข้า (IN)</div>
                        <div class="h3 fw-bold text-success mb-0"><?= number_format((int) ($stats['in_orders'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <div class="text-muted small">จ่ายออก (OUT)</div>
                        <div class="h3 fw-bold text-warning mb-0"><?= number_format((int) ($stats['out_orders'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <div class="text-muted small">Sync แล้ว / รอ sync</div>
                        <div class="h4 fw-bold mb-0">
                            <span class="text-info"><?= number_format($synced) ?></span>
                            <span class="text-muted">/</span>
                            <span class="text-danger"><?= number_format($pending) ?></span>
                        </div>
                        <div class="progress mt-2" style="height:6px;">
                            <div class="progress-bar bg-info" style="width:<?= $pct ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $pct ?>%</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('<i class="fa-solid fa-eye"></i> Preview รายการที่จะ sync',
                ['preview', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'warehouse_id' => $whId],
                ['class' => 'btn btn-outline-primary']) ?>

            <form method="post" action="<?= Url::to(['run']) ?>"
                  onsubmit="return confirm('ยืนยันการ sync ข้อมูลในช่วง <?= $dateFrom ?> ถึง <?= $dateTo ?>?\nระบบจะใช้เวลาสักครู่');"
                  class="d-inline">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                <input type="hidden" name="date_from" value="<?= Html::encode($dateFrom) ?>">
                <input type="hidden" name="date_to" value="<?= Html::encode($dateTo) ?>">
                <input type="hidden" name="warehouse_id" value="<?= Html::encode($whId) ?>">
                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-bolt"></i> Run Sync ตอนนี้
                </button>
            </form>

            <?= Html::a('<i class="fa-solid fa-check-double"></i> Verify เทียบยอด V1 vs V2',
                ['verify', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'warehouse_id' => $whId],
                ['class' => 'btn btn-outline-info ms-auto']) ?>
        </div>
    </div>
</div>
