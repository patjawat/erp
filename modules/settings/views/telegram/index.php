<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use app\models\Categorise;
use yii\grid\ActionColumn;

/** @var yii\web\View $this */
/** @var app\models\CategoriseSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ตั้งค่า Telegram Notify';
$this->params['breadcrumbs'][] = $this->title;
?>  

<?php $this->beginBlock('page-title'); ?>
<i class="fa-brands fa-telegram fs-1"></i> ตั้งค่า Telegram Notify
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?><?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/settings/views/menu',['active' => 'telegram']) ?>
<?php $this->endBlock(); ?>

<div class="categorise-index">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <?= Html::a('<i class="fa-solid fa-circle-plus me-2"></i>สร้างใหม่', ['/settings/telegram/create', 'name' => 'telegram', 'title' => 'telegram สร้าง  Telegram Notify'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>

            </div>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'line-group-container']); ?>

    <div class="card">
        <div class="card-body">

            <?= GridView::widget([
                'filterModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'code',
                        'width' => '40px',
                        'value' => function ($model) {
                            return $model->code;
                        }
                    ],
                    'title',
                    [
                        'header' => 'telegram API',
                        'format' => 'raw',
                        'value' => function ($model) {
                            try {
                                return Html::a(
                                    'getUpdate',
                                    'https://api.telegram.org/bot' . $model->data_json['token'] . '/getUpdates',
                                    ['target' => '_blank']
                                );
                            } catch (\Throwable $th) {
                                // throw $th;
                            }
                        }
                    ],
                    [
                        'header' => 'ดำเนินการ',
                        'format' => 'raw',
                        'hAlign' => 'center',
                        'vAlign' => 'middle',
                        'width' => '170px',
                        'value' => function ($model) {
                            return '<div class="d-flex gap-2">'
                            .Html::a('ทดสอบ',['/settings/telegram/send','id' => $model->code,'msg' => 'ทดสอบส่งข้อความ'.$model->title],['class' => 'btn btn-sm btn-primary send-Telegram'])
                            . Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']])
                                . Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-danger',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to delete this item?',
                                        'method' => 'post',
                                    ],
                                ]).'</div>';
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <?php Pjax::end(); ?>

</div>
<?php 
$js = <<< JS
$("body").on("click", ".send-Telegram", function (e) {
    e.preventDefault();
    $.ajax({
        type: "get",
        url:$(this).attr('href'),
        dataType: "json",
        success: function (res) {
            if(res.status == 'success'){
                success();
            }
        }
    });
});
JS;
$this->registerJS($js,View::POS_END);

?>