<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\modules\purchase\models\Tor;
use app\components\widgets\DataSummaryWidget;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\TorSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'เขียน TOR';
$this->params['breadcrumbs'][] = ['label' => 'งานพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-file-earmark-text"></i> <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'tor']) ?>
<?php $this->endBlock(); ?>

<div class="alert alert-info d-flex gap-2 align-items-start">
    <i class="bi bi-info-circle mt-1"></i>
    <div>
        TOR คือข้อกำหนดขอบเขตงานและรายละเอียดคุณลักษณะเฉพาะของพัสดุที่จะจัดซื้อจัดจ้าง
        <span class="fw-medium">ต้องไม่ระบุยี่ห้อหรือแหล่งกำเนิดสินค้า</span>
        ตามพระราชบัญญัติการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. 2560 มาตรา 7
        และต้องสืบราคาไม่น้อยกว่า 3 แหล่งก่อนกำหนดราคากลาง
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>
</div>

<?php Pjax::begin(['id' => 'tor-container', 'enablePushState' => false]); ?>
<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-ui-checks"></i> ทะเบียน TOR
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">
                <?= number_format($dataProvider->getTotalCount()) ?>
            </span>
        </h6>
        <?= Html::a('<i class="bi bi-plus-circle me-1"></i>สร้าง TOR ใหม่', ['create'], [
            'class' => 'btn btn-success btn-sm',
            'data' => ['pjax' => 0],
        ]) ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary">
                    <tr>
                        <th style="width:42px" class="text-center">#</th>
                        <th style="min-width:130px">เลขที่</th>
                        <th style="min-width:240px">ชื่อโครงการ/รายการพัสดุ</th>
                        <th style="min-width:140px">ประเภทพัสดุ</th>
                        <th style="min-width:120px" class="text-end">วงเงิน</th>
                        <th style="min-width:120px" class="text-end">ราคากลาง</th>
                        <th style="min-width:100px">วันที่</th>
                        <th style="min-width:110px">สถานะ</th>
                        <th style="width:140px" class="text-end">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php $offset = $dataProvider->pagination ? $dataProvider->pagination->offset : 0; ?>
                    <?php foreach ($dataProvider->getModels() as $i => $item): ?>
                        <?php
                        $badge = Tor::statusBadge($item->status);
                        $sources = $item->countPriceSources();
                        ?>
                        <tr>
                            <td class="text-center text-muted"><?= $offset + $i + 1 ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode($item->doc_no ?: '—') ?></span></td>
                            <td>
                                <?= Html::a(Html::encode($item->title), ['view', 'id' => $item->id], [
                                    'class' => 'fw-semibold text-decoration-none',
                                    'data' => ['pjax' => 0],
                                ]) ?>
                                <?php if ($item->egp_no): ?>
                                    <div class="small text-muted">e-GP <?= Html::encode($item->egp_no) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= Html::encode($item->assetTypeName()) ?></td>
                            <td class="text-end"><?= number_format((float) $item->budget, 2) ?></td>
                            <td class="text-end">
                                <?= number_format((float) $item->mid_price, 2) ?>
                                <?php if ($sources < 3): ?>
                                    <div class="small text-warning-emphasis" title="ระเบียบกำหนดให้สืบราคาไม่น้อยกว่า 3 แหล่ง">
                                        สืบราคา <?= $sources ?>/3
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= $item->tor_date ? AppHelper::convertToThai($item->tor_date) : '—' ?></td>
                            <td><span class="badge text-bg-<?= $badge['color'] ?>"><?= $badge['label'] ?></span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <?= Html::a('<i class="bi bi-search"></i>', ['view', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'title' => 'ดูรายละเอียด ' . $item->title,
                                        'aria-label' => 'ดูรายละเอียด ' . $item->title,
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                    <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'แก้ไข ' . $item->title,
                                        'aria-label' => 'แก้ไข ' . $item->title,
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                    <?= Html::a('<i class="bi bi-file-earmark-word"></i>', ['word', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'ส่งออก Word ' . $item->title,
                                        'aria-label' => 'ส่งออก Word ' . $item->title,
                                        'target' => '_blank',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$dataProvider->getTotalCount()): ?>
                        <tr>
                            <td colspan="9">
                                <div class="text-center py-5">
                                    <div class="fw-semibold mb-1">ยังไม่มีเอกสาร TOR ในปีงบประมาณนี้</div>
                                    <div class="text-muted small mb-3">
                                        เริ่มจากสร้าง TOR ใหม่ แล้วเลือกแม่แบบคุณลักษณะเพื่อลดเวลาพิมพ์
                                    </div>
                                    <?= Html::a('สร้าง TOR ใหม่', ['create'], [
                                        'class' => 'btn btn-success',
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-body-tertiary">
        <?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
    </div>
</div>
<?php Pjax::end(); ?>
