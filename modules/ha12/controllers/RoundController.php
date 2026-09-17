<?php

namespace app\modules\ha12\controllers;

use app\components\AppHelper;
use app\modules\ha12\models\Ha12Activity;
use app\modules\ha12\models\Ha12Assessment;
use app\modules\ha12\models\Ha12Audit;
use app\modules\ha12\models\Ha12Round;
use app\modules\ha12\services\Ha12RoundService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * รอบสรุป/ประเมิน PCT (เฟส 3)
 *
 * สร้าง/ปิด/เปิดรอบ = ผู้ดูแล/ทีมคุณภาพ ; ดูรอบ = ผู้ล็อกอินทุกคน (เห็นผลที่เผยแพร่)
 */
class RoundController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'close' => ['POST'], 'reopen' => ['POST'], 'delete' => ['POST'],
            ]],
        ]);
    }

    private function fiscalYear(): int
    {
        return (int) Yii::$app->request->get('fy') ?: (int) AppHelper::YearBudget();
    }

    private function findRound(int $id): Ha12Round
    {
        $r = Ha12Round::findOne($id);
        if (!$r) {
            throw new NotFoundHttpException('ไม่พบรอบประเมิน');
        }
        return $r;
    }

    private function assertManager(): void
    {
        if (!Ha12RoundService::isManager()) {
            throw new ForbiddenHttpException('เฉพาะผู้ดูแล/ทีมคุณภาพเท่านั้น');
        }
    }

    public function actionIndex()
    {
        $fiscalYear = $this->fiscalYear();
        $query = Ha12Round::find()->with('scopeUnit')->where(['fiscal_year' => $fiscalYear]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20], 'sort' => false,
        ]);

        // นับความคืบหน้าแบบ batch
        $rounds = $dataProvider->getModels();
        $progress = [];
        foreach ($rounds as $r) {
            $progress[$r->id] = Ha12RoundService::progress($r);
        }

        return $this->render('round/index', [
            'rounds' => $rounds, 'dataProvider' => $dataProvider, 'progress' => $progress,
            'fiscalYear' => $fiscalYear, 'years' => range($fiscalYear + 1, $fiscalYear - 3),
            'isManager' => Ha12RoundService::isManager(),
        ]);
    }

    public function actionCreate()
    {
        $this->assertManager();
        $req = Yii::$app->request;
        $model = new Ha12Round(['fiscal_year' => $this->fiscalYear(), 'period_type' => Ha12Round::TYPE_QUARTER, 'period_no' => 1]);

        if ($req->isPost) {
            $model->load($req->post());
            if ($model->period_type === Ha12Round::TYPE_YEAR) {
                $model->period_no = null;
            }
            [$start, $end] = Ha12Round::computePeriod((int) $model->fiscal_year, (string) $model->period_type, $model->period_no !== null ? (int) $model->period_no : null);
            $model->period_start = $start;
            $model->period_end = $end;
            $model->status = Ha12Round::STATUS_OPEN;

            if ($model->save()) {
                Ha12Audit::log('round', (int) $model->id, 'create', $model->displayTitle());
                if ($req->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'redirect_url' => \yii\helpers\Url::to(['view', 'id' => $model->id])];
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $data = ['model' => $model, 'units' => Ha12RoundService::scopeUnitOptions()];
        if ($req->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => 'สร้างรอบประเมิน', 'content' => $this->renderAjax('round/_form', $data)];
        }
        return $this->render('round/_form', $data);
    }

    /** แดชบอร์ดรอบ — 12 กิจกรรม + สถานะประเมิน */
    public function actionView($id)
    {
        $round = $this->findRound((int) $id);
        $activities = Ha12Activity::activeList();
        $assessments = Ha12Assessment::find()->where(['round_id' => $round->id])->indexBy('activity_id')->all();

        return $this->render('round/view', [
            'round' => $round,
            'activities' => $activities,
            'assessments' => $assessments,
            'progress' => Ha12RoundService::progress($round),
            'canClose' => Ha12RoundService::canClose($round),
            'isManager' => Ha12RoundService::isManager(),
        ]);
    }

    public function actionClose($id)
    {
        $this->assertManager();
        $round = $this->findRound((int) $id);
        if (!Ha12RoundService::canClose($round)) {
            Yii::$app->session->setFlash('error', 'ปิดรอบได้เมื่อเผยแพร่ครบทุกกิจกรรมแล้ว');
            return $this->redirect(['view', 'id' => $round->id]);
        }
        $round->status = Ha12Round::STATUS_CLOSED;
        $round->save(false);
        Ha12Audit::log('round', (int) $round->id, 'close', $round->displayTitle());
        Yii::$app->session->setFlash('success', 'ปิดรอบแล้ว');
        return $this->redirect(['view', 'id' => $round->id]);
    }

    public function actionReopen($id)
    {
        $this->assertManager();
        $round = $this->findRound((int) $id);
        $reason = trim((string) Yii::$app->request->post('reopen_reason'));
        if ($reason === '') {
            Yii::$app->session->setFlash('error', 'ต้องระบุเหตุผลการเปิดรอบใหม่');
            return $this->redirect(['view', 'id' => $round->id]);
        }
        $round->status = Ha12Round::STATUS_OPEN;
        $round->reopen_reason = $reason;
        $round->save(false);
        Ha12Audit::log('round', (int) $round->id, 'reopen', $reason);
        Yii::$app->session->setFlash('success', 'เปิดรอบใหม่แล้ว');
        return $this->redirect(['view', 'id' => $round->id]);
    }

    public function actionDelete($id)
    {
        $this->assertManager();
        $round = $this->findRound((int) $id);
        $round->delete();
        Yii::$app->session->setFlash('success', 'ลบรอบแล้ว');
        return $this->redirect(['index', 'fy' => $round->fiscal_year]);
    }
}
