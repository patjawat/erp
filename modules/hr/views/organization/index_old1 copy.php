<?php

use app\models\Categorise;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\modules\hr\models\Employees;

use yii\bootstrap5\LinkPager;
/** @var yii\web\View $this */
/** @var app\models\CategoriseSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ผังองค์กร/กลุ่มงาน';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = $this->title;

$sql = "SELECT 
t1.id, t1.root, t1.lft, t1.rgt, t1.lvl,t1.name as name
FROM tree t1 
JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1 WHERE t1.lvl = 2;";
   $querys = Yii::$app->db->createCommand($sql)
   ->queryAll();


?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-users-viewfinder"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php // echo $this->render('_search', ['model' => $searchModel]); ?>
<?php Pjax::begin(); ?>

<?php Pjax::end(); ?>

<?php
/** @var yii\web\View $this */
?>



<div class="card">
    <div class="card-body d-flex justify-content-between">
        <h4 class="card-title"> ผังองค์กร/กลุ่มงาน</h4>

        <!-- cta -->
        <div class="row">
            <div class="col-5">
                <form class="float-sm-end mt-3 mt-sm-0">
                    <div class="search-box">
                        <div class="position-relative">
                            <input type="text" placeholder="ค้นหา กลุ่ม/แผนก/ฝ่าย..." class="form-control">
                            <i class="bx bx-search icon"></i>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-7">
                <div class="float-sm-end">
                    <?=Html::a('<i class="bi bi-diagram-3"></i>  ผังองค์กร',['/hr/organization/diagram2'],['class' => 'btn btn-primary'])?>
                    <?=Html::a('<i class="fa-solid fa-gear"></i> ตั้งค่าผังองค์กร',['/hr/organization/setting'],['class' => 'btn btn-primary'])?>
                </div>
        </div>
        </div>
    </div>
</div>


<div class="row">
    <?php foreach($dataProvider->getModels() as $model):?>
    <!-- Start col-4 -->
    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <?=Html::img(( isset($model->getLeaderFormWorkGroup()->id) ? $model->getLeaderFormWorkGroup()->showAvatar() : ''),['class' => 'avatar rounded-circle','width' => 30])?>
                    <div class="flex-grow-1 overflow-hidden">
                        <h5 class="card-title mb-2 pr-4 text-truncate">
                            <a href="<?=Url::to(['view', 'id' => $model->id])?>" class="text-dark"><?=$model->title?>
                            </a>
                        </h5>
                        <p class="text-muted mb-3">
                            <?=isset($model->getLeaderFormWorkGroup()->fullname) ? $model->getLeaderFormWorkGroup()->fullname : '-'?>
                            <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                                    class="fa-solid fa-flag-checkered"></i> ผู้นำทีม</label></p>


                        <div class="avatar-stack">
                            <?php
                            $total = (int)count($model->EmpOnWorkGroup($model->code));
                            ?>
                            <?php if($total >  11):?>
                            <a href="javascript: void(0);" class="condense-profile" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="" data-bs-title="ProfileUser">
                                <div class="avatar bg-light text-secondary op-6 fw-light avatar-sm me-0">
                                    +<?=$total-11?></div>
                            </a>

                            <?php endif;?>
                            <?php if($total == 0):?>
                            <a href="javascript: void(0);" class="condense-profile" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="" data-bs-title="">
                                <div class="avatar bg-light text-secondary op-6 fw-light avatar-sm me-0">
                                    0</div>
                            </a>

                            <?php endif;?>


                            <?php foreach($model->EmpOnWorkGroup($model->code) as  $key => $avatar):?>
                            <?php 
                                
                                    $emp = Employees::findOne(['id' => $avatar['id']]);
                                    ?>
                            <?php if($key < 11):?>
                            <a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="" data-bs-title="<?=$emp->fullname?>">
                                <?=Html::img($emp->ShowAvatar(),['class' => 'avatar-sm rounded-circle'])?>

                            </a>
                            <?php endif;?>
                            <?php endforeach;?>


                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body border-top">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item">
                                <h5 class="fs-14 mb-0" data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                    data-bs-title="Task">
                                    <i class="bi bi-people-fill  fs-sm text-secondary op-5 align-middle"></i>
                                    <span class="align-middle"> จำนวสมาชิก
                                        <?=count($model->EmpOnWorkGroup($model->code))?> คน</span>
                                </h5>
                            </li>
                        </ul>
                    </div>
                    <div class="col pl-2">
                        <span class="badge bg-primary text-white float-end">In Progress</span>
                    </div>
                </div>
            </div>
            <div class="dropdown edit-field-half-left">
                <div class="btn-icon btn-icon-sm btn-icon-soft-primary me-0 edit-field-icon" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </div>
                <div class="dropdown-menu dropdown-menu-right">
                    <?= Html::a('<i class="mdi mdi-pencil align-middle me-1 text-primary"></i>
                        <span>Edit</span>', ['view', 'id' => $model->id], ['class' => 'dropdown-item']) ?>
                    <a href="#" class="dropdown-item">
                        <i class="mdi mdi-delete align-middle me-1 text-danger"></i>
                        <span>Delete</span>
                    </a>
                </div>
            </div>
        </div>
        <!-- end card -->
    </div>
    <!-- end col-4 -->
    <?php endforeach;?>


</div>

<div class="d-flex justify-content-center">

    <div class="text-muted">
        <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'listOptions' => 'pagination pagination-sm',
                        'class' => 'pagination-sm',
                    ],
                ]); ?>
    </div>
</div>

<?php
$js = <<< JS

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

JS;
$this->registerJS($js);
      
      ?>