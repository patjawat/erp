<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $model */

$this->title = $model->fullname;
$this->params['breadcrumbs'][] = ['label' => 'Employees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);


?>

<style>
.shadow {
    box-shadow: 0 2px 5px 0 rgba(0, 0, 0, .2), 0 2px 10px 0 rgba(0, 0, 0, .1) !important;
}
</style>


<div class="row">
    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
        <?=$this->render('box_summary')?>
        <?php if($detail):?>
        <div data-aos="fade-up" data-aos-delay="400">
            <?=$this->render('detail',['model' => $model])?>
        </div>
        <?php else :?>
        <?=$this->render('general',['model' => $model])?>
        <br>
        <?=$this->render('company',['model' => $model])?>
        <?php endif;?>
    </div>

    <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-sx-12">
        <?=$this->render('avatar',['model' => $model])?>


        <div class="list-group">
        <?php foreach($generalMenu as $list):?>
    <a href="#" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
      <!-- <img src="https://github.com/twbs.png" alt="twbs" width="32" height="32" class="rounded-circle flex-shrink-0"> -->
      <div class="rounded-2 flex-shrink-0 bg-primary-subtle px-3 py-2"><i class="bi bi-award"></i></div>
      <div class="d-flex gap-2 w-100 justify-content-between">
        <div>
          <h6 class="mb-0"><?=$list['title']?></h6>
          <p class="mb-0 opacity-75"><?=$list['subtitle']?></p>
        </div>
        <small class="opacity-50 text-nowrap">now</small>
      </div>
    </a>
    <?php endforeach;?>
  </div>


        <ol class="list-group list-group-numbered">
            <?php foreach($generalMenu as $list):?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                    <div class="fw-medium"><?=$list['title']?></div>
                    <?=Html::a($list['subtitle'],['/hr/employees/view','id' => $model->id,'detail' => $list['detail']],['class' => $detail == $list['detail'] ? 'text-primary fw-light' : 'text-dark fw-light'])?>
                </div>
                <span class="badge bg-secondary shadow rounded-pill"><?=$list['count']?></span>
            </li>
            <?php endforeach;?>

        </ol>

       
    </div>



</div>





<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12 bg-primaryx">

    </div>
    <!-- End Col-4 -->
    <!-- End Col-6 -->
    <div class="col-lg-4 col-md-12 col-sm-12">
        <?php // $this->render('notify_date',['model' => $model])?>

        <div class="bd-callout bd-callout-default">
            <div class="d-flex justify-content-between">
                <h5><i class="fa-solid fa-file-circle-check"></i> แบบสารสนเทศเบื้องต้น</h5>
                <?=Html::a('<i class="fa-solid fa-file-arrow-up"></i> ดำเนินการ', ['upload-basic-doc', 'id' => $model->id], ['class' => 'btn btn-warning shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
            </div>
            <strong>30 กันยายน พ.ศ.2566</strong>
        </div>


    </div>
</div>
<!-- End Col-3 -->