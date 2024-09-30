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

</div>
<?=Html::a('<i class="fa-solid fa-box-open me-1"></i> รายงานค่าเสื่อม',['/am/report'],['class' => 'btn btn-light'])?>

<!-- <div class="dropdown btn btn-light">
    <a href="javascript:void(0)" class="dropdown-toggle me-0" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-file-circle-check"></i> ดำเนินการ
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <?=Html::a('<i class="fa-regular fa-circle-check me-1"></i> เบิกจ่ายครุภัณฑ์',['/am/asset-disbursement'],['class' => 'btn btn-light dropdown-item'])?>
        <?=Html::a('<i class="fa-regular fa-circle-check me-1"></i> ยืมคืนครุภัณฑ์',['/am/asset-borrow'],['class' => 'btn btn-light dropdown-item'])?>
        <?=Html::a('<i class="fa-regular fa-circle-check me-1"></i> ขายทอดตลาด',['/am/asset-sell'],['class' => 'btn btn-light dropdown-item'])?>
    </div>
</div> -->
<?=Html::a('<i class="fa-solid fa-gear me-2"></i> ตั้งค่าทรัพย์สิน',['/am/setting'],['class' => 'btn btn-light'])?>
</div>