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
    <?=Html::a('<i class="fa-solid fa-gauge-high me-1"></i> Dashboard',['/helpdesk/general/dashboard'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-bars"></i> ทะเบียนงานซ่อม',['/helpdesk/general'],['class' => 'btn btn-light'])?>
   

    <div class="btn-group">
       <span class="btn btn-light">
       <i class="fa-solid fa-gear"></i>
          ตั้งค่า
        </span>
        <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
            <i class="bi bi-caret-down-fill"></i>
        </button>
        <ul class="dropdown-menu" style="">
            <li> <?=Html::a('<i class="fa-solid fa-user-gear me-1"></i> กำหนดช่าง',['/helpdesk/general/technician'],['class' => 'dropdown-item'])?></li>
</ul>
    </div>
    
</div>