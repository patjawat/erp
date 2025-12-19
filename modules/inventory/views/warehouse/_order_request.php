<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\inventory\models\StockEvent;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = 'ทะเบียนขอเบิก';
// $this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/inventory/warehouse']];

$createIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-plus-2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M3 15h6"/><path d="M6 12v6"/></svg>';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        ทะเบียนขอเบิก<?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/inventory/menu',['active' => 'list']) ?>
<?php $this->endBlock(); ?>



<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search_order', ['model' => $searchModel,'dataProvider' => $dataProvider]); ?>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>
                            <i class="bi bi-ui-checks"></i> ขอเบิกจำนวน 
                            <span class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?></span>รายการ</h6>
                    </div>
                    <div>
                        <?php echo Html::a('<i class="fa-solid fa-angles-right"></i> แสดงท้ังหมด', ['/inventory/warehouse/order-request'], ['class' => 'btn btn-sm btn-light rounded-pill','data' =>['pjax' => 0]]) ?>
                    </div>
                </div>
                <?= $this->render('list_order',[  'searchModel' => $searchModel,'dataProvider' => $dataProvider, 'totalPrice' =>($totalPrice ?? 0)])?>
            </div>
        </div>
    </div>
</div>

<?php // Pjax::end(); ?>