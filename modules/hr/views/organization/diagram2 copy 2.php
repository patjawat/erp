<?php
use yii\helpers\Html;
use app\modules\hr\models\Organization;
$this->title = "ผังโครงสร้างองค์กร";

$this->params['breadcrumbs'][] = $this->title;
?>
<style>
/* body {
  font-family: Calibri, Segoe, "Segoe UI", "Gill Sans", "Gill Sans MT", sans-serif;
} */

/* It's supposed to look like a tree diagram */
.tree,
.tree ul,
.tree li {
    list-style: none;
    margin: 0;
    padding: 0;
    position: relative;
}

.tree {
    margin: 0 0 1em;
    text-align: center;
}

.tree,
.tree ul {
    display: table;
}

.tree ul {
    width: 100%;
}

.tree li {
    display: table-cell;
    padding: .5em 0;
    vertical-align: top;
}

/* _________ */
.tree li:before {
    outline: solid 1px #666;
    content: "";
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
}

.tree li:first-child:before {
    left: 50%;
}

.tree li:last-child:before {
    right: 50%;
}

.tree code,
.tree span {
    border: solid .1em #666;
    border-radius: .2em;
    display: inline-block;
    margin: 0 .2em .5em;
    padding: .2em .5em;
    position: relative;
}

/* If the tree represents DOM structure */
.tree code {
    font-family: monaco, Consolas, 'Lucida Console', monospace;
}

/* | */
.tree ul:before,
.tree code:before,
.tree span:before {
    outline: solid 1px #666;
    content: "";
    height: .5em;
    left: 50%;
    position: absolute;
}

.tree ul:before {
    top: -.5em;
}

.tree code:before,
.tree span:before {
    top: -.55em;
}

/* The root node doesn't connect upwards */
.tree>li {
    margin-top: 0;
}

.tree>li:before,
.tree>li:after,
.tree>li>code:before,
.tree>li>span:before {
    outline: none;
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

<?php

use muhsamsul\treeimage\TreeImage;
use app\models\Tree;
?>

<?= TreeImage::widget(
[
    'query' => Organization::find()->where(['tb_name' => 'organization'])->addOrderBy('root, lft'), 
]
); ?>

<?php
use kongoon\orgchart\OrgChart;

echo OrgChart::widget([
    'data' => [
            [['v' => 'Mike', 'f' => '<img src="https://placeholdit.imgix.net/~text?txtsize=20&txt=Mike&w=120&h=150" /><br  /> <strong>Mike</strong><br  />The President'],'', 'The President'],
            [['v' => 'Jim', 'f' => '<img src="https://placeholdit.imgix.net/~text?txtsize=20&txt=Jim&w=120&h=150" /><br  /><strong>Jim</strong><br  />The Test'],'Mike', 'VP'],
            [['v' => 'ทดสอบ', 'f' => '<img src="https://placeholdit.imgix.net/~text?txtsize=20&txt=ทดสอบ&w=120&h=150" /><br  /><strong>ทดสอบ</strong><br  />The Test'], 'Mike', ''],
            [['v' => 'Bob', 'f' => '<img src="https://placeholdit.imgix.net/~text?txtsize=20&txt=Bob&w=120&h=150" /><br  /><strong>Bob</strong><br  />The Test'], 'Jim', 'Bob Sponge'],
            [['v' => 'Caral', 'f' => '<img src="https://placeholdit.imgix.net/~text?txtsize=20&txt=Caral&w=120&h=150" /><br  /><strong>Caral</strong><br  />The Test'], 'Mike', 'Caral Title'],

    ]
]);
?>
<hr>
<div class="d-flex flex-column">
    <?php foreach(Organization::find()->where(['tb_name' => 'organization'])->andWhere(['lvl' => 1])->groupBy('lvl')->all() as $model):?>
    <div class="p-2">
        <?=$model->lvl?>
        <?php foreach(Organization::find()->where(['tb_name' => 'organization'])->andWhere(['lvl' => $model->lvl])->all() as $lvl):?>
        <?=$lvl->name?>
        <?php endforeach ?>
    </div>

    <?php endforeach ?>
</div>

<figure class="d-flex justify-content-center">
    <ul class="tree">

        <li>
        <?php foreach(Organization::find()->where(['tb_name' => 'organization'])->andWhere(['lvl' => 1])->groupBy('lvl')->all() as $model):?>
            <span><?=$model->name?> <?=$model->lvl?></span>
            <?php // foreach(Organization::find()->where(['tb_name' => 'organization'])->andWhere(['lvl' => 1])->all() as $lvl):?>
            <ul>
                <li><span><?php // $lvl->name?></span>
                    <!-- <ul>
                                <li><span>Founder</span></li>
                            </ul> -->
                </li>

            </ul>
            <?php //endforeach ?>
            <?php endforeach ?>
        </li>
        <ul>


            <li><span>กลุ่มบริการงานปฐมภูมิ</span>
                <ul>
                    <!-- <li><span>กลุ่มงานเวชกรรม</span>
                            <ul>
                                <li><span>Founder</span></li>
                            </ul>
                        </li> -->
                    <!-- <li><span>กลุ่มงานระบาด</span>
                            <ul>
                                <li><span>Brad Whiteman</span></li>
                                <li><span>Cynthia Tolken</span></li>
                                <li><span>Bobby Founderson</span></li>
                            </ul>
                        </li> -->
                </ul>
            </li>
            <li><span>Our products</span>
                <ul>
                    <!-- <li><span>The Widget 2000™</span>
                            <ul>
                                <li><span>Order form</span></li>
                            </ul>
                        </li>
                        <li><span>The McGuffin V2</span>
                            <ul>
                                <li><span>Order form</span></li>
                            </ul>
                        </li> -->
                </ul>
            </li>

        </ul>

        </li>

    </ul>
</figure>