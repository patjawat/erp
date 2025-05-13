<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\hr\models\Development;
/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'อบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-briefcase fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/development/menu') ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h4 class="card-title">สรุปข้อมูลการอบรมประจำปีงบประมาณ 2568</h4>
            <?php echo $this->render('_search_year',['model' => $searchModel])?>
        </div>

        <div
            class="table-responsive"
        >
            <table
                class="table table-primary"
            >
                <thead>
                    <tr>
                        <th class="text-start fw-semibold" scope="col">ประเภทการอบรม</th>
                        <th class="text-center fw-semibold" scope="col">ต.ค.</th>
                        <th class="text-center fw-semibold" scope="col">พ.ย.</th>
                        <th class="text-center fw-semibold" scope="col">ธ.ค.</th>
                        <th class="text-center fw-semibold" scope="col">ม.ค.</th>
                        <th class="text-center fw-semibold" scope="col">ก.พ.</th>
                        <th class="text-center fw-semibold" scope="col">มี.ค.</th>
                        <th class="text-center fw-semibold" scope="col">เม.ย.</th>
                        <th class="text-center fw-semibold" scope="col">พ.ค.</th>
                        <th class="text-center fw-semibold" scope="col">มิ.ย.</th>
                        <th class="text-center fw-semibold" scope="col">ก.ค.</th>
                        <th class="text-center fw-semibold" scope="col">ส.ค.</th>
                        <th class="text-center fw-semibold" scope="col">ก.ย.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($searchModel->listSummaryMonth() as $item):?>
                    <tr class="">
                        <td scope="row"><?=$item['title']?></td>
                        <td class="text-center fw-semibold"><?=$item['m10']?></td>
                        <td class="text-center fw-semibold"><?=$item['m11']?></td>
                        <td class="text-center fw-semibold"><?=$item['m12']?></td>
                        <td class="text-center fw-semibold"><?=$item['m1']?></td>
                        <td class="text-center fw-semibold"><?=$item['m2']?></td>
                        <td class="text-center fw-semibold"><?=$item['m3']?></td>
                        <td class="text-center fw-semibold"><?=$item['m4']?></td>
                        <td class="text-center fw-semibold"><?=$item['m5']?></td>
                        <td class="text-center fw-semibold"><?=$item['m6']?></td>
                        <td class="text-center fw-semibold"><?=$item['m7']?></td>
                        <td class="text-center fw-semibold"><?=$item['m8']?></td>
                        <td class="text-center fw-semibold"><?=$item['m9']?></td>
                    
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>
