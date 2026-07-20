<?php
namespace app\modules\appreciation\services;

use app\modules\appreciation\models\Appreciation;
use app\modules\appreciation\models\AppreciationLevel;
use app\modules\appreciation\models\AppreciationProgramYear;
use app\modules\appreciation\models\AppreciationRedemption;
use app\modules\appreciation\models\AppreciationParticipation;

class AppreciationPointService
{
    public static function summary($empId, AppreciationProgramYear $year = null)
    {
        $year = $year ?: AppreciationProgramYear::active();
        if (!$year) return ['earned' => 0, 'used' => 0, 'balance' => 0, 'level' => null, 'nextLevel' => null];
        $thanksPoints = (int) Appreciation::find()->where(['to_emp_id' => $empId])->andWhere(['between', 'created_at', $year->start_at . ' 00:00:00', $year->end_at . ' 23:59:59'])->sum('points_given');
        $activityPoints = (int) AppreciationParticipation::find()->where(['emp_id'=>$empId,'program_year_id'=>$year->id,'status'=>AppreciationParticipation::STATUS_COMPLETED])->sum('points_awarded');
        $earned = $thanksPoints + $activityPoints;
        $used = (int) AppreciationRedemption::find()->where(['emp_id' => $empId, 'program_year_id' => $year->id])->andWhere(['status' => [AppreciationRedemption::STATUS_PENDING, AppreciationRedemption::STATUS_APPROVED, AppreciationRedemption::STATUS_DELIVERED]])->sum('points_used');
        $levels = AppreciationLevel::find()->where(['program_year_id' => $year->id])->orderBy(['min_points' => SORT_ASC])->all();
        $level = $next = null;
        foreach ($levels as $item) { if ($earned >= $item->min_points) $level = $item; elseif ($next === null) $next = $item; }
        return ['earned' => $earned, 'thanksPoints'=>$thanksPoints, 'activityPoints'=>$activityPoints, 'used' => $used, 'balance' => max(0, $earned - $used), 'level' => $level, 'nextLevel' => $next];
    }
}
