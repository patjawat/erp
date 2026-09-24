<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\models\FinanceCheque;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $q */
/** @var string $status */
/** @var array $summary */

$this->title = 'ทะเบียนคุมเช็ค';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'เลขที่เช็ค · ผู้รับ · ยอด · สถานะ (พิมพ์/ส่งมอบ/ขึ้นเงิน/เด้ง/ยกเลิก)';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'cheque']);
$this->endBlock();

$badge = [
    FinanceCheque::STATUS_DRAFT => 'bg-secondary-subtle text-secondary-emphasis',
    FinanceCheque::STATUS_PRINTED => 'bg-info-subtle text-info-emphasis',
    FinanceCheque::STATUS_HANDED => 'bg-primary-subtle text-primary-emphasis',
    FinanceCheque::STATUS_CLEARED => 'bg-success-subtle text-success-emphasis',
    FinanceCheque::STATUS_BOUNCED => 'bg-warning-subtle text-warning-emphasis',
    FinanceCheque::STATUS_VOID => 'bg-danger-subtle text-danger-emphasis',
];
$thDate = fn($d) => $d ? (date_create($d) ? date_create($d)->format('d/m/') . ((int) date_create($d)->format('Y') + 543) : $d) : '–';
$models = $dataProvider->getModels();
?>

<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
    <div class="d-flex flex-wrap gap-2">
        <?php foreach (FinanceCheque::statusOptions() as $sk => $sl): $c = $summary[$sk]['count'] ?? 0; ?>
            <a href="<?= Url::to(['index', 'status' => $status === $sk ? '' : $sk, 'q' => $q]) ?>"
               class="badge <?= $badge[$sk] ?? 'bg-light' ?> text-decoration-none <?= $status === $sk ? 'border border-2 border-dark-subtle' : '' ?>"
               style="font-size:.8rem;padding:.4rem .6rem">
                <?= Html::encode($sl) ?> <span class="fw-bold"><?= $c ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['create']) ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>ออกเช็คใหม่</a>
        <a href="<?= Url::to(['book-index']) ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-journals me-1"></i>เล่มเช็ค</a>
        <a href="<?= Url::to(['template']) ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-ruled me-1"></i>แม่แบบเช็ค</a>
    </div>
</div>

<?= Html::beginForm(['index'], 'get', ['class' => 'input-group input-group-sm mb-2', 'style' => 'max-width:420px']) ?>
    <?= Html::hiddenInput('status', $status) ?>
    <input type="text" name="q" class="form-control" placeholder="ค้นหา เลขที่เช็ค / ผู้รับ / เล่ม" value="<?= Html::encode($q) ?>">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    <?php if ($q !== '' || $status !== ''): ?><a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">ล้าง</a><?php endif; ?>
<?= Html::endForm() ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>วันที่</th><th>เลขที่เช็ค</th><th>จ่ายให้</th>
                    <th class="text-end">จำนวนเงิน</th><th class="text-center">สถานะ</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$models): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">
                    ยังไม่มีเช็คในทะเบียน — กด <a href="<?= Url::to(['create']) ?>">ออกเช็คใหม่</a> หรือสร้างจากการจ่ายเจ้าหนี้
                </td></tr>
            <?php else: foreach ($models as $c): ?>
                <tr>
                    <td><?= $thDate($c->cheque_date) ?></td>
                    <td><a href="<?= Url::to(['view', 'id' => $c->id]) ?>" class="fw-semibold text-decoration-none"><?= Html::encode($c->cheque_no) ?></a></td>
                    <td><?= Html::encode($c->payee_name) ?></td>
                    <td class="text-end"><?= number_format((float) $c->amount, 2) ?></td>
                    <td class="text-center"><span class="badge <?= $badge[$c->status] ?? 'bg-light' ?>"><?= Html::encode($c->statusLabel()) ?></span></td>
                    <td class="text-end">
                        <a href="<?= Url::to(['view', 'id' => $c->id]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <a href="<?= Url::to(['print', 'id' => $c->id]) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-body">
        <?= yii\widgets\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
    </div>
</div>
