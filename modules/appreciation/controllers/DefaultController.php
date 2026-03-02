<?php

namespace app\modules\appreciation\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use app\components\UserHelper;
use app\modules\appreciation\models\Appreciation;
use app\modules\appreciation\models\AppreciationChallenge;
use app\modules\appreciation\models\AppreciationChallengeProgress;
use app\modules\appreciation\models\AppreciationLike;
use app\modules\appreciation\models\AppreciationSearch;
use app\modules\hr\models\Employees;
use app\modules\notify\models\Notify;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/me']);
        }

        $leaderboard = [];
        $activeChallenges = [];
        $myChallengeProgress = [];

        try {
            $searchModel = new AppreciationSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            // แสดงเฉพาะคำขอบคุณที่เกี่ยวข้องกับเรา (ส่งโดยเรา หรือส่งถึงเรา)
            $dataProvider->query->andWhere(['or',
                ['from_emp_id' => $me->id],
                ['to_emp_id' => $me->id],
            ]);
            $receivedCount = (int) Appreciation::find()->andWhere(['to_emp_id' => $me->id])->count();
            $totalPoints = (int) Appreciation::find()->andWhere(['to_emp_id' => $me->id])->sum('points_given');

            $leaderboardRows = (new Query())
                ->select(['to_emp_id as emp_id', 'SUM(points_given) as total_points'])
                ->from(Appreciation::tableName())
                ->groupBy('to_emp_id')
                ->orderBy(['total_points' => SORT_DESC])
                ->limit(10)
                ->all();
            $empIds = array_column($leaderboardRows, 'emp_id');
            $employees = $empIds ? Employees::find()->where(['id' => $empIds])->indexBy('id')->all() : [];
            $leaderboard = [];
            foreach ($leaderboardRows as $row) {
                $leaderboard[] = [
                    'emp_id' => $row['emp_id'],
                    'total_points' => (int) $row['total_points'],
                    'employee' => $employees[$row['emp_id']] ?? null,
                ];
            }

            $activeChallenges = AppreciationChallenge::find()
                ->andWhere(['status' => AppreciationChallenge::STATUS_ACTIVE])
                ->andWhere(['<=', 'start_at', date('Y-m-d')])
                ->andWhere(['>=', 'end_at', date('Y-m-d')])
                ->orderBy(['end_at' => SORT_ASC])
                ->limit(3)
                ->all();

            foreach ($activeChallenges as $ch) {
                $prog = AppreciationChallengeProgress::findOne(['challenge_id' => $ch->id, 'emp_id' => $me->id]);
                $myChallengeProgress[$ch->id] = $prog;
            }
        } catch (\Throwable $e) {
            $dataProvider = new ArrayDataProvider(['allModels' => [], 'pagination' => ['pageSize' => 20]]);
            $receivedCount = 0;
            $totalPoints = 0;
            Yii::$app->session->setFlash('info', 'ยังไม่ได้ติดตั้งตารางโมดูลคำขอบคุณ กรุณารัน: php yii migrate --migrationPath=@app/modules/appreciation/migrations');
        }

        return $this->render('index', [
            'me' => $me,
            'searchModel' => $searchModel ?? new AppreciationSearch(),
            'dataProvider' => $dataProvider,
            'receivedCount' => $receivedCount,
            'totalPoints' => $totalPoints,
            'leaderboard' => $leaderboard,
            'activeChallenges' => $activeChallenges ?? [],
            'myChallengeProgress' => $myChallengeProgress ?? [],
        ]);
    }

    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'ไม่พบข้อมูลพนักงาน', 'redirect_url' => Url::to(['/me'])];
            }
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/me']);
        }

        $model = new Appreciation();
        $model->from_emp_id = $me->id;
        $model->points_given = Yii::$app->getModule('appreciation')->pointsPerThank;

        if ($model->load(Yii::$app->request->post())) {
            try {
                if ($model->save()) {
                    Notify::createForAppreciation($model);
                    $this->updateChallengeProgress($model, 'send');
                    Yii::$app->session->setFlash('success', 'ส่งคำขอบคุณแล้ว ขอบคุณที่ส่งพลังบวกให้เพื่อนร่วมงานครับ');
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            'success' => true,
                            'redirect_url' => Url::to(['index', 'celebrate' => 1]),
                        ];
                    }
                    return $this->redirect(['index', 'celebrate' => 1]);
                }
            } catch (\Throwable $e) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'message' => 'ยังไม่ได้ติดตั้งตารางโมดูลคำขอบคุณ กรุณารัน migration',
                        'redirect_url' => Url::to(['/me']),
                    ];
                }
                Yii::$app->session->setFlash('error', 'ยังไม่ได้ติดตั้งตารางโมดูลคำขอบคุณ กรุณารัน migration: php yii migrate --migrationPath=@app/modules/appreciation/migrations');
                return $this->redirect(['/me']);
            }
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'content' => $this->renderAjax('create', ['model' => $model, 'me' => $me, 'isModal' => true]),
                ];
            }
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="bi bi-heart me-1"></i> ส่งคำขอบคุณให้เพื่อน',
                'content' => $this->renderAjax('create', ['model' => $model, 'me' => $me, 'isModal' => true]),
                'footer' => '',
            ];
        }

        return $this->render('create', [
            'model' => $model,
            'me' => $me,
            'isModal' => false,
        ]);
    }

    public function actionLike()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return ['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ'];
        }

        try {
            $id = (int) Yii::$app->request->post('id');
            $appreciation = Appreciation::findOne($id);
            if (!$appreciation) {
                return ['success' => false, 'message' => 'ไม่พบรายการ'];
            }

            $like = AppreciationLike::findOne(['appreciation_id' => $id, 'emp_id' => $me->id]);
            if ($like) {
                $like->delete();
                $liked = false;
            } else {
                $like = new AppreciationLike(['appreciation_id' => $id, 'emp_id' => $me->id]);
                $like->save(false);
                $liked = true;
            }

            $count = $appreciation->getLikeCount();
            return ['success' => true, 'liked' => $liked, 'count' => $count];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาลองใหม่'];
        }
    }

    protected function updateChallengeProgress(Appreciation $model, $type)
    {
        $challenges = \app\modules\appreciation\models\AppreciationChallenge::find()
            ->andWhere(['status' => \app\modules\appreciation\models\AppreciationChallenge::STATUS_ACTIVE])
            ->andWhere(['<=', 'start_at', date('Y-m-d')])
            ->andWhere(['>=', 'end_at', date('Y-m-d')])
            ->all();

        foreach ($challenges as $ch) {
            if ($ch->goal_type === \app\modules\appreciation\models\AppreciationChallenge::GOAL_SEND_COUNT) {
                $empId = $model->from_emp_id;
            } else {
                $empId = $model->to_emp_id;
            }
            $prog = \app\modules\appreciation\models\AppreciationChallengeProgress::findOne([
                'challenge_id' => $ch->id,
                'emp_id' => $empId,
            ]);
            if (!$prog) {
                $prog = new \app\modules\appreciation\models\AppreciationChallengeProgress([
                    'challenge_id' => $ch->id,
                    'emp_id' => $empId,
                ]);
            }
            $prog->current_value = (int) $prog->current_value + 1;
            $prog->updated_at = date('Y-m-d H:i:s');
            $justCompleted = false;
            if ($prog->current_value >= $ch->goal_value && empty($prog->completed_at)) {
                $prog->completed_at = date('Y-m-d H:i:s');
                $justCompleted = true;
            }
            $prog->save(false);
            if ($justCompleted) {
                Notify::createForChallengeWinner($prog);
            }
        }
    }
}
