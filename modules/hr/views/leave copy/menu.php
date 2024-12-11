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
    <?=Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashboard',['/hr/leave/dashboard'],['class' => 'btn btn-light'])?>
        <?=Html::a('<i class="fa-solid fa-bars"></i> ทะเบียนประวัติ',['/hr/leave'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-gear"></i> ตั้งค่า',['/hr/leave-permission'],['class' => 'btn btn-light'])?>
</div>