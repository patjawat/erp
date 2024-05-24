<?php
use app\components\AppHelper;
use kartik\export\ExportMenu;
use kartik\grid\GridView;

$gridColumns = [
    'cid',
    'gender',
    'prefix',
    'fname',
    'lname',
    [
        'header' => 'วันเกิด พ.ศ.',
        'value' => function ($model) {
            return Yii::$app->thaiFormatter->asDate(
                AppHelper::DateToDb($model->birthday), 'php:d/m/Y'
            );
        }
    ],
    'email',
    'address',
    'zipcode',
    [
        'header' => 'วันที่เริ่มงาน',
        'value' => function ($model) {
            return Yii::$app->thaiFormatter->asDate($model->joinDate(), 'php:d/m/Y');
        }
    ],
    [
        'header' => 'เกษียณ พ.ศ.',
        'value' => function ($model) {
            return Yii::$app->thaiFormatter->asDate($model->year60(), 'php:d/m/Y');
        }
    ],
    [
        'header' => 'คงเหลือ/ปี',
        'value' => function ($model) {
            return $model->leftYear60();
        }
    ],
    'phone',
    [
        'header' => 'สถานะ',
        'value' => function ($model) {
            return $model->statusName();
        }
    ],
    [
        'header' => 'ตำแหน่งปัจจุบัน',
        'value' => function ($model) {
            return $model->positionName(['icon' => true]);
        }
    ],
    [
        'header' => 'วันที่แต่งตั้ง',
        'value' => function ($model) {
            return Yii::$app->thaiFormatter->asDate($model->nowPosition()['date_start'], 'php:d/m/Y');
        }
    ],
    [
        'header' => 'เลขตำแหน่ง',
        'value' => function ($model) {
            return $model->nowPosition()['position_number'];
        }
    ],
    [
        'header' => 'ประเภท',
        'value' => function ($model) {
            return $model->positionTypeName();
        }
    ],
    [
        'header' => 'ระดับตำแหน่ง',
        'value' => function ($model) {
            return $model->positionLevelName();
        }
    ],
    [
        'header' => 'ความเชี่ยวชาญ',
        'value' => function ($model) {
            return $model->expertiseName();
        }
    ],
    [
        'header' => 'ประเภท/กลุ่มงาน',
        'value' => function ($model) {
            return $model->positionGroupName();
        }
    ],
    [
        'header' => 'เงินเดือน',
        'value' => function ($model) {
            return $model->salary ? number_format($model->salary, 2) : '';
        }
    ],
];

echo ExportMenu::widget([
    'dataProvider' => $dataProvider,
    'columns' => $gridColumns,
    'clearBuffers' => true,
    'target' => GridView::TARGET_BLANK,
    'filename' => 'ข้อมูลบุคลากร_' . date('d/n/Y'),
    'exportConfig' => [
        ExportMenu::FORMAT_EXCEL_X => [
            'label' => 'ส่งออก Excel',
            'iconOptions' => ['class' => 'text-success me-1'],
            'linkOptions' => [],
            'options' => ['title' => 'titlex'],
            'alertMsg' => 'ส่งออกข้อมูลบุคลากร Excel File',
            'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'extension' => 'xlsx',
            'writer' => ExportMenu::FORMAT_EXCEL_X
        ],
        ExportMenu::FORMAT_TEXT => false,
        ExportMenu::FORMAT_PDF => false,
        ExportMenu::FORMAT_HTML => false,
        ExportMenu::FORMAT_EXCEL => false,
        // ExportMenu::FORMAT_EXCEL_X => false,
        ExportMenu::FORMAT_CSV => false,
    ]
]);
?>