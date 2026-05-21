<?php

namespace app\modules\purchaseV2\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use app\components\UserHelper;
use app\modules\purchaseV2\services\PurchaseMigrationService;

class MigrationController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'migrate' => ['POST'],
                    'migrate-all' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->ensureManageAccess();
        $service = new PurchaseMigrationService();
        $preview = $service->previewLegacyOrders(30, $this->request->queryParams);

        return $this->render('index', [
            'preview' => $preview,
        ]);
    }

    public function actionMigrate($id)
    {
        $this->ensureManageAccess();
        try {
            $service = new PurchaseMigrationService();
            $actor = UserHelper::GetEmployee();
            $request = $service->migrateLegacyOrder((int) $id, $actor);

            Yii::$app->session->setFlash('success', 'ย้ายข้อมูลสำเร็จ');
            return $this->redirect(['/purchase-v2/request/view', 'id' => $request->id]);
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    public function actionMigrateAll()
    {
        $this->ensureManageAccess();
        try {
            $service = new PurchaseMigrationService();
            $count = 0;
            foreach ($service->buildLegacyOrderQuery([], true)->orderBy(['id' => SORT_ASC])->each(100) as $legacyOrder) {
                $service->migrateLegacyOrder((int) $legacyOrder->id, UserHelper::GetEmployee());
                $count++;
            }

            Yii::$app->session->setFlash('success', 'ย้ายข้อมูลสำเร็จ ' . number_format($count) . ' รายการ');
            return $this->redirect(['index']);
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    protected function ensureManageAccess(): void
    {
        if (Yii::$app->user->can('admin') || Yii::$app->user->can('purchase')) {
            return;
        }

        throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้านี้');
    }
}
