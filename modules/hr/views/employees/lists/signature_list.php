
<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
$title = '<i class="fa-solid fa-file-signature"></i> ลายเซ็น';
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">            
            <h5 class="card-title"><?= $title ?></h5>
            <a href="#" class="select-signature btn btn-primary"><i class="fa-solid fa-arrow-up-from-bracket"></i> อัปโหลดลายเซ็น 150 * 70</a>

        </div>
            <div class="d-flex justify-content-center">

<input type="file" id="my_file_signature" style="display: none;" />

            <?=Html::img($model->SignatureShow())?>
        </div>
        </div>
        <div class="card-footer">
                
            </div>
        </div>


<?php
$ref = $model->ref;
$urlUpload = Url::to('/filemanager/uploads/single');
$js = <<< JS

$(".select-signature").click(function() {
    $("input[id='my_file_signature']").click();
});

$('#my_file_signature').change(function (e) { 
	e.preventDefault();
	formdata = new FormData();
    const maxSize = 5 * 150 * 70; // 5MB in bytes

    if($(this).prop('files').length > 0)
    {
		file =$(this).prop('files')[0];

         // Validate image size
         if (file.size > maxSize) {
            alert("ขนาดภาพใหญ่เกินไป");
            return;
        }
        
        formdata.append("signature", file);
        formdata.append("id", 1);
        formdata.append("ref", '$ref');
        formdata.append("name", 'signature');

        console.log(file);
		$.ajax({
			url: '$urlUpload',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (res) {
                console.log(res);
                location.reload();
			}
		});
    }
 });


JS;
$this->registerJS($js,View::POS_END);

?>