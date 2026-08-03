<?php

namespace app\modules\pm\controllers;

use Yii;
use app\modules\pm\models\Projects;
use app\modules\hr\models\Organization;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * Default controller for the `pm` module — หน้าภาพรวม
 */
class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $year = (int) Yii::$app->request->get('thai_year', date('Y') + 543 + (date('n') >= 10 ? 1 : 0));

        $base = Projects::find()->where(['deleted_at' => null]);

        $byStatus = [];
        foreach (array_keys(Projects::statusList()) as $st) {
            $byStatus[$st] = (int) (clone $base)->andWhere(['status' => $st, 'thai_year' => $year])->count();
        }

        $total = (int) (clone $base)->andWhere(['thai_year' => $year])->count();
        $budgetSum = (float) (clone $base)->andWhere(['thai_year' => $year])->sum('budget_total');

        // งบประมาณ + จำนวนโครงการ แยกตามสถานะ
        $budgetByStatus = [];
        foreach (array_keys(Projects::statusList()) as $st) {
            $budgetByStatus[$st] = (float) (clone $base)->andWhere(['status' => $st, 'thai_year' => $year])->sum('budget_total');
        }

        // งบประมาณ + จำนวนโครงการ แยกตามหน่วยงาน
        $deptRows = (clone $base)
            ->select([
                'department_id',
                'cnt' => 'COUNT(*)',
                'budget' => 'COALESCE(SUM(budget_total),0)',
            ])
            ->andWhere(['thai_year' => $year])
            ->groupBy('department_id')
            ->orderBy(['budget' => SORT_DESC])
            ->asArray()
            ->all();

        $orgNames = ArrayHelper::map(
            Organization::find()->select(['id', 'name'])->asArray()->all(),
            'id',
            'name'
        );
        $byDept = [];
        foreach ($deptRows as $r) {
            $byDept[] = [
                'name' => $orgNames[$r['department_id']] ?? 'ไม่ระบุหน่วยงาน',
                'count' => (int) $r['cnt'],
                'budget' => (float) $r['budget'],
            ];
        }

        $recent = (clone $base)->andWhere(['thai_year' => $year])->orderBy(['id' => SORT_DESC])->limit(8)->all();

        $years = Projects::find()
            ->select('thai_year')
            ->where(['deleted_at' => null])
            ->andWhere(['not', ['thai_year' => null]])
            ->distinct()
            ->orderBy(['thai_year' => SORT_DESC])
            ->column();

        return $this->render('index', [
            'year' => $year,
            'years' => $years,
            'byStatus' => $byStatus,
            'budgetByStatus' => $budgetByStatus,
            'byDept' => $byDept,
            'total' => $total,
            'budgetSum' => $budgetSum,
            'recent' => $recent,
        ]);
    }
}
