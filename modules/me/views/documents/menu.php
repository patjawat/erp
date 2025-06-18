<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\UserHelper;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem;
$listAssetGroups = Categorise::find()
->where(['name' => 'asset_group'])
// ->andWhere(['NOT',['code'=>[1]]])
->all();
?>
<div class="d-flex gap-2">
    <?=Html::a('ถึง'.UserHelper::GetEmployee()->fullname,['/me/documents/index'],['class' => (isset($action) && $action == 'index') ? 'btn btn-primary' : 'btn btn-light'])?>
    <?=Html::a('ถึงหน่วยงาน',['/me/documents/department'],['class' => (isset($action) && $action == 'department') ? 'btn btn-primary' : 'btn btn-light'])?>
</div>