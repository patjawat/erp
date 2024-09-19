<?php
use yii\helpers\Html;
use app\modules\lm\models\LeaveType;
?>


    <div class="row d-flex justify-content-center">
    <div class="col-8">

<div class="row">

    <?php foreach(LeaveType::find()->where(['name' => 'leave_type'])->all() as $model):?>
        <div class="col-2">
            
            <div class="card">
                <div class="card-body">
                    
                    <?=$model->title?>
                </div>
            </div>
        </div>
        <?php endforeach;?>
    </div>
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