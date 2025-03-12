<?php
use yii\helpers\Html;
?>

<div class="row">
    <div class="col-8">
<?php foreach($model->listItems() as $item):?>
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Title</h4>
            <p class="card-text">Body</p>
        </div>
    </div>
    
<?php endforeach;?>    
</div>
    <div class="col-4">

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Title</h4>
                <p class="card-text">Body</p>
            </div>
        </div>

    </div>
    
</div>
