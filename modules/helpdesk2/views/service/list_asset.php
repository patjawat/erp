<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\hr\models\Employees;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = $title;
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ทะเบียนทะเบียนครุภัณฑ์';

$equipStats = $equipStats ?? [
    'total' => 0,
    'good' => 0,
    'fair' => 0,
    'damaged' => 0,
    'repairing' => 0,
    'waiting_dispose' => 0,
    'total_value' => 0.0,
];

?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">

        <?= $icon ?> <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/helpdesk2/menu', ['active' => $active]) ?>
<?php $this->endBlock(); ?>


<?= $this->render('@app/modules/am/views/equip/kpi_summary', ['equipStats' => $equipStats]) ?>


<div class="card">
    <div class="card-body">
        <?= $this->render('@app/modules/am/views/equip/_search', ['model' => $searchModel, 'listAssetType' => $listAssetType]) ?>
    </div>
</div>


<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card">
             <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3" style="border-color: rgb(241, 245, 249); background-color: rgba(248, 250, 252, 0.5);">
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 rounded-3" style="background-color: rgb(219, 234, 254);"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1E4E91" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list" aria-hidden="true">
                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1"></rect>
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <path d="M12 11h4"></path>
                        <path d="M12 16h4"></path>
                        <path d="M8 11h.01"></path>
                        <path d="M8 16h.01"></path>
                    </svg></div>
                <h3 class="m-0 fw-bold" style="font-size: 16px; color: rgb(30, 41, 59);">รายการทะเบียนคุมครุภัณฑ์</h3><span class="badge rounded-pill fw-bold" style="background-color: rgb(226, 232, 240); color: rgb(71, 85, 105); font-size: 10px; padding: 4px 8px;"><?=number_format($dataProvider->getTotalCount(),0)?> รายการ</span>
            </div>
            
        </div>
<div class="equip-list-scroll">
    <div class="table-responsive">
        <table class="table equip-register-table mb-0">
            <thead style="background-color: white;">
                <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ข้อมูลครุภัณฑ์</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 160px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">หมวดหมู่</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 224px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สถานที่ตั้ง / ผู้รับผิดชอบ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 128px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">วันที่รับ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-end" style="width: 144px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ราคาแรกรับ (฿)</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 112px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สภาพ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 112px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สถานะ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 200px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $item): ?>
                    <?php
                    $price = (float) ($item->price ?? 0);
                    $location = '';
                    if (is_array($item->data_json) && !empty($item->data_json['location'])) {
                        $location = (string) $item->data_json['location'];
                    }
                    if ($location === '') {
                        $location = $item->departmentName();
                    }
                    $catTitle = $item->assetCategory?->title ?? $item->assetType?->title ?? '-';
                    $titleName = $item->asset_name ?: ($item->AssetitemName() ?: '-');
                     $licensePlate = trim((string) ($item->license_plate ?? ''));
                    $ownerEmp = $item->ownerEmployee;
                    if ($ownerEmp === null && $item->owner !== null && $item->owner !== '' && is_numeric($item->owner)) {
                        $ownerEmp = Employees::findOne((int) $item->owner);
                    }
                    $ownerName = $ownerEmp?->fullname ?: '';
                    ?>

                    <tr style="border-bottom: 1px solid rgb(241, 245, 249);">
                       <td class="px-4 py-3 border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 border" style="width: 40px; height: 40px; background-color: rgb(248, 250, 252); border-color: rgb(226, 232, 240); color: rgb(148, 163, 184);">
                                    <?= Html::img(
                                        $item->ShowImg()['image'],
                                        [
                                            'class' => 'rounded border flex-shrink-0',
                                            'style' => 'width:56px;height:56px;object-fit:cover;',
                                            'alt' => $titleName,
                                        ]
                                    ) ?>
                                </div>
                                <div><span class="fw-bold d-block text-truncate" style="color: rgb(30, 41, 59); cursor: pointer; max-width: 200px;"><?= $titleName; ?></span>
                                    <div class="d-flex align-items-center mt-1 font-monospace" style="font-size: 11px; color: rgb(148, 163, 184);"><span><?= $item->code; ?></span></div>
                                    <?php if ($licensePlate !== ''): ?>
                                        <div class="d-flex align-items-center mt-1"><span style="font-size: 11px; color: rgb(100, 116, 139);">ทะเบียน :</span> <span class="fw-bold"><?= Html::encode($licensePlate) ?></span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-0"><span class="badge rounded-2 fw-medium border" style="background-color: rgb(241, 245, 249); color: rgb(71, 85, 105); border-color: rgb(226, 232, 240); font-size: 11px; padding: 4px 10px;"><?= $catTitle ?></span></td>
                        <td class="px-4 py-3 border-0">
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex align-items-center gap-2" style="color: rgb(30, 41, 59);"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin flex-shrink-0" aria-hidden="true">
                                        <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg><span class="fw-semibold text-truncate" style="font-size: 14px; max-width: 180px;"><?= $item->departmentName() ?></span></div>
                                <div class="d-flex align-items-center gap-2" style="color: rgb(100, 116, 139);"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user flex-shrink-0" aria-hidden="true">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg><span class="text-truncate" style="font-size: 12px; max-width: 180px;"><?= $ownerName ?></span></div>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-0 text-center fw-medium" style="color: rgb(100, 116, 139); font-size: 12px;"><?= $item->receive_date ? Html::encode(Yii::$app->thaiFormatter->asDate($item->receive_date, 'medium')) : '-' ?></td>
                        <td class="px-4 py-3 border-0 text-end fw-bold font-monospace" style="color: rgb(30, 41, 59);"><?= number_format($price,2) ?></td>
                        <td class="px-4 py-3 border-0 text-center"><?= $item->getConditionBadge() ?></td>
                        <td class="px-4 py-3 border-0 text-center"><?= $item->getStatusBadge() ?></td>
                        <td class="text-center align-middle equip-actions-cell px-2 px-md-3  border-0">
                            <div class="equip-actions-inner d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                                <?= Html::a('<i class="fa-regular fa-eye"></i>', ['view-asset', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-primary',
                                    'title' => 'บำรุงรักษา',
                                    'data-pjax' => 0,
                                ]) ?>
                                
                                <?= Html::a('<i class="bi bi-qr-code-scan"></i>', ['/am/asset/view-qr-pdf', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-light',
                                    'title' => 'พิมพ์',
                                    'data-pjax' => 0,
                                    'target' => '_blank',
                                ]) ?>
                               
                                <div class="dropdown d-inline-block">
        <button class="btn btn-sm btn-secondary delete-asset dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-angle-down"></i>
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a('<i data-lucide="calendar" class="me-2" style="width:1rem;height:1rem;"></i> ประมวลผลรายเดือน', ['/am/depreciation/monthly-processing'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="file-text" class="me-2" style="width:1rem;height:1rem;"></i> รายงานค่าเสื่อมรายเดือน', ['/am/report/monthly-depreciation'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="file-check" class="me-2" style="width:1rem;height:1rem;"></i> ตรวจนับพัสดุประจำปี', ['/am/audit'], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i data-lucide="trash-2" class="me-2" style="width:1rem;height:1rem;"></i> จำหน่ายพัสดุ', ['/am/disposal'], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>

                            </div>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="card-footer bg-body border-top py-3 px-4">
    <?php
    echo DataSummaryWidget::widget([
        'dataProvider' => $dataProvider,
        'pagerOptions' => [],
    ]);
    ?>
</div>
