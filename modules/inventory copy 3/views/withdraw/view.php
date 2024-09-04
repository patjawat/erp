<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
?>



<?php yii\widgets\Pjax::begin(['id' => 'inventory', 'enablePushState' => false, 'timeout' => 5000]);?>

<div class="card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <?=$model->CreateBy('ผู้ขอเบิก')['avatar']?>
            <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div class="dropdown-menu">
                    <?php echo  Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/inventory/withdraw/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
<div class="col-8">
  <div id="viewOrderItem"></div>
</div>
<div class="col-4">

<div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0">ผู้เห็นชอบ</h6>
                <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#leader"
                    aria-expanded="true" aria-controls="collapseCard">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="card-body collapse show" id="leader">
                <!-- Start Flex Contriler -->
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-truncate">
                        <?= $model->viewChecker() ?>
                    </div>
                </div>
                <!-- End Flex Contriler -->
            </div>
            <div class="card-footer d-flex justify-content-between">

                <h6>อนุมัติ/เห็นชอบ</h6>
                <div>
                    <?php if(isset($model->data_json['checker_confirm'])  && $model->data_json['checker_confirm'] == 'Y'):?>
                    <?=Html::a('<i class="bi bi-check2-circle"></i> เห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-success rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php elseif($model->data_json['checker_confirm'] == 'N'):?>
                    <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่เห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-danger rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php else:?>
                    <?=Html::a('<i class="fa-regular fa-clock"></i> รอเห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-sm btn-warning rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                    <?php endif?>
                </div>
            </div>
        </div>

</div>
</div>




<?php
$url = Url::to(['/inventory/withdraw/list-order-item','id' => $model->id]);
$js = <<< JS


  loadOrderItem()
  async function loadOrderItem()
  {
    await $.ajax({
      type: "get",
      url: "$url",
      dataType: "json",
      success: function (res) {
        $('#viewOrderItem').html(res.content)
      }
    });
  }


    $("body").on("click", ".delete-order-item", async function (e) {
      e.preventDefault();
      var url = $(this).attr("href");
      // console.log('delete',url);
      // $('#main-modal').modal('show');

      await Swal.fire({
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
            success:  function (response) {
              if (response.status == "success") {
                loadOrderItem()
                // await  $.pjax.reload({container:response.container, history:false,url:response.url});
                // $.pjax.reload({
                //   container: response.container,
                //   history: false,
                //   url: response.url,
                // });
                success("ดำเนินการลบสำเร็จ!.");
                if (response.close) {
                  $("#main-modal").modal("hide");
                }
              }
            },
          });
        }
      });
    });


  
  JS;
$this->registerJS($js, View::POS_END);

?>


<?php yii\widgets\Pjax::End()?>