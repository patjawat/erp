<?php

use app\modules\am\components\AssetHelper;
use app\modules\am\models\Fsn;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\am\models\FsnSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'การกําหนดรหัสครุภัณฑ์';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>
<div class="fsn-index">

    <?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>

    <div class="row">

        <div class="col-8">

            <div class="card">
                <div class="card-body">
                    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">รายการครุภัณฑ์ </th>
                                    <th style="width:180px">หมายเลข</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dataProvider->getModels() as $key => $model): ?>
                                <tr class="">
                                    <td><?=Html::a($model->title, ['view', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']])?>
                                    </td>
                                    <td><?=$model->code?></td>
                                </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center">

                <div class="text-muted">
                    <?=LinkPager::widget([
    'pagination' => $dataProvider->pagination,
    'firstPageLabel' => 'หน้าแรก',
    'lastPageLabel' => 'หน้าสุดท้าย',
    'options' => [
        'listOptions' => 'pagination pagination-sm',
        'class' => 'pagination-sm',
    ],
]);?>
                </div>
            </div>


        </div>
        <!-- End col-8 -->

        <div class="col-4">

        <div class="card">
            <div class="card-body">
            <div class="d-flex justify-content-between">
                        <h5>หมวดหมุ่</h5>
                        <?=Html::a('<i class="fa-solid fa-gear"></i> การตั้งค่า', ['/am/fsn/group-setting'], ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']])?>
                    
                    </div>
            </div>
        </div>
            <ol class="list-group list-group-numbered">
            <?php foreach (AssetHelper::FsnGroup() as $fsnGroup): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start <?=$fsnGroup->code == $group ? 'active' : ''?>">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">หมวด (<?=$fsnGroup->code?>)</div>
                        <?=Html::a($fsnGroup->title, ['/am/fsn', 'group' => $fsnGroup->code])?>
                    </div>
                    <span class="badge bg-primary rounded-pill text-white">0</span>
                </li>
                <?php endforeach;?>
            </ol>


        </div>

    </div>
    <!-- End Row -->



    <?php Pjax::end();?>

</div>