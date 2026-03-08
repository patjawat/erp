<?php

namespace app\modules\development\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\development\models\Development;
use app\modules\development\models\DevelopmentSearch;

/**
 * ผู้ตรวจสอบอบรม/ประชุม/ดูงาน — แสดงรายการขอไปราชการสำหรับผู้ตรวจสอบ/ผู้อนุมัติ
 */
class ApproverController extends Controller
{
    /**
     * เฉพาะ hr หรือ admin เข้าหน้าผู้ตรวจสอบได้
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['hr', 'admin'],
                    ],
                ],
                'denyCallback' => function () {
                    throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้าผู้ตรวจสอบ');
                },
            ],
        ];
    }

    /**
     * รายการอบรม/ประชุม/ดูงาน สำหรับผู้ตรวจสอบ (กรองตามปี ประเภท สถานะ ผู้ขอ วันที่)
     * @return string
     */
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/development/default/dashboard']);
        }

        $thaiYear = (int) (Yii::$app->request->get('thai_year') ?: AppHelper::YearBudget());
        $searchModel = new DevelopmentSearch(['thai_year' => $thaiYear]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere([Development::tableName() . '.thai_year' => $thaiYear]);
        $dataProvider->query->joinWith(['developmentType', 'emp', 'statusCategorise']);
        $dataProvider->query->orderBy(['development.id' => SORT_DESC]);
        $dataProvider->pagination = ['pageSize' => 20, 'pageParam' => 'development-page'];

        $summaryByStatus = (clone $dataProvider->query)
            ->select([Development::tableName() . '.status', 'COUNT(' . Development::tableName() . '.id) as cnt'])
            ->groupBy(Development::tableName() . '.status')
            ->orderBy(['cnt' => SORT_DESC])
            ->asArray()
            ->all();

        return $this->render('index', [
            'thaiYear' => $thaiYear,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'summaryByStatus' => $summaryByStatus,
        ]);
    }
}
