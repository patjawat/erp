<?php
use yii\helpers\Url;
use yii\helpers\Html;
$total  = 0;

?>
<?php if($model->leader()):?>

<div class="card">
    <div class="card-body">
        <div class="d-flex">
            <?= Html::img($model->leader()->showAvatar(),['class' => 'avatar rounded-circle','width' => 30])?>
            <div class="flex-grow-1 overflow-hidden">
                <h5 class="card-title mb-2 pr-4 text-truncate">
                    <a href="<?=Url::to(['view', 'id' => $model->id])?>" class="text-dark"><?= $model->departmentName()?>
                    </a>
                </h5>
                <p class="text-muted mb-3">
                    <?=$model->leader()->fullname?>
                    <?php // isset($model->getLeaderFormWorkGroup()->fullname) ? $model->getLeaderFormWorkGroup()->fullname : '-'?>
                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                            class="fa-solid fa-flag-checkered"></i> ผู้นำทีม</label></p>


                <div class="avatar-stack">
                    <?php
                            $total = (int)count($model->listMenberOnDep());
                            ?>
                    <?php if($total >  11):?>
                    <a href="javascript: void(0);" class="condense-profile" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="" data-bs-title="ProfileUser">
                        <div class="avatar bg-light text-secondary op-6 fw-light avatar-sm me-0">
                            +<?=$total-11?></div>
                    </a>

                    <?php endif;?>
                    <?php  if($total == 0):?>
                    <a href="javascript: void(0);" class="condense-profile" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="" data-bs-title="">
                        <div class="avatar bg-light text-secondary op-6 fw-light avatar-sm me-0">
                            0</div>
                    </a>

                    <?php  endif;?>

                    <?php  foreach($model->listMenberOnDep() as  $key => $emp):?>

                    <?php if($key < 11):?>
                    <a href="<?=Url::to(['/hr/employees/view','id' => $emp->id])?>" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="" data-bs-title="<?php // $emp->fullname?>">


                        <?php if($model->id == $emp->id):?>
                            <?= Html::img($emp->ShowAvatar(),['class' => 'avatar-sm rounded-circle border-primary'])?>
                            <??>
                        <?php else:?>
                            <?= Html::img($emp->ShowAvatar(),['class' => 'avatar-sm rounded-circle'])?>
                        <?php endif ;?>


                    </a>
                    <?php  endif;?>
                    <?php  endforeach;?>


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
                            <span class="align-middle"> จำนวสมาชิก <?= count($model->listMenberOnDep())?> คน</span>
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

<?php endif;?>