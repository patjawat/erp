<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var array $rows */
/** @var string $dateFrom */
/** @var string $dateTo */
/** @var int|null $whId */
/** @var array $warehouseOptions */

$this->title = 'Preview รายการที่จะ Sync';
$this->params['breadcrumbs'][] = ['label' => 'Sync V1→V2', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="text-white mb-0">
            <i class="fa-solid fa-eye"></i> Preview รายการ stock_events ที่จะ sync
        </h6>
        <span class="badge bg-light text-dark"><?= number_format(count($rows)) ?> ใบ</span>
    </div>
    <div class="card-body">
        <?= $this->render('_filter', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'whId' => $whId,
            'warehouseOptions' => $warehouseOptions,
            'action' => 'preview',
        ]) ?>

        <div class="d-flex gap-2 mb-2">
            <?= Html::a('<i class="fa-solid fa-arrow-left"></i> กลับ',
                ['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'warehouse_id' => $whId],
                ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            <form method="post" action="<?= Url::to(['run']) ?>"
                  onsubmit="return confirm('ยืนยันการ sync ข้อมูล <?= count($rows) ?> รายการ?');"
                  class="d-inline ms-auto">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                <input type="hidden" name="date_from" value="<?= Html::encode($dateFrom) ?>">
                <input type="hidden" name="date_to" value="<?= Html::encode($dateTo) ?>">
                <input type="hidden" name="warehouse_id" value="<?= Html::encode($whId) ?>">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-bolt"></i> Run Sync (<?= count($rows) ?> ใบ)
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>#</th>
                        <th>วันที่</th>
                        <th>เลขที่ V1</th>
                        <th>ประเภท</th>
                        <th>คลังต้นทาง</th>
                        <th>คลังปลายทาง</th>
                        <th>จำนวน items</th>
                        <th>สถานะ Sync</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-3">— ไม่พบรายการในช่วงที่เลือก —</td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($rows as $r): ?>
                            <?php $synced = ((int) $r['has_ref']) > 0; ?>
                            <tr class="<?= $synced ? '' : 'table-warning' ?>">
                                <td class="text-center"><?= $i++ ?></td>
                                <td class="text-center"><?= AppHelper::convertToThai(date('Y-m-d', strtotime($r['movement_date']))) ?></td>
                                <td><?= Html::encode($r['code']) ?> <small class="text-muted">(id=<?= $r['id'] ?>)</small></td>
                                <td class="text-center">
                                    <?php if ($r['transaction_type'] === 'IN'): ?>
                                        <span class="badge bg-success">รับเข้า</span>
                                    <?php elseif ($r['transaction_type'] === 'OUT'): ?>
                                        <span class="badge bg-warning text-dark">จ่ายออก</span>
                                    <?php else: ?>
                                        <?= Html::encode($r['transaction_type']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= Html::encode($r['wh_from'] ?? '-') ?></td>
                                <td><?= Html::encode($r['wh_name'] ?? '-') ?></td>
                                <td class="text-center"><?= number_format((int) $r['item_count']) ?></td>
                                <td class="text-center">
                                    <?php if ($synced): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-check"></i> Synced</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">รอ sync</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
