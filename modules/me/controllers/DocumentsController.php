<?php

namespace app\modules\me\controllers;
use Yii;
use yii\db\Expression;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentSearch;

class DocumentsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $emp = UserHelper::GetEmployee();
        $searchModel = new DocumentSearch([
            'document_group' => 'receive', 
        ]);
        $dataProviderDepartment = $searchModel->search($this->request->queryParams);
        $dataProviderDepartment->query->andFilterWhere([
            '>', 
            new Expression("FIND_IN_SET(:department, JSON_UNQUOTE(data_json->'$.department_tag'))"), 
            0
            ])->addParams([':department' => $emp->department]);
            $dataProviderDepartment->query->andFilterWhere([
                'or',
                ['like', 'topic', $searchModel->q],
                ['like', 'doc_regis_number', $searchModel->q],
            ]);
            $dataProviderDepartment->setSort(['defaultOrder' => [
                'doc_regis_number'=>SORT_DESC,
                'thai_year'=>SORT_DESC,
                ]]);
                $dataProviderEmployee = $searchModel->search($this->request->queryParams);
                $dataProviderEmployee->query->andWhere(new Expression("JSON_CONTAINS(data_json->'$.employee_tag','\"$emp->id\"')"));
                $dataProviderEmployee->query->andFilterWhere([
                    'or',
                    ['like', 'topic', $searchModel->q],
                    ['like', 'doc_regis_number', $searchModel->q],
                ]);
                $dataProviderEmployee->setSort(['defaultOrder' => [
                    'doc_regis_number'=>SORT_DESC,
                    'thai_year'=>SORT_DESC,
                    ]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProviderDepartment' => $dataProviderDepartment,
            'dataProviderEmployee' => $dataProviderEmployee
        ]);
    }

}
