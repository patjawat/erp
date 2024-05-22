<?php

use app\modules\sm\models\Product;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">

    <div class="col-9">
        <div class="card">
            <div class="card-body d-flex justify-content-between">
                <h4 class="card-title">รายการวัสดุ</h4>
                <div>

                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/sm/product/create'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </div>
            </div>
        </div>



        <?php Pjax::begin(); ?>
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


        <div class="row">
        <?php for ($i = 0; $i < 12; $i++): ?>
            <div class="col-3">
                <div class="card">
                    <img class="card-img-top" src="https://angular.spruko.com/vexel/preview/assets/images/shop/1.png"
                        alt="Title" />
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex flex-column">
                                <span>เก้าอี้นั่ง</span>
                                <span class="text-light">วัสดุสำนักงาน</span>
                            </div>
                            <span class="badge text-bg-primary ">วัสดุสำนักงาน</span>
                        </div>
                        <span>คงเหลือ 10 ชิ้น</span>
                    </div>
                </div>
            </div>
            <?php endfor; ?>

        </div>
    

        <?php Pjax::end(); ?>


    </div>
    <div class="col-3">
        <div class="card" style="height: 1400px;">
            <div class="card-body ">
                <h4 class="card-title">ตัวกรอง</h4>
                <?= $this->render('_search', ['model' => $searchModel]) ?>
            </div>
        </div>
    </div>
</div>