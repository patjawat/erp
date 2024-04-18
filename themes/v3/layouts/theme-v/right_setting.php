<div class="right-setting">
    <div class="card mb-0 w-100">
        <div class="card-header">
            <h5 class="card-title d-flex justify-content-between">
                <?php if(isset($this->blocks['right-setting-title'])):?>
                <?=$this->blocks['right-setting-title']?>
                <?php endif;?>
                <a href="javascript:void(0)"><i class="bi bi-x-circle setting-close"></i></a>
            </h5>
        </div>
        <div class="card-body">
            <?php if(isset($this->blocks['right-setting'])):?>
            <?=$this->blocks['right-setting']?>
            <?php endif;?>
        </div>
    </div>
</div>