<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\models\FinanceCheque;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ทะเบียนคุมเช็ค';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'เลขที่เช็ค · ผู้รับ · ยอด · สถานะ (พิมพ์/ยกเลิก/ขึ้นเงิน)';
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

<div class="d-flex justify-content-end mb-2">
    <a href="<?= Url::to(['template']) ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-ruled me-1"></i>แม่แบบเช็ค</a>
</div>

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
                <tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีเช็คในทะเบียน — สร้างจากการจ่ายเจ้าหนี้</td></tr>
            <?php else: foreach ($models as $c): ?>
                <tr>
                    <td><?= $thDate($c->cheque_date) ?></td>
                    <td><?= Html::encode($c->cheque_no) ?></td>
                    <td><?= Html::encode($c->payee_name) ?></td>
                    <td class="text-end"><?= number_format((float) $c->amount, 2) ?></td>
                    <td class="text-center"><span class="badge <?= $badge[$c->status] ?? 'bg-light' ?>"><?= Html::encode($c->statusLabel()) ?></span></td>
                    <td class="text-end">
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
