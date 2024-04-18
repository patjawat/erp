<?php

namespace app\components;

use Yii;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\hr\models\EmployeeDetailSearch;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeesSearch;
use app\modules\hr\models\UploadCsv;
use ruskid\csvimporter\CSVImporter;
use ruskid\csvimporter\CSVReader;
use ruskid\csvimporter\MultipleImportStrategy;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\validators\DateValidator;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AppController extends Controller
{
    
    public function beforeAction($action) {
     
        // $visit_ = TCDSHelper::getVisit();
        // $hn = $visit_['hn'];
        // if (empty($hn)) {
        //     return $this->redirect(['/site/index']);
        // }
        // if(empty($vn)){
        //     return $this->redirect(['/nursescreen']);
        // }
        // return parent::beforeAction($action);
    }

}