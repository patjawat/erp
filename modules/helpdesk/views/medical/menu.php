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
    <?=Html::a('<i class="fa-solid fa-gauge-high"></i> Dashboard',['dashboard'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนงานซ่อม',['index'],['class' => 'btn btn-light'])?>
</div>