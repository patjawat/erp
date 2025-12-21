
<?php
use yii\bootstrap5\Modal;
      Modal::begin([
         'options' => [
            'id' => 'main-modal',
            // 'tabindex' => false // important for Select2 to work properly
        ],
         'dialogOptions' => [
        'class' => 'modal-dialog modal-dialog-scrollable',
    ],
            'title' => '',
            // 'size' => 'modal-sm',
             'closeButton' => [
                'label' => 'X',
                'class' => 'btn-close text-white',
                'style' => 'font-size: 20px;'
            ],
            'footer' => '',
            'clientOptions' => ['backdrop' => 'static', 'keyboard' => false],
        ]);
        Modal::end();

        ?>

