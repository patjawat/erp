<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Booking $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Bookings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('icon'); ?>
<?php if($model->car_type == 'general'):?>
    <i class="fa-solid fa-car fs-1"></i>
    <?php endif;?>
    <?php if($model->car_type == 'ambulance'):?>
        <i class="fa-solid fa-truck-medical fs-1"></i>
    <?php endif;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
ระบบยานพาหนะ
<?php $this->endBlock(); ?>


<?php $this->beginBlock('sub-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<div class="booking-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'ref',
            'name',
            'thai_year',
           
        ],
    ]) ?>

</div>


<div class="table-responsive">
    <table class="table table-primary align-middle">
        <thead>
            <tr>
                <th scope="col" style="width:120px;">วันที่</th>
                <th scope="col" style="width: 400px;">พนักงานขับ</th>
                <th scope="col" style="width: 400px;">รถยนต์</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($model->listDriverDetails() as $item):?>
            <tr class="">
                <td scope="row"><?php echo Yii::$app->thaiFormatter->asDate($item->date_start, 'medium');?> </td>
                <td id="selectCar<?php echo $item->id?>">

                    <div class="card mb-0 border-1 border-primary" data-id="<?php echo $item->id?>" >
                        <div class="card-body p-2 d-flex justify-content-center">
                            <a href="#" class="show-car" data-id="<?php echo $item->id?>" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"> <i class="bi bi-plus-circle text-primary"></i></a>
                        </div>
                    </div>
                </td>
                <td id="showSelectDriver<?php echo $item->id?>"><div class="card mb-0 border-1 border-primary">
                <div class="card-body p-2 d-flex justify-content-center">
                    <a href="#" class="show-driver" data-id="<?php echo $item->id?>" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRightDriver"
                        aria-controls="offcanvasRightDriver"> <i
                            class="bi bi-plus-circle text-primary"></i></a>
                </div>
            </div></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>
