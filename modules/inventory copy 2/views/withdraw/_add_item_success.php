<?php
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php Pjax::begin(['id' => 'inventory', 'enablePushState' => true, 'timeout' => 5000]);?>

<div class="d-flex justify-content-center">
    <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เลือกรายการต่อ',['/inventory/withdraw/product-list'],['class' => 'btn btn-primary rounded-pill shadow text-center'])?>

</div>

<?php Pjax::end(); ?>