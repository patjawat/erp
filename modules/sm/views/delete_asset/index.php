<?php

use app\modules\am\models\Asset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Assetsearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'วัสดุ';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<div class="card">
        <div
            class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
            <div class="d-flex justify-content-start">
                <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> Create Asset',
                    'url' =>['create'],
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

<div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col" style="text-align: center;">#</th>
                            <th scope="col">ตัวอย่างวัสดุ</th>
                            <th scope="col">ชื่อ</th>
                            <th scope="col">จำนวน</th>
                            <th scope="col" >ราคา</th>
                            <th scope="col" >ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataProvider->getModels() as $key => $model):?>
                        <tr>
                            <th scope="row" style="text-align: center;"><?= $model->id ?></th>
                            <!-- <th scope="row" style="text-align: center;"><?php //$key+1?></th> -->
                            <td class="align-middle">
                            

                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                            <?=Html::img($searchModel->ShowLogoImg($model->ref),['class' => 'product']);?>
                                    </div>
                                </div>

                            </td>
                            <td class="align-middle"><?= $model->data_json['name'] ?></td>
                            <td class="align-middle"><?= $model->qty ?></td>
                            <td class="align-middle"> <?= $model->price ?></td>
                            <td class="align-middle">
                            <div class="btn-group">
                                <?=Html::a('ดูรายละเอียดเพิ่มเติม',['view', 'id' => $model->id],['class' => 'btn btn-secondary'])?>
                                <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?=Html::a('แก้ไข',['update', 'id' => $model->id], ['class' => 'btn btn-primary dropdown-item', 'data' => ['size' => 'modal-lg']])?></li>
                                    <li><?=Html::a('ลบ',['deleted', 'id' => $model->id], ['class' => 'btn btn-primary dropdown-item', 'data' => ['size' => 'modal-lg']])?></li>
                                </ul>
                            </div>
                         </td>

                        </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <?php Pjax::end(); ?>

</div>