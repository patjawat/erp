<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use app\models\Categorise;
use app\modules\am\models\AssetDetail;

$this->title = 'รายงานค่าเสื่อม';
$this->params['breadcrumbs'][] = ['label' => 'บริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"></path>
            <path d="M15 5.764v15"></path>
            <path d="M9 3.236v15"></path>
        </svg>
        ทะเบียน<?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>


<style>
    .kv-page-summary-container{
        font-weight: 500;
    }
</style>
<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<?php
echo GridView::widget([
    'id' => 'kv-grid-demo',
    'dataProvider' => $dataProvider,
    // 'filterModel' => $searchModel,
    'columns' => require(__DIR__.'/_columns.php'),        
    'headerContainer' => ['style' => 'top:50px', 'class' => 'kv-table-header'], // offset from top
    'floatHeader' => true, // table header floats when you scroll
    'floatPageSummary' => true, // table page summary floats when you scroll
    'floatFooter' => false, // disable floating of table footer
    'pjax' => false, // pjax is set to always false for this demo
    // parameters from the demo form
    'responsive' => false,
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'hover' => true,
    'showPageSummary' => true,
    'panel' => [
        'after' => '<div class="float-right float-end"><button type="button" class="btn btn-primary"><i class="fas fa-download"></i> ดาวน์โหลด </button></div><div style="padding-top: 5px;"><em>* สรุปค่าเสื่อมทรัพย์สิน '.Yii::$app->thaiFormatter->asDate($searchModel->q_lastDay, 'long').'</em></div><div class="clearfix"></div>',
        'heading' => '<h6 class="text-white"><i class="fa-solid fa-chart-line"></i>  ค่าเสื่อมทรัพย์สิน</h6>',
        'type' => 'primary',
        'before' => '<div style="padding-top: 7px;"><em>*</em></div>',

    ],
    
    // set export properties
    'export' => [
        'fontAwesome' => true
    ],
    'exportConfig' => [
        'html' => [],
        'csv' => [],
        'txt' => [],
        'xls' => [],
        // 'pdf' => [],
        'json' => [],
    ],
    // set your toolbar
    'toolbar' =>  [
        [
            // 'content' => $this->render('_search',['model' => $searchModel]), 
            'options' => ['class' => 'btn-group mr-2 me-2']
        ],
    ],
    'toggleDataContainer' => ['class' => 'btn-group mr-2 me-2'],
    'persistResize' => false,
    'toggleDataOptions' => ['minCount' => 10],
    'itemLabelSingle' => 'book',
    'itemLabelPlural' => 'books'
]);
?>
<?php Pjax::end();?>
