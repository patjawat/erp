<?php
use yii\helpers\Html;
use app\components\widgets\DataSummaryWidget;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


$this->title = 'ฐานข้อมูลพัสดุครุภัณฑ์กระทรวงสาธารณสุข';
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
            <h6 class="text-white"><i class="bi bi-ui-checks me-1"></i> จำนวน <span class="badge bg-light"><?=number_format($dataProvider->getTotalCount(),0)?></span> รายการ</h6>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', 
            ['create','asset_type_id' => $searchModel->asset_type_id,
            'category_id' => $searchModel->asset_category_id,
            'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่'], ['class' => 'btn btn-light shadow open-modal','data' => ['size' => 'modal-lg']]) ?>
        </div>
</div>
    <div class="card-body">

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center" scope="col" style="width: 5%">#</th>
                    <th scope="col" style="width: 8%">รหัส</th>
                    <th scope="col" style="width: 12%">FSN</th>
                    <th scope="col" style="width: 35%">ชื่อทรัพย์สิน</th>
                    <th scope="col" style="width: 5%">หน่วย</th>
                    <th scope="col" style="width: 12%">ประเภท</th>
                    <th scope="col" style="width: 8%">หมวดหมู่</th>
                    <th class="text-end fw-blod" scope="col" style="width: 4%">ราคากลาง</th>
                    <th class="text-center" scope="col" style="width: 120px;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                    <td><?= $item->id?></td>
                    <td class="fw-semibold text-primary"><?=$item->fsn?></td>
                    <td><?=$item->title?></td>
                    <td><?=$item->data_json['unit'] ?? '-'?></td>
                    <td><?php
                    // echo $item->asset_type_id;
                    echo $item->assetType->title ?? '-';
                    ?></td>
                    <td><?=$item->category->title ?? '-'?></td>
                    <td class="text-end fw-blod"><?=$item->price ?? '-'?></td>
                    <td class="text-center">
                          <?=Html::a('<i class="bi bi-eye"></i>',['view','id' => $item->id,'title' => '<i class="fa-solid fa-eye"></i> แสดงข้อมูลครุภัณฑ์'],['class' => 'btn btn-sm btn-info open-modal','data' => ['size' => 'modal-lg']])?>
                                <?=Html::a('<i class="bi bi-pencil"></i>',['update','id' => $item->id,'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไขรหัสทรัพย์สิน'],['class' => 'btn btn-sm btn-warning open-modal','data' => ['size' => 'modal-lg']])?>
                                <?=Html::a('<i class="bi bi-trash"></i>',['delete','id' => $item->id],['class' => 'btn btn-sm btn-danger delete-item'])?>

                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>

        </table>


        <div class="card-footer bg-body border-top py-3 px-4">
    <?php
    echo DataSummaryWidget::widget([
        'dataProvider' => $dataProvider,
        'pagerOptions' => [],
    ]);
    ?>
</div>

    </div>
</div>
