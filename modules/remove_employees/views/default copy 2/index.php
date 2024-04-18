<?php

// use app\models\Employees;
// use yii\helpers\Html;
// use yii\helpers\Url;
// use yii\grid\ActionColumn;
// use yii\grid\GridView;
// use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\EmployeesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

// $this->title = 'Employees';
// $this->params['breadcrumbs'][] = $this->title;
?>
<?php

use app\components\SiteHelper;
use frontend\models\Employees;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var frontend\models\EmployeesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

// $this->title = 'ทะเบียนพนักงาน';
// $this->params['breadcrumbs'][] = $this->title;
?>
       

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="page-title"><i class="fa-solid fa-user-group"></i> ทะเบียนพนักงาน</h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin-dashboard.html">แผงควบคุม</a></li>
                <li class="breadcrumb-item active">ทะเบียนพนักงาน</li>
            </ul>
        </div>
        <div class="col-auto float-end ms-auto">
                    <?=Html::a('<i class="fa fa-plus"></i> สร้างใหม่',['create'],['class' => 'btn btn-add-gradient open-modal','data' => [
                        'size' => 'modal-lg'
                    ]])?>
            <div class="view-icons">
                <?=Html::a('<i class="fa fa-bars"></i>',['/employees','display' => 'list'],['class' => SiteHelper::getDisplay() == 'list' ? 'btn btn-secondary shadow' : 'btn btn-light'])?>
                <?=Html::a('<i class="fa fa-th"></i>',['/employees','display' => 'grid'],['class' => SiteHelper::getDisplay() == 'grid' ? 'btn btn-secondary shadow' : 'btn btn-light'])?>

            </div>
        </div>
    </div>
</div>

<div class="employees-index">
<?php
// $v = substr("patjawat", 0,4);  // returns "abcde"
// $s = substr("sriboonruang", 0,2);  // returns "abcde"
// echo $v.$s;
?>

    <?php Pjax::begin(['id' => 'employee-container']); ?>

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php if(SiteHelper::getDisplay() == 'grid'):?>
    <?=$this->render('display_grid', [
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
    ])
    ?>

    <?php else: ?>
    <?=$this->render('display_list', [
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
    ])
    ?>
    <?php endif; ?>


    <?php Pjax::end(); ?>

</div>