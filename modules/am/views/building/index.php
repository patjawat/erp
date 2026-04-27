<?php
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'อาคาร';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
<i data-lucide="building-2"></i>  
        ทะเบียน<?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'building']) ?>
<?php $this->endBlock(); ?>


<div class="card border-0 shadow-sm rounded-2 mb-4">
    <div class="card-header border-bottom bg-white d-flex justify-content-between align-items-center">
        <h6 class="m-0 text-uppercase text-secondary d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                <i data-lucide="search"></i>
            </div>
            การค้นหา
        </h6>
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-file-excel me-1"></i> Excel
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><?= Html::a('<i class="fa-solid fa-table me-2"></i> ดาวน์โหลด Template', ['/am/building/download-template'], ['class' => 'dropdown-item', 'target' => '_blank', 'rel' => 'noopener', 'data-pjax' => 0]) ?></li>
                <li><hr class="dropdown-divider"></li>
                <li><?= Html::a('<i class="fa-solid fa-file-import me-2"></i> นำเข้าข้อมูล', ['/am/building/import'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-2 mb-4">
    <div class="card-header border-bottom bg-white">
        <div class="d-flex justify-content-between">
            <h6 class="m-0 text-uppercase text-secondary d-flex align-items-center gap-2 flex-wrap">
                <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                    <i data-lucide="list-checks"></i>
                </div>
                ทะเบียน<?= $this->title ?>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                    <?= $dataProvider->getTotalCount() ?>
                </span>
                <span class="text-muted fw-normal text-uppercase">รายการ</span>
                <span class="text-muted fw-normal">|</span>
                <span class="text-muted fw-normal">มูลค่ารวม</span>
                <span class="fw-semibold text-dark">
                    <?= number_format($totalValue ?? 0, 2) ?>
                </span>
                <span class="text-muted fw-normal">บาท</span>
            </h6>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create'], ['class' => 'btn btn-light shadow']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-center py-2 text-dark" style="width: 50px;">#</th>
                      <th class="text-center" style="width:80px;">รูปภาพ</th>
                    <th class="py-2 text-dark">รหัสพัสดุ / ชื่ออาคาร</th>
                    <th class="py-2 text-dark">ประเภท</th>
                    <th class="py-2 text-dark">ที่ตั้ง / ปีสร้าง</th>
                    <th class="py-2 text-end text-dark">พื้นที่ (ตร.ม.)</th>
                    <th class="py-2 text-end text-dark">มูลค่า</th>
                    <th class="text-center" style="width: 130px;">สถานะ</th>
                    <th class="text-center" style="width:200px;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                         <td style="width:70px;">
                            <?= Html::a(
                                Html::img(
                                    $item->showImg()['image'],
                                    [
                                        'class' => 'rounded mx-auto d-block text-white lazyautosizes ls-is-cached lazyloaded',
                                        'style' => 'max-width:60px; max-height:60px; object-fit:cover;',
                                        'alt' => $item->asset_name
                                    ]
                                ),
                                ['view', 'id' => $item->id],
                                ['class' => '']
                            ) ?>
                        </td>
                         <td class="align-middle">
                            <div class="fw-semibold text-dark"><?= $item->code ?></div>
                            <div class="text-muted small"><?= $item->asset_name ?></div>
                        </td>
                        <td class="align-middle"><?= $item->data_json['building_type_name'] ?? '-' ?></td>
                        <td class="align-middle">
                            <div class="fw-semibold text-dark"><?= $item->on_year ?></div>
                            <div class="text-muted small"><?= $item->data_json['location'] ?? 'ไม่ระบุ' ?></div>
                        </td>
                        <td class="align-middle">
                            <?= $item->data_json['area'] ?? 'ไม่ระบุ' ?>
                        </td>
                        <td class="align-middle text-end">
                            <span class="fw-semibold">
                                <?= number_format($item->price ?? 0, 2) ?>
                        </td>
                        </span>
                        <td class="align-middle text-center">
                            <?= $item->viewStatus() ?>
                        </td>
  <td class="text-center align-middle equip-actions-cell px-2 px-md-3">
                            <div class="equip-actions-inner d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                                <?= Html::a('<i class="fa-regular fa-eye"></i>', ['maintenance', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-primary',
                                    'title' => 'บำรุงรักษา',
                                    'data-pjax' => 0,
                                ]) ?>
                                <?php if (Yii::$app->user->can('asset')): ?>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-warning',
                                        'title' => 'แก้ไข',
                                        'data-pjax' => 0,
                                    ]) ?>
                                <?php endif; ?>
                                  <?= Html::a('<i class="bi bi-qr-code-scan"></i>', ['/am/asset/view-qr-pdf', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-light',
                                    'title' => 'พิมพ์',
                                    'data-pjax' => 0,
                                    'target' => '_blank',
                                ]) ?>
                                <?php if (Yii::$app->user->can('admin')): ?>
                                    <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-danger delete-asset',
                                        'title' => 'ลบ',
                                        'data-pjax' => 0,
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </td>


                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>