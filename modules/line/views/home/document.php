<?php 
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
use app\modules\dms\models\DocumentsDetail;

$emp = UserHelper::GetEmployee();

$docDepartment = DocumentsDetail::find()
    ->joinWith('document')
    ->andWhere(['thai_year' => (date('Y') + 543)])
    ->andWhere(['to_id' => $emp->department])
    ->andWhere(['name' => 'department'])
    // ->andWhere(['doc_read' => null])
    ->all();



    $docTags = DocumentsDetail::find()
        ->joinWith('document')
        ->andWhere(['thai_year' => (date('Y') + 543)])
        ->andWhere(['to_id' => $emp->id])
        ->andWhere(['name' => 'tags'])
        // ->andWhere(['doc_read' => null])
        ->all();
    



?>
<div class="d-flex justify-content-between">

    <h6 class="text-white"><i class="fa-regular fa-file-lines"></i> หนังสือ/ประกาศ/ประชาสัมพันธ์
        <?php echo count($docDepartment)?></h6>
    <?php echo Html::a('ทั้งหมด',['/'],['class' => 'text-white'])?>
</div>
<div class="card rounded-4">
    <div class="card-body rounded-4" style="background:rgba(241, 238, 240, 0.98); min-height:200px">
        <?php foreach($docDepartment as $itemDep):?>
        <?php echo $this->render('document_item',['item' => $itemDep])?>
        <?php endforeach?>
        <?php foreach($docTags as $itemTag):?>
        <?php echo $this->render('document_item',['item' => $itemTag])?>

        <hr>
        <?php endforeach?>
    </div>
</div>
</div>