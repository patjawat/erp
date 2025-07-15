<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\hr\models\Development;
/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'อบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-briefcase fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/development/menu') ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/hr/views/development/menu',['active' => 'index']) ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-body">
  <div class="d-flex justify-content-center  align-top align-items-center">
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> ทะเบียน<?=$this->title?>
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
            </h6>
        </div>
      

        <?php
        echo $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
        ?>

    </div>
</div>
<?php
$js = <<< JS

    

    \$('.filter-status').click(function (e) { 
        e.preventDefault();
        var id = \$(this).data('id');
        \$('#leavesearch-status').val(id);
        \$('#w0').submit();
        console.log(id);
        
        
    });
    JS;
$this->registerJs($js);
?>
<?php //  Pjax::end(); ?>