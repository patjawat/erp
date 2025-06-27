<?php
namespace app\modules\hr\controllers;

use yii;
use DateTime;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\Processor;
use app\components\SiteHelper;
use PhpOffice\PhpWord\Settings;
use yii\helpers\BaseFileHelper;
use app\modules\am\models\Asset;
use app\modules\hr\models\Leave;
use yii\web\NotFoundHttpException;

class Documentv2Controller extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionDesign()
    {
        return $this->render('design');
    }
}
