<?php

use app\modules\sm\models\Inventory;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\sm\models\InventorySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ขอซื้อขอจ้าง';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>


<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start">  
            <?=app\components\AppHelper::Btn([
                    'url' =>['create'],
                    'model' =>true,
                    'size' => 'lg',
            ])?>
        </div>

        <div class="d-flex gap-2">
            <?=Html::a('<i class="bi bi-list-ul"></i>',['#','view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="bi bi-grid"></i>',['#','view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="fa-solid fa-gear"></i>',['#','title' => 'การตั้งค่าบุคลากร'],['class' => 'btn btn-outline-primary open-modal','data' => ['size' => 'modal-md']])?>
        </div>

    </div>
</div>

<div class="inventory-index">
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<div class="card">
    <div class="card-body p-1">
       
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">เลขที่</th>
                <th scope="col">ผู้แทนจำหน่าย</th>
                <th scope="col">ราคารวม</th>
                <th scope="col">วันที่ขอ</th>
                <th scope="col" width="100px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dataProvider->getModels() as $model):?>
            <tr class="">
                <td scope="row">
                <?= Html::a('AB1234', ['update', 'id' => $model->id]) ?>
            </td>
            <td>R1C2</td>
            <td>R1C3</td>
            <td>R1C3</td>
            <td>
                <?= Html::a('<i class="fa-solid fa-eye"></i>', ['view', 'id' => $model->id]) ?>
            </td>
            </tr>
<?php endforeach;?>
        </tbody>
    </table>
</div>

    </div>
</div>
    <?php Pjax::end(); ?>

</div>
