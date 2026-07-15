<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\am\models\AssetDepreciationChange;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var mixed $assetId */

$this->title = 'ประวัติการเปลี่ยนเกณฑ์ค่าเสื่อม';
$scopeLabels = AssetDepreciationChange::scopeOptions();
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="history"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= Html::a('<i data-lucide="replace"></i> เปลี่ยนเกณฑ์', ['form', 'asset_id' => $assetId], ['class' => 'btn btn-outline-primary']) ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">

    <div class="card"><div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-sm table-hover align-middle'],
            'layout' => "{items}\n{pager}",
            'columns' => [
                ['attribute' => 'asset_id', 'label' => 'ทรัพย์สิน'],
                ['label' => 'เกณฑ์เดิม→ใหม่', 'value' => fn($m) => ($m->old_depreciation_profile_id ?: '-') . ' → ' . ($m->new_depreciation_profile_id ?: '-')],
                ['label' => 'อายุ (เดือน)', 'value' => fn($m) => ($m->old_useful_life_months ?: '-') . ' → ' . ($m->new_useful_life_months ?: '-')],
                ['label' => 'อัตรา', 'value' => fn($m) => ($m->old_rate ?? '-') . ' → ' . ($m->new_rate ?? '-')],
                ['attribute' => 'effective_date', 'label' => 'วันที่มีผล'],
                ['label' => 'ขอบเขต', 'value' => fn($m) => $scopeLabels[$m->change_scope] ?? $m->change_scope],
                ['attribute' => 'document_reference', 'label' => 'เอกสาร'],
                ['attribute' => 'reason', 'label' => 'เหตุผล'],
                ['attribute' => 'created_at', 'label' => 'บันทึกเมื่อ'],
            ],
        ]) ?>
    </div></div>
</div>
