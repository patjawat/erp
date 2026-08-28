<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AccountingPeriod;
use app\modules\am\services\AccountingPeriodService;
use app\modules\am\services\DepreciationPostingService;

/**
 * งวดบัญชี: สร้าง/เปิดงวดปีงบ, บันทึกบัญชี (post), ล็อก (screens 3, 6)
 */
class AccountingPeriodController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['depreciationView']],
                    ['allow' => true, 'roles' => ['depreciationRun']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'generate' => ['POST'],
                    'post' => ['POST'],
                    'lock' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex($fiscal_year = null)
    {
        $fyBE = (int) ($fiscal_year ?: (date('n') >= 10 ? date('Y') + 544 : date('Y') + 543));

        $periods = AccountingPeriod::find()
            ->where(['fiscal_year' => $fyBE])
            ->orderBy([
                'period_type' => SORT_ASC,
                'period_no' => SORT_ASC,
            ])
            ->all();

        // ปีงบที่มีอยู่ในระบบ
        $years = AccountingPeriod::find()->select('fiscal_year')->distinct()->orderBy(['fiscal_year' => SORT_DESC])->column();

        return $this->render('index', [
            'fyBE' => $fyBE,
            'periods' => $periods,
            'years' => $years,
            'canRun' => Yii::$app->user->can('depreciationRun'),
        ]);
    }

    public function actionGenerate()
    {
        $fyBE = (int) Yii::$app->request->post('fiscal_year');
        if ($fyBE < 2500 || $fyBE > 2700) {
            Yii::$app->session->setFlash('error', 'ปีงบประมาณ (พ.ศ.) ไม่ถูกต้อง');
            return $this->redirect(['index']);
        }
        try {
            $res = (new AccountingPeriodService())->generateFiscalYear($fyBE);
            Yii::$app->session->setFlash('success', "สร้างงวดปีงบ {$fyBE}: ใหม่ {$res['created']} ข้าม {$res['skipped']} งวด");
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', 'สร้างงวดไม่สำเร็จ: ' . $e->getMessage());
        }
        return $this->redirect(['index', 'fiscal_year' => $fyBE]);
    }

    public function actionPost($id)
    {
        $period = $this->findModel($id);
        $res = (new DepreciationPostingService())->postPeriod($period, Yii::$app->user->id);
        Yii::$app->session->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        return $this->redirect(['index', 'fiscal_year' => $period->fiscal_year]);
    }

    public function actionLock($id)
    {
        $period = $this->findModel($id);
        $res = (new DepreciationPostingService())->lockPeriod($period, Yii::$app->user->id);
        Yii::$app->session->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        return $this->redirect(['index', 'fiscal_year' => $period->fiscal_year]);
    }

    protected function findModel($id)
    {
        if (($model = AccountingPeriod::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบงวดบัญชี');
    }
}
