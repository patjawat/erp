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
    <?=Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashbroad',['/hr/leave/dashbroad'],['class' => 'btn btn-light'])?>
    <div class="btn-group">
    <?=Html::a('<i class="fa-solid fa-bars"></i> ทะเบียนประวัติ',['/hr/leave'],['class' => 'btn btn-light'])?>

</div>
</div>