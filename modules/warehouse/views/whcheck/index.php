<?php

use app\modules\warehouse\models\Whcheck;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\WhcheckSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ตรวจรับ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/warehouse']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<div class="whcheck-index">

 
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

    <!-- <p>
        <?= Html::a('เพิ่มรายการ', ['create'], ['class' => 'btn btn-info']) ?>
    </p> -->

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                            <th scope="col" style="text-align: center;">#</th>
                            <th scope="col" width="5%">รหัส</th>
                            <th scope="col" style="text-align: center;" width="10%">วันที่ตรวจรับ</th>
                            <th scope="col" style="text-align: center;">ประเภท</th>
                            <th scope="col" style="text-align: center;" >ประเภทวัสดุ</th>
                            <th scope="col" style="text-align: center;" >รับเข้าคลัง</th>
                            <th scope="col" style="text-align: center;" >รับจาก</th>
                            <th scope="col" style="text-align: center;" >เจ้าหน้าที่</th>
                            <th scope="col" style="text-align: center;">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                          
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<!-- 
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'ref',
            'check_code',
            'check_date',
            'check_type',
            //'check_store',
            //'check_from',
            //'check_hr',
            //'check_status',
            //'data_json',
            //'updated_at',
            //'created_at',
            //'created_by',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Whcheck $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?> -->

    <?php Pjax::end(); ?>

</div>
