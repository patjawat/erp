
<?php
use yii\bootstrap5\Modal;
      Modal::begin([
         'options' => [
            'id' => 'main-modal',
            // 'tabindex' => false // important for Select2 to work properly
        ],
            'title' => '',
            // 'size' => 'modal-sm',
            'footer' => '',
            'clientOptions' => ['backdrop' => 'static', 'keyboard' => false],
        ]);
        Modal::end();

        ?>
