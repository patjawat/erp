<h6 class="text-center text-white"> คําประกาศความเป็นส่วนตัว (Privacy Notice) สําหรับผู้รับบริการทางการแพทย์</h6>

<iframe 
    src="https://drive.google.com/file/d/1EYNl8tHtBZ9R6VeD3RJ2leyz5cACOAdW/preview" 
    width="100%" 
    height="600" 
    allow="autoplay">
</iframe>


<div class="d-flex justify-content-center">
    <p id="accept-condition" class="btn btn-primary"><i class="fa-regular fa-circle-check"></i> ฉันยอมรับ</p>
</div>


<?php
$js = <<< JS
$('#accept-condition').click(function (e) { 
    e.preventDefault();
    $('#btn-regster').removeClass('disabled');
    $('#customCheck1').prop( "checked", true );
    $('#customCheck1').prop('disabled', false)
    $('#main-modal').modal('toggle');
    success('ฉันยอมรับข้อกำหนดและเงื่อนไข');
    
});
JS;
$this->registerJs($js);
?>