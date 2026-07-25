<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\housing\models\GuestRequest;
use app\modules\housing\models\Occupancy;
use app\modules\housing\services\HousingContextService;
use app\modules\housing\services\RequestNumberService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class GuestController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['mine', 'create'], 'roles' => ['housing.user']],
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['housing.staff', 'housing.admin', 'housing.guest.approver']],
                    ['allow' => true, 'actions' => ['decide'], 'roles' => ['housing.guest.approver', 'housing.admin']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['decide' => ['POST']],
            ],
        ];
    }

    public function actionMine()
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id);
        $occupancy = $context['occupancy'];
        $query = GuestRequest::find()->where(['occupancy_id' => $occupancy?->id ?: 0])->orderBy(['id' => SORT_DESC]);
        return $this->render('mine', [
            'context' => $context,
            'dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 20]]),
        ]);
    }

    public function actionCreate()
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id);
        if ($context['mode'] !== 'resident' || !$context['occupancy']) {
            throw new \DomainException('ต้องเป็นผู้พักปัจจุบันจึงสามารถแจ้งบุคคลภายนอกได้');
        }
        $model = new GuestRequest([
            'request_no' => (new RequestNumberService())->next('HGS'),
            'occupancy_id' => $context['occupancy']->id,
            'requested_by_emp_id' => $context['employee']->id,
        ]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'ส่งคำขออนุญาตเรียบร้อย');
            return $this->redirect(['mine']);
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => GuestRequest::find()->with('occupancy')->orderBy(['status' => SORT_DESC, 'id' => SORT_DESC]),
                'pagination' => ['pageSize' => 30],
            ]),
        ]);
    }

    public function actionDecide(int $id, string $decision)
    {
        $model = GuestRequest::findOne($id);
        if (!$model || $model->status !== 'pending') {
            throw new NotFoundHttpException('ไม่พบคำขอที่รออนุญาต');
        }
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            throw new \DomainException('ผลการพิจารณาไม่ถูกต้อง');
        }
        $model->status = $decision;
        $model->decision_note = Yii::$app->request->post('decision_note');
        $model->decided_at = date('Y-m-d H:i:s');
        $model->decided_by = Yii::$app->user->id;
        $model->save(false, ['status', 'decision_note', 'decided_at', 'decided_by', 'updated_at', 'updated_by']);
        Yii::$app->session->setFlash('success', 'บันทึกผลการอนุญาตเรียบร้อย');
        return $this->redirect(['index']);
    }
}
