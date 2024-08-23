<?php
use yii\helpers\Html;
?>
<?php if($model->status > 4):?>


    <div class="card border border-primary">
    <div class="card-body">
    <?php if($model->group_id == 3):?>
        <?=Html::a('แสดงทะเบียนทรัพย์สิน',['/purchase/order/view-asset','po_number' => $model->po_number],['class' => 'h6 open-modal','data' => ['size' => 'modal-xl']])?>
        <?php else:?>

        <h6 class="text-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-check-2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m3 15 2 2 4-4"/></svg>
        รับเข้าคลังสำเร็จ
        </h6>
        <?php endif?>
    </div>
</div>

<?php else:?>

        <div class="card border border-primary">
            <div class="card-body text-center">
                
                <?php if($model->group_id == 3):?>
                    <?=Html::a('สร้างทะเบียนทรัพย์สิน',['/purchase/order/register-asset','id' => $model->id],['class' => 'h6 confirm-order','data' => ['title' => 'ยืนยัน','text' => 'สร้างทะเบียนทรัพย์สิน']])?>
                    
                    <?php else:?>
                        <h6 class="text-center">
                            
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-hourglass">
                            <path d="M5 22h14" />
                            <path d="M5 2h14" />
                            <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22" />
                            <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2" />
                        </svg>
                        รอกำเนินการ
                    </h6>
                    <?php endif?>
        </div>
    </div>
 
<?php endif;?>


<?php
// echo "<pre>";
// print_r($model);
// echo "</pre>";
?>