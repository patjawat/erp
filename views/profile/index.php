<?php
use app\components\UserHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'โปรไฟล์';

$this->params['breadcrumbs'][] = $this->title;


$user  = UserHelper::GetUser();
?>

<?php $this->beginBlock('page-title'); ?>
<?=$this->title;?>
<?php $this->endBlock(); ?>

<div class="row d-flex flex-sm-row-reverse">
    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
        <?=$this->render('@app/modules/hr/views/employees/avatar',['model' => $model])?>
        <?php if($model->leader()):?>
        <?=$this->render('@app/modules/hr/views/employees/member_on_dep',['model' => $model])?>
        <?php endif;?>
        <div class="list-group border-0">
            <a href="<?=Url::to(['/profile','name' => ''])?>"
                class="list-group-item list-group-item-action d-flex gap-3 py-3">
                <div class="avatar-md rounded-circle bg-light">
                    <i class="bi bi-hourglass-split avatar-title text-primary"></i>
                </div>
                <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                        <h6 class="mb-0 text-primary">โปรไฟล์</h6>
                        <p class="mb-0 opacity-75 fw-light">ข้อมูลพื้นฐานตามบัตรประชาชน</p>
                    </div>
                    <small class="opacity-50 text-nowrap">0</small>
                </div>
            </a>

            <a href="<?=Url::to(['/profile','name' => 'general'])?>"
                class="list-group-item list-group-item-action d-flex gap-3 py-3">
                <div class="avatar-md rounded-circle bg-light">
                    <i class="bi bi-hourglass-split avatar-title text-primary"></i>
                </div>

                <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                        <h6 class="mb-0 text-primary">ข้อมูลพื้นฐาน</h6>
                        <p class="mb-0 opacity-75 fw-light">ข้อมูลพื้นฐานตามบัตรประชาชน</p>
                    </div>
                    <small class="opacity-50 text-nowrap">0</small>
                </div>
            </a>

        </div>






    </div>

    <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12">
        <?=$this->render('@app/modules/hr/views/employees/box_summary',['model'=> $model])?>
        <div class="row">
            <div class="col-12">
                <?php if($name):?>
                <div>
                    <?php if($name == 'general'):?>
                    <?=$this->render('@app/modules/hr/views/employees/general',['model' => $model,'name' => $name])?>
                    <?php else :?>
                    <?=$this->render('@app/modules/hr/views/employees/lists/'.$name.'_list',['model' => $model,'name' => $name])?>

                    <?php endif;?>

                </div>
                <?php else :?>

                <?=$this->render('point_chart')?>
                <?=$this->render('estimate_chart')?>

                <?php endif;?>

            </div>
        </div>
        <?=$this->render('@app/modules/hr/views/employees/team',['model' => $model])?>

    </div>

</div>