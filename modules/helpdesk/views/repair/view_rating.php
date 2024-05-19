<?php
use app\modules\helpdesk\models\Helpdesk;
use app\components\AppHelper;
$sql = "select x2.*,ROUND(((rating /total) * 100),0) as p FROM (SELECT x1.*,(SELECT count(id) FROM helpdesk where repair_group = :repair_group) as total FROM(SELECT c.title,c.code,count(h.rating) as rating FROM categorise c
LEFT JOIN helpdesk h ON h.rating = c.code AND h.name = 'repair' AND h.repair_group = :repair_group
WHERE c.name = 'rating'
GROUP BY c.code ORDER BY c.code DESC) as x1) as x2;";
$querys  = Yii::$app->db->createCommand($sql)
->bindValue('repair_group',$repair_group)
->queryAll();
$querysAvg  = Yii::$app->db->createCommand("SELECT CAST(avg(rating) as DECIMAL(10,0))  FROM helpdesk WHERE name = 'repair' AND repair_group = :repair_group")->bindValue('repair_group',$repair_group)->queryScalar();
$model = new Helpdesk();
?>

<style>
.ratings {
    margin-right: 10px;
}

.ratings i {

    color: #cecece;
    font-size: 32px;
}

.rating-color {
    color: #fbc634 !important;
}

.review-count {
    font-weight: 400;
    margin-bottom: 2px;
    font-size: 24px !important;
}

.small-ratings i {
    color: #cecece;
}

.review-stat {
    font-weight: 300;
    font-size: 18px;
    margin-bottom: 2px;
}
</style>




<div class="">
    <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
        <?=kartik\widgets\StarRating::widget([
                                                    'name' => 'rating',
                                                    'value' => $querysAvg,
                                                    'disabled' => true,
                                                    'pluginOptions' => [
                                                        'step' => 1,
                                                        'size' => 'sm',
                                                        'starCaptions' => $model->listRating(),
                                                        'starCaptionClasses' => [
                                                            1 => 'text-danger',
                                                            2 => 'text-warning',
                                                            3 => 'text-info',
                                                            4 => 'text-success',
                                                            5 => 'text-success',
                                                        ],
                                                    ],
                                                ]);
                                                ?>
            <h5 class="review-count">5 การให้คะแนน</h5>
        </div>
        <?php foreach($querys as $model):?>

        <div class="mt-1 d-flex justify-content-between align-items-center">
            <h5 class="review-stat"><?=$model['title']?></h5>
            <?=AppHelper::viewProgressBar($model['p'])?>
        </div>
        <?php endforeach;?>

    </div>

</div>