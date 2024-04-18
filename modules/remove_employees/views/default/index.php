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
       

       <style>

        .tw-btn-primary{
            box-sizing: border-box; 
display: inline-flex; 
overflow: hidden; 
position: relative; 
z-index: 0; 
justify-content: center; 
align-items: center; 
outline-style: none; 
-webkit-font-smoothing: auto;
-moz-osx-font-smoothing: auto; 
font-weight: 400; 
white-space: nowrap; 
appearance: none; 
user-select: none; 
box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); 


        }
       </style>
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h5 class="page-title"><i class="fa-solid fa-user-group"></i> ทะเบียนพนักงาน</h5>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin-dashboard.html">แผงควบคุม</a></li>
                <li class="breadcrumb-item active">ทะเบียนพนักงาน</li>
            </ul>
        </div>
        <div class="col-auto float-end ms-auto">
                    <?=Html::a('<i class="bi bi-cloud-arrow-up"></i> น้ำเข้า',['create'],['class' => 'btn btn-success rounded-pill open-modal','data' => [
                        'size' => 'modal-lgss',
                        'data-bs-toggle' => 'modal'
                    ]])?>
                     <?=Html::a('<i class="bi bi-plus-circle"></i> เพิ่มบุคลากร',['create'],['class' => 'btn btn-primary rounded-pill open-modal'])?>
                   
            <!-- <div class="view-icons">
                <?=Html::a('<i class="fa fa-bars"></i>',['/employees','display' => 'list'],['class' => SiteHelper::getDisplay() == 'list' ? 'btn btn-secondary shadow' : 'btn btn-light'])?>
                <?=Html::a('<i class="fa fa-th"></i>',['/employees','display' => 'grid'],['class' => SiteHelper::getDisplay() == 'grid' ? 'btn btn-secondary shadow' : 'btn btn-light'])?>

            </div> -->
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

    <?php //  echo $this->render('_search', ['model' => $searchModel]); ?>

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