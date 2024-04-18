<?php

use app\modules\sm\models\Supregister;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\sm\models\SupregisterSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ทะเบียนคุม';
$this->params['breadcrumbs'][] = ['label' => 'บริหารพัสดุ', 'url' => ['/sm']];
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
<div class="supregister-index">



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
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>




    <div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                            <th scope="col" style="text-align: center;">#</th>
                            <th scope="col">เลขทะเบียนคุม</th>
                            <th scope="col">วันที่</th>
                            <th scope="col" style="text-align: center;">รายละเอียด</th>
                            <th scope="col" style="text-align: center;">มูลค่า</th>
                            <th scope="col" style="text-align: center;">หน่วยงาน</th>
                            <th scope="col" style="text-align: center;">สถานะ</th>
                            <th scope="col" style="text-align: center;">ดำเนินการ</th>
                            
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($dataProvider->getModels() as $key => $model):?>
                            <tr>
                            <th scope="row" style="text-align: center;"><?=$key+1?></th>
                            <td class="align-middle">
                                    <div class="d-flex flex-column">
                                        <span>
                                                                                        <?=$model->asset_name;?><br>
                                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
                                                        <?=$model->fsn?></label>
                                                    </span>
                                                </div>
                                            </td>
                            <td>
                                    <div class="d-flex flex-column">
                                        <span>
                                            <label class="badge rounded-pill text-primary-emphasis bg-info-subtle me-1"><i
                                                    class="bi bi-clipboard-check"></i>&nbsp;ครุภัณฑ์สำนักงาน</label>
                                            |  <i class="bi bi-calendar-check-fill" >&nbsp;ฝ่ายแผนงานสารสนเทศ</i> 
                                        </span>
                                    </div>
                            </td>
                            <td class="align-middle" align="right"> <?=number_format($model->price,2)?></td>
                            <td class="align-middle" align="center">ต่ำ</td>
                            <td class="align-middle" align="center">ปกติ</td>
                            </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>



    <!-- <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'ref',
            'regisnumber',
            'start_date',
            'price',
            //'status',
            //'dep_code',
            //'data_json',
            //'updated_at',
            //'created_at',
            //'created_by',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Supregister $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?> -->

    <?php Pjax::end(); ?>

</div>
