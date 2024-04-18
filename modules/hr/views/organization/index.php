<?php
use yii\helpers\Url;
use app\models\Categorise;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\CategoriseSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ผังองค์กร/กลุ่มงาน';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = $this->title;

$sql = "SELECT
t1.id, t1.root, t1.lft, t1.rgt, t1.lvl,t1.name as name,t2.name as type_name
FROM tree t1
JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1 WHERE t1.lvl = 2;";
$querys = Yii::$app->db->createCommand($sql)
    ->queryAll();

?>

<?php $this->beginBlock('page-title');?>
<i class="fa-solid fa-users-viewfinder"></i> <?=$this->title;?>
<?php $this->endBlock();?>

<?php // echo $this->render('_search', ['model' => $searchModel]); ?>


<?php
/** @var yii\web\View $this */
?>
<?php Pjax::begin(['id' => 'hr-container','enablePushState' => true,'timeout' => 50000 ]); ?>

<?php //$this->render('./menu')?>





<div class="card">
    <div class="card-body d-flex justify-content-between">
    <?php echo app\components\AppHelper::Btn([
                        'title' => '<i class="fa-solid fa-circle-plus"></i> เพิ่มตำแหน่ง',
                        'url' => ['/hr/organization/create'],
                        'modal' => true,
                        'size' => 'md',
                        'class' => 'btn btn-primary rounded-pill shadow open-modal float-end'
                        ])?>

</div>
</div>
    

<div class="row">
    <?php foreach ($dataProvider->getModels() as $model): ?>
    <!-- Start col-4 -->
    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <?php // echo Html::img(( isset($model->getLeaderFormWorkGroup()->id) ? $model->getLeaderFormWorkGroup()->showAvatar() : ''),['class' => 'avatar rounded-circle','width' => 30])?>
                    <div class="flex-grow-1 overflow-hidden">
                        <h5 class="card-title mb-2 pr-4 text-truncate">
                            <a href="<?php echo Url::to(['view', 'id' => $model->id])?>"
                                class="text-dark"><?php echo $model->title?>
                            </a>
                        </h5>
                        <p class="text-muted mb-3">
                            <?php // isset($model->getLeaderFormWorkGroup()->fullname) ? $model->getLeaderFormWorkGroup()->fullname : '-'?>
                            <!-- <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                                    class="fa-solid fa-flag-checkered"></i> ผู้นำทีม</label> -->
                        </p>

                        <!-- <div class="row">
                            <div class="col-12 text-truncate">
                                <?php // foreach(Categorise::find()->where(['name' => 'position_name','category_id' => $model->code])->all() as $item):?>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-check2-circle text-primary"></i>
                                    <?php // $item->title?>
                                </p>
                                <?php // endforeach;?>
                            </div>
                        </div> -->



                        <div class="avatar-stack">
                            <?php
//  $total = (int)count($model->EmpOnWorkGroup($model->code));
$total = 0;
?>
                            <?php if ($total > 11): ?>
                            <a href="javascript: void(0);" class="condense-profile" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="" data-bs-title="ProfileUser">
                                <div class="avatar bg-light text-secondary op-6 fw-light avatar-sm me-0">
                                    +<?=$total - 11?></div>
                            </a>

                            <?php endif;?>
                            <?php if ($total == 0): ?>
                            <a href="javascript: void(0);" class="condense-profile" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="" data-bs-title="">
                                <div class="avatar bg-light text-secondary op-6 fw-light avatar-sm me-0">
                                    0</div>
                            </a>

                            <?php endif;?>


                            <?php // foreach($model->EmpOnWorkGroup($model->code) as  $key => $avatar):?>
                            <?php

//  $emp = Employees::findOne(['id' => $avatar['id']]);
?>
                            <?php // if($key < 11):?>
                            <a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="" data-bs-title="<?php // $emp->fullname?>">
                                <?php // Html::img($emp->ShowAvatar(),['class' => 'avatar-sm rounded-circle'])?>

                            </a>
                            <?php // endif;?>
                            <?php // endforeach;?>


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
                                        <?php // count($model->EmpOnWorkGroup($model->code))?> คน</span>
                                </h5>
                            </li>
                        </ul>
                    </div>
                    <div class="col pl-2">
                       <!-- xx -->
                    </div>
                </div>
            </div>
            <div class="dropdown edit-field-half-left">
                <div class="btn-icon btn-icon-sm btn-icon-soft-primary me-0 edit-field-icon" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </div>
                <div class="dropdown-menu dropdown-menu-right">
                    <?php Html::a('<i class="mdi mdi-pencil align-middle me-1 text-primary"></i>
                        <span>Edit</span>', ['view', 'id' => 0], ['class' => 'dropdown-item'])?>
                    <!-- <a href="#" class="dropdown-item">
                        <i class="mdi mdi-delete align-middle me-1 text-danger"></i>
                        <span>Delete</span>
                    </a> -->
                    <?=Html::a('<i class="fa-solid fa-circle-plus me-1"></i>
                        <span>แก้ไข</span>',['/hr/organization/update','id' => $model->id],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-md']]);?>
                </div>
            </div>
        </div>
        <!-- end card -->
    </div>
    <!-- end col-4 -->
    <?php endforeach;?>


</div>
<?php Pjax::end();?>


<?php
$js = <<< JS

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

JS;
$this->registerJS($js);

?>