<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem;
$listAssetGroups = Categorise::find()
->where(['name' => 'asset_group'])
// ->andWhere(['NOT',['code'=>[1]]])
->all();
?>
<div class="d-flex gap-2">
    <?=Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashboard',['/dms/dashboard'],['class' => 'btn btn-light'])?>
        <?=Html::a('<i class="fa-solid fa-inbox"></i> หนังสือรอรับ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">20</span>',['/dms/documents','doc_type' => 'inbox'],['class' => 'btn btn-light'])?>
        <?=Html::a('<i class="fa-solid fa-download"></i> หนังสือรับ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">100</span>',['/dms/documents','doc_type' => 'received'],['class' => 'btn btn-light'])?>
        <?=Html::a('<i class="fa-solid fa-paper-plane"></i> หนังสือส่ง <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">10</span>',['/dms/documents','doc_type' => 'send'],['class' => 'btn btn-light'])?>

    <?=Html::a('<i class="fa-solid fa-gear"></i> ตั้งค่า',['/hr/leave-permission'],['class' => 'btn btn-light'])?>
</div>