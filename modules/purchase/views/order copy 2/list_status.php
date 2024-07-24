<style>
/* Steps */
.step {
    position: relative;
    min-height: 1em;
    color: gray;
}

.step+.step {
    margin-top: 1.5em
}

.step>div:first-child {
    position: static;
    height: 0;
}

.step>div:not(:first-child) {
    margin-left: 1.5em;
    padding-left: 1em;
}

.step.step-active {
    color: #4285f4
}


.step.step-active .circle {
    background-color: #4285f4;
}

.step.step-warning {
    color: #dc3445;
}

.step.step-warning .circle {
    background-color: #dc3445;
    box-shadow: 0 0 12px 3px #e7a94e;
}


/* Circle */
.circle {
    background: gray;
    position: relative;
    width: 1.5em;
    height: 1.5em;
    line-height: 1.5em;
    border-radius: 100%;
    color: #fff;
    text-align: center;
    box-shadow: 0 0 0 3px #fff;
}

/* Vertical Line */
.circle:after {
    content: ' ';
    position: absolute;
    display: block;
    top: 1px;
    right: 50%;
    bottom: 1px;
    left: 50%;
    height: 100%;
    width: 1px;
    transform: scale(1, 2);
    transform-origin: 50% -100%;
    background-color: rgba(0, 0, 0, 0.25);
    z-index: -1;
}

.step:last-child .circle:after {
    display: none
}

/* Stepper Titles */
.title {
    line-height: 1.5em;
    font-weight: 500;
}

.caption {
    font-size: 0.8em;
}
</style>
<div class="mt-3">

<?php if ($model->status == ''): ?>
    <div class="step step-warning">
        <div>
            <div class="circle"><i class="fa-solid fa-clock"></i></div>
        </div>
        <div>
            <div class="title">รอส่งคำขอ</div>
            <div class="caption"></div>
        </div>
    </div>
<?php endif; ?>

    <?php foreach ($model->ListStatus() as $status): ?>
    <?php if ($status->code < $model->status): ?>
  
        
    <div class="step step-active">
        <div>
            <div class="circle"><i class="fa-solid fa-check"></i></div>
        </div>
        <div>
            <div class="title"><?= $status->title ?></div>
            <div class="caption"><?= $model->pr_number ?></div>
        </div>
    </div>

    <?php elseif ($model->status == $status->code): ?>

    <div class="step step-warning">
        <div>
            <div class="circle"><i class="fa-solid fa-clock"></i></div>
        </div>
        <div>
            <div class="title"><?= $status->title ?></div>
            <div class="caption"><?= $model->pr_number ?></div>
        </div>
    </div>

    <?php else: ?>
    <div class="step">
        <div>
            <div class="circle"><?= $status->code ?></div>
        </div>
        <div>
            <div class="title"><?= $status->title ?></div>
            <div class="caption"><?= $model->pr_number ?></div>
        </div>
    </div>
    <?php endif ?>
    <?php endforeach; ?>
</div>