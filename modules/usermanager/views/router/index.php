<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\web\View;
$this->title = "กำหนดเส้นทาง";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-right-left"></i> <?=$this->title;?>  
<?php $this->endBlock(); ?>
<div class="card">
    <div class="card-body d-flex justify-content-between">
        <div id="showForm" style="width:700px"></div>
        <?=$this->render('../default/navlink')?>
    </div>
</div>


<div class="intro-x cursor-pointer box relative items-center p-3">


    <?php Pjax::begin(['id' => 'grid-router']); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
        'columns' => [
            'name',
            [
                'class' => 'kartik\grid\ActionColumn',
                'width'=> '110px',
                'template'=>'{update} {delete}',
                'buttons'=>[
                    'update' => function($url,$model,$key){
                        return Html::a('<i class="far fa-edit"></i>', ['update', 'id' => $model->name], [
                            'class' => 'text-warning mr-3 update',
                        ]);
                    },
                    'delete' => function($url,$model,$key){
                        return Html::a('<i class="far fa-trash-alt"></i>', ['delete', 'id' => $model->name], [
                            'class' => 'text-danger delete',

                        ]);
                    }
                    ]
            ],
        ],
    ]); ?>
</div>

<?php
$formCreate = Url::to(['/usermanager/router/create']);
$js = <<< JS
// โหลด Form
loadFormCreate();

// แก้ไขข้อมูล
$('.update').click(function (e) { 
    e.preventDefault();
    var form = $(this).attr('href');
    $.ajax({
        type: "get",
        url: form,
        dataType: "json",
        success: function (response) {
            $('#showForm').html(response);
        }
    });
});

// ลบข้แมูล
$('.delete').click(function (e) { 
    e.preventDefault();
    var form = $(this).attr('href');
    if (confirm('ลบหรือไม่?')) {
    $.ajax({
        type: "post",
        url: form,
        dataType: "json",
        success: function (response) {
            $.pjax.reload({container:"#grid-router"});
        }
    });
}
});

//เรียก Form
function loadFormCreate(){
    $.ajax({
        type: "get",
        url: "$formCreate",
        dataType: "json",
        success: function (response) {
            $('#showForm').html(response);
        }
    });
}

JS;
$this->registerJs($js,View::POS_END);
?>
<?php Pjax::end();?>