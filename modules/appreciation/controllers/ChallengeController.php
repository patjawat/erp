<?php

namespace app\modules\appreciation\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\components\UserHelper;
use app\modules\appreciation\models\AppreciationChallenge;
use app\modules\appreciation\models\AppreciationChallengeProgress;
use app\modules\appreciation\models\Appreciation;

class ChallengeController extends Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/me']);
        }

        try {
            $active = AppreciationChallenge::find()
                ->andWhere(['status' => AppreciationChallenge::STATUS_ACTIVE])
                ->andWhere(['<=', 'start_at', date('Y-m-d')])
                ->andWhere(['>=', 'end_at', date('Y-m-d')])
                ->orderBy(['end_at' => SORT_ASC])
                ->all();

            $upcoming = AppreciationChallenge::find()
                ->andWhere(['status' => AppreciationChallenge::STATUS_ACTIVE])
                ->andWhere(['>', 'start_at', date('Y-m-d')])
                ->orderBy(['start_at' => SORT_ASC])
                ->limit(5)
                ->all();

            $ended = AppreciationChallenge::find()
                ->andWhere(['status' => AppreciationChallenge::STATUS_ENDED])
                ->orderBy(['end_at' => SORT_DESC])
                ->limit(5)
                ->all();
        } catch (\Throwable $e) {
            $active = $upcoming = $ended = [];
            Yii::$app->session->setFlash('info', 'ยังไม่ได้ติดตั้งตารางโมดูลคำขอบคุณ กรุณารัน migration ก่อน');
        }

        return $this->render('index', [
            'me' => $me,
            'active' => $active ?? [],
            'upcoming' => $upcoming ?? [],
            'ended' => $ended ?? [],
        ]);
    }

    public function actionView($id)
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/me']);
        }

        try {
            $challenge = $this->findChallenge($id);
            $myProgress = AppreciationChallengeProgress::findOne([
                'challenge_id' => $challenge->id,
                'emp_id' => $me->id,
            ]);

            $leaderboard = AppreciationChallengeProgress::find()
                ->andWhere(['challenge_id' => $challenge->id])
                ->andWhere(['not', ['completed_at' => null]])
                ->orderBy(['completed_at' => SORT_ASC])
                ->with('emp')
                ->limit(20)
                ->all();

            $inProgress = AppreciationChallengeProgress::find()
                ->andWhere(['challenge_id' => $challenge->id])
                ->with('emp')
                ->orderBy(['current_value' => SORT_DESC])
                ->limit(20)
                ->all();
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', 'ไม่พบกิจกรรมหรือยังไม่ได้ติดตั้งตารางโมดูลคำขอบคุณ');
            return $this->redirect(['index']);
        }

        return $this->render('view', [
            'challenge' => $challenge,
            'me' => $me,
            'myProgress' => $myProgress ?? null,
            'leaderboard' => $leaderboard ?? [],
            'inProgress' => $inProgress ?? [],
        ]);
    }

    protected function findChallenge($id)
    {
        $model = AppreciationChallenge::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบกิจกรรมที่ต้องการ');
    }
}
