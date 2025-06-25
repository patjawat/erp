<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use kartik\color\ColorInput;
use app\modules\hr\models\LeavePolicies;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeavePoliciesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'สถานะการลา';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?=$this->title; ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu_settings') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/hr/views/leave/menu',['active' => 'setting'])?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(); ?>
<?php
$palette =  ['#2196f3', '#4caf50', '#ffeb3b', '#ff9800', '#f44336', '#9c27b0', '#00bcd4', '#e91e63', '#607d8b'];
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> <?=$this->title?>
                <span class="badge rounded-pill text-bg-primary"><?php echo $dataProvider->getTotalCount() ?></span>
                รายการ
            </h6>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr >
                    <th scope="col" class="fw-semibold">รหัส</th>
                    <th scope="col" class="fw-semibold">รายการ</th>
                    <th scope="col" class="fw-semibold">ค่าสี</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-self-center">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td scope="row"><?php echo $item->code?></td>
                    <td class="fw-semibold"><?php echo $item->title;?></td>
                    <td class="fw-semibold">
                        <?php 
                            echo kartik\color\ColorInput::widget([
                                'name' => 'color_' . $item->id,
                                'value' => $item->data_json['color'] ?? '', // assuming 'color' is the attribute
                                'options' => [
                                    'placeholder' => 'Choose your color ...',
                                    'class' => 'leave-color-input',
                                    'data-id' => $item->id,
                                    'value' => $item->data_json['color'] ?? '#2196f3', // preset default color if not set
                                ],
                                'pluginOptions' => [
                                    'showDefaultPalette' => true,
                                    'palette' => [$palette],
                                    'allowEmpty' => false,
                                ],
                                'pluginEvents' => [
                                    "change" => "function(event) {
                                        let color = $(this).val();
                                        let id = $(this).data('id');
                                        $.ajax({
                                            url: '" . Url::to(['/hr/leave-status/update-color','id' => $item->id]) . "',
                                            type: 'POST',
                                            data: {id: id, color: color},
                                            success: function(res) {
                                            console.log(res.data.data_json.color)
                                                $('body').find('.' + res.data.code).css('background-color', res.data.data_json.color);
                                            }
                                        });
                                    }"
                                ]
                            ]);
                        ?>
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            <?php echo  yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
            </div>
        <?php Pjax::end(); ?>
    </div>
</div>