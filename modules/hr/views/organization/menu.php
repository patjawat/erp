
<?php
use yii\helpers\Html;
?>
<div class="card">
    <div class="card-body d-flex justify-content-between">
        <h4 class="card-title"> <?=$this->title?></h4>

        <!-- cta -->
        <div class="row">

            <div class="col-12">
                <div class="float-sm-end">
                    <?=Html::a('<i class="bi bi-diagram-3"></i>  กำหนดผังองค์กร', ['/hr/organization/diagram'], ['class' => 'btn btn-primary'])?>
                    <?=Html::a('<i class="fa-solid fa-gear"></i> กำหนดตำแหน่ง', ['/hr/organization/position'], ['class' => 'btn btn-primary'])?>
                </div>
            </div>
        </div>
    </div>
</div>
