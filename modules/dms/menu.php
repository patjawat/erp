<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem; 

try {
 $countReceive = $model->CountType('receive') ?? 0;
$countSend = $model->CountType('send') ?? 0;
} catch (\Throwable $th) {
    $countReceive =  0;
    $countSend =  0;
}


?>
<div class="d-flex gap-2">
    <?php echo Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashboard',['/dms/dashboard'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-inbox"></i> หนังสือรับ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">'.$countReceive.'</span>',['/dms/documents/receive'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-paper-plane"></i> หนังสือส่ง <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">'.$countSend.'</span>',['/dms/documents/send'],['class' => 'btn btn-light'])?>

</div>