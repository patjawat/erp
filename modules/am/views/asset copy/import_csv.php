<?php
use yii\helpers\Html;
use yii\bootstrap4\Toast;

use app\models\Categorise;
use yii\widgets\ActiveForm;


$this->title = 'นำเข้าข้อมูล';
$this->params['breadcrumbs'][] = ['label' => 'ทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>


<?php if($status == 'success'):?>
<div class="row d-felx justify-content-center">
  <div class="col-6">
  <div class="alert alert-success" role="alert">
  นำเข้าสำเร็จ
</div>

  </div>
</div>
  <?php else:?>
<div class="d-flex justify-content-center" style="width: 100%">
  <div class="card" style="width: 25%; margin-right:10px;">
    <div class="card-body">
      <h5 class="card-title"><i class="fa-solid fa-file-import me-1"></i> นำเข้า CSV</h5>
      <h6 class="card-subtitle mb-2 text-muted ">ระบบนำเข้าข้อมูลด้วยไฟล์ CSV</h6>
      <div style="width: 100%" class="d-flex justify-content-center mt-3">
          <?php
          $form = ActiveForm::begin(['id' => 'form-csv','options' => ['enctype' => 'multipart/form-data']]);
          ?>
          <div >
            <?= $form->field($model, 'csvFile')->fileInput(['id' => 'file','class'=>'form-control']) ?>
          </div>

          <div style="width: 100%" class="d-flex justify-content-center">
            <button class="mt-1 btn btn-success" type="submit"><i class="bi bi-upload"></i> Upload</button>
          </div>
          <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>
  <div class="card" style="width: 25%; margin-left:10px;"  >
    <div class="card-body">
        <h5 class="card-title"><i class="bi bi-file-earmark-text-fill"></i> ตัวอย่างข้อมูล </h5>
        <h6 class="card-subtitle mb-2 text-muted ">ตัวอย่างข้อมูลที่ต้องการในรูปแบบไฟล์ CSV</h6>
        <div class="d-flex justify-content-around align-items-center mt-5">
          <?=html::a('<i class="bi bi-download"></i> Download',"https://docs.google.com/spreadsheets/d/1M3nRMQI1MTGeCZPaq3cEhbGBBN6tBmKJBq3n59t1Nlo/edit?fbclid=IwAR377D9mkZkx4BH6iF1OavOhZT5Tmcxg3S8x5KITo3GVh6RkgIb_AJ7vUrY",['class' => 'btn btn-outline-success','target'=>'_blank'])?>
          
        </div>
      </div>
  </div>
</div>

<?php endif;?>