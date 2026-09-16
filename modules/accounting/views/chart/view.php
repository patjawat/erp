<?php

use app\modules\accounting\models\AccountingChartVersion;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'ระบบบัญชี', 'url' => ['/accounting/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ผังบัญชี', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-diagram-3" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ปีงบประมาณ <?= Html::encode($model->fiscal_year) ?> · เวอร์ชัน <?= Html::encode($model->version_code) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?>
<div class="d-flex flex-wrap gap-2">
    <?= $this->render('@app/modules/accounting/menu', ['active' => 'chart']) ?>
    <?php if ($model->status === AccountingChartVersion::STATUS_DRAFT && Yii::$app->user->can('accountingChartManage')): ?>
        <?= Html::beginForm(['activate', 'id' => $model->id], 'post') ?>
        <?= Html::submitButton('<i class="bi bi-check2-circle me-1" aria-hidden="true"></i>เปิดใช้เป็นมาตรฐานอ้างอิง', [
            'class' => 'btn btn-success',
            'data' => ['confirm' => 'ยืนยันใช้เวอร์ชันนี้เป็นมาตรฐานอ้างอิง? เวอร์ชันก่อนหน้าในปีเดียวกันจะถูกเก็บเป็นประวัติ โดยยังไม่เปลี่ยนทะเบียนเจ้าหนี้'],
        ]) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>
</div>
<?php $this->endBlock(); ?>

<section class="card border mb-3" aria-labelledby="chart-summary-heading">
    <div class="card-body d-flex flex-wrap justify-content-between gap-3">
        <div>
            <h5 class="mb-2" id="chart-summary-heading">ข้อมูลเวอร์ชัน</h5>
            <div class="text-body-secondary small">ไฟล์ต้นทาง: <?= Html::encode($model->source_file_name ?: 'ไม่ระบุ') ?></div>
            <div class="text-body-secondary small">นำเข้าเมื่อ <?= Yii::$app->formatter->asDatetime($model->created_at) ?></div>
        </div>
        <div class="d-flex align-items-start gap-2">
            <span class="badge rounded-pill <?= AccountingChartVersion::statusBadgeClass($model->status) ?>"><?= Html::encode(AccountingChartVersion::statusOptions()[$model->status] ?? $model->status) ?></span>
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis"><?= number_format($model->account_count) ?> รหัส</span>
        </div>
    </div>
</section>

<section class="card border" aria-labelledby="account-list-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="account-list-heading">รายการบัญชี</h5></div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
        'layout' => "{items}\n<div class=\"card-footer bg-body d-flex flex-wrap justify-content-between gap-2\"><span class=\"text-body-secondary small\">แสดงครั้งละ 100 รหัส</span>{pager}</div>",
        'columns' => [
            ['attribute' => 'code', 'label' => 'รหัส', 'contentOptions' => ['class' => 'font-monospace text-nowrap']],
            ['attribute' => 'name', 'label' => 'ชื่อบัญชี'],
            ['attribute' => 'category', 'label' => 'หมวด', 'contentOptions' => ['class' => 'text-center'], 'headerOptions' => ['class' => 'text-center']],
        ],
    ]) ?>
</section>
