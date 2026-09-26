<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\components\ThaiDate;
use app\modules\finance\models\FinanceInbox;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $counts */
/** @var string|null $status */

$this->title = 'กล่องรอรับ';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'เอกสารที่พัสดุส่งมา — กด "รับ" แล้วบิลเข้าทะเบียนเจ้าหนี้ทันที';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'inbox']);
$this->endBlock();

$total = array_sum(array_map(static fn($row) => (int) $row['count'], $counts));
$canReceive = Yii::$app->user->can('financeOperate');
$isPendingView = $status === FinanceInbox::STATUS_PENDING_REVIEW;
?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<nav class="d-flex flex-wrap gap-2 mb-3" aria-label="กรองสถานะ">
    <?php foreach (FinanceInbox::statusOptions() as $value => $label): ?>
        <?php $count = (int) ($counts[$value]['count'] ?? 0); ?>
        <a class="btn btn-sm <?= $status === $value ? 'btn-primary' : 'btn-outline-primary' ?>"
           href="<?= Url::to(['index', 'status' => $value]) ?>">
            <?= Html::encode($label) ?> <span class="badge text-bg-secondary ms-1"><?= number_format($count) ?></span>
        </a>
    <?php endforeach; ?>
    <a class="btn btn-sm <?= $status === 'all' ? 'btn-secondary' : 'btn-outline-secondary' ?>" href="<?= Url::to(['index', 'status' => 'all']) ?>">
        ทั้งหมด <span class="badge text-bg-secondary ms-1"><?= number_format($total) ?></span>
    </a>
</nav>

<?= Html::beginForm(['receive-bulk'], 'post', ['id' => 'receive-bulk-form']) ?>
<section class="card border shadow-sm">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <span class="text-body-secondary small"><?= number_format($dataProvider->getTotalCount()) ?> รายการ</span>
        <?php if ($canReceive && $isPendingView && $dataProvider->getTotalCount() > 0): ?>
            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('รับเอกสารที่เลือกเข้าทะเบียนเจ้าหนี้?');">
                <i class="bi bi-check2-all me-1"></i>รับที่เลือก
            </button>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class=\"card-footer bg-body d-flex justify-content-between align-items-center flex-wrap gap-2\">{summary}{pager}</div>",
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'columns' => [
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    'name' => 'ids',
                    'visible' => $canReceive && $isPendingView,
                    'checkboxOptions' => static fn(FinanceInbox $m) => ['value' => $m->id, 'disabled' => (bool) $m->validationMessages()],
                    'headerOptions' => ['style' => 'width:36px'],
                ],
                [
                    'label' => 'ส่งมาเมื่อ',
                    'value' => static fn(FinanceInbox $m) => ThaiDate::datetime($m->received_at),
                    'contentOptions' => ['class' => 'text-nowrap'],
                ],
                [
                    'label' => 'เอกสาร',
                    'format' => 'raw',
                    'value' => static fn(FinanceInbox $m) => Html::a(Html::encode($m->source_document_no ?: $m->source_id), ['view', 'id' => $m->id], ['class' => 'fw-semibold']),
                ],
                [
                    'label' => 'บริษัท',
                    'value' => static fn(FinanceInbox $m) => $m->vendor_name_snapshot ?: '-',
                ],
                [
                    'attribute' => 'amount',
                    'label' => 'ยอดเงิน',
                    'format' => ['decimal', 2],
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'headerOptions' => ['class' => 'text-end'],
                ],
                [
                    'label' => 'สถานะ',
                    'format' => 'raw',
                    'value' => static function (FinanceInbox $m) {
                        $html = Html::tag('span', Html::encode(FinanceInbox::statusOptions()[$m->status] ?? $m->status),
                            ['class' => 'badge ' . FinanceInbox::statusBadgeClass($m->status)]);
                        if ($m->status === FinanceInbox::STATUS_PENDING_REVIEW && $m->validationMessages()) {
                            $html .= ' <span class="badge bg-warning-subtle text-warning-emphasis" title="' . Html::encode(implode(' / ', $m->validationMessages())) . '">ข้อมูลไม่ครบ</span>';
                        }
                        return $html;
                    },
                ],
                [
                    'label' => '',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'value' => static function (FinanceInbox $m) use ($canReceive) {
                        if ($m->status === FinanceInbox::STATUS_PENDING_REVIEW && $canReceive && !$m->validationMessages()) {
                            return Html::a('<i class="bi bi-check2 me-1"></i>รับ', ['receive', 'id' => $m->id],
                                ['class' => 'btn btn-sm btn-success', 'data-method' => 'post']);
                        }
                        if ($m->payable) {
                            return Html::a(Html::encode($m->payable->payable_no), ['/finance/payable/view', 'id' => $m->payable->id],
                                ['class' => 'btn btn-sm btn-outline-primary']);
                        }
                        return Html::a('ดู', ['view', 'id' => $m->id], ['class' => 'btn btn-sm btn-outline-secondary']);
                    },
                ],
            ],
            'emptyText' => $isPendingView ? 'ไม่มีเอกสารรอรับ' : 'ไม่มีรายการ',
            'emptyTextOptions' => ['class' => 'text-center text-body-secondary py-5'],
        ]) ?>
    </div>
</section>
<?= Html::endForm() ?>
