<?php

namespace app\modules\plan\services;

use Yii;
use app\modules\plan\models\PlanOrder;
use app\modules\plan\models\PlanOrderRevision;

class PlanRevisionService
{
    public const INITIAL = 'initial_approved';
    public const BEFORE_ADJUST = 'before_adjust';
    public const ADJUSTED = 'adjusted_approved';

    public static function nextCycle(PlanOrder $plan): int
    {
        return (int) PlanOrderRevision::find()->where(['plan_order_id' => $plan->id])->max('cycle_no') + 1;
    }

    public static function capture(PlanOrder $plan, int $cycleNo, string $versionType): PlanOrderRevision
    {
        $revision = PlanOrderRevision::findOne([
            'plan_order_id' => $plan->id,
            'cycle_no' => $cycleNo,
            'version_type' => $versionType,
        ]) ?: new PlanOrderRevision();

        $revision->plan_order_id = $plan->id;
        $revision->cycle_no = $cycleNo;
        $revision->version_type = $versionType;
        $revision->status = (string) $plan->status;
        $revision->order_price = (float) $plan->order_price;
        foreach (range(1, 12) as $month) {
            $revision->{'month_' . $month} = (float) $plan->{'month_' . $month};
        }
        $revision->plan_json = $plan->attributes;
        $revision->items_json = $plan->getPlanItems()->orderBy(['id' => SORT_ASC])->asArray()->all();
        $revision->created_at = date('Y-m-d H:i:s');
        $revision->created_by = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        if (!$revision->save()) {
            throw new \RuntimeException('ไม่สามารถบันทึกประวัติแผนได้');
        }
        return $revision;
    }
}
