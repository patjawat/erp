<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\lm\models\LeaveType;
$this->title = "ระบบลา";
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-check"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

    

    <div class="row d-flex justify-content-center">
    <div class="col-4">

    <?php foreach(LeaveType::find()->where(['name' => 'leave_type','active' => 1])->all() as $item):?>
        <a href="<?=Url::to(['/lm/leave/create','leave_type_id' => $item->code,'title' => $item->title])?>">
            <div class="card mb-2 zoom-in">
                <div class="card-body">
                    <?= isset($item->data_json['icon']) ? $item->data_json['icon'] : '-' ?> <?=$item->title?>
                </div>
            </div>
        </a>
        <?php endforeach;?>
    </div>
    </div>
    



<div class="d-flex justify-content-center">
    <?=Html::a('<i class="fa-regular fa-circle-check"></i> ตกลง',['/helpdesk/repair/create','send_type' => 'general','title' => '<i class="fa-solid fa-triangle-exclamation text-danger"></i> แจ้งงานซ่อมทั่วไป'],['class' => 'btn btn-primary open-modal get-general','data' => ['size' => 'modal-lg']])?>
    <?=Html::a('<i class="fa-regular fa-circle-check"></i> ตกลง',['/am/asset/index'],['class' => 'btn btn-primary get-asset','data' => ['pjax' => false]])?>
</div>

<?php
$js = <<< JS
  $(".get-general").css("display", "none");
  $(".get-asset").css("display", "none");
  $('input:radio').change(function() {
      if($(this).val() == 'get-general'){
          $(".get-general").css("display", "block");
        $(".get-asset").css("display", "none");
    }
    
    if($(this).val() == 'get-asset'){
        $(".get-general").css("display", "none");
        $(".get-asset").css("display", "block");
    }
    });

    $('.get-asset').click(function (e) { 
        beforLoadModal();
        
    });

JS;
$this->registerJS($js);
?>