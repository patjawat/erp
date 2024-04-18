<?php

use app\modules\warehouse\models\Whwithdrow;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\WhwithdrowSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'เบิกวัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/warehouse']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<div class="whwithdrow-index">

  
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
                            <th scope="col" width="5%">รหัส</th>
                            <th scope="col" style="text-align: center;" width="10%">วันที่เบิก</th>
                            <th scope="col" style="text-align: center;" width="10%">วันที่จ่าย</th>
                            <th scope="col" style="text-align: center;">เบิกจากคลัง</th>
                            <th scope="col" style="text-align: center;">รายละเอียด</th>
                            <th scope="col" style="text-align: center;" width="10%">หน่วยงาน</th>
                            <th scope="col" style="text-align: center;" width="10%">ผู้ขอเบิก</th>
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
  
    <?php Pjax::end(); ?>

</div>
