
<?php
use yii\bootstrap5\Modal;

Modal::begin([
    'id' => 'fullscreen-modal',
    'title' => 'Full Screen Modal',
    'size' => Modal::SIZE_LARGE,
    'options' => [
        'class' => 'modal-fullscreen fade',
        'tabindex' => '-1',
    ],
    'footer' => '',
    'clientOptions' => ['backdrop' => 'static', 'keyboard' => false],
]);
Modal::end();
        ?>

