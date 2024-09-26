<?php

use app\modules\lm\models\Leave;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ระบบการลา';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<div class="card text-start">
    <div class="card-body">

    <p>
        <?= Html::a('สร้างใหม่', ['type-select'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">ชื่อ-นามสกุล</th>
                <th scope="col">ประเภท</th>
                <th scope="col">จากวันที่</th>
                <th scope="col">ถึงวันที่</th>
                <th scope="col">เหตุผล</th>
                <th scope="col">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dataProvider->getModels() as $model):?>
            <tr class="">
                <td scope="row"><?=$model->CreateBy()->getAvatar(false)?></td>
                <td>R1C2</td>
                <td>R1C3</td>
                <td>R1C3</td>
                <td>R1C3</td>
                <td>R1C3</td>
            </tr>
<?php endforeach;?>
        </tbody>
    </table>


    <?php Pjax::end(); ?>



    </div>
</div>
