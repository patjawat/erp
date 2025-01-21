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
    <!-- <?php Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashboard',['/dms/dashboard'],['class' => 'btn btn-light'])?> -->
        <?php // Html::a('<i class="fa-solid fa-inbox"></i> หนังสือรอรับ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/dms/documents','doc_type' => 'inbox'],['class' => 'btn btn-light'])?>
        <?=Html::a('<i class="fa-solid fa-download"></i> ทะเบียนหนังสือ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/me/documents'],['class' => 'btn btn-light'])?>
</div>