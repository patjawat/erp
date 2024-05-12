<?php
use yii\helpers\Html;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\am\models\AssetDetail;

$repair = Helpdesk::findOne(['code' => $model->code]);
$modelCar = AssetDetail::find()->where(['name' => "tax_car",'code'=>$model->code])->orderBy(['date_start' => SORT_DESC])->one();
?>

<!-- Nav pills -->
<ul class="nav nav-pills" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="pill" href="#general">ข้อมูลทั่วไป</a>
    </li>
    <?php if($model->isCar()):?>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="pill" href="#isCar"><i class="fa-solid fa-car-on"></i> การต่อภาษี</a>
    </li>
    <?php endif;?>

    <?php if($model->isComputer()):?>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="pill" href="#isComputer">Spec</a>
    </li>
    <?php endif;?>
    
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div id="general" class="container tab-pane active"><br>
    <?=$this->render('general_detail',['model' => $model])?>
</div>
<?php if($model->isCar()):?>
    <div id="isCar" class="container tab-pane fade"><br>
    <?=$model->isCar() ? $this->render('./is_cars/tax', ['model' => $model]) : ''?>
</div>
<?php endif;?>
<?php if($model->isComputer()):?>
    <div id="isComputer" class="container tab-pane fade"><br>
    <h3>Menu 2</h3>
    <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.</p>
</div>
<?php endif;?>
  </div>

