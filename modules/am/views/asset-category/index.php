<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\am\models\AssetItem;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


$this->title = 'หมวดทรัพย์สิน';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">

    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-bar-stacked-icon lucide-chart-bar-stacked">
            <path d="M11 13v4" />
            <path d="M15 5v4" />
            <path d="M3 3v16a2 2 0 0 0 2 2h16" />
            <rect x="7" y="13" width="9" height="4" rx="1" />
            <rect x="7" y="5" width="12" height="4" rx="1" />
        </svg>
        <?= $this->title ?>
    </h4>
</div>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn'); ?>
<?php $this->endBlock() ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between align-item-center">
            <h6 class="text-white"><i class="bi bi-ui-checks me-1"></i><?= $this->title; ?> <span
                    class="badge bg-light"><?= number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ</h6>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create', 'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่'], ['class' => 'btn btn-light shadow open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        </div>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center" scope="col" style="width: 5%">#ลำดับ</th>
                    <th scope="col" style="width: 15%">รหัส</th>
                    <th scope="col">ชื่อรายการ</th>
                    <th scope="col">ประเภท</th>
                    <th class="text-center" scope="col" style="width:120px;">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td class="fw-semibold text-primary"><?= $item->code ?></td>
                        <td class="fw-semibold"><?= $item->title ?></td>
                        <td><?= $item->assetType?->title ?? '-' ?></td>
                        <td class="text-center">
                             <div class="d-flex justify-content-center gap-3">
                            <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $item->id, 'title' => '<i class="fa-solid fa-eye"></i> แสดงข้อมูล' . $this->title], ['class' => 'btn btn-sm btn-info open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข' . $this->title], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger delete-item']) ?>
                </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>


        <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>

    </div>
</div>

<?php
// $sql = "INSERT INTO categorise (name,title, code, data_json) VALUES
// ('asset_group','ที่ดิน', 'LAND', JSON_OBJECT('description', 'ที่ดินและสิทธิในที่ดิน')),
// ('asset_group','อาคาร', 'BLDG', JSON_OBJECT('description', 'อาคารและสิ่งปลูกสร้างขนาดใหญ่')),
// ('asset_group','สิ่งปลูกสร้าง', 'CONST', JSON_OBJECT('description', 'โครงสร้างพื้นฐานและสาธารณูปโภค')),
// ('asset_group','ครุภัณฑ์', 'EQUIP', JSON_OBJECT('description', 'อุปกรณ์และเครื่องมือต่างๆ')),
// ('asset_group','ครุภัณฑ์ต่ำกว่าเกณฑ์', 'MINOR', JSON_OBJECT('description', 'ครุภัณฑ์มูลค่าต่ำ')),
// ('asset_group','สินทรัพย์ไม่มีตัวตน', 'INTAN', JSON_OBJECT('description', 'ลิขสิทธิ์ สิทธิบัตร ซอฟต์แวร์')),
// ('asset_group','วัสดุ', 'MATER', JSON_OBJECT('description', 'วัสดุสิ้นเปลืองและคงคลัง'));
// ;";
?>