<?php
use yii\web\View;
use yii\helpers\Html;
use yii\db\Expression;
use yii\widgets\DetailView;
use app\modules\inventory\models\Stock;
$warehouse = Yii::$app->session->get('warehouse');
$stockEvents = Stock::find()
->andWhere([
    'asset_item' => $asset_item,
    'warehouse_id' => $warehouse->id,
])
->andWhere(['>', 'qty', 0]);

// Debug raw SQL
// echo $stockEvents->createCommand()->getRawSql();

$stockEvents = $stockEvents->all();

$balance = 0;
$balanceQty = 0;

?>

<?php if(count($stockEvents) > 0):?>
<table class="table">
    <thead>
        <tr>
            <th class="fw-semibold" scope="col" style="width:130px">หมายเลขล็อต</th>
            <th class="fw-semibold text-center">คงเหลือ</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach($stockEvents as $item2):?>
        <tr class="">
            <td><?=$item2->lot_number?></td>
            <td class="fw-semibold text-center"><?=$item2->qty?></td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
<?php else:?>
<h3 class="text-center">หมด</h3>
<?php endif;?>
<?php
$js = <<< JS

$('body').on('click', '.add-item-lot', function(e) {
    e.preventDefault();
    let id = $(this).attr('data-id');
    let category_id = $(this).attr('data-category-id');
    // let url = '/inventory/stock-order/add-to-cart?id=' + id + '&category_id=' + category_id;
    let url = $(this).attr('href');

    Swal.fire({
        title: 'ยืนยันการเพิ่มสินค้า?',
        text: "คุณต้องการเพิ่มสินค้านี้ลงในตะกร้าหรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, เพิ่มเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "get",
                url: url,
                data: {},
                dataType: "json",
                success: function(data) {
                    Swal.fire(
                        'สำเร็จ!',
                        'สินค้าได้ถูกเพิ่มลงในตะกร้าแล้ว.',
                        'success'
                    ).then(() => {
                        window.location.href = '/inventory/stock-order';
                    });
                },
                error: function(data) {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถเพิ่มสินค้าได้.',
                        'error'
                    );
                    console.log(data);
                }
            });
        }
    });
});

JS;
$this->registerJS($js,View::POS_END);

?>