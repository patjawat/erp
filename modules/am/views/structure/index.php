<?php
use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use app\components\SiteHelper;

$this->title = 'สิ่งปลูกสร้าง';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;

$viewQuery = Yii::$app->request->queryParams;
$viewListUrl = Url::to(array_merge(['/am/structure/index'], $viewQuery, ['view' => 'list']));
$viewGridUrl = Url::to(array_merge(['/am/structure/index'], $viewQuery, ['view' => 'grid']));
$isTableView = SiteHelper::getDisplay() !== 'grid';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
<i data-lucide="construction"></i>  
        ทะเบียน<?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'structure']) ?>
<?php $this->endBlock(); ?>


<style>
    .structure-register-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .structure-register-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: var(--bs-body-bg);
        color: var(--bs-secondary-color);
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid var(--bs-border-color);
        white-space: nowrap;
        vertical-align: middle;
    }

    .structure-register-table tbody td {
        padding: 1rem 1rem;
        border-bottom: 1px solid var(--bs-border-color);
        vertical-align: middle;
        font-size: 0.9375rem;
        color: var(--bs-body-color);
    }

    .structure-register-table tbody tr:last-child td {
        border-bottom: none;
    }

    .structure-register-table tbody tr:hover td {
        background-color: var(--bs-secondary-bg);
    }

    .structure-list-scroll {
        max-height: min(68vh, 760px);
        overflow: auto;
    }

    .structure-register-table th.structure-actions-th,
    .structure-register-table td.structure-actions-cell {
        width: 150px;
    }

    .structure-register-table .structure-actions-inner .btn {
        flex-shrink: 0;
        min-width: 2.375rem;
        min-height: 2.375rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="card border shadow-sm mb-4">
    <div class="card-header border-bottom bg-body-tertiary d-flex justify-content-between align-items-center">
        <h6 class="m-0 text-body-secondary d-flex align-items-center gap-2">
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
                <li><?= Html::a('<i class="fa-solid fa-table me-2"></i> ดาวน์โหลด Template', ['/am/structure/download-template'], ['class' => 'dropdown-item', 'target' => '_blank', 'rel' => 'noopener', 'data-pjax' => 0]) ?></li>
                <li><hr class="dropdown-divider"></li>
                <li><?= Html::a('<i class="fa-solid fa-file-import me-2"></i> นำเข้าข้อมูล', ['/am/structure/import'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
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

<div class="card border shadow-sm mb-4">
    <div class="card-header bg-body-tertiary px-4 py-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <h5 class="m-0 fw-bold d-flex align-items-center gap-2 flex-wrap">
                <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                    <i data-lucide="list-checks"></i>
                </div>
                ทะเบียน<?= $this->title ?>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                    <?= $dataProvider->getTotalCount() ?>
                </span>
                <span class="text-body-secondary fw-normal">รายการ</span>
                <span class="text-muted fw-normal">|</span>
                <span class="text-muted fw-normal">มูลค่ารวม</span>
                <span class="fw-semibold text-dark">
                    <?= number_format($totalValue ?? 0, 2) ?>
                </span>
                <span class="text-muted fw-normal">บาท</span>
            </h5>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex gap-2 p-1 rounded-3 border bg-body">
                    <?= Html::a('<i class="fa-solid fa-table me-1"></i> ตาราง', $viewListUrl, ['class' => 'btn ' . ($isTableView ? 'btn-primary' : 'btn-outline-primary'), 'data-pjax' => 0]) ?>
                    <?= Html::a('<i class="fa-solid fa-grip me-1"></i> การ์ด', $viewGridUrl, ['class' => 'btn ' . (!$isTableView ? 'btn-primary' : 'btn-outline-primary'), 'data-pjax' => 0]) ?>
                </div>
                <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> สร้างใหม่', ['create'], ['class' => 'btn btn-primary fw-semibold', 'data-pjax' => 0]) ?>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if ($isTableView): ?>
        <div class="structure-list-scroll">
            <div class="table-responsive">
                <table class="table structure-register-table mb-0">
                    <thead style="background-color: white;">
                        <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                            <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 50px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">#</th>
                            <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 80px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">รูปภาพ</th>
                            <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">รหัสพัสดุ / ชื่อสิ่งปลูกสร้าง</th>
                            <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 160px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ประเภท</th>
                            <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ที่ตั้ง / ปีสร้าง</th>
                            <th class="px-4 py-3 border-0 text-uppercase fw-bold text-end" style="width: 144px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ราคาแรกรับ (฿)</th>
                            <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 112px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สภาพ</th>
                            <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 112px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สถานะ</th>
                            <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 200px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                            <?php
                            $price = (float) ($item->price ?? 0);
                            $location = '';
                            if (is_array($item->data_json) && !empty($item->data_json['location'])) {
                                $location = (string) $item->data_json['location'];
                            }
                            if ($location === '') {
                                $location = $item->departmentName();
                            }
                            $structureGroupName    = is_array($item->data_json) ? ($item->data_json['structure_group_name']    ?? '') : '';
                            $structureSubgroupName = is_array($item->data_json) ? ($item->data_json['structure_subgroup_name'] ?? '') : '';
                            $structureTypeName  = is_array($item->data_json) ? ($item->data_json['structure_type_name']  ?? '') : '';
                            $structureOtherNote = is_array($item->data_json) ? ($item->data_json['structure_type_other'] ?? '') : '';
                            $catTitle           = $structureGroupName !== '' ? ($structureGroupName . ($structureSubgroupName !== '' ? ' › ' . $structureSubgroupName : '')) : ($item->assetCategory?->title ?? $item->assetType?->title ?? '-');
                            $titleName          = $item->asset_name ?: ($item->AssetitemName() ?: '-');
                            ?>
                            <tr style="border-bottom: 1px solid rgb(241, 245, 249);">
                                <td class="px-4 py-3 border-0 text-center fw-medium" style="color: rgb(100, 116, 139);"><?= (($dataProvider->pagination->offset + 1) + $key) ?></td>
                                <td class="px-4 py-3 border-0" style="width:70px;">
                                    <?= Html::a(
                                        Html::img(
                                            $item->ShowImg()['image'],
                                            [
                                                'class' => 'rounded border flex-shrink-0',
                                                'style' => 'width:56px;height:56px;object-fit:cover;',
                                                'alt' => $titleName,
                                            ]
                                        ),
                                        ['view', 'id' => $item->id],
                                        ['class' => '']
                                    ) ?>
                                </td>
                                <td class="px-4 py-3 border-0">
                                    <div class="fw-bold d-block text-truncate" style="color: rgb(30, 41, 59); cursor: pointer; max-width: 260px;"><?= Html::encode($titleName) ?></div>
                                    <div class="d-flex align-items-center mt-1 font-monospace" style="font-size: 11px; color: rgb(148, 163, 184);">
                                        <span><?= Html::encode($item->code) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center mt-1" style="font-size: 11px; color: rgb(100, 116, 139);">
                                        <span>GFMIS: <?= Html::encode($item->gfmis ?: '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-0">
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge rounded-2 fw-medium border align-self-start text-truncate" style="background-color: rgb(241, 245, 249); color: rgb(71, 85, 105); border-color: rgb(226, 232, 240); font-size: 11px; padding: 4px 10px; max-width: 160px;" title="<?= Html::encode($catTitle) ?>"><?= Html::encode($catTitle) ?></span>
                                        <?php if ($structureTypeName !== ''): ?>
                                            <span class="text-truncate" style="font-size: 12px; color: rgb(100, 116, 139); max-width: 160px;" title="<?= Html::encode($structureTypeName) ?><?= $structureOtherNote !== '' ? ' · ' . Html::encode($structureOtherNote) : '' ?>">
                                                <?= Html::encode($structureTypeName) ?><?php if ($structureOtherNote !== ''): ?> · <?= Html::encode($structureOtherNote) ?><?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-0">
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center gap-2" style="color: rgb(30, 41, 59);">
                                            <i class="fa-solid fa-location-dot text-secondary flex-shrink-0"></i>
                                            <span class="fw-semibold text-truncate" style="font-size: 14px; max-width: 180px;"><?= Html::encode($location ?: 'ไม่ระบุ') ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2" style="color: rgb(100, 116, 139);">
                                            <i class="fa-regular fa-calendar text-secondary flex-shrink-0"></i>
                                            <span class="text-truncate" style="font-size: 12px; max-width: 180px;"><?= Html::encode($item->on_year ?: '-') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 border-0 text-end fw-bold font-monospace" style="color: rgb(30, 41, 59);"><?= number_format($price, 2) ?></td>
                                <td class="px-4 py-3 border-0 text-center"><?= $item->getConditionBadge() ?></td>
                                <td class="px-4 py-3 border-0 text-center"><?= $item->getStatusBadge() ?></td>
                                <td class="text-center align-middle structure-actions-cell px-2 px-md-3 border-0">
                                    <div class="structure-actions-inner d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
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
        <?php else: ?>
            <?= $this->render('_grid', ['dataProvider' => $dataProvider]) ?>
        <?php endif; ?>
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
