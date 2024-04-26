<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\Categorise;
use app\modules\am\models\AssetDetail;
use yii\widgets\Pjax;

$this->title = 'รายงานค่าเสื่อม';
$this->params['breadcrumbs'][] = ['label' => 'บริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock();?>
<?php $this->beginBlock('sub-title');?>
<?php $this->endBlock();?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock()?>

<style>
    .kv-page-summary-container{
        font-weight: 500;
    }
</style>
<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>

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
        'heading' => '<i class="fa-solid fa-chart-line"></i>  ค่าเสื่อมทรัพย์สิน',
        'type' => 'light',
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
            'content' => $this->render('_search',['model' => $searchModel]), 
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
