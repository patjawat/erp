<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\helpdesk\models\Helpdesk;
/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\HelpdeskSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Helpdesks';
$this->params['breadcrumbs'][] = $this->title;
?>

<table class="table table-primary">
    <thead class="table-primary">
        <tr>
            <th scope="col">ชื่อ-นามสกุล</th>
            <th scope="col" style="width: 120px;">ดำเนินการ</th>
            </div>
        </tr>
    </thead>
    <tbody class="table-group-divider">
        <?php foreach ($dataProvider->getModels() as $item): ?>
        <tr class="">
            <td scope="row">
                <?php 
                try {
                    echo $item->empTeam->getAvatar(false,''); 

                } catch (\Throwable $th) {
                    //throw $th;
                }
                ?>
            </td>
           
            <td class="align-middle">
                <div class="d-flex gap-2">

                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/helpdesk/team/update', 'id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    <?= Html::a('<i class="fa-solid fa-trash"></i>', ['/helpdesk/team/delete', 'id' => $item->id], [
                        'class' => 'btn btn-sm btn-danger rounded-pill delete-item',
                        ]) ?>
                        </div>

            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>



<?php
$js = <<< JS
$("body").on("click", ".delete-item", function (e) {
  e.preventDefault();
  var url = $(this).attr("href");
     Swal.fire({
    title: "คุณแน่ใจไหม?",
    text: "ลบรายการที่เลือก!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ลบเลย!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    console.log("result", result.value);
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: url,
        dataType: "json",
        success:   async function (response) {
          if (response.status == "success") {
              await success("ดำเนินการลบสำเร็จ!.");
              location.reload();
          }
        },
      });
    }
  });
});
JS;
$this->registerJS($js,View::POS_END);
?>