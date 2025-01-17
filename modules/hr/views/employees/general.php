<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
$title = '<i class="fa-regular fa-address-card"></i> ข้อมูลพื้นฐาน';
?>
<div class="card border-0 rounded-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">

            <h5 class="card-title"><?=$title;?></h5>
        </div>

        <table class="table border-0 table-striped-columns mt-3">
            <tbody>
                <tr>
                    <td>ชื่อ-สกุล : </td>
                    <td><span class="text-pink fw-semibold"><?=$model->fullname?></span></td>

                    <td>เลขบัตรประชาชน : </td>
                    <td><span class="text-pink fw-semibold"><?=$model->cid?></span></td>
                </tr>
                <tr>
                    <td>วันเกิด : </td>
                    <td><?=Yii::$app->thaiFormatter->asDate(AppHelper::DateToDb($model->birthday),'medium')?></td>

                    <td>อายุปัจจุบัน : </td>
                    <td><?=$model->age?></td>
                </tr>

                <tr>
                    <td>อายุครบหกสิบปี : </td>
                    <td><?=$model->year60()?></td>

                    <td>ภูมิลำเนาเดิม : </td>
                    <td><?=$model->born?></td>
                </tr>
                <tr>
                    <td>ที่อยู่ : </td>
                    <td colspan="4"><?=$model->fulladdress?></td>
                </tr>
                <tr>
                    <td>หมายเลขโทรศัพท์ : </td>
                    <td><?=$model->phone?></td>

                    <td>อีเมล : </td>
                    <td><?=$model->email?></td>
                </tr>


                <tr>
                    <td scope="row">ตำแหน่ง</td>
                    <td><span class="text-pink fw-semibold"><?=$model->positionName()?></span></td>
                    <td>ประเภท</td>
                    <td><span class="text-pink fw-semibold"><?=$model->positionTypeName()?></span></td>
                   

                </tr>
                <tr>
               
                    <td scope="row">ตำแหน่งเลขที่</td>
                    <td><span class="text-pink fw-semibold"><?=$model->position_number?></span></td>
                    <td>ระดับตำแหน่ง</td>
                    <td><span class="text-pink fw-semibold"><?=$model->positionLevelName()?></span></td>
                </tr>
                <tr>
                    <td>ตำแหน่งบริหาร</td>
                    <td><span class="text-pink fw-semibold"><?=$model->positionManageName();?></span></td>
                    <td>ความเชี่ยวชาญ</td>
                    <td><span class="text-pink fw-semibold"><?=$model->expertiseName();?></span></td>
                </tr>
                <tr>
                    <td>ระดับการศึกษา</td>
                    <!-- <td colspan="3"> -->
                        <td>
                       <?=$model->educationName()?>
                        </div>
                    </td>

                    <td>ประเภท/กลุ่มงาน</td>
                    <td><span class="text-pink fw-semibold"><?=$model->positionGroupName();?></span></td>
                </tr>

                <tr>
                    <td>ลายเซ็น</td>
                    <td colspan="3">
                            <?=Html::img($model->SignatureShow())?>
                            <br>
                            <br>
                            <a href="#" class="select-signature btn btn-primary"><i class="fa-solid fa-arrow-up-from-bracket"></i> อัปโหลดลายเซ็น 150 * 70</a>
                        </div>
                    </td>
                </tr>
                
            </tbody>
        </table>
    </div>
</div>

<input type="file" id="my_file_signature" style="display: none;" />

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