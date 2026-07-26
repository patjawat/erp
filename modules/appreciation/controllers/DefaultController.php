<?php

namespace app\modules\appreciation\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use app\components\UserHelper;
use app\modules\appreciation\models\Appreciation;
use app\modules\appreciation\models\AppreciationChallenge;
use app\modules\appreciation\models\AppreciationChallengeProgress;
use app\modules\appreciation\models\AppreciationLike;
use app\modules\appreciation\models\AppreciationSearch;
use app\modules\appreciation\models\AppreciationProgramYear;
use app\modules\appreciation\models\AppreciationReward;
use app\modules\appreciation\models\AppreciationValue;
use app\modules\appreciation\services\AppreciationPointService;
use app\modules\appreciation\services\AppreciationTelegramService;
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
        $programYear = null;
        $featuredRewards = [];
        $pointSummary = ['earned' => 0, 'used' => 0, 'balance' => 0, 'level' => null, 'nextLevel' => null];

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
            $programYear = AppreciationProgramYear::active();
            $pointSummary = AppreciationPointService::summary($me->id, $programYear);
            if ($programYear) {
                $featuredRewards = AppreciationReward::find()
                    ->andWhere([
                        'program_year_id' => $programYear->id,
                        'is_active' => 1,
                    ])
                    ->andWhere(['>', 'stock_qty', 0])
                    ->orderBy(['points_cost' => SORT_ASC])
                    ->limit(2)
                    ->all();
            }

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
            Yii::$app->session->setFlash('info', 'ยังไม่ได้ติดตั้งตารางโมดูลคำขอบคุณ กรุณารัน: php yii migrate');
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
            'programYear' => $programYear,
            'pointSummary' => $pointSummary,
            'featuredRewards' => $featuredRewards,
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
        $model->frame_style = Appreciation::FRAME_CLASSIC;
        $programYear = AppreciationProgramYear::active();
        $model->points_given = $programYear ? $programYear->points_per_thank : Yii::$app->getModule('appreciation')->pointsPerThank;
        $requestToken = (string) Yii::$app->request->post('appreciation_request_token', '');
        if ($requestToken === '') {
            $requestToken = Yii::$app->security->generateRandomString(32);
        }

        if ($model->load(Yii::$app->request->post())) {
            $processedRequests = (array) Yii::$app->session->get('appreciation_processed_requests', []);
            if (isset($processedRequests[$requestToken])) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => true,
                        'message' => 'เพื่อนของคุณได้รับข้อความขอบคุณแล้ว',
                        'feed_url' => Url::to(['index']),
                    ];
                }
                return $this->redirect(['/me']);
            }

            $savedImagePath = null;
            try {
                if ($programYear && $model->badge_type) {
                    $value = AppreciationValue::findOne(['code' => $model->badge_type, 'is_active' => 1]);
                    $model->points_given = $value && $value->points !== null ? (int)$value->points : (int)$programYear->points_per_thank;
                }
                $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
                if ($model->validate() && $model->imageFile) {
                    $uploadDir = Yii::getAlias('@webroot/uploads/appreciation');
                    FileHelper::createDirectory($uploadDir, 0755, true);
                    $fileName = Yii::$app->security->generateRandomString(24) . '.' . strtolower($model->imageFile->extension);
                    $savedImagePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
                    if ($model->imageFile->saveAs($savedImagePath)) {
                        $model->image_path = '/uploads/appreciation/' . $fileName;
                    } else {
                        $model->addError('imageFile', 'ไม่สามารถบันทึกภาพได้ กรุณาลองใหม่');
                    }
                }
                if (!$model->hasErrors() && $model->save(false)) {
                    $processedRequests[$requestToken] = (int) $model->id;
                    if (count($processedRequests) > 20) {
                        $processedRequests = array_slice($processedRequests, -20, null, true);
                    }
                    Yii::$app->session->set('appreciation_processed_requests', $processedRequests);
                    Notify::createForAppreciation($model);
                    AppreciationTelegramService::sendToRecipient($model);
                    $this->updateChallengeProgress($model, 'send');
                    Yii::$app->session->setFlash('success', 'เพื่อนของคุณได้รับข้อความขอบคุณแล้ว');
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return [
                            'success' => true,
                            'message' => 'เพื่อนของคุณได้รับข้อความขอบคุณแล้ว',
                            'feed_url' => Url::to(['index']),
                        ];
                    }
                    return $this->redirect(['/me']);
                }
            } catch (\Throwable $e) {
                if ($savedImagePath && is_file($savedImagePath)) {
                    @unlink($savedImagePath);
                }
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'message' => 'ยังไม่ได้ติดตั้งตารางโมดูลคำขอบคุณ กรุณารัน migration',
                        'redirect_url' => Url::to(['/me']),
                    ];
                }
                Yii::$app->session->setFlash('error', 'ยังไม่ได้ติดตั้งตารางโมดูลคำขอบคุณ กรุณารัน migration: php yii migrate');
                return $this->redirect(['/me']);
            }
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'content' => $this->renderAjax('create', ['model' => $model, 'me' => $me, 'isModal' => true, 'requestToken' => $requestToken]),
                ];
            }
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="bi bi-heart me-1"></i> ส่งคำขอบคุณให้เพื่อน',
                'content' => $this->renderAjax('create', ['model' => $model, 'me' => $me, 'isModal' => true, 'requestToken' => $requestToken]),
                'footer' => '',
            ];
        }

        return $this->render('create', [
            'model' => $model,
            'me' => $me,
            'isModal' => false,
            'requestToken' => $requestToken,
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
            if ((int)$appreciation->from_emp_id !== (int)$me->id && (int)$appreciation->to_emp_id !== (int)$me->id) {
                return ['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าถึงคำขอบคุณนี้'];
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
