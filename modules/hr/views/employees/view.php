<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $model */

$this->title = $model->fullname;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);


?>
<?php Pjax::begin(['id' => 'hr-container','enablePushState' => true,'timeout' => 50000 ]); ?>
<?php $this->beginBlock('page-title'); ?>
ข้อมูลส่วนบุคคล | <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('menu')?>

<?php $this->endBlock(); ?>

<style>
.shadow {
    box-shadow: 0 2px 5px 0 rgba(0, 0, 0, .2), 0 2px 10px 0 rgba(0, 0, 0, .1) !important;
}
</style>

<div class="row d-flex flex-sm-row-reverse">


    <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-sx-12">
        <?= $this->render('avatar',['model' => $model])?>

        <?php //  $this->render('member_on_dep',['model' => $model])?>

        <?=Html::a('<i class="bi bi-cloud-plus-fill fs-3"></i> แบบสารสนเทศเบื้องต้น', ['upload-basic-doc', 'id' => $model->id], ['class' => 'w-100 mb-3 btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']])?>


        <div class="list-group">
            <?php foreach($model->generalMenu() as $list):?>
            <a href="<?=Url::to(['/hr/employees/view','id' => $model->id,'name' => $list['name']])?>"
                class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
                <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><?=$list['icon']?></div>
                <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                        <h6 class="mb-0 text-primary"><?=$list['title']?></h6>
                        <p class="mb-0 opacity-75 fw-light"><?=$list['subtitle']?></p>
                    </div>
                    <small class="opacity-50 text-nowrap"><?=$list['count']?></small>
                </div>
            </a>
            <?php endforeach;?>
        </div>
    </div>

    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
        <?php echo $this->render('box_summary',['model' => $model])?>
        <?php if($name):?>
        <!-- <div data-aos="fade-up" data-aos-delay="400"> -->
        <div>
            <?php echo $this->render('./lists/'.$name.'_list',['model' => $model,'name' => $name, 'dataProvider' => $dataProvider])?>
        </div>
        <?php else :?>
        <?php echo $this->render('general',['model' => $model])?>
        <?php echo $this->render('@app/views/profile/point_chart',['model' => $model])?>
        <?php // echo $this->render('@app/views/profile/estimate_chart')?>
        <div class="card">
            <div class="card-body">


                <div class="d-flex flex-column flex-sm-row justify-content-between mb-4 text-center text-sm-left">
                    <h5>หน้าที่รับมอบหมาย</h5>

                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                                aria-selected="true"> คณะกรรมการทีมประสาน</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile"
                                aria-selected="false" tabindex="-1">ครุภัณฑ์ที่รับผิดชอบ</button>
                        </li>


                    </ul>
                </div>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade active show" id="pills-home" role="tabpanel"
                        aria-labelledby="pills-home-tab" tabindex="0">
                            <?=$this->render('list_committee')?>
                        <?php // $this->render('@app/modules/hr/views/employees/team',['model' => $model])?>
                    </div>
                    <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab"
                        tabindex="0">
                        <?php //$this->render('@app/modules/hr/views/employees/assets',['model' => $model])?>
                    </div>

                </div>
            </div>
        </div>




        <!-- <br> -->
        <?php // $this->render('company',['model' => $model])?>
        <?php endif;?>

    </div>

</div>

<?php Pjax::end(); ?>