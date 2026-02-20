<?php
use yii\helpers\Html;
use yii\web\View;

/** @var yii\data\ActiveDataProvider $dataProvider */
?>
<table class="table">
    <tbody>
        <?php foreach ($dataProvider->getModels() as $item): ?>
            <tr>
                <td><?= Html::a($item->warehouse_name, ['/inventory-v2/warehouse/view', 'id' => $item->id], ['class' => 'select-warehouse-v2', 'data' => ['title' => 'เลือก ' . $item->warehouse_name]]) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::a('ล้างการเลือก', ['/inventory-v2/warehouse/clear'], ['class' => 'btn btn-danger btn-sm rounded-pill']) ?>
</div>
<?php
$js = <<< 'JS'
$('.select-warehouse-v2').on('click', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var title = $(this).data('title');
    if (typeof Swal !== 'undefined') {
        Swal.fire({ title: title, text: 'ต้องการเข้าใช้งานคลังนี้หรือไม่?', icon: 'question', showCancelButton: true })
            .then(function (result) { if (result.isConfirmed) window.location.href = url; });
    } else {
        window.location.href = url;
    }
});
JS;
$this->registerJs($js, View::POS_END);
