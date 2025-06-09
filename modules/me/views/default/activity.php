<?php
use app\modules\helpdesk\models\Helpdesk;
use app\modules\purchase\models\Order;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\Pjax;

// $repairs = Helpdesk::find()->where(['created_by' => Yii::$app->user->id])->all();
// $orders = Order::find()->where(['name' => 'order', 'created_by' => Yii::$app->user->id])->all();
?>
<?php Pjax::begin(['id' => 'me-container', 'timeout' => 5000]); ?>
<table class="table">
    <thead>
        <tr>
            <th scope="col">ประเภท</th>
            <th scope="col">รายการ</th>
            <th scope="col">สถานะ</th>
            <th scope="col">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dataProvider->getModels() as $repair): ?>
        <tr class="">
            <td scope="row">

                <span> แจ้งซ่อม</span>


            </td>
            <td><span> <?= $repair->data_json['title'] ?></span></td>
            <td> <?= $repair->viewStatus() ?></td>
            <td>
                <?= Html::a('<i class="fa-solid fa-eye"></i>', ['/helpdesk/repair/timeline', 'id' => $repair->id, 'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'], ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
            </td>
        </tr>
        <?php endforeach; ?>

    </tbody>
</table>

<div class="d-flex justify-content-center">

    <div class="text-muted">
        <?= LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'firstPageLabel' => 'หน้าแรก',
            'lastPageLabel' => 'หน้าสุดท้าย',
            'options' => [
                'listOptions' => 'pagination pagination-sm',
                'class' => 'pagination-sm',
            ],
        ]); ?>
    </div>
</div>
<?php Pjax::end(); ?>