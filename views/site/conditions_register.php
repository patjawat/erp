<?php
use yii\helpers\Html;
use app\components\SiteHelper;
$info = SiteHelper::getInfo();

?>
<h6 class="text-center text-white"> คําประกาศความเป็นส่วนตัว (Privacy Notice) สําหรับผู้รับบริการทางการแพทย์</h6>

<?php if (!empty($info['pdpa_url'])): ?>
<iframe 
    src="<?= Html::encode($info['pdpa_url']) ?>" 
    width="100%" 
    height="600" 
    allow="autoplay">
</iframe>
<?php else: ?>
<p class="text-center text-danger">PDPA URL is not available.</p>
<?php endif; ?>


<div class="d-flex justify-content-center">
    <?=Html::a('<i class="fa-solid fa-shield-halved"></i> ฉันยอมรับ',['/site/accept-condition'],['class' => 'btn btn-light','id' => 'accept-condition'])?>
</div>


<?php
$js = <<< JS
$('#accept-condition').click(function (e) { 
    e.preventDefault();
    Swal.fire({
        title: 'ยืนยันการยอมรับ',
        text: "คุณยอมรับเงื่อนไขข้อตกลงหรือไม่?",
        iconHtml: '<i class="fa-solid fa-shield-halved"></i>',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ยอมรับ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        console.log(result.value == true);
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            dataType: "json",
            success: function (response) {
                console.log('success');
                
            }
        });
    });
});
JS;
$this->registerJs($js);
?>