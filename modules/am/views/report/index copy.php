<?php
/** @var yii\web\View $this */
use app\models\Categorise;
use app\modules\am\models\AssetDetail;

$this->title = 'รายงานค่าเสื่อม';
$this->params['breadcrumbs'][] = ['label' => 'บริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock();?>
<?php $this->beginBlock('sub-title');?>
<?php $this->endBlock();?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>

<?php
// $price = 10000;
// $sum = 0;
// for ($i=0; $i < 5; $i++) {

//    $values=  $price * (100-40)/100;
//     // echo $a  (100-40)/100;
//     $sum += $values;
//     echo '<p>'.$sum.'</p>';

// }

?>

<div class="card">
    <img class="card-img-top" src="holder.js/100x180/" alt="Title" />
    <div class="card-body">

<?php


?>

<?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

        <div class="row">
            <div class="col-12">

                <div class="card rounded-4">

                    <div class="card-body">
                        <div class="d-flex justify-content-between">

                            <div class="card-title">รายงานค่าเสื่อม</div>

                        </div>

                        <div class="table-responsive">
                            <table class="table text-nowrap">
                                <thead>
                                    <tr>
                                        <th>รหัสสินทรัพย์</th>
                                        <th>วันที่ได้รับ</th>
                                        <th class="text-end">มูลค่า</th>
                                        <th class="text-end">ค่าเสื่อมยกมา</th>
                                        <th class="text-center">อัตรา</th>
                                        <th class="text-end">ค่าเสื่อมราคาสะสม</th>
                                        <!-- <th class="text-end">ค่าเสื่อมยกไป</th> -->
                                        <th class="text-end">มูลค่าสุทธิ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($dataProvider->getModels() as $model):?>
                                    <tr>
                                        <th scope="row">
                                            <div> <span class="d-block fw-semibold mb-1"> <?=isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-'?></span> <span
                                                    class="d-block text-muted fs-12"> <?=$model->code?></span> </div>
                                        </th>
                                        <td><?=Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium') ?></td>
                                        <td align="right"><?=number_format($model->price,2)?> </td>
                                        <td align="right"><?=$model->Depreciation()['last_price']?></td>
                                        <td align="right"><?=$model->Depreciation()['depreciation']?></td>
                                        <td align="right"><?=number_format($model->Depreciation()['total_month_price'],2)?> </td>
                                        <td align="right"><?=number_format($model->Depreciation()['total'],2)?> </td>
                                    </tr>
                                   
<?php endforeach;?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


            </div>
        </div>
