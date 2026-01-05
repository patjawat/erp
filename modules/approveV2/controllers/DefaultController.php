<?php

namespace app\modules\approveV2\controllers;

use yii\web\Controller;
use app\components\UserHelper;
use app\modules\approve\models\ApproveSearch;

/**
 * Default controller for the `approve-v2` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $name = trim($this->request->get("name", "")); // กำหนดค่าเริ่มต้นเป็น "" ถ้าไม่มีค่า name
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['emp_id' => $me->id, 'status' => 'Pending']);
        // ตรวจสอบว่ามีค่า name จริงก่อนเพิ่มเงื่อนไข
        if (!empty($name)) {

            $dataProvider->query->andFilterWhere(['name' => $name]);
            return $this->render('../' . $name . '/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
