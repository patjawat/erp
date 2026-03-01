<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'ประเภทการลา';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-gear"></i> <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/views/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(); ?>
<?php $palette = ['#2196f3', '#4caf50', '#ffeb3b', '#ff9800', '#f44336', '#9c27b0', '#00bcd4', '#e91e63', '#607d8b']; ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> <?= $this->title ?>
                <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?></span> รายการ
            </h6>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col" class="fw-semibold">รหัส</th>
                    <th scope="col" class="fw-semibold">รายการ</th>
                    <th scope="col" class="fw-semibold">ค่าสี</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-self-center">
                <?php foreach ($dataProvider->getModels() as $item): ?>
                <tr>
                    <td scope="row"><?= Html::encode($item->code) ?></td>
                    <td class="fw-semibold"><?= Html::encode($item->title) ?></td>
                    <td class="fw-semibold">
                        <?= kartik\color\ColorInput::widget([
                            'name' => 'color_' . $item->id,
                            'value' => $item->data_json['color'] ?? '#2196f3',
                            'options' => [
                                'class' => 'leave-color-input',
                                'data-id' => $item->id,
                            ],
                            'pluginOptions' => [
                                'showDefaultPalette' => true,
                                'palette' => [$palette],
                                'allowEmpty' => false,
                            ],
                            'pluginEvents' => [
                                'change' => "function(event) {
                                    var color = $(this).val();
                                    var id = $(this).data('id');
                                    $.ajax({
                                        url: '" . Url::to(['/leave/leave-type/update-color', 'id' => $item->id]) . "',
                                        type: 'POST',
                                        data: {id: id, color: color},
                                        success: function(res) {
                                            $('body').find('.' + res.data.code).css('background-color', res.data.data_json.color);
                                        }
                                    });
                                }"
                            ],
                        ]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => ['class' => 'pagination pagination-sm'],
            ]) ?>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>
