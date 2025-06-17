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
    <?=Html::a('ถึง'.UserHelper::GetEmployee()->fullname.' <span class="badge rounded-pill text-bg-primary">'. $dataProviderTags->getTotalCount().'</span>',['/dms/dashboard'],['class' => 'btn btn-light'])?>
    <?=Html::a('ถึงหน่วยงาน <span class="badge rounded-pill text-bg-primary">'.$dataProviderDepartment->getTotalCount().'</span>',['/dms/documents','doc_type' => 'inbox'],['class' => 'btn btn-light'])?>
        <?=Html::a('บันทึก <span class="badge rounded-pill text-bg-primary">0</span>',['/me/documents'],['class' => 'btn btn-light'])?>
</div>