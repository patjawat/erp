
<!-- Start BxStatus -->

<div class="row">
<div class="col-3">
        <?=$this->render('@app/components/ui/cardSummary',[
            'title' => $model->getStatus('Pending')['view'],
            'count' =>  $model->getStatus('Pending')['count'],
            'icon' => '<i class="fa-regular fa-hourglass-half text-black-50 fs-1"></i>',
            'progress' => $model->getStatus('Pending')['percent'],
            ])
            ?>
    </div>
    
    <div class="col-3">
        <?=$this->render('@app/components/ui/cardSummary',[
            'title' => $model->getStatus('Pass')['view'],
            'count' =>  $model->getStatus('Pass')['count'],
            'icon' => '<i class="fa-solid fa-circle-check text-black-50 fs-1"></i>',
            'progress' => $model->getStatus('Pass')['percent'],
            ])
            ?>
        
    </div>
    <div class="col-3">
        <?=$this->render('@app/components/ui/cardSummary',[
            'title' => $model->getStatus('Cancel')['view'],
            'count' =>  $model->getStatus('Cancel')['count'],
            'icon' => '<i class="fa-regular fa-circle-xmark text-black-50 fs-1"></i>',
            'progress' => $model->getStatus('Cancel')['percent'],
            ])
            ?>
        
    </div>
    <div class="col-3">
        <?=$this->render('@app/components/ui/cardSummary',[
            'title' => $model->getStatus('Approve')['view'],
            'count' =>  $model->getStatus('Approve')['count'],
            'icon' => '<i class="fa-regular fa-star text-black-50 fs-1"></i>',
            'progress' => $model->getStatus('Approve')['percent'],
        ])
        ?>
    </div>
</div>
<!--End  BoxStatus -->
<?php

use app\modules\booking\models\Vehiclee;

?>