<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Calendar $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Calendars', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="calendar-view">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title',
            'date_start',
        ],
    ]) ?>
        <div class="d-flex justify-content-center">
        <?= Html::a('<i class="fa-regular fa-trash-can"></i> ลบ', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-danger rounded-pill shadow delete-event',
        ]) ?>
    </div>

</div>
<?php
$js = <<< JS

$('.delete-event').click(function (e) { 
    e.preventDefault();
    $.ajax({
        type: "post",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (res) {
            console.log(res);
            $('#calendar-me').click();
        }
    });
});
JS;
$this->registerJS($js);
?>