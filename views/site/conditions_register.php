<h1 class="text-center">PDPA</h1>


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