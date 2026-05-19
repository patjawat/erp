<?php

use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\EmployeePositionGroup;
use app\modules\hr\models\EmployeeType;
use kartik\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */

$view = $this;

$badgePill = static function (string $text, string $color = 'primary'): string {
    return '<span class="badge rounded-pill bg-' . $color . ' bg-opacity-10 text-' . $color . '">' . Html::encode($text) . '</span>';
};

$badgeActive = static function ($active): string {
    return (int) $active === 1
        ? '<span class="badge rounded-pill bg-success bg-opacity-10 text-success">ใช้งาน</span>'
        : '<span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary">ปิดใช้งาน</span>';
};

$actionDropdown = static function (string $type, $model, string $size = 'modal-md') use ($view): string {
    return $view->render('_action', [
        'type' => $type,
        'model' => $model,
        'size' => $size,
    ]);
};

$meta = [
    'type' => [
        'emptyIcon' => 'fa-user-tag',
        'emptyTitle' => 'ยังไม่มีประเภทพนักงาน',
        'emptyText' => 'กดปุ่มสร้างด้านบนเพื่อเพิ่มประเภทพนักงานชุดแรก',
    ],
    'group' => [
        'emptyIcon' => 'fa-layer-group',
        'emptyTitle' => 'ยังไม่มีกลุ่มตำแหน่ง',
        'emptyText' => 'เริ่มจากสร้างกลุ่มตำแหน่งชุดแรกเพื่อใช้งานเป็น master กลาง',
    ],
    'position' => [
        'emptyIcon' => 'fa-briefcase',
        'emptyTitle' => 'ยังไม่มีตำแหน่งพนักงาน',
        'emptyText' => 'หลังจากมีกลุ่มตำแหน่งแล้ว สามารถสร้างตำแหน่งเพื่อใช้งานจริงได้ทันที',
    ],
][$type] ?? [
    'emptyIcon' => 'fa-inbox',
    'emptyTitle' => 'ยังไม่มีรายการ',
    'emptyText' => 'กดปุ่มสร้างเพื่อเพิ่มข้อมูล',
];

$columns = [];

if ($type === 'type') {
    $columns = [
        ['class' => 'kartik\grid\SerialColumn'],
        [
            'attribute' => 'title',
            'label' => 'ประเภทพนักงาน',
            'format' => 'raw',
            'value' => static function (EmployeeType $model) use ($badgePill): string {
                return '<div class="d-flex flex-column gap-1">'
                    . '<div class="fw-semibold text-body">' . Html::encode($model->title) . '</div>'
                    . '</div>';
            },
        ],
        [
            'attribute' => 'sort',
            'label' => 'ลำดับ',
            'hAlign' => 'center',
            'width' => '90px',
        ],
        [
            'attribute' => 'active',
            'label' => 'สถานะ',
            'format' => 'raw',
            'hAlign' => 'center',
            'value' => static function (EmployeeType $model) use ($badgeActive): string {
                return $badgeActive($model->active);
            },
        ],
        [
            'header' => 'ดำเนินการ',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '110px',
            'value' => static function ($model) use ($actionDropdown): string {
                return $actionDropdown('type', $model, 'modal-md');
            },
        ],
    ];
} elseif ($type === 'group') {
    $columns = [
        ['class' => 'kartik\grid\SerialColumn'],
        [
            'attribute' => 'title',
            'label' => 'กลุ่มตำแหน่ง',
            'format' => 'raw',
            'value' => static function (EmployeePositionGroup $model) use ($badgePill): string {
                return '<div class="d-flex flex-column gap-1">'
                    . '<div class="fw-semibold text-body">' . Html::encode($model->title) . '</div>'
                    . '</div>';
            },
        ],
        [
            'attribute' => 'sort',
            'label' => 'ลำดับ',
            'hAlign' => 'center',
            'width' => '90px',
        ],
        [
            'attribute' => 'active',
            'label' => 'สถานะ',
            'format' => 'raw',
            'hAlign' => 'center',
            'value' => static function (EmployeePositionGroup $model) use ($badgeActive): string {
                return $badgeActive($model->active);
            },
        ],
        [
            'header' => 'ดำเนินการ',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '110px',
            'value' => static function ($model) use ($actionDropdown): string {
                return $actionDropdown('group', $model, 'modal-md');
            },
        ],
    ];
} else {
    $columns = [
        ['class' => 'kartik\grid\SerialColumn'],
        [
            'attribute' => 'employee_position_group_id',
            'label' => 'กลุ่มตำแหน่ง',
            'format' => 'raw',
            'value' => static function (EmployeePosition $model) use ($badgePill): string {
                $title = $model->employeePositionGroup->title ?? '-';
                return $badgePill($title, 'secondary');
            },
        ],
        [
            'attribute' => 'title',
            'label' => 'ตำแหน่ง',
            'format' => 'raw',
            'value' => static function (EmployeePosition $model): string {
                return '<div class="d-flex flex-column gap-1">'
                    . '<div class="fw-semibold text-body">' . Html::encode($model->title) . '</div>'
                    . '</div>';
            },
        ],
        [
            'attribute' => 'sort',
            'label' => 'ลำดับ',
            'hAlign' => 'center',
            'width' => '90px',
        ],
        [
            'attribute' => 'active',
            'label' => 'สถานะ',
            'format' => 'raw',
            'hAlign' => 'center',
            'value' => static function (EmployeePosition $model) use ($badgeActive): string {
                return $badgeActive($model->active);
            },
        ],
        [
            'header' => 'ดำเนินการ',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '110px',
            'value' => static function ($model) use ($actionDropdown): string {
                return $actionDropdown('position', $model, 'modal-lg');
            },
        ],
    ];
}

$emptyText = '<div class="text-center py-5 px-3">'
    . '<div class="d-inline-flex align-items-center justify-content-center rounded-4 bg-primary bg-opacity-10 text-primary p-3 mb-3">'
    . '<i class="fa-solid ' . Html::encode($meta['emptyIcon']) . ' fs-4"></i>'
    . '</div>'
    . '<div class="fw-semibold mb-1">' . Html::encode($meta['emptyTitle']) . '</div>'
    . '<div class="text-muted small mb-0">' . Html::encode($meta['emptyText']) . '</div>'
    . '</div>';
?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '<div class="table-responsive">{items}</div>',
    'tableOptions' => [
        'class' => 'table table-striped table-hover align-middle mb-0',
    ],
    'emptyText' => $emptyText,
    'striped' => true,
    'hover' => true,
    'responsive' => false,
    'responsiveWrap' => false,
    'pager' => [
        'options' => [
            'class' => 'pagination justify-content-end mb-0',
        ],
    ],
    'columns' => $columns,
]); ?>
