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
    <?php echo Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashboard',['/dms/dashboard'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-inbox"></i> หนังสือรับ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/dms/documents','document_group' => 'receive'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-paper-plane"></i> หนังสือส่ง <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/dms/documents','document_group' => 'send'],['class' => 'btn btn-light'])?>

</div>