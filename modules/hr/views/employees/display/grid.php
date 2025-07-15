
<div class="row">
    <?php foreach($dataProvider->getModels() as $model):?>
    <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6 col-sm-12">
        <?=$this->render('@app/modules/hr/views/employees/avatar',['model' => $model,'list' => true])?>
    </div>
    <?php endforeach;?>
</div>