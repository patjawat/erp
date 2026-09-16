<?php

use app\modules\accounting\models\AccountingChartVersion;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'ผังบัญชี';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบัญชี', 'url' => ['/accounting/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-diagram-3" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>จัดเก็บผังมาตรฐานแยกตามปีและเวอร์ชัน โดยไม่เขียนทับประวัติเดิม<?php $this->endBlock();
$this->beginBlock('page-action'); ?>
<div class="d-flex flex-wrap gap-2">
    <?= $this->render('@app/modules/accounting/menu', ['active' => 'chart']) ?>
    <?php if (Yii::$app->user->can('accountingChartManage')): ?>
        <?= Html::a('<i class="bi bi-file-earmark-arrow-up me-1" aria-hidden="true"></i>นำเข้า Excel', ['import'], ['class' => 'btn btn-success']) ?>
    <?php endif; ?>
</div>
<?php $this->endBlock(); ?>

<div class="alert alert-info d-flex gap-2 align-items-start" role="status">
    <i class="bi bi-shield-check mt-1" aria-hidden="true"></i>
    <div><strong>ผังที่นำเข้าจะเป็นฉบับรอตรวจสอบ</strong><div class="small">เวอร์ชันนี้เป็นผังมาตรฐานอ้างอิง ไม่ใช่ผังโรงพยาบาลฉบับเต็ม และยังไม่เชื่อมทะเบียนเจ้าหนี้หรือสร้างรายการบัญชี แม้เปิดใช้เป็นฉบับอ้างอิงแล้ว</div></div>
</div>

<section class="card border" aria-labelledby="chart-list-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="chart-list-heading">เวอร์ชันผังบัญชี</h5></div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
        'layout' => "{items}\n<div class=\"card-footer bg-body\">{pager}</div>",
        'emptyText' => 'ยังไม่มีผังบัญชีในระบบ เริ่มต้นด้วยการนำเข้าไฟล์ Excel มาตรฐาน',
        'columns' => [
            ['attribute' => 'fiscal_year', 'label' => 'ปีงบประมาณ', 'contentOptions' => ['class' => 'fw-semibold text-nowrap']],
            ['attribute' => 'version_code', 'label' => 'เวอร์ชัน', 'contentOptions' => ['class' => 'text-nowrap']],
            ['attribute' => 'title', 'label' => 'ชื่อผังบัญชี'],
            ['attribute' => 'account_count', 'label' => 'จำนวนรหัส', 'format' => ['decimal', 0], 'contentOptions' => ['class' => 'text-end text-nowrap'], 'headerOptions' => ['class' => 'text-end']],
            [
                'attribute' => 'status', 'label' => 'สถานะ', 'format' => 'raw',
                'value' => static fn(AccountingChartVersion $model) => Html::tag('span', AccountingChartVersion::statusOptions()[$model->status] ?? $model->status, ['class' => 'badge rounded-pill ' . AccountingChartVersion::statusBadgeClass($model->status)]),
            ],
            [
                'label' => '', 'format' => 'raw', 'contentOptions' => ['class' => 'text-end'],
                'value' => static fn(AccountingChartVersion $model) => Html::a('เปิดดู', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']),
            ],
        ],
    ]) ?>
</section>
