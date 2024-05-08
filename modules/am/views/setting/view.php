<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */

$this->title = 'ประเภท' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Fsns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock();?>

<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>
<div class="row">
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex gap-2  justify-content-start mb-3">
                    <?=Html::a('<i class="fa-solid fa-house"></i> ย้อนกลับ', ['/am/setting'], ['class' => 'btn btn-primary'])?>
                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i> แก้ไข', ['/am/setting/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-warning  open-modal', 'data' => ['size' => 'modal-lg']])?>
                    <?=Html::a('<i class="bx bx-trash me-1"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], ['class' => 'btn btn-danger  delete-item'])?>
                </div>
                <?=DetailView::widget([
    'model' => $model,
    'attributes' => [
        [
            'label' => 'ชื่อรายการ',
            'value' => function ($model) {
                return $model->title;
            },
        ],
        [
            'label' => 'รหัส',
            'value' => function ($model) {
                return $model->code;
            },
        ],
        [
            'label' => 'อายุการใช้งาน (ปี)',
            'value' => function ($model) {
                return isset($model->data_json["service_life"]) ? $model->data_json["service_life"] : '';
            },
        ],
        [
            'label' => 'อัตราค่าเสื่อม',
            'value' => function ($model) {
                return isset($model->data_json["depreciation"]) ? $model->data_json["depreciation"] : '';
            },
        ],
        [
            'label' => 'ประเภท',
            'value' => function ($model) {
                switch ($model->code) {
                case 1:
                    return 'ที่ดิน';
                    break;
                case 2:
                    return 'สิ่งปลูกสร้าง';
                case 3:
                    return 'ครุภัณฑ์';
                    break;
                    break;
                default:
                    # code...
                    break;
                }
            },
        ],
    ],
])?>
            </div>
        </div>



    </div>


    <div class="col-8">
        <?=$this->render('list_items', ['model' => $model, 'dataProvider' => $dataProvider]);?>
    </div>
</div>

<?php Pjax::end();?>