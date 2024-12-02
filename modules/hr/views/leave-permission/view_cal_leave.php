<?php
use yii\helpers\Html;
use app\components\AppHelper;
use app\modules\hr\models\LeavePermission;
$thaiYear = AppHelper::YearBudget();
$checkOnYear = LeavePermission::find()->where(['thai_year' => $thaiYear])->count('id');
?>
<h1>View Call leave</h1>
<?php echo Html::a('คำนวน',['/hr/leave-permission/cal-leave-days'],['class'=>'btn btn-success','id' => 'calLeaveDays','data' => ['text' => 'คำนวนวันลาปีงบประมาณ"'.$thaiYear.'"']])?>

<?php
$js = <<< JS

$("body").on("click", "#calLeaveDays", async function (e) {
  e.preventDefault();
  var title = $(this).data('title');
  var text = $(this).data('text');
    await Swal.fire({
    title: 'การยืนยัน',
    text: text,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ยืนยัน!",
    cancelButtonText: "ยกเลิก",
    }).then(async (result) => {
    if (result.value == true) {
        await $.ajax({
        type: "post",
        url: $(this).attr('href'),
        dataType: "json",
        success: async function (response) {
            if (response.status == "success") {
            location.reload();
            // await  $.pjax.reload({container:response.container, history:false,url:response.url});
            success(text+"บัำเร็จ!.");
            }
        },
        
        });
    }
    });

});


JS;
$this->registerJS($js);
?>