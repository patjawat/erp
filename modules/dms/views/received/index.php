<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\modules\dms\models\Documents;
/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'หนังสือรับ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>
<?php echo  yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
<div class="documents-index">
    <?php Pjax::begin(); ?>
    <?php echo  yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
    <div class="card">
        <div class="card-body">

<div class="d-flex justify-content-between">
 
    <?php  echo $this->render('@app/modules/dms/views/documents/_search', ['model' => $searchModel]); ?>
    
</div>



    <table
        class="table table-hover table-striped"
    >
        <thead>
            <tr>
                <th scope="col" style="width:55px;">เลขรับ</th>
                <th scope="col">เรื่อง</th>
                <th scope="col">วันที่หนังสือ</th>
                <th scope="col">แก้ไข</th>
                <th scope="col">ส่งต่อ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dataProvider->getModels() as $item):?>
            <tr class="">
                <td><?php echo $item->doc_regis_number?></td>
                <td class="fw-light align-middle">
                    <a href="<?php echo Url::to(['/dms/documents/view','id' => $item->id])?>">
                        
                        <div class=" d-flex flex-column">
                            <span class="fw-normal fs-6"><?php echo $item->topic?></span>
                            
                        </div>
                        <!-- <span class="badge rounded-pill badge-soft-primary text-primary fw-lighter fs-13"> -->
                            <span class="text-primary fw-lighter fs-13">
                                <?php echo $item->documentOrg->title ?? '-';?></span>
                            </a>
                            </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <span class="fw-normal fs-6"><?php echo $item->viewDocDate()?></span>
                            <span class="fw-lighter fs-13"><?php echo AppHelper::timeDifference($item->doc_date)?></span>
                       </div>
                    </td>
                <td><?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i>',)?></td>
                <td>R1C3</td>
            </tr>
            <?php endforeach;?>
           
        </tbody>
    </table>

    </div>
    </div>
    
</div>

<?php echo  yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
<?php Pjax::end(); ?>
