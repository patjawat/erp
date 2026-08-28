<?php
use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'อาคาร';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="building-2"></i>
        ทะเบียน<?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'building']) ?>
<?php $this->endBlock(); ?>


<style>
    .building-register-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .building-register-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.7rem 0.75rem;
        border-bottom: 1px solid var(--bs-border-color);
        white-space: nowrap;
        vertical-align: middle;
    }

    .building-register-table tbody td {
        padding: 0.7rem 0.75rem;
        border-bottom: 1px solid var(--bs-border-color);
        vertical-align: middle;
        font-size: 0.95rem;
        color: var(--bs-body-color);
    }

    .building-register-table tbody tr:last-child td {
        border-bottom: none;
    }

    .building-register-table tbody tr:hover td {
        background-color: var(--bs-secondary-bg);
    }

    .building-list-scroll {
        max-height: min(72vh, 800px);
        overflow: auto;
    }

    .building-register-table th.building-actions-th,
    .building-register-table td.building-actions-cell {
        width: 150px;
    }

    .building-register-table .building-actions-inner .btn {
        flex-shrink: 0;
        min-width: 2.25rem;
        min-height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .building-register-table .building-thumbnail {
        width: 48px;
        height: 48px;
        object-fit: cover;
    }

    .building-register-table .building-name {
        max-width: 260px;
    }

    .building-register-table .building-location {
        max-width: 180px;
    }
</style>

<div class="card border shadow-sm rounded-2 mb-3">
    <div class="card-header bg-body d-flex justify-content-between align-items-center py-3">
        <h6 class="m-0 fw-semibold text-body d-flex align-items-center gap-2">
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
                <li><hr class="dropdown-divider"></li>
                <li><?= Html::a('<i class="fa-solid fa-file-import me-2"></i> อัปเดต GFMIS', ['/am/import/gfmis', 'title' => 'อัปเดต GFMIS'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
                <li><?= Html::a('<i class="fa-solid fa-table me-2"></i> ดาวน์โหลด Template GFMIS', ['/am/import/download-gfmis-template'], ['class' => 'dropdown-item', 'target' => '_blank', 'rel' => 'noopener', 'data-pjax' => 0]) ?></li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card border shadow-sm rounded-2 mb-4">
    <div class="card-header bg-body py-3">
        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <h6 class="m-0 fw-semibold text-body d-flex align-items-center gap-2 flex-wrap">
                <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                    <i data-lucide="list-checks"></i>
                </div>
                ทะเบียน<?= $this->title ?>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                    <?= $dataProvider->getTotalCount() ?>
                </span>
                <span class="text-body-secondary fw-normal">รายการ</span>
                <span class="text-body-secondary fw-normal">|</span>
                <span class="text-body-secondary fw-normal">มูลค่ารวม</span>
                <span class="fw-semibold text-body">
                    <?= number_format($totalValue ?? 0, 2) ?>
                </span>
                <span class="text-body-secondary fw-normal">บาท</span>
            </h6>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> สร้างใหม่', ['create'], ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="building-list-scroll">
            <div class="table-responsive">
                <table class="table building-register-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th style="width: 72px;">รูปภาพ</th>
                            <th>รหัสพัสดุ / ชื่ออาคาร</th>
                            <th style="width: 150px;">ประเภท</th>
                            <th>ที่ตั้ง / ปีสร้าง</th>
                            <th class="text-end" style="width: 144px;">ราคาแรกรับ (฿)</th>
                            <th class="text-center" style="width: 112px;">สภาพ</th>
                            <th class="text-center" style="width: 112px;">สถานะ</th>
                            <th class="text-center building-actions-th">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                            <?php
                            $price = (float) ($item->price ?? 0);
                            $titleName = $item->asset_name ?: '-';
                            $location = '';
                            if (is_array($item->data_json) && !empty($item->data_json['location'])) {
                                $location = (string) $item->data_json['location'];
                            }
                            if ($location === '') {
                                $location = $item->departmentName();
                            }
                            ?>
                            <tr>
                                <td class="text-center text-body-secondary fw-medium"><?= (($dataProvider->pagination->offset + 1) + $key) ?></td>
                                <td>
                                    <?= Html::a(
                                        Html::img(
                                            $item->showImg()['image'],
                                            [
                                                'class' => 'building-thumbnail rounded border flex-shrink-0',
                                                'alt' => $titleName
                                            ]
                                        ),
                                        ['view', 'id' => $item->id],
                                        ['class' => '']
                                    ) ?>
                                </td>
                                <td>
                                    <div class="building-name fw-semibold d-block text-body text-truncate"><?= Html::encode($titleName) ?></div>
                                    <div class="d-flex align-items-center mt-1 font-monospace small text-body-secondary">
                                        <span><?= Html::encode($item->code) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center mt-1 small text-body-secondary">
                                        <span>GFMIS: <?= Html::encode($item->gfmis ?: '-') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-body-tertiary text-body-secondary rounded-2 fw-medium border px-2 py-1"><?= Html::encode($item->data_json['building_type_name'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center gap-2 text-body">
                                            <i class="fa-solid fa-location-dot text-secondary flex-shrink-0"></i>
                                            <span class="building-location fw-semibold text-truncate"><?= Html::encode($location ?: 'ไม่ระบุ') ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 small text-body-secondary">
                                            <i class="fa-regular fa-calendar text-secondary flex-shrink-0"></i>
                                            <span class="building-location text-truncate"><?= Html::encode($item->on_year ?: '-') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold font-monospace text-body"><?= number_format($price, 2) ?></td>
                                <td class="text-center"><?= $item->getConditionBadge() ?></td>
                                <td class="text-center"><?= $item->getStatusBadge() ?></td>
                                <td class="text-center align-middle building-actions-cell">
                                    <div class="building-actions-inner d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                                        <?= Html::a('<i class="fa-regular fa-eye"></i>', ['view', 'id' => $item->id], [
                                            'class' => 'btn btn-sm btn-primary',
                                            'title' => 'ดูรายละเอียด',
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
                                            'class' => 'btn btn-sm btn-outline-secondary',
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
    </div>
    <div class="card-footer bg-body border-top py-3 px-4">
    <?php
    echo DataSummaryWidget::widget([
        'dataProvider' => $dataProvider,
        'pagerOptions' => [],
    ]);
    ?>
</div>
</div>
