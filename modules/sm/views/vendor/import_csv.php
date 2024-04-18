<?php
use yii\widgets\ActiveForm;
use yii\bootstrap4\Toast;
use yii\helpers\Html;
$this->title = 'นำเข้าข้อมูล';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

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
            <?= $form->field($model, 'file')->fileInput(['id' => 'file','class'=>'form-control']) ?>
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
          <?=html::a('<i class="bi bi-download"></i> Download',"https://docs.google.com/spreadsheets/d/1x40-gFkoCECQSs7x-E5TRVhbHSq9TD93HAvY5YS1HcU/edit?usp=sharing",['class' => 'btn btn-outline-success','target'=>'_blank'])?>
          
        </div>
      </div>
  </div>
</div>




<?php if(!empty($error)) {
?>
<?php foreach($error as $x):?>
  <div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    <strong><i class="bi bi-exclamation-circle-fill"></i>  แจ้งเตือน!</strong> <?= $x ?>
  </div>
<?php endforeach;?>
<?php 
}else{
  if ($success == true){
?>
  
  <div class="alert alert-success alert-dismissible fade show">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      <strong><i class="bi bi-check-circle-fill"></i> แจ้งเตือน!</strong> นำเข้าไฟล์ CSV และอัปโหลดข้อมูลสำเร็จ
    </div>
  </div>
  <?php 
  }
}

?>