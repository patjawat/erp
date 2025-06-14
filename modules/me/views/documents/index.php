<?php
use yii\helpers\Url;
use yii\db\Expression;
use yii\bootstrap5\Html;
use app\modules\dms\models;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
$this->title = 'ทะเบียนหนังสือ';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-regular fa-file-lines fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'document']) ?>
<?php $this->endBlock(); ?>
<?php if(!isset($list)):?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-top align-items-center">

        <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#home"><span
                        class="badge rounded-pill text-bg-danger"><?php  echo $dataProviderTags->getTotalCount()?></span>
                        ถึง<?=UserHelper::GetEmployee()->fullname?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#menu1"><span
                        class="badge rounded-pill text-bg-danger"><?php echo $dataProviderDepartment->getTotalCount()?></span>
                        ถึงหน่วยงาน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#bookmark"><span
                        class="badge rounded-pill text-bg-danger"><?php echo $dataProviderBookmark->getTotalCount()?></span>
                        บันทึก</a>
                    </li>
                </ul>  
                
            <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
              

        </div>
    </div>
</div>
<?php endif;?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-top align-items-center">
            <h6>
                <i class="bi bi-ui-checks"></i> ทะเบียนหนังสือ
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProviderTags->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
            <?php if(isset($list)):?>
            <?=Html::a('แสดงทั้งหมด',['/me/documents'],['class' => 'btn btn-sm btn-light rounded-pill','data' => ['pjax' => 0]])?>
            <?php endif;?>

              
        </div>
        <!-- Tab panes -->
        <div class="tab-content">
            <div id="home" class="tab-pane active"><br>
                <?= $this->render('list_document2',[
                            'searchModel' => $searchModel,
                            'dataProvider' => $dataProviderTags
                        ])
        ?>
            </div>
            <div id="menu1" class="tab-pane fade"><br>
                <?php 
    echo $this->render('list_document2',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProviderDepartment,
        ])
        ?>
            </div>

            <div id="bookmark" class="tab-pane fade"><br>
                <?php 
    echo $this->render('list_document2',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProviderBookmark,
        ])
        ?>
            </div>

        </div>


    </div>
</div>