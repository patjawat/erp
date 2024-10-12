<?php

use app\modules\inventory\models\Stock;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = $this->title;


?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'card-container']); ?>
<?php
$cart = \Yii::$app->cart2;
$products = $cart->getItems();
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> วัสดุในสต๊อก <span class="badge rounded-pill text-bg-primary">
                    <?=$dataProvider->getTotalCount();?> </span> รายการ</h6>
            <div class="d-flex">
                    <?=Html::a('<button type="button" class="btn btn-primary rounded-pill">
                    <i class="fa-solid fa-cart-plus"></i> เลือก <span class="badge text-bg-danger">'.$cart->getCount().'</span> รายการ
                    </button>',['/inventory/store/view-cart'],['class' => 'brn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>

                <?php // $this->render('_search', ['model' => $searchModel]); ?>
                <?php // echo Html::a('<i class="fa-solid fa-angles-right"></i> แสดงท้ังหมด', ['/inventory/stock/warehouse'], ['class' => 'btn btn-sm btn-light','data' => ['pjax' => 0]]) ?>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">ชื่อรายการ</th>
                    <th class="text-start">ประเภท</th>
                    <th scope="col" class="text-center">คงเหลือ</th>
                    <th scope="col" class="text-center">หน่วย</th>
                    <th scope="col" class="text-end">มูลค่า</th>
                    <th scope="col" class="text-end">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle">
      <?php
      $models = $dataProvider->getModels();
      usort($models, function($a, $b) {
        return $b->SumPriceByItem() - $a->SumPriceByItem();
    });
      ?>
                <?php foreach($models as $item):?>
                <tr>
                    <th scope="row"><?=Html::a($item->product->Avatar(),['/inventory/stock/view','id' => $item->id])?>
                    </th>
                    <td class="text-start">
                        <?=isset($item->product->productType->title) ? $item->product->productType->title : 'ไม่พบข้อมูล' ?>
                    </td>
                    <td class="text-center"><?=$item->SumQty()?></td>
                    <td class="text-center"><?=$item->product->data_json['unit']?></td>
                    <td class="text-end">
                        <span class="fw-semibold"><?=$item->SumPriceByItem()?></span>
                    </td>
                    <td class="text-end">
                        <?php if($item->SumQty() > 0):?>
                    <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เลือก',['/inventory/store/add-to-cart','id' => $item->id],['class' => 'add-cart btn btn-sm btn-primary shadow rounded-pill'])?>
                   <?php else:?>
                    <button type="button" class="btn btn-sm btn-primary shadow rounded-pill" disabled><i class="fa-solid fa-circle-plus"></i> เลือก</button>
                    <?php endif?>
                </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'class' => 'pagination pagination-sm',
                ],
            ]); ?>

        </div>
    </div>
    </div>
    <?php Pjax::end(); ?>


<?php
$storeProductUrl = Url::to(['/inventory/store/product']);
$showCartUrl = Url::to(['/inventory/stock-out/show-cart']);
$deleteItemUrl = Url::to(['/inventory/store/delete']);
$updateItemUrl = Url::to(['/inventory/store/update']);
$js = <<< JS


    $("body").on("click", ".add-cart", function (e) {
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (res) {
            if(res.status == 'error'){
                    Swal.fire({
                    icon: "warning",
                    title: "เกินจำนวน",
                    showConfirmButton: false,
                    timer: 1500,
                });
                }
                getViewCar()
                // success()
                // $.pjax.reload({ container:'#card-container', history:false,replace: false,timeout: false});
        }
    });
});



$("body").on("keypress", ".update-qty", function (e) {
    var keycode = e.keyCode ? e.keyCode : e.which;
    if (keycode == 13) {
        let qty = $(this).val()
        let id = $(this).attr('id')
        console.log(qty);
        
        $.ajax({
            type: "get",
            url: "/inventory/store/update-qty",
            data: {
                'id':id,
                'qty':qty 
            },
            dataType: "json",
            success: function (res) {
                if(res.status == 'error'){
                    Swal.fire({
                    icon: "warning",
                    title: "เกินจำนวน",
                    showConfirmButton: false,
                    timer: 1500,
                });
                }
                getViewCar()
            }
        });
    }
});


        $("body").on("click", ".update-cart", function (e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            data: {},
            dataType: "json",
            success: function (res) {
                if(res.status == 'error'){
                    Swal.fire({
                    icon: "warning",
                    title: "เกินจำนวน",
                    showConfirmButton: false,
                    timer: 1500,
                });
                }
                // success()
                $.pjax.reload({ container:'#card-container', history:false,replace: false,timeout: false});
            }
        });
    });

//     $("body").on("click", ".delete-item-cart", function (e) {
//     e.preventDefault();
//     $.ajax({
//         type: "get",
//         url: $(this).attr('href'),
//         dataType: "json",
//         success: function (response) {
//             success()
//                 $.pjax.reload({ container:'#card-container', history:false,replace: false,timeout: false});
//         }
//     });
// });

$("body").on("click", ".checkout", async function (e) {
    e.preventDefault();

  await Swal.fire({
    title: "ยืนยัน?",
    text: "บันทึกรายการนี้!",
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
            console.log(response);
            
          if (response.status == "success") {
            // await  $.pjax.reload({container:response.container, history:false,url:response.url});
            success("บันสำเร็จ!.");
          }
        },
      });
    }
  });

  });


JS;
$this->registerJS($js, View::POS_END);

?>

