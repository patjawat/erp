<?php

/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'อบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = $this->title;
?>
<!-- https://www.canva.com/ai/code/thread/7537949c-ec8b-4ea4-b910-eee147b74607 -->


<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-check-icon lucide-book-check">
            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" />
            <path d="m9 9.5 2 2 4-4" />
        </svg>
        ทะเบียน<?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/hr/views/development/menu',['active' => 'index']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?=$this->render('_search', ['model' => $searchModel,'type' => 'development'])?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white"><i class="bi bi-ui-checks"></i> ทะเบียน<?=$this->title?> <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ</h6>
        </div>

    </div>
    <div class="card-body">

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