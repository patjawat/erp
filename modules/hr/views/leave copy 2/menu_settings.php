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

<?=Html::a('<i class="fa-solid fa-angle-left"></i> หน้าหลัก',['/hr/leave'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-gear"></i> นโยบายการลา',['/hr/leave-policies'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-gear"></i> กำหนดสิทธิลาพักผ่อน',['/hr/leave-entitlements'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-mug-hot"></i> วันหยุด',['/hr/holiday'],['class' => 'btn btn-light'])?>
</div>