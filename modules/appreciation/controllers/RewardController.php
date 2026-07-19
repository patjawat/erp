<?php
namespace app\modules\appreciation\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\UserHelper;
use app\modules\appreciation\models\AppreciationProgramYear;
use app\modules\appreciation\models\AppreciationReward;
use app\modules\appreciation\models\AppreciationRedemption;
use app\modules\appreciation\services\AppreciationPointService;

class RewardController extends Controller
{
    public function behaviors(){ return ['verbs'=>['class'=>VerbFilter::class,'actions'=>['redeem'=>['post']]]]; }
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) return $this->redirect(['/me']);
        $year = AppreciationProgramYear::active();
        $rewards = $year ? AppreciationReward::find()->where(['program_year_id' => $year->id, 'is_active' => 1])->orderBy(['points_cost' => SORT_ASC])->all() : [];
        $history = $year ? AppreciationRedemption::find()->with('reward')->where(['emp_id' => $me->id, 'program_year_id' => $year->id])->orderBy(['requested_at' => SORT_DESC])->all() : [];
        return $this->render('index', ['me' => $me, 'year' => $year, 'rewards' => $rewards, 'history' => $history, 'summary' => AppreciationPointService::summary($me->id, $year)]);
    }

    public function actionRedeem($id)
    {
        $me = UserHelper::GetEmployee();
        $reward = AppreciationReward::findOne((int)$id);
        if (!$me || !$reward || !$reward->is_active) throw new NotFoundHttpException('ไม่พบของรางวัล');
        $tx = Yii::$app->db->beginTransaction();
        try {
            $yearTable = Yii::$app->db->quoteTableName(AppreciationProgramYear::tableName());
            $rewardTable = Yii::$app->db->quoteTableName(AppreciationReward::tableName());
            Yii::$app->db->createCommand("SELECT id FROM {$yearTable} WHERE id=:id FOR UPDATE", [':id'=>$reward->program_year_id])->queryScalar();
            Yii::$app->db->createCommand("SELECT id FROM {$rewardTable} WHERE id=:id FOR UPDATE", [':id'=>$reward->id])->queryScalar();
            $reward = AppreciationReward::findOne($reward->id);
            $year = AppreciationProgramYear::findOne($reward->program_year_id);
            $summary = AppreciationPointService::summary($me->id, $year);
            if ($reward->stock_qty < 1) throw new \DomainException('ของรางวัลหมดแล้ว');
            if ($summary['balance'] < $reward->points_cost) throw new \DomainException('คะแนนคงเหลือไม่เพียงพอ');
            $model = new AppreciationRedemption(['reward_id'=>$reward->id, 'program_year_id'=>$year->id, 'emp_id'=>$me->id, 'points_used'=>$reward->points_cost, 'status'=>AppreciationRedemption::STATUS_PENDING, 'requested_at'=>date('Y-m-d H:i:s')]);
            if (!$model->save()) throw new \RuntimeException('บันทึกคำขอไม่สำเร็จ');
            $reward->updateCounters(['stock_qty' => -1]);
            $tx->commit();
            Yii::$app->session->setFlash('success', 'ส่งคำขอแลกรางวัลแล้ว ผู้ดูแลจะตรวจสอบและแจ้งจุดรับของ');
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', $e instanceof \DomainException ? $e->getMessage() : 'ไม่สามารถแลกรางวัลได้ กรุณาลองใหม่');
        }
        return $this->redirect(['index']);
    }
}
