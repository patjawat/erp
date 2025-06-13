<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\am\models\AssetItem;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


$this->title = 'หมวดสินทรัพย์';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check fs-1"></i> <?=$this->title;?>
<?php $this->endBlock();?>


<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/am/views/default/menu',['active' => 'setting'])?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('@app/modules/am/views/default/_sub_menu',['active' => 'group'])?>
<?php $this->endBlock(); ?>



<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-item-center">
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create','title' => '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่'], ['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']]) ?>

        </div>
    </div>
</div>


<div class="card">
    <div class="card-body">
        <h5 class="card-title"><i class="bi bi-ui-checks text-primary"></i><?=$this->title;?> <span class="badge rounded-pill text-bg-primary"><?=number_format($dataProvider->getTotalCount(),0)?></span> รายการ</h5>

        <table class="table">
            <thead>
                <tr>
                    <th class="text-center" scope="col" style="width: 5%">#ลำดับ</th>
                    <th scope="col" style="width: 15%">รหัส</th>
                    <th scope="col" style="width: 70%">ชื่อรายการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                    <td class="fw-semibold text-primary"><?=$item->code?></td>
                    <td><?=$item->title?></td>

                </tr>
                <?php endforeach;?>
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