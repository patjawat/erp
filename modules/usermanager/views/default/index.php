<?php
$this->title = "ตั้งค่าผู้ใช้งานระบบ";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?=$this->title;?>  
<?php $this->endBlock(); ?>

<div class="show-user"></div>

<?php
$js = <<< JS
loadUser();

function loadUser(){
    $.ajax({
        type: "get",
        url: "/usermanager/user",
        dataType: "json",
        beforeSend:function(){
            $('.loading-page').show();
        },
        success: function (response) {
            $('.show-user').html(response);
            $('.loading-page').hide();
        }
    });
}
JS;
$this->registerJS($js);
?>