<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use app\modules\hr\models\Organization;
use app\modules\hr\models\EmployeesSearch;
use app\modules\hr\models\Employees;

/**
 * กำหนดเวร 8 — ย้ายจาก hr
 */
class WorkShiftController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้าตั้งค่า');
        }
        return true;
    }

    public function actionIndex()
    {
        $searchModel = new EmployeesSearch(['branch' => 'MAIN']);
        $dataProvider = $searchModel->search($this->request->queryParams);
        if (isset($searchModel->user_register) && $searchModel->user_register == 0) {
            $dataProvider->query->andWhere(['user_id' => 0]);
        }
        if (isset($searchModel->user_register) && $searchModel->user_register == 1) {
            $dataProvider->query->andWhere(['!=', 'user_id', 0]);
        }
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
            ['like', 'fname', $searchModel->q],
            ['like', 'lname', $searchModel->q],
        ]);
        $dataProvider->query->andWhere(['NOT', ['id' => 1]]);
        $org1 = Organization::findOne($searchModel->q_department);
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.name = :name;';
            $querys = Yii::$app->db->createCommand($sql)->bindValue(':name', $org1->name)->queryAll();
            $arrDepartment = array_column($querys, 'id');
            $dataProvider->query->andWhere(count($arrDepartment) > 0 ? ['in', 'department', $arrDepartment] : ['department' => $searchModel->q_department]);
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        $dataProvider->query->andWhere(['status' => 1]);
        $dataProvider->query->andWhere(new \yii\db\Expression("CONCAT(fname,' ', lname) LIKE :term", [':term' => '%' . $searchModel->fullname . '%']));
        $dataProvider->query->orderBy(['id' => SORT_DESC]);
        $notStatus = Yii::$app->db->createCommand('SELECT count(id) as total FROM employees WHERE status IS NULL')->queryScalar();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'notStatus' => $notStatus,
        ]);
    }

    public function actionUpdateShift()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $workType = Yii::$app->request->post('work_shift');
        $model = Employees::findOne($id);
        if ($model) {
            $model->work_shift = $workType;
            if ($model->save(false)) {
                return ['success' => true];
            }
        }
        return ['success' => false];
    }
}
