<?php
$this->title = "แผนงานและโครงการ";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check fs-4"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <h4 class="card-title"><?=$this->title?></h4>
        <p class="card-text">อยู่ระหว่างการพัฒนา</p>
    </div>
</div>

<div class="pm-default-index">
    <h1><?= $this->context->action->uniqueId ?></h1>
    <p>
        This is the view content for action "<?= $this->context->action->id ?>".
        The action belongs to the controller "<?= get_class($this->context) ?>"
        in the "<?= $this->context->module->id ?>" module.
    </p>
    <p>
        You may customize this page by editing the following file:<br>
        <code><?= __FILE__ ?></code>
    </p>
</div>
