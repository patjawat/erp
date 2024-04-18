<?php
use yii\helpers\Html;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem;
use app\models\Categorise;
$listAssetGroups = Categorise::find()
->where(['name' => 'asset_group'])
// ->andWhere(['NOT',['code'=>[1]]])
->all();
?>
<div class="d-flex gap-2">
    <?=Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashbroad',['/am'],['class' => 'btn btn-light'])?>
    <div class="btn-group">
        <?=Html::a('<i class="fa-solid fa-box-open me-1"></i> ทะเบียนทรัพย์สิน',['/am/asset'],['class' => 'btn btn-light'])?>
        <!-- <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"
            aria-expanded="false" data-bs-reference="parent">
            <i class="bi bi-caret-down-fill"></i>
        </button> -->
        <!-- <ul class="dropdown-menu">
            <?php foreach($listAssetGroups as $assetGroup):?>
            <li><?=Html::a('<i class="bi bi-check2-circle me-1 text-primary"></i>'.$assetGroup->title.' '.AssetItem::find()->where(['category_id' => $assetGroup->code])->count(),['/am/asset','group' => $assetGroup->code,'title' => $assetGroup->title], ['class' => 'dropdown-item'])?>
            </li>
            <?php endforeach;?>
            <li>
                <hr class="dropdown-divider">
            </li>
            </li>
            <li><?=Html::a('<i class="fa-solid fa-gear me-2"></i> ตั้งค่าทรัพย์สิน',['/am/asset-item','name' => 'asset_group'],['class' => ' dropdown-item'])?>
            </li>
        </ul> -->
    </div>
    <?=Html::a('<i class="fa-solid fa-box-open me-1"></i> รายงานค่าเสื่อม',['/am/report'],['class' => 'btn btn-light'])?>

    <div class="dropdown btn btn-light">
        <a href="javascript:void(0)" class="dropdown-toggle me-0" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-file-circle-check"></i> ดำเนินการ
        </a>
        <div class="dropdown-menu dropdown-menu-right" style="">
            <?=Html::a('<i class="fa-regular fa-circle-check me-1"></i> เบิกจ่ายครุภัณฑ์',['/am/asset-disbursement'],['class' => 'btn btn-light dropdown-item'])?>
            <?=Html::a('<i class="fa-regular fa-circle-check me-1"></i> ยืมคืนครุภัณฑ์',['/am/asset-borrow'],['class' => 'btn btn-light dropdown-item'])?>
            <?=Html::a('<i class="fa-regular fa-circle-check me-1"></i> ขายทอดตลาด',['/am/asset-sell'],['class' => 'btn btn-light dropdown-item'])?>
        </div>
    </div>

    <!-- <div class="dropdown btn btn-light">
        <a href="javascript:void(0)" class="dropdown-toggle me-0" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-gear"></i> การตั้งค่า
        </a>
        <div class="dropdown-menu dropdown-menu-right" style="">
            <?=Html::a('<i class="fa-solid fa-layer-group me-1"></i> การให้หมายเลข FSN',['/am/fsn'],['class' => 'btn btn-outline-primary open-modal-x dropdown-item','data' => ['size' => 'modal-md']])?>
            <?php // Html::a('<i class="fa-solid fa-file-import me-1"></i> นำเข้า CSV',['/hr/employees/import-csv'],['class' => 'dropdown-item btn btn-outline-primary'])?>
        </div>
    </div> -->
</div>