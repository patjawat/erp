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
    <?=Html::a('<i class="fa-solid fa-gauge-high me-1"></i> Dashboard',['/hr/development/dashboard'],['class' => 'btn btn-light'])?>
        <?=Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติ',['/hr/development','status' => 'Checking'],['class' => 'btn btn-light'])?>
</div>