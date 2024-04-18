<?php
use yii\helpers\Html;
use app\modules\hr\models\Organization;
use app\models\Categorise;
$this->title = "ผังโครงสร้างองค์กร";

$this->params['breadcrumbs'][] = $this->title;
?>
<style>
#tree {
    display: inline-block;
    padding: 10px;
}

#tree * {
    box-sizing: border-box;
}

#tree .branch {
    padding: 5px 0 5px 95px;
}

#tree .branch:not(:first-child) {
    margin-left: 170px;
}

#tree .branch:not(:first-child):after {
    content: "";
    width: 20px;
    border-top: 1px solid #ccc;
    position: absolute;
    left: 226px;
    top: 50%;
    margin-top: 1px;
}

.entry {
    position: relative;
    min-height: 82px;
    display: block;
}

.entry:before {
    content: "";
    height: 100%;
    border-left: 1px solid #ccc;
    position: absolute;
    left: -20px;
}

.entry:first-child:after {
    height: 10px;
    border-radius: 10px 0 0 0;
}

.entry:first-child:before {
    width: 10px;
    height: 50%;
    top: 50%;
    margin-top: 1px;
    border-radius: 10px 0 0 0;
}

.entry:after {
    content: "";
    width: 20px;
    transition: border 0.5s;
    border-top: 1px solid #ccc;
    position: absolute;
    left: -20px;
    top: 50%;
    margin-top: 1px;
}

.entry:last-child:before {
    width: 10px;
    height: 50%;
    border-radius: 0 0 0 10px;
}

.entry:last-child:after {
    height: 10px;
    border-top: none;
    transition: border 0.5s;
    border-bottom: 1px solid #ccc;
    border-radius: 0 0 0 10px;
    margin-top: -9px;
}

.entry:only-child:after {
    width: 10px;
    height: 0px;
    margin-top: 1px;
    border-radius: 0px;
}

.entry:only-child:before {
    display: none;
}

.entry span {
    border: 1px solid #ccc;
    display: block;
    min-width: 150px;
    padding: 5px 10px;
    line-height: 20px;
    text-align: center;
    position: absolute;
    left: 0;
    top: 50%;
    margin-top: -15px;
    color: #666;
    font-family: arial, verdana, tahoma;
    font-size: 14px;
    display: inline-block;
    border-radius: 5px;
    transition: all 0.5s;
}

#tree .entry span:hover,
#tree .entry span:hover+.branch .entry span {
    background: #e6e6e6;
    color: #000;
    border-color: #a6a6a6;
}

#tree .entry span:hover+.branch .entry::after,
#tree .entry span:hover+.branch .entry::before,
#tree .entry span:hover+.branch::before,
#tree .entry span:hover+.branch .branch::before {
    border-color: #a6a6a6;
}
</style>


<div class="card">
    <div class="card-body d-flex justify-content-between">
        <h4 class="card-title"><?=$this->title?></h4>

        <!-- cta -->
        <div class="row">
            <div class="col-12">
                <div class="float-sm-end">
                    <?=Html::a('<i class="bi bi-diagram-3"></i>  ผังองค์กร',['/hr/organization/diagram2'],['class' => 'btn btn-primary'])?>
                    <?=Html::a('<i class="fa-solid fa-gear"></i> ตั้งค่าผังองค์กร',['/hr/organization/setting'],['class' => 'btn btn-primary'])?>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="tree">
    <div class="branch">
        <div class="entry">
            <?php foreach(Categorise::find()->where(['name' => 'organization'])->all() as $lvl1):?>
            <span>
                <?=$this->render('./card')?>
            </span>
            <?php // foreach(Categorise::find()->where(['name' => 'organization','category_id' => $lvl1->code])->all() as $lvl2):?>
            
            <?php // endforeach;?>

            <?php endforeach;?>
            <!-- End lvl loop -->
            <div class="branch">
                <div class="entry"><span><?=$this->render('./card')?></span>
                    <div class="branch">
                        <div class="entry"><span>Grandfather</span>
                            <!-- <div class="branch">
                                <div class="entry"><span>1Great Grandfather</span></div>
                                <div class="entry"><span>2Great Grandmother</span></div>
                            </div> -->
                        </div>
                        <div class="entry"><span>Grandmother</span>
                            <!-- <div class="branch">
                                <div class="entry"><span>3Great Grandfather</span></div>
                                <div class="entry"><span>4Great Grandmother</span></div>
                            </div> -->
                        </div>
                    </div>
                </div>
                <div class="entry"><span><?=$this->render('./card')?></span>
                    <div class="branch">
                        <div class="entry"><span>Grandfather</span>
                            <!-- <div class="branch">
                                <div class="entry"><span>5Great Grandfather</span></div>
                                <div class="entry"><span>6Great Grandmother</span></div>
                            </div> -->
                        </div>
                        <div class="entry"><span>Grandmother</span>
                            <!-- <div class="branch">
                                <div class="entry"><span>7Great Grandfather</span></div>
                                <div class="entry"><span>8Great Grandmother</span></div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Entry -->
    </div>
    <!-- End branch -->
</div>
<!-- End Tree -->