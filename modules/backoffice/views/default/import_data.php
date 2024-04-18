<?php
use yii\helpers\Html;
use yii\web\View;
?>
<h4 class="text-center btn-import">
    <?php //Html::a('<i class="bi bi-database-down fs-1"></i> คลิกที่นี่เพื่อโอนข้อมูล',['/backoffice/default/import-data'],['id' => 'import'])?>
</h4>

<h4 class="text-center import" style="display:none">
    <?=Html::img('@web/svg/animate-database.gif')?> กำลังดำเนินการ..
</h4>

<h4 class="text-center import-success" style="display:none">
<i class="bi bi-check2-circle"></i> นำเข้าสำเร็จ !
</h4>
<?php 
$js = <<< JS

$('#import').click(function (e) { 

    e.preventDefault();
    $('.btn-import').hide();
    $('.import').show();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (res) {
            if(res){
                $('#main-modal').modal('toggle');
                $('.import').hide();
                $('.import-suess').show();
                success('นำเข้าข้อมูลรสำเร็จ')
            }
            
        }
    });
    
});


JS;
$this->registerJS($js,View::POS_END)
?>