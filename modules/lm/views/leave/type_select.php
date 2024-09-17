<?php
use yii\helpers\Html;
use app\modules\lm\models\LeaveType;
?>
<style>
.paper {
    width: 400px;
    padding: 30px;
    background: #fff;
    /* box-shadow: 0 10px 32px rgba(0, 0, 0, 0.5); */
    border: 1px solid #2196F3;
    border-radius: 10px;
}

.header-title {
    font-size: 20px;
    font-weight: 700;
}

.header-title span {
    font-weight: 400;
    opacity: 0.5;
}

.radio-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 20px 0;
}

.lbl-radio {
    display: block;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    padding: 15px;
    padding-left: 50px;
    position: relative;
    cursor: pointer;
}

.lbl-radio .content .title {
    font-weight: 600;
    margin-bottom: 7px;
}

.lbl-radio .content .subtext {
    opacity: 0.5;
    font-size: 14px;
}

.marker {
    width: 15px;
    height: 15px;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 15px;
    position: absolute;
    left: 20px;
    top: 40%;
}

input[type='radio']:checked+.lbl-radio {
    border-color: var(--theme);
}

input[type='radio']:checked+.lbl-radio>.marker {
    border: 4px solid var(--theme);
}

input[type='radio'] {
    display: none;
}
</style>




<aside class="radio-container">
    <!-- radio -->
    <div class="d-flex flex-wrap align-items-start gap-3">
        <?php foreach(LeaveType::find()->where(['name' => 'leave_type'])->all() as $model):?>
        <div class="bg-light p-4">
            <?=$model->title?>
        </div>

        <?php endforeach;?>
    </div>



</aside>
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