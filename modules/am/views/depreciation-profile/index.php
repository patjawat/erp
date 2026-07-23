<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\grid\GridView;
use app\modules\am\models\DepreciationProfile;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var bool $hasStd */

$this->title = 'เกณฑ์ค่าเสื่อมราคา';
// ปุ่มจัดการเป็น icon-only btn-sm — คุมขนาดไอคอน lucide (default 24px) + จัดกึ่งกลาง
$this->registerCss('#am-dp-container td .btn{display:inline-flex;align-items:center;justify-content:center;padding-inline:.5rem;}#am-dp-container td .btn svg{width:1rem;height:1rem;}');
$statusBadge = static function ($s) {
    $map = [
        DepreciationProfile::STATUS_ACTIVE => 'success',
        DepreciationProfile::STATUS_INACTIVE => 'secondary',
        DepreciationProfile::STATUS_DRAFT => 'warning',
    ];
    $labels = DepreciationProfile::statusOptions();
    return '<span class="badge bg-' . ($map[$s] ?? 'secondary') . '">' . ($labels[$s] ?? $s) . '</span>';
};

$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="percent"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="fw-semibold">รายการเกณฑ์</span>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($hasStd): ?>
                    <?= Html::a('<i data-lucide="eraser"></i> ล้างข้อมูลตั้งต้น', ['clear-standard'], [
                        'class' => 'btn btn-outline-danger btn-sm',
                        'data' => ['method' => 'post', 'confirm' => 'ลบเกณฑ์ตั้งต้น (STD-) และถอนการผูกทั้งหมด?'],
                    ]) ?>
                <?php else: ?>
                    <?= Html::a('<i data-lucide="download"></i> นำเข้าข้อมูลตั้งต้น', ['seed-standard'], [
                        'class' => 'btn btn-outline-primary btn-sm',
                        'title' => 'สร้างเกณฑ์มาตรฐานกรมบัญชีกลางครบทุกประเภท + ผูกเข้าลำดับชั้นให้พร้อมใช้',
                        'data' => ['method' => 'post', 'confirm' => 'สร้างเกณฑ์ตั้งต้นมาตรฐานครบทุกประเภท (อาคาร/สิ่งก่อสร้าง/ครุภัณฑ์) และผูกเข้าลำดับชั้น?'],
                    ]) ?>
                <?php endif; ?>
                <?= Html::a('<i data-lucide="plus"></i> เพิ่มเกณฑ์', ['create', 'title' => 'เพิ่มเกณฑ์ค่าเสื่อม'], [
                    'class' => 'btn btn-primary btn-sm open-modal',
                    'data' => ['size' => 'modal-xl'],
                ]) ?>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(['id' => 'am-dp-container', 'enablePushState' => false]); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle'],
                'layout' => "{items}\n{pager}",
                'columns' => [
                    ['attribute' => 'code', 'label' => 'รหัส'],
                    ['attribute' => 'name', 'label' => 'ชื่อเกณฑ์'],
                    [
                        'label' => 'วิธี',
                        'value' => fn($m) => DepreciationProfile::methodOptions()[$m->method] ?? $m->method,
                    ],
                    [
                        'label' => 'อายุ (เดือน)',
                        'value' => fn($m) => $m->useful_life_months ?: '-',
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'label' => 'อัตรา/ปี',
                        'value' => fn($m) => $m->annual_rate !== null ? number_format($m->annual_rate, 2) . '%' : '-',
                        'contentOptions' => ['class' => 'text-end'],
                    ],
                    [
                        'label' => 'ฐานคำนวณ',
                        'value' => fn($m) => DepreciationProfile::basisOptions()[$m->calculation_basis] ?? $m->calculation_basis,
                    ],
                    [
                        'label' => 'สถานะ',
                        'format' => 'raw',
                        'value' => fn($m) => $statusBadge($m->status),
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'จัดการ',
                        'headerOptions' => ['class' => 'text-end', 'style' => 'width:8.5rem;'],
                        'contentOptions' => ['class' => 'text-end text-nowrap'],
                        // ปุ่มจัดการตามมาตรฐาน List page pattern (DESIGN.md): btn btn-sm btn-outline-* ไม่ใช่ไอคอนเปล่า
                        'template' => '{view} {update} {delete}',
                        'urlCreator' => fn($action, $model) => Url::to([$action, 'id' => $model->id]),
                        'buttons' => [
                            // view = หน้าเต็ม (มีจัดการช่วงอัตราต่อ) → กันไม่ให้ pjax ดักลิงก์
                            'view' => fn($url) => Html::a('<i data-lucide="eye"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-primary me-1',
                                'title' => 'ดูรายละเอียด',
                                'aria-label' => 'ดูรายละเอียด',
                                'data' => ['pjax' => 0],
                            ]),
                            'update' => fn($url, $model) => Html::a('<i data-lucide="pencil"></i>', ['update', 'id' => $model->id, 'title' => 'แก้ไขเกณฑ์ค่าเสื่อม'], [
                                'class' => 'btn btn-sm btn-outline-secondary me-1 open-modal',
                                'title' => 'แก้ไข',
                                'aria-label' => 'แก้ไข',
                                'data' => ['size' => 'modal-xl'],
                            ]),
                            'delete' => fn($url) => Html::a('<i data-lucide="trash-2"></i>', $url, [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'title' => 'ลบ',
                                'aria-label' => 'ลบ',
                                'data' => ['method' => 'post', 'confirm' => 'ยืนยันการลบเกณฑ์นี้?'],
                            ]),
                        ],
                    ],
                ],
            ]) ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
