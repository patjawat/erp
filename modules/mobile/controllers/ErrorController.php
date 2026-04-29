<?php

namespace app\modules\mobile\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

/**
 * แสดงหน้าข้อผิดพลาด (รวม 404) สำหรับโมดูล mobile — ไม่บังคับล็อกอิน
 */
class ErrorController extends Controller
{
    public $layout = 'main';

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $exception = Yii::$app->errorHandler->exception;
        $is404 = $exception instanceof HttpException && (int) $exception->statusCode === 404;

        $this->view->params['current_page'] = 'error';
        $this->view->params['mobileTitle'] = $is404 ? 'ไม่พบหน้า' : 'ข้อผิดพลาด';
        $this->view->params['mobileSubtitle'] = $is404 ? 'รหัส 404' : 'ระบบ';

        return true;
    }

    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;

        if ($exception !== null) {
            $name = $exception instanceof HttpException ? $exception->getName() : Yii::t('yii', 'Error');
            if (YII_DEBUG) {
                $message = $exception->getMessage();
            } elseif ($exception instanceof HttpException) {
                $message = $exception->getMessage();
            } else {
                $message = Yii::t('yii', 'An internal server error occurred.');
            }
        } else {
            $message = Yii::t('yii', 'An internal server error occurred.');
            $name = Yii::t('yii', 'Error');
            $exception = null;
        }

        $params = [
            'name' => $name,
            'message' => $message,
            'exception' => $exception,
        ];

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'content' => $this->renderAjax('error', $params),
            ];
        }

        return $this->render('error', $params);
    }
}
