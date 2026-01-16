<?php

use app\modules\am\models\AssetDetail;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
    use app\components\AppHelper;
    use yii\web\View;


/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Asset Details';
$this->params['breadcrumbs'][] = $this->title;
$iconClean = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brush-cleaning-icon lucide-brush-cleaning">
            <path d="m16 22-1-4"></path>
            <path d="M19 14a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2h-3a1 1 0 0 1-1-1V4a2 2 0 0 0-4 0v5a1 1 0 0 1-1 1H6a2 2 0 0 0-2 2v1a1 1 0 0 0 1 1"></path>
            <path d="M19 14H5l-1.973 6.767A1 1 0 0 0 4 22h16a1 1 0 0 0 .973-1.233z"></path>
            <path d="m8 22 1-4"></path>
        </svg>'
?>
<div class="asset-detail-index">
    <div class="d-flex justify-content-between">
        <h6><?= Html::encode($this->title) ?></h6>

        <p>
            <?= Html::a('<i data-lucide="circle-plus"></i> สร้างใหม่', ['create', 'code' => $searchModel->code, 'title' => $iconClean . ' การบำรุงรักษา'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        </p>
    </div>



    <?php Pjax::begin(['id' => 'tax-container']); ?>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                 <th class="text-center" style="width:30px">ลำดับ</th>
                <th><i class="fa-solid fa-tag"></i> ข้อมูลการต่อภาษี</th>
                <th><i class="fa-solid fa-user-injured"></i> ข้อมูล พรบ.</th>
                <th><i class="fa-solid fa-car-burst"></i> ประกันภัย</th>
                <th style="width:100px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($dataProvider->getModels() as $key => $item): ?>

                <?php
                $startDate = AppHelper::DateFormDb($item->date_start);
                $endDate = AppHelper::DateFormDb($item->date_end);
                ?>

                <tr>
                    <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                    <td>
                        <div>ค่าภาษี : <?= $item->data_json['price'] ?></div>
                        <div><?= $startDate ?> ถึง <?= $endDate ?></div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <?= isset($item->data_json['company1']) ? $item->data_json['company1'] : '-' ?>
                            <br>
                            <?php
                            try {
                                $dateStart1 =  isset($item->data_json["date_start1"]) ? Yii::$app->thaiFormatter->asDate($item->data_json["date_start1"], 'medium') : '';
                                $dateEnd1 =  isset($item->data_json["date_end1"]) ? Yii::$app->thaiFormatter->asDate($item->data_json["date_end1"], 'medium') : '';
                                echo $dateStart1 . ' ถึง ' . $dateEnd1;
                            } catch (\Throwable $th) {
                                echo '-';
                            }
                            ?>
                        </div>
                    </td>
                    <td>
                        <?php if (isset($item->data_json['company2']) && $item->data_json['company2'] != ""): ?>
                            <div class="d-flex flex-column">
                                <?= isset($item->data_json['company2']) ? $item->data_json['company2'] : '-' ?>
                                <div>
                                    <div>
                                        <?php
                                        try {
                                            $dateStart2 =  isset($item->data_json["date_start2"]) ? Yii::$app->thaiFormatter->asDate($item->data_json["date_start2"], 'medium') : '';
                                            $dateEnd2 =  isset($item->data_json["date_end2"]) ? Yii::$app->thaiFormatter->asDate($item->data_json["date_end2"], 'medium') : '';
                                            echo $dateStart2 . ' ถึง ' . $dateEnd2;
                                        } catch (\Throwable $th) {
                                            echo '-';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center py-2">
                        <div class="d-flex justify-content-center">
                            <a href="<?= Url::to(['/am/vehicle-tax/view', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <a href="<?= Url::to(['/am/vehicle-tax/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข']) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path>
                                </svg>
                            </a>

                            <a href="<?= Url::to(['/am/vehicle-tax/delete', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary delete-item" title="ดูรายละเอียด">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 11v6"></path>
                                    <path d="M14 11v6"></path>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php Pjax::end(); ?>
    <?php
    $js = <<< JS

JS;
    $this->registerJS($js, View::POS_END);
    ?>


</div>