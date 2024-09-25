<?php

use app\modules\lm\models\LeavePermission;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeavePermissionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'กำหนดค่าวันลาพักผ่อน';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(); ?>
<div class="card `text-start`">
    <div class="card-body">
<div class="d-flex justify-content-between">
    <h4><?= Html::encode($this->title) ?></h4>
    <div class="d-flex justify-content-between align-items-center gap-2">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่',['/lm/leave-permission/create'],['class' => 'btn btn-primary mt-3'])?>
    <?=Html::a('<i class="fa-solid fa-rotate"></i> ปรับปรุงวันลาพักผ่อน',['/lm/leave-permission/create'],['class' => 'btn btn-warning mt-3'])?>
    </div>
</div>

        <table
            class="table table-primary"
        >
            <thead>
                <tr>
                    <th scope="col">สำดับ</th>
                    <th scope="col">ปีงบประมาณ</th>
                    <th scope="col">ชื่อ-นามสกุล</th>
                    <th scope="col">อายุงาน</th>
                    <th scope="col">ประเภทบุคลากร</th>
                    <th scope="col">วันลา</th>
                </tr>
            </thead>
            <tbody>
                <tr class="">
                    <td scope="row">R1C1</td>
                    <td>R1C2</td>
                    <td>R1C3</td>
                    <td>R1C3</td>
                    <td>R1C3</td>
                    <td>R1C3</td>
                </tr>
               
            </tbody>
        </table>

        
    </div>
</div>
<?php Pjax::end(); ?>

