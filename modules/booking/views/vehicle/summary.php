
<!-- Start BxStatus -->

<div class="row">
<div class="col-3">
        <?=$this->render('@app/components/ui/cardSummary',[
            'title' => $model->getStatus('Pending')['title'],
            'color' =>  $model->getStatus('Pending')['color'],
            'count' =>  $model->getStatus('Pending')['count'],
            'icon' => '<i class="fa-regular fa-hourglass-half text-black-50 fs-1"></i>',
            'progress' => $model->getStatus('Pending')['percent'],
            ])
            ?>
    </div>
    
    <div class="col-3">
        <?=$this->render('@app/components/ui/cardSummary',[
            'title' => $model->getStatus('Pass')['title'],
             'color' =>  $model->getStatus('Pass')['color'],
            'count' =>  $model->getStatus('Pass')['count'],
            'icon' => '<i class="fa-solid fa-circle-check text-black-50 fs-1"></i>',
            'progress' => $model->getStatus('Pass')['percent'],
            ])
            ?>
        
    </div>
    <div class="col-3">
        <?=$this->render('@app/components/ui/cardSummary',[
            'title' => $model->getStatus('Cancel')['title'],
             'color' =>  $model->getStatus('Cancel')['color'],
            'count' =>  $model->getStatus('Cancel')['count'],
            'icon' => '<i class="fa-regular fa-circle-xmark text-black-50 fs-1"></i>',
            'progress' => $model->getStatus('Cancel')['percent'],
            ])
            ?>
        
    </div>
    <div class="col-3">
        <?=$this->render('@app/components/ui/cardSummary',[
            'title' => $model->getStatus('Approve')['title'],
            'color' =>  $model->getStatus('Approve')['color'],
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