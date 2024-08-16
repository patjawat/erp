<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
?>
<table class="table">
  <tbody>
    <?php foreach($dataProvider->getModels() as $key => $item):?>
    <tr>
      <td><?=Html::a($item->warehouse_name,['/inventory/warehouse/select-warehouse','id' => $item->id],['class' => 'select-warehouse','data' => ['title' => 'เลือก'.$item->warehouse_name,'text' => 'รายการที่เลือกไว้ในตะกร้าจะถูกลบ']])?></td>
    </tr>
  <?php endforeach;?>
  </tbody>
</table>

<div class="form-group mt-3 d-flex justify-content-center">
<?=Html::a('Clear',['/inventory/warehouse/clear-select-warehouse'],['class' => 'btn btn-danger shadow rounded-pill'])?>
</div>

<?php
$js  = <<< JS


$('.select-warehouse').click(function (e) { 
    e.preventDefault();
    var title = \$(this).data('title');
    var text = \$(this).data('text');
    Swal.fire({
            title: title,
            text: text,
            icon: "info",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "<i class='bi bi-x-circle'></i> ยกเลิก",
            confirmButtonText: "<i class='bi bi-check-circle'></i> ยืนยัน"
            }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "get",
                    url:$(this).attr('href'),
                    dataType: "json",
                    success: function (res) {
                        console.log(res);
                    }
                });
            }
            });

  
    
});
JS;
$this->registerJS($js,View::POS_END);
?>