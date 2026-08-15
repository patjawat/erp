<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\models\FinanceInbox;

$this->title = 'กล่องรับงานบัญชี';
$this->params['breadcrumbs'][] = ['label' => 'บัญชี', 'url' => ['/accounting/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'สำเนารายการจากระบบต้นทางสำหรับตรวจสอบก่อนตั้งเจ้าหนี้และลงบัญชี';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/accounting/menu', ['active' => 'inbox']);
$this->endBlock();

$total = array_sum(array_map(static fn($row) => (int) $row['count'], $counts));
?>

<div class="alert alert-info d-flex gap-2 align-items-start" role="status">
    <i class="bi bi-shield-check fs-5" aria-hidden="true"></i>
    <div>
        <strong>โหมดตรวจสอบคู่ขนาน</strong>
        <div>รายการในหน้านี้ยังไม่สร้างเจ้าหนี้ ไม่ลงบัญชี และไม่เปลี่ยนข้อมูลในระบบต้นทาง</div>
    </div>
</div>

<nav class="d-flex flex-wrap gap-2 mb-3" aria-label="กรองสถานะกล่องรับบัญชี">
    <a class="btn <?= $status ? 'btn-outline-secondary' : 'btn-secondary' ?>" href="<?= Url::to(['index']) ?>">
        ทั้งหมด <span class="badge text-bg-secondary ms-1"><?= number_format($total) ?></span>
    </a>
    <?php foreach (FinanceInbox::statusOptions() as $value => $label): ?>
        <?php $count = (int) ($counts[$value]['count'] ?? 0); ?>
        <a class="btn <?= $status === $value ? 'btn-primary' : 'btn-outline-primary' ?>"
           href="<?= Url::to(['index', 'status' => $value]) ?>">
            <?= Html::encode($label) ?> <span class="badge text-bg-secondary ms-1"><?= number_format($count) ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<section class="card border shadow-sm" aria-labelledby="finance-inbox-list-heading">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-3">
        <h5 class="mb-0" id="finance-inbox-list-heading">รายการจากระบบต้นทาง</h5>
        <span class="text-body-secondary small"><?= number_format($dataProvider->getTotalCount()) ?> รายการ</span>
    </div>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class=\"card-footer bg-body d-flex justify-content-between align-items-center flex-wrap gap-2\">{summary}{pager}</div>",
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'columns' => [
                [
                    'attribute' => 'received_at',
                    'label' => 'รับเมื่อ',
                    'format' => ['datetime', 'php:d/m/Y H:i'],
                    'contentOptions' => ['class' => 'text-nowrap'],
                ],
                [
                    'label' => 'เอกสารต้นทาง',
                    'format' => 'raw',
                    'value' => static function (FinanceInbox $model) {
                        $no = $model->source_document_no ?: $model->source_id;
                        return Html::a(Html::encode($no), ['view', 'id' => $model->id], ['class' => 'fw-semibold'])
                            . '<div class="small text-body-secondary">'
                            . Html::encode($model->source_system . ' · ' . $model->source_type . ' · รุ่น ' . $model->source_version)
                            . '</div>';
                    },
                ],
                [
                    'attribute' => 'vendor_name_snapshot',
                    'label' => 'ผู้แทนจำหน่าย',
                    'value' => static fn(FinanceInbox $model) => $model->vendor_name_snapshot ?: 'รอตรวจสอบ',
                ],
                [
                    'attribute' => 'amount',
                    'label' => 'ยอดเงิน',
                    'format' => ['decimal', 2],
                    'contentOptions' => ['class' => 'text-end text-nowrap'],
                    'headerOptions' => ['class' => 'text-end'],
                ],
                [
                    'label' => 'ผลตรวจเบื้องต้น',
                    'format' => 'raw',
                    'value' => static function (FinanceInbox $model) {
                        $count = count($model->validationMessages());
                        return $count === 0
                            ? '<span class="badge bg-success-subtle text-success-emphasis">ข้อมูลขั้นต่ำครบ</span>'
                            : '<span class="badge bg-warning-subtle text-warning-emphasis">พบ ' . $count . ' จุด</span>';
                    },
                ],
                [
                    'attribute' => 'status',
                    'label' => 'สถานะ',
                    'format' => 'raw',
                    'value' => static fn(FinanceInbox $model) => Html::tag(
                        'span',
                        Html::encode(FinanceInbox::statusOptions()[$model->status] ?? $model->status),
                        ['class' => 'badge ' . FinanceInbox::statusBadgeClass($model->status)]
                    ),
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'contentOptions' => ['class' => 'text-end'],
                    'buttons' => [
                        'view' => static fn($url) => Html::a(
                            '<i class="bi bi-eye" aria-hidden="true"></i><span class="visually-hidden">ดูรายละเอียด</span>',
                            $url,
                            ['class' => 'btn btn-sm btn-outline-primary', 'aria-label' => 'ดูรายละเอียด']
                        ),
                    ],
                ],
            ],
            'emptyText' => 'ยังไม่มีรายการจากระบบต้นทาง ระบบพัสดุและคลังยังทำงานได้ตามปกติ',
            'emptyTextOptions' => ['class' => 'text-center text-body-secondary py-5'],
        ]) ?>
    </div>
</section>
