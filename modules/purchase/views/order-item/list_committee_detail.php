<?php
use app\models\Categorise;
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\web\View;

?>
<!-- กรรมการตรวจรับ -->
<?php Pjax::begin(['id' => 'committee']); ?>
<?php
$model = Yii::$app->session->get('order');
$listBoard = Order::find()
    ->where(['name' => 'committee_detail'])
    ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.board') asc"))
    ->all();
?>


<table class="table">
    <thead class="table-primary">
        <tr>
            <th scope="col">รายการ</th>
            <th scope="col">ตำแหน่ง</th>
            <th scope="col" style="width: 120px;">
            ดำเนินการ
        </th>
        </tr>
    </thead>
    <tbody class="table-group-divider">
        <?php foreach ($listBoard as $item): ?>
            <tr class="">
                <td scope="row">
                    <?= $item->ShowCommittee()['avatar']; ?>
            </td>
            <td>
                <?php
                try {
                    echo $item->data_json['committee_name'];
                } catch (\Throwable $th) {
                }
                ?>
                </td>
                <td class="align-middle">
                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/purchase/order-item/update', 'id' => $item->id, 'name' => 'board', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                <?= Html::a('<i class="fa-solid fa-trash"></i>', ['/purchase/order-item/delete', 'id' => $item->id, 'container' => 'committee','url' => Url::to(['/purchase/order-item/committee-detail','category_id' => $item->category_id,'title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับ'])], [
                    'class' => 'btn btn-sm btn-danger rounded-pill delete-committee',
                ]) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>



<?php
$js = <<< JS
$("body").on("click", ".delete-committee", function (e) {
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
              await $.pjax.reload({
                  container: response.container,
                  history: false,
                  url: response.url,
                });
                await $.pjax.reload({container:'#purchase-container', history:false,timeout: false});
                await success("ดำเนินการลบสำเร็จ!.");
          }
        },
      });
    }
  });
});
JS;
$this->registerJS($js,View::POS_END);
?>
<?php Pjax::end(); ?>