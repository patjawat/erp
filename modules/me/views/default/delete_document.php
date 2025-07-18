<?php
use yii\helpers\Url;
use yii\db\Expression;
use yii\bootstrap5\Html;
use app\modules\dms\models;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentTags;
?>
 <?php
 $emp = UserHelper::GetEmployee();
 $departmentTags = Documents::find()
 ->andWhere([
     '>', 
     new Expression("FIND_IN_SET(:department, JSON_UNQUOTE(data_json->'$.department_tag'))"), 
     0
 ])
 ->addParams([':department' => $emp->department])
 ->all();

//  $dataProviderTag = Documents::find()
//  ->andWhere(new Expression("JSON_CONTAINS(data_json->'$.employee_tag','\"$emp->id\"')"))
// ->all();
 $dataProviderTag = DocumentTags::find()->andWhere(['emp_id' => $emp->id])->all();

?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h6>เอกสาร/หนังสือ/ประกาศ</h6>
            <?=Html::a('ดูทั้งหมด',['/me/documents'],['class' => 'btn btn-light'])?>
        </div>
            <table
        class="table table-striped table-fixed"
    >
        <thead>
            <tr>
                <th scope="col" style="width:55px;">เลขรับ</th>
                <th scope="col">เรื่อง</th>
                <th scope="col" class="text-center" style="width:105px;">ไฟล์แนบ</th>
                <th scope="col" style="width:130px;">วันที่หนังสือ</th>
                <!-- <th scope="col" style="width:60px;">ส่งต่อ</th> -->
            </tr>
        </thead>
        <tbody class="align-middle  table-group-divider table-hover">
            <?php foreach($departmentTags as $item):?>
            <tr class="">
                <td class="fw-semibold"><?php echo $item->doc_regis_number?></td>
                <td class="fw-light align-middle">
                    <a href="<?php echo Url::to(['/me/documents/view','id' => $item->id])?>" class="text-dark">
                        <div class=" d-flex flex-column">
                        
                            <span class="fw-semibold fs-6">
                                <?php if($item->doc_speed == 'ด่วนที่สุด'):?>
                            <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด</span> 
                            <?php endif;?>   

                            <?php if($item->doc_speed == 'ด่วน'):?>
                            <span class="badge text-bg-waring fs-13"><i class="fa-solid fa-circle-exclamation"></i> ด่วน</span> 
                            <?php endif;?>   
                            <?php echo $item->topic?>
                            
                        </div>
                        <!-- <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13"> -->
                            <span class="text-primary fw-normal fs-13">
                            <i class="fa-solid fa-inbox"></i> <?php  echo $item->documentOrg->title ?? '-';?>
                            </span>
                            </a>
                            </td>
                            <td class="text-center">
                            <?php echo $item->isFile() ? Html::a('<i class="fas fa-paperclip"></i>',['/me/documents/file-comment','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']]) : ''?>    
                           </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <span class="fw-normal fs-6"><?php echo $item->viewDocDate()?></span>
                            <span class="fw-lighter fs-13"><?php echo AppHelper::timeDifference($item->doc_date)?></span>
                       </div>
                    </td>
                <!-- <td>
                <?php  Html::a(' <i class="fas fa-share fa-2x text-secondary"></i>',['/me/documents/share-file','id' => $item->id,'title' => '<i class="fas fa-share"></i>ส่งต่อ'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>    
               </td> -->
            </tr>
            <?php endforeach;?>

            <?php foreach($dataProviderTag as $empTag):?>
            <tr class="">
                <td class="fw-semibold"><?php echo $empTag->doc_regis_number?></td>
                <td class="fw-light align-middle">
                    <a href="<?php echo Url::to(['/me/documents/view','id' => $empTag->id])?>" class="text-dark">
                        <div class=" d-flex flex-column">
                        
                            <span class="fw-semibold fs-6">
                                <?php if($empTag->doc_speed == 'ด่วนที่สุด'):?>
                            <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด</span> 
                            <?php endif;?>   

                            <?php if($empTag->doc_speed == 'ด่วน'):?>
                            <span class="badge text-bg-waring fs-13"><i class="fa-solid fa-circle-exclamation"></i> ด่วน</span> 
                            <?php endif;?>   
                            <?php echo $empTag->topic?>
                            
                        </div>
                        <!-- <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13"> -->
                            <span class="text-primary fw-normal fs-13">
                            <i class="fa-solid fa-inbox"></i> <?php  echo $empTag->documentOrg->title ?? '-';?>
                            </span>
                            </a>
                            </td>
                            <td class="text-center">
                            <?php echo $empTag->isFile() ? Html::a('<i class="fas fa-paperclip"></i>',['/me/documents/file-comment','id' => $empTag->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']]) : ''?>    
                           </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <span class="fw-normal fs-6"><?php echo $empTag->viewDocDate()?></span>
                            <span class="fw-lighter fs-13"><?php echo AppHelper::timeDifference($empTag->doc_date)?></span>
                       </div>
                    </td>
                <!-- <td>
                <?php  Html::a(' <i class="fas fa-share fa-2x text-secondary"></i>',['/me/documents/share-file','id' => $empTag->id,'title' => '<i class="fas fa-share"></i>ส่งต่อ'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>    
               </td> -->
            </tr>
            <?php endforeach;?>
            
           
        </tbody>
                            </table>
        

    </div>
</div>
